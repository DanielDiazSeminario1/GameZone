<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\InventarioModel;
use App\Models\AreaModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Config\Database;
use Ramsey\Uuid\Uuid;

class InventarioController extends ResourceController
{
    protected $format = 'json';

    private InventarioModel $inventarioModel;
    private AreaModel $areaModel;
    protected $db;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->db = Database::connect();
        $this->inventarioModel = new InventarioModel();
        $this->areaModel = new AreaModel();
    }

    /**
     * 📋 Listar inventario con MÚLTIPLES FILTROS y PAGINACIÓN (Estilo Notificaciones)
     */
    public function index()
    {
        // --- 1. CONFIGURACIÓN DE PAGINACIÓN ---
        $size = max(1, (int) ($this->request->getGet('size') ?? 10));
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));

        // --- 2. CAPTURA DE VARIABLES (FILTROS) ---
        $uuid   = $this->request->getGet('uuid');    // Filtro por Activo específico
        $idArea = $this->request->getGet('id_area'); // Filtro por Área
        $nombre = $this->request->getGet('nombre');  // Búsqueda por nombre

        // --- 3. CONSTRUCCIÓN DE LA CONSULTA ---
        $query = $this->inventarioModel
            ->select('uuid')
            ->where('deleted_at', 0);

        // Aplicar filtros si existen
        if (!empty($uuid)) {
            $query->where('uuid', $uuid);
        }
        if (!empty($idArea)) {
            $query->where('id_area', $idArea);
        }
        if (!empty($nombre)) {
            $query->like('nombre', $nombre);
        }

        $query->orderBy('id', 'DESC'); // Ordenamos por el más reciente

        // --- 4. EJECUTAR CONSULTA PAGINADA ---
        $result = $query->paginate($size, 'inventario', $page);
        $pager  = $this->inventarioModel->pager;

        // --- 5. BUCLE DE PROCESAMIENTO (Hidratación) ---
        foreach ($result as &$item) {
            // Obtenemos el objeto completo con su relación (Area) usando tu modelo
            $fullData = $this->inventarioModel->findByUuid($item['uuid']);

            // Si falla la búsqueda, lo marcamos nulo para limpiarlo luego
            if (!$fullData) {
                $item = null;
                continue;
            }
            $item = $fullData;
        }

        // Limpiamos nulos y reindexamos el array
        $result = array_values(array_filter($result));

        // --- 6. RESPUESTA JSON (Estructura idéntica a tu ejemplo) ---
        return $this->respond([
            'data'  => $result,
            'pager' => [
                'currentPage' => $pager->getCurrentPage('inventario'),
                'totalPages'  => $pager->getPageCount('inventario'),
                'totalItems'  => $pager->getTotal('inventario'),
                'perPage'     => $size,
            ]
        ]);
    }

    /**
     * ➕ Crear un activo (POST)
     */
    public function create()
    {
        $data = $this->request->getJSON(true) ?? [];

        // Validaciones previas
        if (empty($data['id_area'])) {
            return $this->failValidationErrors('El id_area (UUID del área) es obligatorio.');
        }

        // Verificar que el Área existe
        $existeArea = $this->areaModel->where('uuid', $data['id_area'])->first();
        if (!$existeArea) {
            return $this->failValidationErrors('El Área proporcionada no existe.');
        }

        // Preparar datos
        $data['uuid'] = $data['uuid'] ?? Uuid::uuid4()->toString();
        // deleted_at se maneja por defecto en 0 en la BD o Modelo

        // Insertar
        if (!$this->inventarioModel->insert($data)) {
            return $this->failValidationErrors($this->inventarioModel->errors());
        }

        // Recuperar el creado para retornarlo completo
        $recienCreado = $this->inventarioModel->findByUuid($data['uuid']);

        return $this->respondCreated([
            'message' => 'Activo creado correctamente.',
            'data'    => $recienCreado,
        ]);
    }

    /**
     * 🔍 Ver detalle (GET /uuid)
     */
    public function show($uuid = null)
    {
        if (empty($uuid)) return $this->failValidationErrors('UUID necesario.');

        $item = $this->inventarioModel->findByUuid($uuid);
        
        if (!$item) {
            return $this->failNotFound('Activo no encontrado.');
        }

        return $this->respond($item);
    }

    /**
     * ✏️ Actualizar (PATCH /uuid)
     */
    public function update($uuid = null)
    {
        if (empty($uuid)) return $this->failValidationErrors('UUID necesario.');

        // Buscamos el ID interno para el update
        $itemRaw = $this->inventarioModel->where('uuid', $uuid)->where('deleted_at', 0)->first();
        
        if (!$itemRaw) {
            return $this->failNotFound('Activo no encontrado.');
        }

        $data = $this->request->getJSON(true) ?? [];
        
        // Limpiar campos protegidos
        unset($data['id'], $data['uuid'], $data['created_at']);

        // Si intentan cambiar de área, validamos que la nueva exista
        if (array_key_exists('id_area', $data)) {
            if (empty($data['id_area'])) {
                return $this->failValidationErrors('El área no puede quedar vacía.');
            }
            $existeArea = $this->areaModel->where('uuid', $data['id_area'])->first();
            if (!$existeArea) {
                return $this->failNotFound('El nuevo UUID de área no existe.');
            }
        }

        // Actualizar
        if (!$this->inventarioModel->update($itemRaw['id'], $data)) {
            return $this->failValidationErrors($this->inventarioModel->errors());
        }

        return $this->respond([
            'message' => 'Activo actualizado correctamente.',
            'data'    => $this->inventarioModel->findByUuid($uuid) // Retornamos el objeto actualizado
        ]);
    }

    /**
     * 🗑️ Eliminar (DELETE /uuid)
     */
    public function delete($uuid = null)
    {
        if (empty($uuid)) return $this->failValidationErrors('UUID necesario.');

        $itemRaw = $this->inventarioModel->where('uuid', $uuid)->where('deleted_at', 0)->first();

        if (!$itemRaw) {
            return $this->failNotFound('Activo no encontrado.');
        }

        // Borrado lógico
        $this->inventarioModel->update($itemRaw['id'], ['deleted_at' => 1]);

        return $this->respondDeleted(['message' => 'Activo eliminado correctamente.']);
    }
}
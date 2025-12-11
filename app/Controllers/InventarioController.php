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

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->inventarioModel = new InventarioModel();
        $this->areaModel = new AreaModel();
    }

    /**
     * GET: Listar inventario con filtros
     * Filtros disponibles: uuid (activo), id_area (área), nombre
     */
    public function index()
    {
        $size = max(1, (int) ($this->request->getGet("size") ?? 10));
        $page = max(1, (int) ($this->request->getGet("page") ?? 1));

        // --- 1. CAPTURAR FILTROS ---
        $uuid   = $this->request->getGet('uuid');    // Filtro por el UUID del activo
        $idArea = $this->request->getGet('id_area'); // Filtro por el UUID del área
       //$nombre = $this->request->getGet('nombre');  // Filtro por nombre (opcional)

        // Query Base
        $query = $this->inventarioModel->select('uuid');
        $query->where('deleted_at', 0); // Solo activos

        // --- 2. APLICAR FILTROS ---
        
        // Filtro por UUID del activo (búsqueda exacta)
        if (!empty($uuid)) {
            $query->where('uuid', $uuid);
        }

        // Filtro por UUID del Área (búsqueda exacta)
        if (!empty($idArea)) {
            $query->where('id_area', $idArea);
        }

        // Filtro por Nombre (búsqueda parcial)
        if (!empty($nombre)) {
            $query->like('nombre', $nombre);
        }

        $query->orderBy('id', 'DESC');

        // Ejecutamos paginación
        $result = $query->paginate($size, 'inventario', $page);

        // Hidratar datos (Traer la info completa + Área)
        foreach ($result as &$item) {
            $dataItem = $this->inventarioModel->findByUuid($item['uuid']);
            $item = $dataItem ? $dataItem : null;
        }
        $result = array_filter($result); // Limpiar nulos

        // Respuesta LIMPIA (Sin pager)
        $totalItems = $this->inventarioModel->pager->getTotal('inventario');
        $message = ($totalItems === 0) ? 'No se encontraron resultados con los filtros aplicados.' : 'OK';

        return $this->respond([
            'status' => 200,
            'message' => $message,
            'data' => array_values($result),
        ]);
    }

    /**
     * POST: Crear activo
     * Requiere 'id_area' (UUID) obligatorio.
     */
    public function create()
    {
        $data = $this->request->getJSON(true) ?? [];

        // 1. Generar UUID
        $data['uuid'] = $data['uuid'] ?? Uuid::uuid4()->toString();

        // 2. Validar que el Área exista (Integridad)
        if (!empty($data['id_area'])) {
            $existeArea = $this->areaModel->where('uuid', $data['id_area'])->first();
            if (!$existeArea) {
                return $this->failNotFound('El UUID del área no existe.');
            }
        } else {
            return $this->failValidationErrors('El campo id_area es obligatorio.');
        }

        // 3. Insertar
        if (!$this->inventarioModel->insert($data)) {
            return $this->failValidationErrors($this->inventarioModel->errors());
        }

        // 4. Responder
        return $this->respondCreated([
            'message' => 'Activo creado correctamente',
            'data' => $this->inventarioModel->findByUuid($data['uuid']),
        ]);
    }

    /**
     * GET: Ver un activo por UUID
     */
    public function show($uuid = null)
    {
        if (empty($uuid)) return $this->failValidationErrors('UUID necesario.');

        $item = $this->inventarioModel->findByUuid($uuid);

        if (!$item) {
            return $this->failNotFound('Activo no encontrado');
        }

        return $this->respond($item);
    }

    /**
     * PUT/PATCH: Actualizar activo
     */
    public function update($uuid = null)
    {
        if (empty($uuid)) return $this->failValidationErrors('UUID necesario.');

        // Buscamos ID interno para el update
        $itemRaw = $this->inventarioModel->where('uuid', $uuid)->where('deleted_at', 0)->first();

        if (!$itemRaw) {
            return $this->failNotFound('Activo no encontrado');
        }

        $id = $itemRaw['id'];
        $data = $this->request->getJSON(true) ?? [];

        // Protección de campos
        unset($data['uuid'], $data['id'], $data['created_at']);

        // Validación de Área si intentan cambiarla
        if (array_key_exists('id_area', $data)) {
            if (empty($data['id_area'])) {
                return $this->failValidationErrors('El área no puede quedar vacía.');
            }
            $existeArea = $this->areaModel->where('uuid', $data['id_area'])->first();
            if (!$existeArea) {
                return $this->failNotFound('El nuevo UUID de área no existe.');
            }
        }

        if (!$this->inventarioModel->update($id, $data)) {
            return $this->failValidationErrors($this->inventarioModel->errors());
        }

        return $this->respond([
            'message' => 'Activo actualizado correctamente',
            'data' => $this->inventarioModel->findByUuid($uuid),
        ]);
    }

    /**
     * DELETE: Borrado lógico (0 -> 1)
     */
    public function delete($uuid = null)
    {
        if (empty($uuid)) return $this->failValidationErrors('UUID necesario.');

        $itemRaw = $this->inventarioModel->where('uuid', $uuid)->where('deleted_at', 0)->first();

        if (!$itemRaw) {
            return $this->failNotFound('Activo no encontrado');
        }

        // Actualizamos deleted_at a 1
        $this->inventarioModel->update($itemRaw['id'], ['deleted_at' => 1]);

        return $this->respondDeleted([
            'message' => 'Activo eliminado correctamente',
        ]);
    }
}
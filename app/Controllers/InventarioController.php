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
     * 📋 Listar inventario (GET)
     * Estructura y Paginación IDÉNTICA a AreaController
     */
    public function index()
    {
        // 1. Configuración Paginación
        $size = max(1, (int) ($this->request->getGet('size') ?? 10));
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $offset = ($page - 1) * $size;

        // 2. Filtros
        $uuid   = $this->request->getGet('uuid');
        $idArea = $this->request->getGet('id_area');
        $nombre = $this->request->getGet('nombre');

        // 3. Query Builder Manual (Para control total y evitar errores de pager)
        $builder = $this->inventarioModel->builder();
        $builder->select('uuid')->where('deleted_at', 0);

        if (!empty($uuid))   $builder->where('uuid', $uuid);
        if (!empty($idArea)) $builder->where('id_area', $idArea);
        if (!empty($nombre)) $builder->like('nombre', $nombre);

        // 4. Obtener Totales (Para calcular totalPages y totalItems)
        $countBuilder = clone $builder;
        $totalItems = $countBuilder->countAllResults();

        // 5. Obtener Datos de la página actual
        $builder->orderBy('id', 'DESC');
        $result = $builder->get($size, $offset)->getResultArray();

        // 6. Hidratar (Llenar info completa de Área)
        foreach ($result as &$item) {
            $fullData = $this->inventarioModel->findByUuid($item['uuid']);
            $item = $fullData ? $fullData : null;
        }
        $result = array_values(array_filter($result));

        // 7. Calcular Paginación
        $totalPages = ($totalItems > 0) ? (int) ceil($totalItems / $size) : 1;

        // 8. RESPUESTA JSON (Estructura Estándar Igual a Área)
        return $this->respond([
            'status'  => 200,
            'message' => ($totalItems === 0) ? 'No se encontraron resultados' : 'OK',
            'data'    => $result,
            
            // 👇 ESTE BLOQUE AHORA ES IDÉNTICO AL DE ÁREA 👇
            'pager'   => [
                'currentPage' => $page,
                'totalPages'  => $totalPages,
                'totalItems'  => $totalItems,
                'perPage'     => $size,
                'next'        => ($page < $totalPages) ? $page + 1 : null,
                'previous'    => ($page > 1) ? $page - 1 : null,
            ]
        ]);
    }

    /**
     * ➕ Crear (POST)
     */
    public function create()
    {
        $data = $this->request->getJSON(true) ?? [];
        if (empty($data['id_area'])) return $this->failValidationErrors('id_area obligatorio.');

        $existeArea = $this->areaModel->where('uuid', $data['id_area'])->first();
        if (!$existeArea) return $this->failValidationErrors('Área no existe.');

        $data['uuid'] = $data['uuid'] ?? Uuid::uuid4()->toString();

        if (!$this->inventarioModel->insert($data)) {
            return $this->failValidationErrors($this->inventarioModel->errors());
        }

        return $this->respondCreated([
            'message' => 'Creado correctamente',
            'data'    => $this->inventarioModel->findByUuid($data['uuid'])
        ]);
    }

    /**
     * 🔍 Ver detalle (GET /uuid)
     */
    public function show($uuid = null)
    {
        if (empty($uuid)) return $this->failValidationErrors('UUID necesario.');
        $item = $this->inventarioModel->findByUuid($uuid);
        return $item ? $this->respond($item) : $this->failNotFound('No encontrado');
    }

    /**
     * ✏️ Actualizar (PATCH /uuid)
     */
    public function update($uuid = null)
    {
        if (empty($uuid)) return $this->failValidationErrors('UUID necesario.');
        
        $itemRaw = $this->inventarioModel->where('uuid', $uuid)->where('deleted_at', 0)->first();
        if (!$itemRaw) return $this->failNotFound('No encontrado');

        $data = $this->request->getJSON(true) ?? [];
        unset($data['id'], $data['uuid'], $data['created_at']);

        if (isset($data['id_area']) && !empty($data['id_area'])) {
            if (!$this->areaModel->where('uuid', $data['id_area'])->first()) {
                return $this->failNotFound('Nueva área no existe.');
            }
        }

        if (!$this->inventarioModel->update($itemRaw['id'], $data)) {
            return $this->failValidationErrors($this->inventarioModel->errors());
        }

        return $this->respond([
            'message' => 'Actualizado',
            'data' => $this->inventarioModel->findByUuid($uuid)
        ]);
    }

    /**
     * 🗑️ Eliminar (DELETE /uuid)
     */
    public function delete($uuid = null)
    {
        if (empty($uuid)) return $this->failValidationErrors('UUID necesario.');
        
        $itemRaw = $this->inventarioModel->where('uuid', $uuid)->where('deleted_at', 0)->first();
        if (!$itemRaw) return $this->failNotFound('No encontrado');

        $this->inventarioModel->update($itemRaw['id'], ['deleted_at' => 1]);

        return $this->respondDeleted(['message' => 'Eliminado correctamente']);
    }
}
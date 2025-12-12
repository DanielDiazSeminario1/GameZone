<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\AreaModel;
use Ramsey\Uuid\Uuid;
use Config\Database;

class AreaController extends ResourceController
{
    protected $format = 'json';

    private AreaModel $areaModel;
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->areaModel = new AreaModel();
    }

    /**
     * GET /api/v1/area
     * Soporta filtros: ?uuid=... &nombre=... &page=1 &size=10
     */
    public function index()
    {
        // 1. Configuración de Paginación
        $size = max(1, (int) ($this->request->getGet("size") ?? 10));
        $page = max(1, (int) ($this->request->getGet("page") ?? 1));

        // 2. Capturar Filtros
        $uuid   = $this->request->getGet('uuid');   // Para búsqueda exacta
        $nombre = $this->request->getGet('nombre'); // Para búsqueda por nombre

        // 3. Construir Query Base
        $query = $this->areaModel->select('uuid, nombre, created_at, updated_at');
        
        // Filtro base: Solo áreas activas
        $query->where('deleted_at', 0);

        // --- APLICAR FILTROS OPCIONALES ---
        if (!empty($uuid)) {
            $query->where('uuid', $uuid);
        }

        if (!empty($nombre)) {
            $query->like('nombre', $nombre);
        }

        $query->orderBy('id', 'DESC');

        // 4. Ejecutar con Paginación
        $result = $query->paginate($size, 'area', $page);

        // 5. Preparar Pager (Metadatos de paginación)
        $pager = $this->areaModel->pager;
        $totalItems = $pager->getTotal('area');
        
        $message = ($totalItems === 0) ? 'No se encontraron áreas con esos criterios.' : 'OK';

        // 6. Respuesta Final (Con Pager incluido)
        return $this->respond([
            'status'  => 200,
            'message' => $message,
            'data'    => $result,
            
            // AQUÍ AGREGAMOS LA PAGINACIÓN
            'pager' => [
                'currentPage' => $pager->getCurrentPage('area'),
                'totalPages'  => $pager->getPageCount('area'),
                'totalItems'  => $totalItems,
                'perPage'     => $size,
                'next'        => $pager->getNextPageURI('area'),
                'previous'    => $pager->getPreviousPageURI('area'),
            ],
        ]);
    }

    /**
     * POST /api/v1/area
     */
    public function create()
    {
        $data = $this->request->getJSON(true) ?? [];

        $data['uuid'] = $data['uuid'] ?? Uuid::uuid4()->toString();
        $data['deleted_at'] = 0;

        if (!$this->areaModel->insert($data)) {
            return $this->failValidationErrors($this->areaModel->errors());
        }

        return $this->respondCreated([
            'message' => 'Área creada correctamente.',
            'data' => $this->areaModel->findByUuid($data['uuid'])
        ]);
    }

    /**
     * GET /api/v1/area/{uuid}
     */
    public function show($uuid = null)
    {
        if (empty($uuid)) {
            return $this->failValidationErrors('UUID necesario.');
        }

        $row = $this->areaModel->findByUuid($uuid);

        if (!$row) {
            return $this->failNotFound('Área no encontrada o inactiva.');
        }

        return $this->respond($row, 200);
    }

    /**
     * PATCH /api/v1/area/{uuid}
     * PUT   /api/v1/area/{uuid}
     */
    public function update($uuid = null)
    {
        if (empty($uuid)) {
            return $this->failValidationErrors('UUID necesario.');
        }

        $row = $this->areaModel->findByUuid($uuid);
        
        if (!$row) {
            return $this->failNotFound('Área no encontrada o inactiva.');
        }

        $data = $this->request->getJSON(true) ?? [];
        
        unset($data['id'], $data['uuid'], $data['deleted_at'], $data['created_at']);

        // Buscamos ID interno para actualizar
        $areaRaw = $this->areaModel->where('uuid', $uuid)->first(); 

        if (!$this->areaModel->update($areaRaw['id'], $data)) {
            return $this->failValidationErrors($this->areaModel->errors());
        }

        return $this->respond([
            'message' => 'Área actualizada correctamente.',
            'data' => $this->areaModel->findByUuid($uuid)
        ], 200);
    }

    /**
     * DELETE /api/v1/area/{uuid}
     */
    public function delete($uuid = null)
    {
        if (empty($uuid)) {
            return $this->failValidationErrors('UUID necesario.');
        }

        $row = $this->areaModel->where('uuid', $uuid)->where('deleted_at', 0)->first();
        
        if (!$row) {
            return $this->failNotFound('Área no encontrada o inactiva.');
        }

        if ($this->areaModel->update($row['id'], ['deleted_at' => 1])) {
            return $this->respondDeleted([
                'message' => 'Área eliminada correctamente.',       
                'uuid' => $uuid
            ]);
        }

        return $this->failServerError('Error al eliminar el área.');
    }
}
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
        $nombre = $this->request->getGet('nombre'); // Para búsqueda por nombre (ej: "Sist")

        // 3. Construir Query Base
        // Seleccionamos solo lo que nos interesa mostrar
        $query = $this->areaModel->select('uuid, nombre, created_at, updated_at');
        
        // Filtro base: Solo áreas activas (deleted_at = 0)
        $query->where('deleted_at', 0);

        // --- APLICAR FILTROS OPCIONALES ---
        
        // Filtro por UUID (Exacto)
        if (!empty($uuid)) {
            $query->where('uuid', $uuid);
        }

        // Filtro por Nombre (Parcial - LIKE)
        if (!empty($nombre)) {
            $query->like('nombre', $nombre);
        }

        $query->orderBy('id', 'DESC');

        // 4. Ejecutar con Paginación
        $result = $query->paginate($size, 'area', $page);

        // 5. Respuesta
        $totalItems = $this->areaModel->pager->getTotal('area');
        $message = ($totalItems === 0) ? 'No se encontraron áreas con esos criterios.' : 'OK';

        return $this->respond([
            'status'  => 200,
            'message' => $message,
            'data'    => $result,
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

        // Recuperamos por UUID para mostrarlo limpio
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

        $row = $this->areaModel->findByUuid($uuid); // Usamos findByUuid que ya filtra deleted_at=0
        
        if (!$row) {
            return $this->failNotFound('Área no encontrada o inactiva.');
        }

        $data = $this->request->getJSON(true) ?? [];
        
        // Protección: No permitimos cambiar ID, UUID ni fecha de creación
        unset($data['id'], $data['uuid'], $data['deleted_at'], $data['created_at']);

        // Necesitamos el ID interno para el update
        // Como $row viene de findByUuid (que limpia el ID), hacemos una búsqueda rápida raw si es necesario,
        // o ajustamos el modelo. Asumiendo que findByUuid limpia el ID, hacemos esto:
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
     * Borrado lógico (deleted_at = 1)
     */
    public function delete($uuid = null)
    {
        if (empty($uuid)) {
            return $this->failValidationErrors('UUID necesario.');
        }

        // Buscamos el registro raw para tener el ID
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
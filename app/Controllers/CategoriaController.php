<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CategoriaModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Config\Database;
use Ramsey\Uuid\Uuid;

class CategoriaController extends ResourceController
{
    protected $format = 'json';

    private CategoriaModel $categoriaModel;
    protected $db;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->db = Database::connect();
        $this->categoriaModel = new CategoriaModel();
    }

    /**
     * 📋 Listar (GET) - Estructura Idéntica a Inventario
     */
    public function index()
    {
        // 1. Configuración Paginación
        $size = max(1, (int) ($this->request->getGet('size') ?? 10));
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $offset = ($page - 1) * $size;

        // 2. Filtros
        $uuid   = $this->request->getGet('uuid');
        $nombre = $this->request->getGet('nombre');

        // 3. Query Builder Manual
        $builder = $this->categoriaModel->builder();
        $builder->select('uuid')->where('deleted_at', 0);

        if (!empty($uuid))   $builder->where('uuid', $uuid);
        if (!empty($nombre)) $builder->like('nombre', $nombre);

        // 4. Totales
        $countBuilder = clone $builder;
        $totalItems = $countBuilder->countAllResults();

        // 5. Datos
        $builder->orderBy('id', 'DESC');
        $result = $builder->get($size, $offset)->getResultArray();

        // 6. Hidratar
        foreach ($result as &$item) {
            $fullData = $this->categoriaModel->findByUuid($item['uuid']);
            $item = $fullData ? $fullData : null;
        }
        $result = array_values(array_filter($result));

        // 7. Pager
        $totalPages = ($totalItems > 0) ? (int) ceil($totalItems / $size) : 1;

        // 8. Respuesta
        return $this->respond([
            'status'  => 200,
            'message' => ($totalItems === 0) ? 'No se encontraron resultados' : 'OK',
            'data'    => $result,
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

        //ver si ya existe
        $existe = $this->categoriaModel
            ->select('nombre')
            ->where('nombre', $data['nombre']);

        if ($existe) return $this->failValidationErrors('Esa categoria ya esta registrada.');

        if (!$this->categoriaModel->insert($data)) {
            return $this->failValidationErrors($this->categoriaModel->errors());
        }

        return $this->respondCreated([
            'message' => 'Creado correctamente',
            'data'    => $this->categoriaModel->find($this->categoriaModel->getInsertID())
        ]);
    }

    /**
     * 🔍 Ver detalle (GET /uuid)
     */
    public function show($uuid = null)
    {
        if (empty($uuid)) return $this->failValidationErrors('UUID necesario.');
        $item = $this->categoriaModel->findByUuid($uuid);
        return $item ? $this->respond($item) : $this->failNotFound('No encontrado');
    }

    /**
     * ✏️ Actualizar (PATCH /uuid)
     */
    public function update($uuid = null)
    {
        if (empty($uuid)) return $this->failValidationErrors('UUID necesario.');

        $itemRaw = $this->categoriaModel->where('uuid', $uuid)->where('deleted_at', 0)->first();
        if (!$itemRaw) return $this->failNotFound('No encontrado');

        $data = $this->request->getJSON(true) ?? [];
        unset($data['id'], $data['uuid'], $data['created_at']);

        if (!$this->categoriaModel->update($itemRaw['id'], $data)) {
            return $this->failValidationErrors($this->categoriaModel->errors());
        }

        return $this->respond([
            'message' => 'Actualizado',
            'data'    => $this->categoriaModel->findByUuid($uuid)
        ]);
    }

    /**
     * 🗑️ Eliminar (DELETE /uuid)
     */
    public function delete($uuid = null)
    {
        if (empty($uuid)) return $this->failValidationErrors('UUID necesario.');

        $itemRaw = $this->categoriaModel->where('uuid', $uuid)->where('deleted_at', 0)->first();
        if (!$itemRaw) return $this->failNotFound('No encontrado');

        $this->categoriaModel->update($itemRaw['id'], ['deleted_at' => 1]);

        return $this->respondDeleted(['message' => 'Eliminado correctamente']);
    }
}

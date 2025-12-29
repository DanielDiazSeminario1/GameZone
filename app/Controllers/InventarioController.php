<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\InventarioModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Config\Database;

class InventarioController extends ResourceController
{
    protected $format = 'json';
    private InventarioModel $inventarioModel;
    protected $db;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->db = Database::connect();
        $this->inventarioModel = new InventarioModel();
    }

    /**
     * 📋 Listar (GET) - Filtro por SKU incluido
     */
    public function index()
    {
        $size = max(1, (int) ($this->request->getGet('size') ?? 10));
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $offset = ($page - 1) * $size;

        // Filtros (Se eliminaron id_area e id_categoria)
        $sku         = $this->request->getGet('sku'); // <--- Filtro por SKU (Llave Primaria)
        $propietario = $this->request->getGet('propietario');

        $builder = $this->inventarioModel->builder();
        $builder->select('sku')->where('deleted_at', 0);

        // Aplicar Filtros
        if (!empty($sku))         $builder->where('sku', $sku);
        if (!empty($propietario)) $builder->like('propietario', $propietario);

        $countBuilder = clone $builder;
        $totalItems = $countBuilder->countAllResults();

        $builder->orderBy('sku', 'ASC'); 
        $result = $builder->get($size, $offset)->getResultArray();

        // Hidratar (Buscamos por SKU)
        foreach ($result as &$item) {
            $fullData = $this->inventarioModel->find($item['sku']); 
            $item = $fullData ? $fullData : null;
        }
        $result = array_values(array_filter($result));

        return $this->respond([
            'status'  => 200,
            'message' => ($totalItems === 0) ? 'No se encontraron resultados' : 'OK',
            'data'    => $result,
            'pager'   => [
                'currentPage' => $page,
                'totalPages'  => (int) ceil($totalItems / $size),
                'totalItems'  => $totalItems,
            ]
        ]);
    }

    /**
     * ➕ Crear (POST) - Validación de SKU Manual y No Repetido
     */
    public function create()
    {
        $data = $this->request->getJSON(true) ?? [];
        
        // 1. Validar que el SKU manual esté presente
        if (empty($data['sku'])) return $this->failValidationErrors('El SKU es obligatorio.');

        // 2. Impedir que el elemento se repita (Kevin's check)
        if ($this->inventarioModel->find($data['sku'])) {
            return $this->failResourceExists('El SKU ya existe en el inventario.');
        }

        // Se eliminaron las validaciones de integridad de Área y Categoría

        if (!$this->inventarioModel->insert($data)) {
            return $this->failValidationErrors($this->inventarioModel->errors());
        }

        return $this->respondCreated([
            'message' => 'Creado correctamente',
            'data'    => $this->inventarioModel->find($data['sku'])
        ]);
    }

    /**
     * 🔍 Ver detalle (GET /sku)
     */
    public function show($sku = null)
    {
        if (empty($sku)) return $this->failValidationErrors('SKU necesario.');
        
        $item = $this->inventarioModel->where('sku', $sku)
                                      ->where('deleted_at', 0)
                                      ->first();

        return $item ? $this->respond($item) : $this->failNotFound('SKU no encontrado');
    }

    /**
     * ✏️ Actualizar (PATCH /sku)
     */
    public function update($sku = null)
    {
        if (empty($sku)) return $this->failValidationErrors('SKU necesario.');
        
        $itemRaw = $this->inventarioModel->where('sku', $sku)->where('deleted_at', 0)->first();
        if (!$itemRaw) return $this->failNotFound('No encontrado');

        $data = $this->request->getJSON(true) ?? [];
        
        // Protegemos la llave primaria y campos críticos
        unset($data['sku'], $data['uuid'], $data['created_at']);

        // Se eliminó la validación de cambio de Área/Categoría

        if (!$this->inventarioModel->update($sku, $data)) {
            return $this->failValidationErrors($this->inventarioModel->errors());
        }

        return $this->respond([
            'message' => 'Actualizado correctamente',
            'data'    => $this->inventarioModel->find($sku)
        ]);
    }

    /**
     * 🗑️ Eliminar (DELETE /sku)
     */
    public function delete($sku = null)
    {
        if (empty($sku)) return $this->failValidationErrors('SKU necesario.');
        
        $itemRaw = $this->inventarioModel->where('sku', $sku)->where('deleted_at', 0)->first();
        if (!$itemRaw) return $this->failNotFound('No encontrado');

        // Soft-delete usando el SKU como referencia
        $this->inventarioModel->update($sku, ['deleted_at' => 1]);

        return $this->respondDeleted(['message' => 'Eliminado correctamente']);
    }
}
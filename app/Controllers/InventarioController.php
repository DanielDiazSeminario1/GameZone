<?php

namespace App\Controllers;

use App\Models\AreaModel;
use App\Models\CategoriaModel;
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
    private AreaModel $areaModel;
    private CategoriaModel $categoriaModel;
    protected $db;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->db = Database::connect();
        $this->inventarioModel = new InventarioModel();
        $this->areaModel = new AreaModel();
        $this->categoriaModel = new CategoriaModel();
    }

    /**
     * 📋 Listar (GET) - Ahora con hidratación de objetos
     */
    public function index()
    {
        $size = max(1, (int) ($this->request->getGet('size') ?? 10));
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $offset = ($page - 1) * $size;
        //filtros
        $sku         = $this->request->getGet('sku');
        $propietario = $this->request->getGet('propietario');
        $idarea = $this->request->getGet('id_area'); //agregar filtro por area
        $idcategoria = $this->request->getGet('id_categoria'); //agregar filtro por categoria

        $builder = $this->inventarioModel->builder();
        $builder->select('sku')->where('deleted_at', 0);

        if (!empty($sku))         $builder->where('sku', $sku);
        if (!empty($propietario)) $builder->like('propietario', $propietario);

        //filtros de area y categoria
        if (!empty($idarea)) $builder->where('id_area', $idarea);
        if (!empty($idcategoria)) $builder->where('id_categoria', $idcategoria);
        //clonamos para contar el total antes de paginar    
        $countBuilder = clone $builder;
        $totalItems = $countBuilder->countAllResults();

        $builder->orderBy('sku', 'ASC'); 
        $result = $builder->get($size, $offset)->getResultArray();

        // CORRECCIÓN: Hidratamos cada item usando findBySku para obtener los objetos area/categoria
        foreach ($result as &$item) {
            $item = $this->inventarioModel->findBySku($item['sku']); 
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
     * ➕ Crear (POST)
     */
    public function create()
    {
        $data = $this->request->getJSON(true) ?? [];

        if (empty($data['id_area'])) return $this->failValidationErrors('id_area obligatorio.');
        if (empty($data['id_categoria'])) return $this->failValidationErrors('id_categoria obligatorio.');

        if (!$this->areaModel->where('uuid', $data['id_area'])->first()) {
            return $this->failValidationErrors('Área no existe.');
        }

        if (!$this->categoriaModel->where('uuid', $data['id_categoria'])->first()) {
            return $this->failValidationErrors('Categoría no existe.');
        }

        // CORRECCIÓN: La lógica estaba invertida. Si existe (first != null), error.
        if ($this->inventarioModel->where('sku', $data['sku'])->where('deleted_at', 0)->first()) {
            return $this->failValidationErrors('El SKU ingresado ya existe.');
        }

        if (!$this->inventarioModel->insert($data)) {
            return $this->failValidationErrors($this->inventarioModel->errors());
        }

        return $this->respondCreated([
            'message' => 'Creado correctamente',
            'data'    => $this->inventarioModel->findBySku($data['sku'])
        ]);
    }

    /**
     * 🔍 Ver detalle (GET /sku)
     */
    public function show($sku = null)
    {
        if (empty($sku)) return $this->failValidationErrors('SKU necesario.');
        
        // CORRECCIÓN: Usamos findBySku que ya devuelve el objeto hidratado
        $item = $this->inventarioModel->findBySku($sku);
        return $item ? $this->respond($item) : $this->failNotFound('No encontrado');
    }

    /**
     * Endpoint alternativo para buscar por SKU
     */
    public function showsku($sku = null)
    {
        return $this->show($sku);
    }

    /**
     * ✏️ Actualizar (PATCH /sku)
     */
    public function update($sku = null)
    {
        if (empty($sku)) return $this->failValidationErrors('SKU necesario.');

        // CORRECCIÓN: Buscamos por SKU, no por UUID inexistente
        $itemRaw = $this->inventarioModel->where('sku', $sku)->where('deleted_at', 0)->first();
        if (!$itemRaw) return $this->failNotFound('No encontrado');

        $data = $this->request->getJSON(true) ?? [];
        unset($data['sku'], $data['uuid'], $data['created_at']);

        if (!$this->inventarioModel->update($sku, $data)) {
            return $this->failValidationErrors($this->inventarioModel->errors());
        }

        return $this->respond([
            'message' => 'Actualizado correctamente',
            'data'    => $this->inventarioModel->findBySku($sku)
        ]);
    }

    /**
     * 🗑️ Eliminar (DELETE /sku)
     */
    public function delete($sku = null)
    {
        if (empty($sku)) return $this->failValidationErrors('SKU necesario.');

        // CORRECCIÓN: Buscamos por SKU para verificar existencia
        $itemRaw = $this->inventarioModel->where('sku', $sku)->where('deleted_at', 0)->first();
        if (!$itemRaw) return $this->failNotFound('No encontrado');

        $this->inventarioModel->update($sku, ['deleted_at' => 1]);

        return $this->respondDeleted(['message' => 'Eliminado correctamente']);
    }
}
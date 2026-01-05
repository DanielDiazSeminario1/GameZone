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

    // =========================================================================
    // 1. SECCIÓN: LECTURA (READ)
    // =========================================================================

    /**
     * 📋 Listar con Filtros y Paginación
     * GET /inventario
     */
    public function index()
    {
        $size = max(1, (int) ($this->request->getGet('size') ?? 10));
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $offset = ($page - 1) * $size;

        $sku         = $this->request->getGet('sku');
        $propietario = $this->request->getGet('propietario');
        $idarea      = $this->request->getGet('id_area'); 
        $idcategoria = $this->request->getGet('id_categoria'); 

        $builder = $this->inventarioModel->builder();
        $builder->select('sku')->where('deleted_at', 0);

        if (!empty($sku))         $builder->where('sku', $sku);
        if (!empty($propietario)) $builder->like('propietario', $propietario);
        if (!empty($idarea))      $builder->where('id_area', $idarea);
        if (!empty($idcategoria)) $builder->where('id_categoria', $idcategoria);

        $countBuilder = clone $builder;
        $totalItems = $countBuilder->countAllResults(false);

        $builder->orderBy('sku', 'ASC'); 
        $result = $builder->get($size, $offset)->getResultArray();

        // Hidratación de objetos (Area y Categoría)
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
     * 🔍 Detalle por UUID
     * GET /inventario/{uuid}
     */
    public function show($uuid = null)
    {
        if (empty($uuid)) return $this->failValidationErrors('UUID necesario.');
        $item = $this->inventarioModel->findByUuid($uuid);
        return $item ? $this->respond($item) : $this->failNotFound('No encontrado');
    }

    /**
     * 🔍 Detalle por SKU
     * GET /inventario/sku/{sku}
     */
    public function showsku($sku = null)
    {
        if (empty($sku)) return $this->failValidationErrors('SKU necesario');
        $item = $this->inventarioModel->findBySku($sku);
        return $item ? $this->respond($item) : $this->failNotFound('No encontrado');
    }

    // =========================================================================
    // 2. SECCIÓN: ESCRITURA (CREATE)
    // =========================================================================

    /**
     * ➕ Crear nuevo registro
     * POST /inventario
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

    // =========================================================================
    // 3. SECCIÓN: ACTUALIZACIÓN (UPDATE)
    // =========================================================================

    /**
     * ✏️ Modificar datos existentes
     * PATCH/PUT /inventario/{sku}
     */
    public function update($sku = null)
    {
        if (empty($sku)) return $this->failValidationErrors('SKU necesario.');

        $itemRaw = $this->inventarioModel->where('sku', $sku)->where('deleted_at', 0)->first();
        if (!$itemRaw) return $this->failNotFound('No encontrado');

        $data = $this->request->getJSON(true) ?? [];
        
        // Protegemos campos que no deben cambiarse manualmente
        unset($data['sku'], $data['uuid'], $data['created_at']);

        if (!$this->inventarioModel->update($sku, $data)) {
            return $this->failValidationErrors($this->inventarioModel->errors());
        }

        return $this->respond([
            'message' => 'Actualizado correctamente',
            'data'    => $this->inventarioModel->findBySku($sku)
        ]);
    }

    // =========================================================================
    // 4. SECCIÓN: ELIMINACIÓN (DELETE)
    // =========================================================================

    /**
     * 🗑️ Borrado Lógico
     * DELETE /inventario/{sku}
     */
    public function delete($uuid = null)
    {
        if (empty($uuid)) return $this->failValidationErrors('UUID necesario.');

        $itemRaw = $this->inventarioModel->where('uuid', $uuid)->where('deleted_at', 0)->first();
        if (!$itemRaw) return $this->failNotFound('No encontrado');

        // --- INICIO CORRECCIÓN TÉCNICA ---
        // Se usa builder()->update() para evitar que las reglas de validación 
        // del modelo (como SKU único o requerido) bloqueen el borrado lógico.
        $this->inventarioModel->builder()
            ->where('uuid', $uuid)
            ->update(['deleted_at' => 1]);
        // --- FIN CORRECCIÓN TÉCNICA ---

        return $this->respondDeleted(['message' => 'Eliminado correctamente']);
    }
}
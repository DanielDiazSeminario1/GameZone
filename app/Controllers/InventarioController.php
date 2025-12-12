<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\InventarioModel;
use App\Models\AreaModel;
use App\Models\CategoriaModel; // <--- Importamos el Modelo de Categoría
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
    private CategoriaModel $categoriaModel; // <--- Propiedad nueva
    protected $db;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->db = Database::connect();
        $this->inventarioModel = new InventarioModel();
        $this->areaModel = new AreaModel();
        $this->categoriaModel = new CategoriaModel(); // <--- Inicializamos
    }

    /**
     * 📋 Listar (GET) - Filtros Actualizados (Sin Nombre, Con Categoría)
     */
    public function index()
    {
        // 1. Configuración Paginación
        $size = max(1, (int) ($this->request->getGet('size') ?? 10));
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $offset = ($page - 1) * $size;

        // 2. Filtros
        $uuid        = $this->request->getGet('uuid');
        $idArea      = $this->request->getGet('id_area');
        $idCategoria = $this->request->getGet('id_categoria'); // <--- Nuevo Filtro
        $sku         = $this->request->getGet('sku');
        $propietario = $this->request->getGet('propietario');
        // NOTA: 'nombre' ya fue eliminado

        // 3. Query Builder Manual
        $builder = $this->inventarioModel->builder();
        $builder->select('uuid')->where('deleted_at', 0);

        // Aplicar Filtros
        if (!empty($uuid))        $builder->where('uuid', $uuid);
        if (!empty($idArea))      $builder->where('id_area', $idArea);
        if (!empty($idCategoria)) $builder->where('id_categoria', $idCategoria); // <--- Aplicamos
        if (!empty($sku))         $builder->where('sku', $sku);
        if (!empty($propietario)) $builder->like('propietario', $propietario);

        // 4. Totales
        $countBuilder = clone $builder;
        $totalItems = $countBuilder->countAllResults();

        // 5. Datos
        $builder->orderBy('id', 'DESC');
        $result = $builder->get($size, $offset)->getResultArray();

        // 6. Hidratar (El modelo se encarga de traer Area y Categoria)
        foreach ($result as &$item) {
            $fullData = $this->inventarioModel->findByUuid($item['uuid']);
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
        
        // Validaciones Manuales de Integridad
        if (empty($data['id_area'])) return $this->failValidationErrors('id_area obligatorio.');
        if (empty($data['id_categoria'])) return $this->failValidationErrors('id_categoria obligatorio.'); // <--- Validar input

        // Verificar existencia de Área
        if (!$this->areaModel->where('uuid', $data['id_area'])->first()) {
            return $this->failValidationErrors('Área no existe.');
        }

        // Verificar existencia de Categoría
        if (!$this->categoriaModel->where('uuid', $data['id_categoria'])->first()) {
            return $this->failValidationErrors('Categoría no existe.');
        }

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
        unset($data['nombre']); // Nos aseguramos de limpiar nombre si lo envían

        // Validar cambio de Área
        if (isset($data['id_area']) && !empty($data['id_area'])) {
            if (!$this->areaModel->where('uuid', $data['id_area'])->first()) {
                return $this->failNotFound('Nueva área no existe.');
            }
        }

        // Validar cambio de Categoría
        if (isset($data['id_categoria']) && !empty($data['id_categoria'])) {
            if (!$this->categoriaModel->where('uuid', $data['id_categoria'])->first()) {
                return $this->failNotFound('Nueva categoría no existe.');
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

        // La base de datos podría bloquear esto si hay dependencias, 
        // pero como es soft-delete (deleted_at=1), no habrá problema con las FK.
        $this->inventarioModel->update($itemRaw['id'], ['deleted_at' => 1]);

        return $this->respondDeleted(['message' => 'Eliminado correctamente']);
    }
}
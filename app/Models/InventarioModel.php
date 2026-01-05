<?php

namespace App\Models;

use CodeIgniter\Model;
use Ramsey\Uuid\Uuid;

class InventarioModel extends Model
{
    // =========================================================================
    // 1. CONFIGURACIÓN DE TABLA Y CAMPOS
    // =========================================================================
    protected $table            = 'inventario';
    protected $primaryKey       = 'sku';
    protected $useAutoIncrement = false; 
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    /**
     * Columnas permitidas para operaciones masivas.
     * Incluye campos de auditoría y relaciones.
     */
    public $allowedFields = [
        'uuid',
        'sku',
        'id_area',      
        'id_categoria', 
        'propietario',
        'serie',
        'descripcion',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // =========================================================================
    // 2. REGLAS DE VALIDACIÓN
    // =========================================================================
    protected $validationRules = [
        'sku'          => 'required|is_unique[inventario.sku,sku,sku]|max_length[50]',
        'id_area'      => 'required',
        'id_categoria' => 'required',
    ];

    protected $validationMessages = [
        'sku' => [
            'required'  => 'El SKU es obligatorio.',
            'is_unique' => 'Este SKU ya existe.'
        ]
    ];

    // =========================================================================
    // 3. EVENTOS (CALLBACKS)
    // =========================================================================
    protected $beforeInsert = ['generateUUID'];

    /**
     * Genera un UUID v4 automáticamente antes de insertar un registro.
     */
    protected function generateUUID(array $data): array
    {
        if (!isset($data['data']['uuid'])) {
            $data['data']['uuid'] = Uuid::uuid4()->toString();
        }
        return $data;
    }

    // =========================================================================
    // 4. MÉTODOS DE BÚSQUEDA E HIDRATACIÓN (READ)
    // =========================================================================

    /**
     * 🔍 Buscar por SKU
     * Retorna el registro hidratado con objetos de Area y Categoría.
     */
    public function findBySku(string $sku): ?array
    {
        $data = $this->where('sku', $sku)->where('deleted_at', 0)->first();
        if (!$data) return null;

        return $this->findByUuid($data['uuid']);
    }

    /**
     * 🔍 Buscar por UUID e Hidratar Objetos
     * Transforma los IDs planos en objetos legibles para el JSON.
     */
    public function findByUuid(string $uuid): ?array
    {
        $data = $this->where('uuid', $uuid)->where('deleted_at', 0)->first();
        if (!$data) return null;

        $db = \Config\Database::connect();

        // 1. Hidratar ÁREA (Objeto con id_area y nombre)
        $data['area'] = null;   
        if (!empty($data['id_area'])) {
            $area = $db->table('area')
                ->select('uuid as id_area, nombre')
                ->where('uuid', $data['id_area'])
                ->get()->getRowArray();
            if ($area) $data['area'] = $area;
        }

        // 2. Hidratar CATEGORÍA (Objeto con id_categoria y nombre)
        $data['categoria'] = null;
        if (!empty($data['id_categoria'])) {
            $cat = $db->table('categoria')
                ->select('uuid as id_categoria, nombre')
                ->where('uuid', $data['id_categoria'])
                ->get()->getRowArray();
            if ($cat) $data['categoria'] = $cat;
        }
        
        // 3. Limpieza de campos técnicos para la respuesta final
        unset(
            $data['id_area'], 
            $data['id_categoria'], 
            $data['updated_at'], 
            $data['deleted_at'], 
            $data['id']
        );

        return $data;
    }
}
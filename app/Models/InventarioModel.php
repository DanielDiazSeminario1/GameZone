<?php

namespace App\Models;

use CodeIgniter\Model;
use Ramsey\Uuid\Uuid;

class InventarioModel extends Model
{
    protected $table            = 'inventario';
    protected $primaryKey       = 'sku';
    protected $useAutoIncrement = false; 
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    // AJUSTADO: Solo las columnas que existen en tu tabla física (image_95ffad.png)
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

    // CORRECCIÓN ERROR 500: Se eliminó el placeholder {sku} que causaba conflicto
    // CORRECCIÓN ERROR 400: Se eliminó 'nombre' porque la tabla no lo tiene
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

    protected $beforeInsert = ['generateUUID'];

    protected function generateUUID(array $data): array
    {
        if (!isset($data['data']['uuid'])) {
            $data['data']['uuid'] = Uuid::uuid4()->toString();
        }
        return $data;
    }

    /**
     * 🔍 Buscar por SKU
     */
    public function findBySku(string $sku): ?array
    {
        $data = $this->where('sku', $sku)->where('deleted_at', 0)->first();
        if (!$data) return null;

        return $this->findByUuid($data['uuid']);
    }

    /**
     * 🔍 Buscar por UUID e Hidratar Objetos (Area y Categoría)
     */
    public function findByUuid(string $uuid): ?array
    {
        $data = $this->where('uuid', $uuid)->where('deleted_at', 0)->first();
        if (!$data) return null;

        $db = \Config\Database::connect();

        // 1. Hidratar ÁREA (Para mostrar objeto con nombre)
        $data['area'] = null;   
        if (!empty($data['id_area'])) {
            $area = $db->table('area')
                ->select('uuid as id_area, nombre')
                ->where('uuid', $data['id_area'])
                ->get()->getRowArray();
            if ($area) $data['area'] = $area;
        }

        // 2. Hidratar CATEGORÍA (Para mostrar objeto con nombre)
        $data['categoria'] = null;
        if (!empty($data['id_categoria'])) {
            $cat = $db->table('categoria')
                ->select('uuid as id_categoria, nombre')
                ->where('uuid', $data['id_categoria'])
                ->get()->getRowArray();
            if ($cat) $data['categoria'] = $cat;
        }
        
        // Limpieza de campos técnicos
        unset($data['id_area'], $data['id_categoria'], $data['updated_at'], $data['deleted_at'], $data['id']);

        return $data;
    }
}   
<?php

namespace App\Models;

use CodeIgniter\Model;
use Ramsey\Uuid\Uuid;

class InventarioModel extends Model
{
    protected $table            = 'inventario';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; 
    protected $protectFields    = true;

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    public $allowedFields = [
        'uuid',
        'sku', // <--- 1. NUEVO CAMPO AGREGADO
        'id_area',
        'nombre',
        'descripcion',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $validationRules = [
        'nombre'      => 'required|max_length[255]',
        'id_area'     => 'required|max_length[36]',
        // 2. REGLA SKU: Opcional (permit_empty), pero si escriben algo, debe ser único
        'sku'           => 'required|is_unique[inventario.sku,id,{id}]|max_length[50]',
        'descripcion' => 'permit_empty',
    ];

    protected $validationMessages = [
        'nombre'  => ['required' => 'El nombre es obligatorio.'],
        'id_area' => ['required' => 'El UUID del área es obligatorio.'],
        'sku'     => [
        'required'  => 'El SKU es obligatorio. No se puede dejar vacío.',
        'is_unique' => 'Este SKU ya existe. Intenta con otro.'
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

    public function findByUuid(string $uuid): ?array
    {
        $data = $this->where('uuid', $uuid)->where('deleted_at', 0)->first();
        if (!$data) return null;

        $db = \Config\Database::connect();
        $data['area'] = null;
        
        if (!empty($data['id_area'])) {
            $area = $db->table('area')
                        ->select('uuid as id_area, nombre') 
                        ->where('uuid', $data['id_area'])
                        ->get()->getRowArray();
            if ($area) $data['area'] = $area;
        }
        
        unset($data['id_area'], $data['updated_at'], $data['deleted_at'], $data['id']);
        return $data;
    }
}
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
        'sku',
        'propietario',
        'id_area',
        'id_categoria', // <--- 1. NUEVO CAMPO (Reemplaza la lógica de nombre)
        // 'nombre',    <--- ELIMINADO
        'descripcion',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $validationRules = [
        // 'nombre' => ... ELIMINADO
        'id_area'      => 'required|max_length[36]',
        'id_categoria' => 'required|max_length[36]', // <--- 2. OBLIGATORIO
        'sku'          => 'required|is_unique[inventario.sku,id,{id}]|max_length[50]',
        'propietario'  => 'permit_empty|max_length[255]',
        'descripcion'  => 'permit_empty',
    ];

    protected $validationMessages = [
        'id_area'      => ['required' => 'El UUID del área es obligatorio.'],
        'id_categoria' => ['required' => 'El UUID de la categoría es obligatorio.'],
        'sku'          => [
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

    public function findByUuid(string $uuid): ?array
    {
        $data = $this->where('uuid', $uuid)->where('deleted_at', 0)->first();
        if (!$data) return null;

        $db = \Config\Database::connect();

        // 1. Hidratar ÁREA (Traer nombre del área)
        $data['area'] = null;
        if (!empty($data['id_area'])) {
            $area = $db->table('area')
                        ->select('uuid as id_area, nombre') 
                        ->where('uuid', $data['id_area'])
                        ->get()->getRowArray();
            if ($area) $data['area'] = $area;
        }

        // 2. Hidratar CATEGORÍA (Traer nombre de la categoría)
        $data['categoria'] = null;
        if (!empty($data['id_categoria'])) {
            $cat = $db->table('categoria')
                        ->select('uuid as id_categoria, nombre') 
                        ->where('uuid', $data['id_categoria'])
                        ->get()->getRowArray();
            if ($cat) $data['categoria'] = $cat;
        }
        
        // Limpiamos los IDs internos para entregar un JSON limpio
        unset($data['id_area'], $data['id_categoria'], $data['updated_at'], $data['deleted_at'], $data['id']);
        
        return $data;
    }
}
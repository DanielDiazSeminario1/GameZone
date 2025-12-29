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

    // CORRECCIÓN: Se agregaron id_area e id_categoria para que la hidratación funcione
    public $allowedFields = [
        'uuid',
        'sku',
        'nombre',
        'ubicacion_nombre',
        'id_area',      
        'id_categoria', 
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $validationRules = [
        'sku'    => 'required|is_unique[inventario.sku]|max_length[50]',
        'nombre' => 'required|max_length[255]',
    ];

    protected $validationMessages = [
        'nombre' => ['required' => 'El nombre del equipo es obligatorio.'],
        'sku'    => [
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
     * 🔍 Buscar por SKU (Nueva función para tu eje principal)
     */
    public function findBySku(string $sku): ?array
    {
        $data = $this->where('sku', $sku)->where('deleted_at', 0)->first();
        if (!$data) return null;

        // Reutilizamos la lógica de hidratación usando el uuid encontrado
        return $this->findByUuid($data['uuid']);
    }

    /**
     * 🔍 Buscar por UUID e Hidratar Objetos
     */
    public function findByUuid(string $uuid): ?array
    {
        $data = $this->where('uuid', $uuid)->where('deleted_at', 0)->first();
        if (!$data) return null;

        $db = \Config\Database::connect();

        // 1. Hidratar ÁREA (Convertir en Objeto)
        $data['area'] = null;   
        if (!empty($data['id_area'])) {
            $area = $db->table('area')
                ->select('uuid as id_area, nombre')
                ->where('uuid', $data['id_area'])
                ->get()->getRowArray();
            
            // CORRECCIÓN: Asignamos el resultado directamente
            if ($area) $data['area'] = $area;
        }

        // 2. Hidratar CATEGORÍA (Convertir en Objeto)
        $data['categoria'] = null;
        if (!empty($data['id_categoria'])) {
            $cat = $db->table('categoria')
                ->select('uuid as id_categoria, nombre')
                ->where('uuid', $data['id_categoria'])
                ->get()->getRowArray();
            
            // CORRECCIÓN: Asignamos el resultado directamente
            if ($cat) $data['categoria'] = $cat;
        }
        
        // 3. Limpieza de IDs planos y campos técnicos para el JSON final
        unset($data['id_area'], $data['id_categoria'], $data['updated_at'], $data['deleted_at'], $data['id']);

        return $data;
    }
}
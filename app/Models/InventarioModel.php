<?php

namespace App\Models;

use CodeIgniter\Model;
use Ramsey\Uuid\Uuid;

class InventarioModel extends Model
{
    // 1. Tabla verificada en tu DB local
    protected $table            = 'inventario';
    
    // 2. Llave Primaria
    protected $primaryKey       = 'sku';
    
    // 3. SKU es manual
    protected $useAutoIncrement = false; 
    
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    // AJUSTADO: Solo columnas que existen en tu tabla física
    public $allowedFields = [
        'uuid',
        'sku',
        'nombre',
        'ubicacion_nombre',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // AJUSTADO: Se eliminaron reglas de campos inexistentes (series, propietario, etc.)
    protected $validationRules = [
        'sku'    => 'required|is_unique[inventario_qr.sku]|max_length[50]',
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
     * 🔍 Buscar por UUID
     */
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
<?php

namespace App\Models;

use CodeIgniter\Model;
use Ramsey\Uuid\Uuid;

class InventarioModel extends Model
{
    // 1. Tabla verificada en tu DB local
    protected $table            = 'inventario_qr';
    
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
     * 🔍 Buscar por SKU
     */
    public function findBySku(string $sku): ?array
    {
        $data = $this->where('sku', $sku)->where('deleted_at', 0)->first();
        return $data ? $this->hidratarDatos($data) : null;
    }

    /**
     * 🔍 Buscar por UUID
     */
    public function findByUuid(string $uuid): ?array
    {
        $data = $this->where('uuid', $uuid)->where('deleted_at', 0)->first();
        return $data ? $this->hidratarDatos($data) : null;
    }

    private function hidratarDatos(array $data): array
    {
        unset($data['updated_at'], $data['deleted_at']);
        return $data;
    }
}
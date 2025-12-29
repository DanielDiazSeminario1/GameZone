<?php

namespace App\Models;

use CodeIgniter\Model;
use Ramsey\Uuid\Uuid;

class InventarioModel extends Model
{
    // 1. Ajustar nombre de la tabla según tu base de datos (vimos que es inventario_qr)
    protected $table            = 'inventario_qr';
    
    // 2. Cambiar Llave Primaria a SKU
    protected $primaryKey       = 'sku';
    
    // 3. Desactivar Auto Increment (el SKU es manual)
    protected $useAutoIncrement = false;
    
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
        'serie',
        'descripcion',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // 4. Actualizar reglas de validación para la nueva PK (Se eliminaron id_area e id_categoria)
    protected $validationRules = [
        // is_unique ahora valida contra el campo sku
        'sku'          => 'required|is_unique[inventario_qr.sku,sku,{sku}]|max_length[50]',
        'serie'        => 'required|is_unique[inventario_qr.serie,sku,{sku}]|max_length[50]',
        'propietario'  => 'required|max_length[255]',
        'descripcion'  => 'permit_empty',
    ];

    protected $validationMessages = [
        'propietario'      => ['required' => 'El propietario es obligatorio.'],
        'sku'          => [
            'required'  => 'El SKU es obligatorio.',
            'is_unique' => 'Este SKU ya existe.'
        ],
        'serie'          => [
            'required'  => 'La serie es obligatorio.',
            'is_unique' => 'Esta serie ya existe.'
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
     * 🔍 Buscar por SKU (Nueva función principal)
     */
    public function findBySku(string $sku): ?array
    {
        // Al ser PK, find($sku) ya funciona, pero hidratamos para el JSON
        $data = $this->where('sku', $sku)->where('deleted_at', 0)->first();
        return $data ? $this->hidratarDatos($data) : null;
    }

    /**
     * 🔍 Mantener búsqueda por UUID (Si se requiere)
     */
    public function findByUuid(string $uuid): ?array
    {
        $data = $this->where('uuid', $uuid)->where('deleted_at', 0)->first();
        return $data ? $this->hidratarDatos($data) : null;
    }

    /**
     * Reutilizamos la lógica de hidratación para no repetir código
     */
    private function hidratarDatos(array $data): array
    {
        // Se mantiene la estructura del método, pero se eliminó la consulta a tablas inexistentes
        // para evitar el error 500 al no tener las tablas 'area' y 'categoria'

        // Limpiar campos internos
        unset($data['updated_at'], $data['deleted_at']);

        return $data;
    }
}
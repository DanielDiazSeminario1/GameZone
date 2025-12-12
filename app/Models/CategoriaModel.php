<?php

namespace App\Models;

use CodeIgniter\Model;
use Ramsey\Uuid\Uuid;

class CategoriaModel extends Model
{
    protected $table            = 'categoria';
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
        'nombre',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $validationRules = [
        'nombre' => 'required|max_length[255]',
        'uuid'   => 'permit_empty|max_length[36]',
    ];

    protected $validationMessages = [
        'nombre' => [
            'required' => 'El nombre de la categoría es obligatorio.',
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

    // ================================
    //  Buscar categoría por UUID
    // ================================
    public function findByUuid(string $uuid): ?array
    {
        $data = $this->where('uuid', $uuid)
            ->where('deleted_at', 0)
            ->first();

        if (!$data) return null;

        // Limpiar campos internos (igual que InventarioModel)
        unset($data['id'], $data['updated_at'], $data['deleted_at']);

        return $data;
    }
}

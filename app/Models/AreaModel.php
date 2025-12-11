<?php

namespace App\Models;

use CodeIgniter\Model;

class AreaModel extends Model
{
    protected $table            = 'area';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $createdField     = 'created_at';
    
    // CORRECCIÓN: Coincide con tu SQL (sin la 'd')
    protected $updatedField     = 'updated_at'; 

    // Lógica manual 0/1 (Desactivamos la automática)
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'uuid',
        'nombre',
        'update_at', // Coincide con tu SQL
        'deleted_at'
    ];

    protected $validationRules = [
        'uuid'       => 'required|max_length[36]|is_unique[area.uuid,id,{id}]',
        'nombre'     => 'required|max_length[255]',
        'deleted_at' => 'permit_empty|integer',
    ];

    protected $validationMessages = [
        'uuid' => [
            'required'  => 'El UUID del área es obligatorio.',
            'is_unique' => 'Este UUID de área ya ha sido registrado.',
        ],
        'nombre' => [
            'required' => 'El nombre del área es obligatorio.',
        ],
    ];

    public function findByUuid(string $uuid)
    {
        return $this
            ->where('uuid', $uuid)
            ->where('deleted_at', 0)
            ->first();
    }
}
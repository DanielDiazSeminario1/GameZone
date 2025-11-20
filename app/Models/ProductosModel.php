<?php namespace App\Models;
use CodeIgniter\Model;

class ProductosModel extends Model
{
    protected $table      = 'productos';
    protected $primaryKey = 'id';
    
    // Estos son los campos exactos que creamos en el SQL Pro
    protected $allowedFields = [
        'modelo', 
        'precio', 
        'stock', 
        'garantia_meses', 
        'id_serie'
    ];
    
    protected $returnType = 'array';

// --- REGLAS DE VALIDACIÓN ---
    protected $validationRules = [
        'modelo'         => 'required|min_length[3]|max_length[100]',
        'precio'         => 'required|numeric|greater_than[0]',
        'stock'          => 'required|integer|greater_than_equal_to[0]',
        'garantia_meses' => 'required|integer',
        'id_serie'       => 'required|integer'
    ];

    protected $validationMessages = [
        'modelo' => [
            'required'   => 'El nombre del modelo es obligatorio.',
            'min_length' => 'El modelo es muy corto, escribe al menos 3 letras.'
        ],
        'precio' => [
            'numeric' => 'El precio debe ser un número real.'
        ]
    ];
}

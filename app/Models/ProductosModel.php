<?php namespace App\Models;

use CodeIgniter\Model;

class ProductosModel extends Model
{
    protected $table      = 'productos';
    protected $primaryKey = 'id';

    protected $returnType = 'array';

    // IMPORTANTE: Estos son los campos que permitimos guardar desde el Controller
    protected $allowedFields = [
        'modelo',
        'precio',
        'stock',
        'garantia_meses',
        'id_serie'
    ];

    // --- IMPORTANTE: DESACTIVAMOS LOS TIMESTAMPS ---
    // Como tu tabla en la BD no tiene columnas created_at ni updated_at,
    // debemos mantener esto comentado o en false para evitar errores.
    // protected $useTimestamps = true; 
    // protected $createdField  = 'created_at';
    // protected $updatedField  = 'updated_at';

    // --- REGLAS DE VALIDACIÓN (Opcional pero recomendado) ---
    protected $validationRules = [
        'modelo'         => 'required|min_length[3]|max_length[100]',
        'precio'         => 'required|numeric|greater_than[0]',
        'stock'          => 'required|integer|greater_than_equal_to[0]',
        'garantia_meses' => 'required|integer',
        'id_serie'       => 'required|integer'
    ];
}
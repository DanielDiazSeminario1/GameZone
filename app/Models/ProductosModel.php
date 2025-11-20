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
}

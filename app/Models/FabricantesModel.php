<?php namespace App\Models;
use CodeIgniter\Model;

class FabricantesModel extends Model
{
    protected $table      = 'fabricantes';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre_empresa', 'pais_origen', 'sitio_web', 'contacto_email'];
    protected $returnType = 'array';
}
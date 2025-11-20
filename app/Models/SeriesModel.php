<?php namespace App\Models;
use CodeIgniter\Model;

class SeriesModel extends Model
{
    protected $table      = 'series';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre_serie', 'publico_objetivo', 'anio_lanzamiento', 'id_fabricante'];
    protected $returnType = 'array';
}
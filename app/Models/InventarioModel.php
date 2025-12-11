<?php

namespace App\Models;

use CodeIgniter\Model;
use Ramsey\Uuid\Uuid;

class InventarioModel extends Model
{
    protected $table            = 'inventario';
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
        'id_area', // Debe estar aquí para guardarse
        'nombre',
        'descripcion',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $validationRules = [
        'nombre'      => 'required|max_length[255]',
        // Validación correcta: Esperamos un UUID (string 36 chars)
        'id_area'     => 'required|max_length[36]', 
        'descripcion' => 'permit_empty',
    ];

    protected $validationMessages = [
        'nombre' => ['required' => 'El nombre es obligatorio.'],
        'id_area' => ['required' => 'El UUID del área es obligatorio.']
    ];

    protected $beforeInsert = ['generateUUID'];

    protected function generateUUID(array $data): array
    {
        if (!isset($data['data']['uuid'])) {
            $data['data']['uuid'] = Uuid::uuid4()->toString();
        }
        return $data;
    }

    public function findByUuid(string $uuid): ?array
    {
        $data = $this->where('uuid', $uuid)->where('deleted_at', 0)->first();
        if (!$data) return null;

        // Enriquecer con Área
        $db = \Config\Database::connect();
        $data['area'] = null;
        
        if (!empty($data['id_area'])) {
            $area = $db->table('area')
                        // --- CORRECCIÓN CLAVE AQUÍ ---
                        // Usamos 'as id_area' para que el JSON salga con la etiqueta correcta
                        ->select('uuid as id_area, nombre') 
                        ->where('uuid', $data['id_area']) // Buscamos por el UUID que tenemos guardado
                        ->get()->getRowArray();
            
            if ($area) $data['area'] = $area;
        }
        
        // Limpiamos el id_area del nivel raíz para no duplicar info, ya que está dentro de 'area'
        unset($data['id_area'], $data['updated_at'], $data['deleted_at'], $data['id']);
        
        return $data;
    }
}
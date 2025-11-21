<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Fabricantes extends ResourceController
{
    // Indica el modelo a usar
    protected $modelName = 'App\Models\FabricantesModel';
    protected $format    = 'json'; 

    // 1. GET /fabricantes (Listar todos)
    public function index() {
        return $this->respond($this->model->findAll());
    }

    // 2. GET /fabricantes/{id} (Ver uno)
    public function show($id = null) {
        $data = $this->model->find($id);
        if ($data) {
            return $this->respond($data);
        }
        return $this->failNotFound('No se encontró el Fabricante con id ' . $id);
    }
    
    // 3. POST /fabricantes (Crear)
    public function create() {
        $json = $this->request->getJSON();
        if ($this->model->insert($json)) {
            return $this->respondCreated(['mensaje' => 'Fabricante creado correctamente']);
        }
        return $this->fail($this->model->errors());
    }

    // 4. PUT/PATCH /fabricantes/{id} (Actualizar)
    public function update($id = null) { 
        $json = $this->request->getJSON();
        if ($this->model->update($id, $json)) {
            return $this->respond(['mensaje' => 'Fabricante actualizado']);
        }
        return $this->fail($this->model->errors());
    }

    // 5. DELETE /fabricantes/{id} (Borrar)
    public function delete($id = null) {
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['mensaje' => 'Fabricante eliminado']);
        }
        return $this->failNotFound('No se pudo eliminar (tal vez no existe)');
    }
}
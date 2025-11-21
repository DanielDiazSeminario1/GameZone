<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Series extends ResourceController
{
    // Indicar al controlador qué modelo debe usar (tu SeriesModel.php)
    protected $modelName = 'App\Models\SeriesModel';
    // Indicar que las respuestas serán en formato JSON
    protected $format    = 'json'; 

    // 1. GET /series (Listar todas las series)
    public function index() {
        // Devuelve todas las series sin filtros ni paginación complejos
        return $this->respond($this->model->findAll());
    }

    // 2. GET /series/{id} (Ver una sola serie)
    public function show($id = null) {
        $data = $this->model->find($id);
        if ($data) {
            return $this->respond($data);
        }
        return $this->failNotFound('No se encontró la Serie con id ' . $id);
    }
    
    // 3. POST /series (Crear una nueva serie)
    public function create() {
        $json = $this->request->getJSON();
        if ($this->model->insert($json)) {
            // Devuelve un mensaje de éxito y el código HTTP 201 (Creado)
            return $this->respondCreated(['mensaje' => 'Serie creada correctamente']);
        }
        // Si falla (ej: faltan campos), devuelve los errores
        return $this->fail($this->model->errors());
    }

    // 4. PUT/PATCH /series/{id} (Actualizar una serie existente)
    public function update($id = null) { 
        $json = $this->request->getJSON();
        if ($this->model->update($id, $json)) {
            return $this->respond(['mensaje' => 'Serie actualizada']);
        }
        return $this->fail($this->model->errors());
    }

    // 5. DELETE /series/{id} (Borrar una serie)
    public function delete($id = null) {
        if ($this->model->delete($id)) {
            // Devuelve el código HTTP 200 (OK)
            return $this->respondDeleted(['mensaje' => 'Serie eliminada']);
        }
        return $this->failNotFound('No se pudo eliminar (tal vez no existe)');
    }
}
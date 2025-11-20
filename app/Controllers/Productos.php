<?php namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Productos extends ResourceController
{
    protected $modelName = 'App\Models\ProductosModel';
    protected $format    = 'json'; // Siempre responderemos en JSON

    // 1. LISTAR TODOS (GET /productos)
    // 1. LISTAR PRODUCTOS (Con datos enriquecidos: Marca, País, Serie, Año)
   public function index()
    {
        // 1. Capturamos la búsqueda del cliente (ej: ?q=asus)
        $busqueda = $this->request->getVar('q');

        // 2. Preparamos la consulta con los nombres bonitos (JOINs)
        $builder = $this->model
            ->select('productos.id, productos.modelo, productos.precio, productos.stock')
            ->select('series.nombre_serie, fabricantes.nombre_empresa')
            ->join('series', 'series.id = productos.id_serie')
            ->join('fabricantes', 'fabricantes.id = series.id_fabricante');

        // 3. SI hay búsqueda, filtramos
        if ($busqueda) {
            $builder->groupStart()
                    ->like('productos.modelo', $busqueda)             // Busca por nombre
                    ->orLike('fabricantes.nombre_empresa', $busqueda) // O por marca
                    ->groupEnd();
        }

        // 4. Entregamos resultados
        $data = $builder->findAll();

        return $this->respond($data);
    }

    // 2. VER UNO SOLO (GET /productos/{id})
    public function show($id = null)
    {
        $data = $this->model->find($id);
        
        if (!$data) {
            return $this->failNotFound('Producto no encontrado');
        }
        return $this->respond($data);
    }

    // 3. CREAR (POST /productos)
    public function create()
    {
        $json = $this->request->getJSON();
        
        $data = [
            'modelo'         => $json->modelo,
            'precio'         => $json->precio,
            'stock'          => $json->stock,
            'garantia_meses' => $json->garantia_meses,
            'id_serie'       => $json->id_serie
        ];

        if ($this->model->insert($data)) {
            return $this->respondCreated(['mensaje' => 'Producto creado correctamente']);
        }
        return $this->fail($this->model->errors());
    }

    // 4. EDITAR (PUT /productos/{id})
    public function update($id = null)
    {
        $json = $this->request->getJSON();
        
        // CodeIgniter es inteligente: solo actualiza los campos que envíes
        if ($this->model->update($id, $json)) {
            return $this->respond(['mensaje' => 'Producto actualizado']);
        }
        return $this->fail($this->model->errors());
    }

    // 5. BORRAR (DELETE /productos/{id})
    public function delete($id = null)
    {
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['mensaje' => 'Producto eliminado']);
        }
        return $this->failNotFound('No se pudo eliminar (tal vez no existe)');
    }
}
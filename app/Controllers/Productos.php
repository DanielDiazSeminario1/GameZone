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
        $data = $this->model
            // A. SELECCIONAMOS SOLO LO QUE EL CLIENTE DEBE VER (Filtro)
            // Datos del Producto
            ->select('productos.id, productos.modelo, productos.precio, productos.stock, productos.garantia_meses')
            // Datos de la Serie (Contexto: Gamer, Militar, Año)
            ->select('series.nombre_serie, series.publico_objetivo, series.anio_lanzamiento')
            // Datos del Fabricante (Origen: Nombre, País) -> OJO: No pedimos email ni web
            ->select('fabricantes.nombre_empresa, fabricantes.pais_origen')
            
            // B. HACEMOS LAS UNIONES (Conectamos las tablas)
            // Unimos Producto con Serie (El Hijo con el Padre)
            ->join('series', 'series.id = productos.id_serie')

            // Unimos Serie con Fabricante (El Padre con el Abuelo)
            ->join('fabricantes', 'fabricantes.id = series.id_fabricante')

            // C. TRAEMOS TODO
            ->findAll();

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
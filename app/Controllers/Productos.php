<?php namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Productos extends ResourceController
{
    protected $modelName = 'App\Models\ProductosModel';
    protected $format    = 'json'; // Siempre responderemos en JSON

    // 1. LISTAR PRODUCTOS (GET /productos) - CON PAGINACIÓN Y FILTROS AVANZADOS
    public function index()
    {
        // 1. CAPTURA Y VALIDACIÓN DE PARÁMETROS
        $busqueda = $this->request->getVar('q');        // Filtro genérico: ?q=Razer
        $pagina   = $this->request->getVar('page');     // Paginación: ?page=2
        $limite_solicitado = (int)$this->request->getVar('limit'); // Límite: ?limit=20
        $filtro_tipo = $this->request->getVar('tipo');   // Filtro por palabra clave (Ej: Laptop, Monitor)
        $filtro_year = $this->request->getVar('anio');   // Filtro por año (Ej: 2020)

        // 2. CONTROL DE LÍMITE
        $limite_por_pagina = 5; // Valor por defecto
        if ($limite_solicitado > 0 && $limite_solicitado <= 100) {
            $limite_por_pagina = $limite_solicitado;
        } else if ($limite_solicitado > 100) {
            $limite_por_pagina = 100; // Máximo de seguridad
        }

        // 3. GUARDIÁN ANTI-PÁGINAS INVÁLIDAS
        // Bloquea números negativos y el cero
        if (isset($pagina) && (int)$pagina < 1) {
            return $this->fail('El número de página debe ser mayor a 0.', 400);
        }

        // 4. PREPARAR CONSULTA (JOINs y Selects de datos enriquecidos)
        $this->model
            // Seleccionamos los campos necesarios
            ->select('productos.id, productos.modelo, productos.precio, productos.stock, productos.garantia_meses')
            ->select('series.nombre_serie, series.publico_objetivo, series.anio_lanzamiento') 
            ->select('fabricantes.nombre_empresa, fabricantes.pais_origen')
            
            ->join('series', 'series.id = productos.id_serie')
            ->join('fabricantes', 'fabricantes.id = series.id_fabricante');

        // 5. APLICAR FILTROS ESPECÍFICOS (Buena Práctica: where() para concretos)
        if ($filtro_tipo) {
            // Ejemplo: ?tipo=Laptop (Busca la palabra 'Laptop' en el nombre del modelo)
            $this->model->like('productos.modelo', $filtro_tipo); 
        }
        if ($filtro_year) {
            // Ejemplo: ?anio=2020 (Busca productos lanzados en 2020 o después)
            $this->model->where('series.anio_lanzamiento >=', $filtro_year);
        }

        // 6. APLICAR FILTRO GENÉRICO ('q')
        if ($busqueda) {
            $this->model->groupStart()
                ->like('productos.modelo', $busqueda)
                ->orLike('fabricantes.nombre_empresa', $busqueda)
                ->orLike('series.nombre_serie', $busqueda) // Búsqueda avanzada en el nombre de la serie
                ->groupEnd();
        }

        // 7. DEVOLVER CON PAGINACIÓN Y METADATOS
        $data = [
            'productos' => $this->model->paginate($limite_por_pagina), 
            'paginacion' => [
                'pagina_actual' => $this->model->pager->getCurrentPage(),
                'por_pagina'    => $limite_por_pagina,
                'total_datos'   => $this->model->pager->getTotal(),
                'total_paginas' => $this->model->pager->getPageCount()
            ]
        ];

        return $this->respond($data);
    }

    // 2. VER UNO SOLO (GET /productos/{id})
    public function show($id = null)
    {
        // El resto de tu CRUD de show, create, update, delete sigue igual
        // ... (código show, create, update, delete)
        
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
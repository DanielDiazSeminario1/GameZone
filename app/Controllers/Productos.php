<?php namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Productos extends ResourceController
{
    protected $modelName = 'App\Models\ProductosModel';
    protected $format    = 'json'; // Siempre responderemos en JSON

    // 1. LISTAR PRODUCTOS (GET /productos) - CON Paginación, Filtros y Ordenamiento
    public function index()
    {
        // 1. CAPTURA Y VALIDACIÓN DE PARÁMETROS
        $busqueda = $this->request->getVar('q');
        $pagina   = $this->request->getVar('page');
        $limite_solicitado = (int)$this->request->getVar('limit');
        $filtro_tipo = $this->request->getVar('tipo');
        $filtro_year = $this->request->getVar('anio');
        $sortField = $this->request->getVar('sort');
        $sortDir   = strtoupper($this->request->getVar('order') ?? 'ASC');

        // 2. CONTROL DE LÍMITE Y VALIDACIÓN
        $limite_por_pagina = 5; // Valor por defecto
        if ($limite_solicitado > 0 && $limite_solicitado <= 100) {
            $limite_por_pagina = $limite_solicitado;
        } else if ($limite_solicitado > 100) {
            $limite_por_pagina = 100; // Máximo de seguridad
        }
        
        // GUARDIÁN ANTI-PÁGINAS INVÁLIDAS (Bloquea page=0 y negativos)
        if (isset($pagina) && (int)$pagina < 1) {
            return $this->fail('El número de página debe ser mayor a 0.', 400);
        }

        // 3. PREPARAR CONSULTA (JOINs y Selects)
        $this->model
            // Seleccionamos los campos necesarios
            ->select('productos.id, productos.modelo, productos.precio, productos.stock, productos.garantia_meses')
            // Datos enriquecidos para el cliente
            ->select('series.nombre_serie, series.publico_objetivo, series.anio_lanzamiento') 
            ->select('fabricantes.nombre_empresa, fabricantes.pais_origen')
            
            // Uniones (JOINs)
            ->join('series', 'series.id = productos.id_serie')
            ->join('fabricantes', 'fabricantes.id = series.id_fabricante');

        // 4. APLICAR ORDENACIÓN DINÁMICA
        $allowedSorts = [
            'nombre'  => 'productos.modelo', 'empresa' => 'fabricantes.nombre_empresa', 
            'publico' => 'series.publico_objetivo', 'precio' => 'productos.precio',
        ];
        $orderByColumn = $allowedSorts[$sortField] ?? 'productos.modelo';
        if (in_array($sortDir, ['ASC', 'DESC'])) {
             $this->model->orderBy($orderByColumn, $sortDir);
        }

        // 5. APLICAR FILTROS ESPECÍFICOS
        if ($filtro_tipo) {
            $this->model->like('productos.modelo', $filtro_tipo); 
        }
        if ($filtro_year) {
            $this->model->where('series.anio_lanzamiento >=', $filtro_year);
        }

        // 6. APLICAR FILTRO GENÉRICO ('q') - Busca en las 3 tablas
        if ($busqueda) {
            $this->model->groupStart()
                ->like('productos.modelo', $busqueda)
                ->orLike('fabricantes.nombre_empresa', $busqueda)
                ->orLike('series.nombre_serie', $busqueda)
        // LÍNEA AÑADIDA: Ahora busca en el país de origen del fabricante
                ->orLike('fabricantes.pais_origen', $busqueda)
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
        $data = $this->model->find($id);
        
        if (!$data) {
            return $this->failNotFound('Producto no encontrado');
        }
        return $this->respond($data);
    }

    // 3. CREAR (POST /productos) - SOPORTA INSERCIÓN ÚNICA Y POR LOTE
    public function create()
    {
        // Usamos getJSON(true) para obtener un array asociativo y poder usar is_array()
        $json = $this->request->getJSON(true); 

        // Verificamos si el JSON contiene un array de objetos (un lote)
        $isBatch = (is_array($json) && count($json) > 0 && is_array($json[0]));

        if ($isBatch) {
            // --- INSERCIÓN DE LOTE (insertBatch) ---
            if ($this->model->insertBatch($json)) {
                // Éxito: Creado con código 201
                return $this->respondCreated(['mensaje' => 'Lote de productos creado correctamente']);
            }
        } else {
            // --- INSERCIÓN UNITARIA (insert) ---
            if ($this->model->insert($json)) {
                return $this->respondCreated(['mensaje' => 'Producto creado correctamente']);
            }
        }
        
        // Si falló (validación, datos faltantes, etc.)
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
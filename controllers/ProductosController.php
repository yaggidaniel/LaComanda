<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;



// traigo la clase y otros archivos necesarios

require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../middlewares/AuthJWT.php';
require_once __DIR__ . '/../interfaces/IApiUsable.php';

class ProductosController implements IApiUsable
{
    public function TraerUno($request, $response, $args)
    {
        // Obtener instancia de conexión 
        $conexionPDO = ConexionPDO::obtenerInstancia();
        
        // Obtén el parámetro 'id' de los parámetros de consulta
        $queryParams = $request->getQueryParams();

        // Verifica si se proporcionó 'idProducto' o 'nombreProducto'
        $idProducto = isset($queryParams['idProducto']) ? $queryParams['idProducto'] : null;
        $nombreProducto = isset($queryParams['nombreProducto']) ? $queryParams['nombreProducto'] : null;

        // Verifica que se haya proporcionado al menos uno de los parámetros
        if ($idProducto === null && $nombreProducto === null) {
            $response->getBody()->write(json_encode(array('error' => 'Se debe proporcionar al menos un parámetro: "idProducto" o "nombreProducto"')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            // Verifica si se proporciona el id o el nombre
            if ($idProducto !== null) {
                // Preparar la consulta SQL para obtener por ID
                $consulta = $conexionPDO->prepararConsulta("SELECT * FROM productos WHERE idProducto = :idProducto");
                $consulta->bindValue(':idProducto', $idProducto, PDO::PARAM_INT);
            } elseif ($nombreProducto !== null) {
                // Preparar la consulta SQL para obtener por nombre
                $consulta = $conexionPDO->prepararConsulta("SELECT * FROM productos WHERE nombreProducto = :nombreProducto");
                $consulta->bindValue(':nombreProducto', $nombreProducto, PDO::PARAM_STR);
            } else {
                // No se proporcionó ni ID ni nombre
                $response->getBody()->write(json_encode(array('error' => 'Se debe proporcionar al menos un parámetro: "idProducto" o "nombreProducto"')));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            // Ejecutar la consulta
            $consulta->execute();

            // Obtener el resultado como un array asociativo
            $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

            // Verificar si se encontró el producto
            if (!$resultado) {
                $response->getBody()->write(json_encode(array('error' => 'Producto no encontrado')));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            // Construir la respuesta
            $response->getBody()->write(json_encode($resultado));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            // En caso de error de base de datos
            $response->getBody()->write(json_encode(array('error' => 'Error de base de datos')));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }


    public function TraerTodos($request, $response, $args)
    {
        // Obtén todos los productos
        $productos = Producto::TraerTodosLosProductos();

        // Construir la respuesta
        $response->getBody()->write(json_encode($productos));
        return $response->withHeader('Content-Type', 'application/json');
    }


    public function CargarUno($request, $response, $args)
    {
        // Obtén los datos del cuerpo de la solicitud
        $data = $request->getParsedBody();

        // Verifica los datos obligatorios
        if (!isset($data['nombreProducto']) || !isset($data['precioProducto']) || !isset($data['categoriaProducto'])) {
            $response->getBody()->write(json_encode(array('error' => 'Datos incompletos')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Verifica si el nombre del producto ya existe en la base de datos
        $productoExistente = Producto::TraerUnProductoPorNombre($data['nombreProducto']);

        if ($productoExistente !== null) {
        // El producto ya existe, devolver un error
        $response->getBody()->write(json_encode(array('error' => 'El producto ya existe')));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Crea un nuevo producto con los datos proporcionados
        $nuevoProducto = new Producto();
        $nuevoProducto->nombreProducto = $data['nombreProducto'];
        $nuevoProducto->precioProducto = $data['precioProducto'];
        $nuevoProducto->categoriaProducto = $data['categoriaProducto'];

        // Inserta el nuevo producto en la base de datos
        $idInsertado = $nuevoProducto->InsertarProductoParametros();

        // Construir la respuesta
        $result = array('idProducto' => $idInsertado, 'mensaje' => 'Producto insertado correctamente');
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function BorrarUno($request, $response, $args)
    {
        // Obtén el ID del producto a borrar
        $idProducto = $args['idProducto'];
    
        // Verifica si el ID es válido
        if (!is_numeric($idProducto) || $idProducto <= 0) {
            $response->getBody()->write(json_encode(array('error' => 'ID de producto no válido')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    
        // Crea un nuevo producto con el ID
        $producto = new Producto();
        $producto->idProducto = $idProducto;
    
        // Intenta borrar el producto
        try {
            $filasAfectadas = $producto->BorrarProducto();
    
            if ($filasAfectadas > 0) {
                // Producto borrado exitosamente
                $response->getBody()->write(json_encode(array('mensaje' => 'Producto borrado correctamente')));
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                // Producto no encontrado
                $response->getBody()->write(json_encode(array('error' => 'Producto no encontrado')));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }
        } catch (PDOException $e) {
            // Error de base de datos
            $response->getBody()->write(json_encode(array('error' => 'Error de base de datos')));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function ModificarUno($request, $response, $args)
    {
        // Obtén el ID del producto a modificar
        $idProducto = $args['idProducto'];

        // Obtén los datos del cuerpo de la solicitud
        $data = $request->getParsedBody();

        // Crea un nuevo producto con los datos proporcionados
        $producto = new Producto();
        $producto->idProducto = $idProducto;
        $producto->nombreProducto = $data['nombreProducto'];
        $producto->precioProducto = $data['precioProducto'];
        $producto->categoriaProducto = $data['categoriaProducto'];

        // Intenta modificar el producto
        $idModificado = $producto->ModificarProductoParametros();

        if ($idModificado !== false) {
            // Producto modificado exitosamente
            $response->getBody()->write(json_encode(array('idProducto' => $idModificado, 'mensaje' => 'Producto modificado correctamente')));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            // Producto no encontrado
            $response->getBody()->write(json_encode(array('error' => 'Producto no encontrado')));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    }

    public function agregarRutas(RouteCollectorProxy $group)
    {
        // Define las rutas relacionadas con los productos
        $group->get('/producto/{idProducto}', [$this, 'TraerUno']);
        $group->get('/productos', [$this, 'TraerTodos']);
        $group->post('/agregar-producto', [$this, 'CargarUno']);
        $group->put('/modificar-producto/{idProducto}', [$this, 'ModificarUno']);
        $group->post('/borrar-producto/{idProducto}', [$this, 'BorrarUno']);
    }
}

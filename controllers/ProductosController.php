<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;



// traigo la clase y otros archivos necesarios

require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../middlewares/AuthJWT.php';
require_once __DIR__ . '/../interfaces/IApiUsable.php';

class ProductoController implements IApiUsable
{
    public function TraerUno($request, $response, $args)
    {
        //  instancia de conexión 
        $conexionPDO = ConexionPDO::obtenerInstancia();
        
        // el parámetro 'id' de los parámetros de consulta
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
        // todos los productos
        $productos = Producto::TraerTodosLosProductos();

        // Construir la respuesta
        $response->getBody()->write(json_encode($productos));
        return $response->withHeader('Content-Type', 'application/json');
    }


    public function CargarUno($request, $response, $args)
    {
        // los datos del cuerpo de la solicitud
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
        $nuevoProducto->sector = $data['categoriaProducto'];

        // Inserta el nuevo producto en la base de datos
        $idInsertado = $nuevoProducto->InsertarProductoParametros();

        // Construir la respuesta
        $result = array('idProducto' => $idInsertado, 'mensaje' => 'Producto insertado correctamente');
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function BorrarUno($request, $response, $args)
    {
        // los datos del cuerpo de la solicitud
        $data = $request->getParsedBody();
    
        // Verifica si se proporcionó el ID del producto en el cuerpo
        if (!isset($data['idProducto'])) {
            $response->getBody()->write(json_encode(array('error' => 'ID de producto no proporcionado')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    
        // el ID del producto a borrar desde el cuerpo de la solicitud
        $idProducto = $data['idProducto'];
    
        // Verifica si el ID es válido
        if (!is_numeric($idProducto) || $idProducto <= 0) {
            $response->getBody()->write(json_encode(array('error' => 'ID de producto no válido')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    
        // Verifica si el producto existe
        $producto = Producto::TraerUnProducto($idProducto);
    
        if (!$producto) {
            $response->getBody()->write(json_encode(array('error' => 'Producto no encontrado')));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    
        // Verifica si el producto ya está en estado "Baja" 
        if ($producto->estado === 'eliminado') {
            $response->getBody()->write(json_encode(array('error' => 'Error, el producto fue dado de baja previamente')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    
        // Realiza la operación de dar de baja al producto utilizando una función adecuada
        $filasAfectadas = $producto->BorrarProducto();
    
        if ($filasAfectadas > 0) {
            // Producto borrado exitosamente
            $response->getBody()->write(json_encode(array('mensaje' => 'Producto borrado correctamente')));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            // Error al borrar el producto
            $response->getBody()->write(json_encode(array('error' => 'Error al borrar el producto')));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function ModificarUno($request, $response, $args)
    {
        // datos del cuerpo de la solicitud
        $data = $request->getParsedBody();

        // el ID del producto a modificar
        $idProducto = $data['idProducto'];
    
        // los datos del cuerpo de la solicitud
        $data = $request->getParsedBody();
    
        // Crea un nuevo producto con los datos proporcionados
        $producto = new Producto();
        $producto->idProducto = $idProducto;
    
        // Verifica si se tienen cada campo en el cuerpo y actualiza el producto
        if (isset($data['nombreProducto'])) {
            $producto->nombreProducto = $data['nombreProducto'];
        }
    
        if (isset($data['precioProducto'])) {
            $producto->precioProducto = $data['precioProducto'];
        }
    
        if (isset($data['categoriaProducto'])) {
            $producto->sector = $data['categoriaProducto'];
        }
    
        if (isset($data['estado'])) {
            // Verifica si el estado proporcionado es válido (eliminado o activo)
            if ($data['estado'] === 'eliminado' || $data['estado'] === 'activo') {
                $producto->estado = $data['estado'];
            } else {
                $response->getBody()->write(json_encode(array('error' => 'Estado no válido')));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
        }
    
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

}

<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

// Incluir clases y archivos necesarios
require_once __DIR__ . '/../models/Mesa.php';
require_once __DIR__ . '/../middlewares/AuthJWT.php';
require_once __DIR__ . '/../interfaces/IApiUsable.php';


class MesaController implements IApiUsable
{
    // Método para cargar una nueva mesa
    public function CargarUno($request, $response, $args)
    {
        // Datos del cuerpo de la solicitud
        $data = $request->getParsedBody();

        // Verificar los datos obligatorios
        if (!isset($data['codigo_identificacion']) || !isset($data['estado'])) {
            $response->getBody()->write(json_encode(array('error' => 'Datos incompletos')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Crear una nueva mesa con los datos proporcionados
        $nuevaMesa = new Mesa();
        $nuevaMesa->codigo_identificacion = $data['codigo_identificacion'];
        $nuevaMesa->estado = $data['estado']; 

        // Insertar la nueva mesa en la base de datos
        $idInsertado = $nuevaMesa->InsertarMesa();

        error_log('ID Insertado: ' . $idInsertado);

        if ($idInsertado === false) {
            // La mesa ya existe, devolver un error
            error_log('Mesa ya existe');
            $response->getBody()->write(json_encode(array('error' => 'Mesa ya existe')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Construir la respuesta
        $result = array('mesa_id' => $idInsertado, 'mensaje' => 'Mesa insertada correctamente');
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    // Método para borrar una mesa
    public function BorrarUno($request, $response, $args)
    {
        // Datos del cuerpo de la solicitud
        $data = $request->getParsedBody();
    
        // Verificar si se proporcionó el ID de la mesa o el código de identificación en el cuerpo
        if (!isset($data['mesa_id']) && !isset($data['codigo_identificacion'])) {
            $response->getBody()->write(json_encode(array('error' => 'ID de mesa o código de identificación no proporcionado')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    
        // El ID de la mesa o el código de identificación desde el cuerpo de la solicitud
        $mesa_id = isset($data['mesa_id']) ? $data['mesa_id'] : null;
        $codigo_identificacion = isset($data['codigo_identificacion']) ? $data['codigo_identificacion'] : null;
    
        // Verificar si se proporcionó al menos uno de los valores
        if ($mesa_id === null && $codigo_identificacion === null) {
            $response->getBody()->write(json_encode(array('error' => 'ID de mesa o código de identificación no válido')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    
        // Verificar si la mesa existe
        $mesa = null;
        if ($mesa_id !== null) {
            $mesa = Mesa::TraerUnaMesa($mesa_id);
        } elseif ($codigo_identificacion !== null) {
            $mesa = Mesa::TraerUnaMesaPorCodigo($codigo_identificacion);
        }
    
        if (!$mesa) {
            $response->getBody()->write(json_encode(array('error' => 'Mesa no encontrada')));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    
        // Realizar la operación de dar de baja a la mesa utilizando una función adecuada
        $filasAfectadas = $mesa->BajaLogicaMesa();
    
        if ($filasAfectadas > 0) {
            // Mesa dada de baja exitosamente
            $response->getBody()->write(json_encode(array('mensaje' => 'Mesa dada de baja correctamente')));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            // Error al dar de baja la mesa
            $response->getBody()->write(json_encode(array('error' => 'Error al dar de baja la mesa')));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    // Método para modificar una mesa
    public function ModificarUno($request, $response, $args)
    {
        // Datos del cuerpo de la solicitud
        $data = $request->getParsedBody();

        // El ID de la mesa a modificar
        $mesa_id = $data['mesa_id'];

        // Crear una nueva mesa con los datos proporcionados
        $mesa = new Mesa();
        $mesa->mesa_id = $mesa_id;

        // Verificar si se tienen cada campo en el cuerpo y actualizar la mesa
        if (isset($data['codigo_identificacion'])) {
            $mesa->codigo_identificacion = $data['codigo_identificacion'];
        }

        if (isset($data['estado'])) {
            // Verificar si el estado proporcionado es válido
            $estadosValidos = array('con cliente esperando pedido', 'con cliente comiendo', 'con cliente pagando', 'cerrada');
            if (in_array($data['estado'], $estadosValidos)) {
                $mesa->estado = $data['estado'];
            } else {
                $response->getBody()->write(json_encode(array('error' => 'Estado no válido')));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
        }

        // Intentar modificar la mesa
        $idModificado = $mesa->ModificarMesaParametros();

        if ($idModificado !== false) {
            // Mesa modificada exitosamente
            $response->getBody()->write(json_encode(array('mesa_id' => $idModificado, 'mensaje' => 'Mesa modificada correctamente')));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            // Mesa no encontrada
            $response->getBody()->write(json_encode(array('error' => 'Mesa no encontrada')));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    }
   
    // Método para traer una mesa específica
    public function TraerUno($request, $response, $args)
    {
        try {
            // Parámetros de la consulta
            $queryParams = $request->getQueryParams();
    
            // Verificar si se proporcionó el 'mesa_id' en los parámetros de la consulta
            $mesa_id = isset($queryParams['mesa_id']) ? $queryParams['mesa_id'] : null;
    
            // Verificar que se haya proporcionado 'mesa_id'
            if ($mesa_id === null) {
                $response->getBody()->write(json_encode(array('error' => 'Se debe proporcionar el parámetro "mesa_id" en los parámetros de la consulta')));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
    
            // Instancia de conexión
            $conexionPDO = ConexionPDO::obtenerInstancia();
    
            // Preparar la consulta SQL para obtener por mesa_id
            $consulta = $conexionPDO->prepararConsulta("SELECT * FROM mesas WHERE mesa_id = :mesa_id");
            $consulta->bindValue(':mesa_id', $mesa_id, PDO::PARAM_INT);
    
            // Ejecutar la consulta
            $consulta->execute();
    
            // Obtener el resultado como un array asociativo
            $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
    
            // Verificar si se encontró la mesa
            if (!$resultado) {
                $response->getBody()->write(json_encode(array('error' => 'Mesa no encontrada')));
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
    
    // Método para traer todas las mesas
    public function TraerTodos($request, $response, $args)
    {
        // Todas las mesas
        $mesas = Mesa::TraerTodasLasMesas();

        // Construir la respuesta
        $response->getBody()->write(json_encode($mesas));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Método para agregar rutas al enrutador Slim
    public function agregarRutas(RouteCollectorProxy $group)
    {
        // Define las rutas relacionadas con las mesas
        $group->get('/mesa/{mesa_id}', [$this, 'TraerUno']);
        $group->get('/mesas', [$this, 'TraerTodos']);
        $group->post('/agregar-mesa', [$this, 'CargarUno']);
        $group->post('/modificar-mesa', [$this, 'ModificarUno']);
        $group->post('/borrar-mesa', [$this, 'BorrarUno']);
    }
}

?>

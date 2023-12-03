<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once __DIR__ . '/../models/Comanda.php'; 
require_once __DIR__ . '/../db/ConexionPDO.php'; 

class ComandaController
{
    public function CargarUno(Request $request, Response $response, array $args)
    {
        $data = $request->getParsedBody();

        if (empty($data['id_mesa']) || empty($data['idUsuario']) || empty($data['codigo']) || empty($data['id_estado']) || empty($data['tiempoEspera']) || empty($data['totalAPagar'])) {
            $response->getBody()->write(json_encode(array('error' => 'Todos los campos son obligatorios')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $nuevaComanda = new Comanda();
        $nuevaComanda->id_mesa = $data['id_mesa'];
        $nuevaComanda->idUsuario = $data['idUsuario'];
        $nuevaComanda->codigo = $data['codigo'];
        $nuevaComanda->id_estado = $data['id_estado'];
        $nuevaComanda->tiempoEspera = $data['tiempoEspera'];
        $nuevaComanda->totalAPagar = $data['totalAPagar'];

        try {
            $idNuevaComanda = $nuevaComanda->InsertarPedido();

            $result = array('mensaje' => 'Comanda insertada correctamente', 'idComanda' => $idNuevaComanda);

            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(array('error' => 'No se pudo insertar la comanda')));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function TraerTodos(Request $request, Response $response, array $args)
    {
        $comandas = Comanda::TraerTodosLosPedidos();

        $response->getBody()->write(json_encode($comandas));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno(Request $request, Response $response, array $args)
    {
        $queryParams = $request->getQueryParams();
        $idComanda = isset($queryParams['idComanda']) ? $queryParams['idComanda'] : null;

        if ($idComanda === null) {
            $response->getBody()->write(json_encode(array('error' => 'Se debe proporcionar el parámetro "idComanda"')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $comanda = Comanda::TraerUnPedido($idComanda);

        if (!$comanda) {
            $response->getBody()->write(json_encode(array('error' => 'Comanda no encontrada')));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode($comanda));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno(Request $request, Response $response, array $args)
    {
        $data = $request->getParsedBody();

        if (empty($data['id']) || empty($data['id_mesa']) || empty($data['idUsuario']) || empty($data['codigo']) || empty($data['id_estado']) || empty($data['tiempoEspera']) || empty($data['totalAPagar'])) {
            $response->getBody()->write(json_encode(array('error' => 'Todos los campos son obligatorios')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $comanda = new Comanda();
        $comanda->id = $data['id'];
        $comanda->id_mesa = $data['id_mesa'];
        $comanda->idUsuario = $data['idUsuario'];
        $comanda->codigo = $data['codigo'];
        $comanda->id_estado = $data['id_estado'];
        $comanda->tiempoEspera = $data['tiempoEspera'];
        $comanda->totalAPagar = $data['totalAPagar'];

        try {
            $idModificada = $comanda->ModificarPedidoParametros();

            if ($idModificada === false) {
                $response->getBody()->write(json_encode(array('error' => 'No se pudo modificar la comanda')));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            $result = array('mensaje' => 'Comanda modificada correctamente', 'idComanda' => $idModificada);

            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(array('error' => 'No se pudo modificar la comanda')));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function BorrarUno(Request $request, Response $response, array $args)
    {
        $data = $request->getParsedBody();
        $idComanda = isset($data['idComanda']) ? $data['idComanda'] : null;

        if ($idComanda === null && isset($args['idComanda'])) {
            $idComanda = $args['idComanda'];
        }

        $comanda = Comanda::TraerUnPedido($idComanda);

        if (!$comanda) {
            $response->getBody()->write(json_encode(array('error' => 'Comanda no encontrada')));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $filasEliminadas = $comanda->BorrarPedido();

        if ($filasEliminadas === 0) {
            $response->getBody()->write(json_encode(array('error' => 'No se pudo eliminar la comanda')));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }

        $result = array('mensaje' => 'Comanda eliminada correctamente');

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function CambiarEstadoPedido(Request $request, Response $response, array $args)
    {
        $data = $request->getParsedBody();

        if (empty($data['codigo_pedido']) || empty($data['id_estado'])) {
            $response->getBody()->write(json_encode(array('error' => 'Todos los campos son obligatorios')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $codigoPedido = $data['codigo_pedido'];
        $idEstado = $data['id_estado'];
        $tiempoRetraso = isset($data['tiempo_retraso']) ? $data['tiempo_retraso'] : null;

        try {
            $resultado = Comanda::CambiarEstadoPedido($codigoPedido, $idEstado, $tiempoRetraso);

            if ($resultado === false) {
                $response->getBody()->write(json_encode(array('error' => 'No se pudo cambiar el estado de la comanda')));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            $result = array('mensaje' => 'Estado de la comanda cambiado correctamente', 'codigoPedido' => $resultado);

            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(array('error' => 'No se pudo cambiar el estado de la comanda')));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function AsignarEmpleado(Request $request, Response $response, array $args)
    {
        $data = $request->getParsedBody();
    
        if (empty($data['idEmpleado']) || empty($data['tiempoEstimado']) || empty($data['codigoPedido'])) {
            $response->getBody()->write(json_encode(array('error' => 'Todos los campos son obligatorios')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    
        $idEmpleado = $data['idEmpleado'];
        $tiempoEstimado = $data['tiempoEstimado'];
        $codigoPedido = $data['codigoPedido'];
    
        try {
            // Crear una instancia de la clase Comanda
            $comanda = new Comanda();
    
            // Llamar al método AsignarEmpleado de la instancia
            $resultado = $comanda->AsignarEmpleado($idEmpleado, $tiempoEstimado, $codigoPedido);
    
            if ($resultado === false) {
                $response->getBody()->write(json_encode(array('error' => 'No se pudo asignar el empleado al pedido')));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }
    
            $result = array('mensaje' => 'Empleado asignado correctamente al pedido');
    
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(array('error' => 'No se pudo asignar el empleado al pedido')));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
    public function InsertarProductosAPedido(Request $request, Response $response, array $args)
    {
        $data = $request->getParsedBody();

        if (empty($data['valuesString'])) {
            $response->getBody()->write(json_encode(array('error' => 'El campo "valuesString" es obligatorio')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $valuesString = $data['valuesString'];

        try {
            $resultado = Comanda::InsertarProductosAPedido($valuesString);

            if ($resultado === false) {
                $response->getBody()->write(json_encode(array('error' => 'No se pudo insertar productos al pedido')));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            $result = array('mensaje' => 'Productos insertados correctamente al pedido');

            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(array('error' => 'No se pudo insertar productos al pedido')));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function IncrementarCantPedido(Request $request, Response $response, array $args)
    {
        $data = $request->getParsedBody();

        if (empty($data['valores'])) {
            $response->getBody()->write(json_encode(array('error' => 'El campo "valores" es obligatorio')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $valores = $data['valores'];

        try {
            Comanda::IncrementarCantPedido($valores);

            $result = array('mensaje' => 'Cantidad de productos en el pedido incrementada correctamente');

            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(array('error' => 'No se pudo incrementar la cantidad de productos en el pedido')));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function ObtenerProductosPorPedido(Request $request, Response $response, array $args)
    {
        $queryParams = $request->getQueryParams();
        $idPedido = isset($queryParams['idPedido']) ? $queryParams['idPedido'] : null;

        if ($idPedido === null) {
            $response->getBody()->write(json_encode(array('error' => 'Se debe proporcionar el parámetro "idPedido"')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $productos = Comanda::ObtenerProductosPorPedido($idPedido);

        $response->getBody()->write(json_encode($productos));
        return $response->withHeader('Content-Type', 'application/json');
    }



    public function ObtenerPedidosConRetraso(Request $request, Response $response, array $args)
    {
        $pedidosConRetraso = Comanda::ObtenerPedidosConRetraso();

        $response->getBody()->write(json_encode($pedidosConRetraso));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ObtenerPedidosSinRetraso(Request $request, Response $response, array $args)
    {
        $pedidosSinRetraso = Comanda::ObtenerPedidosSinRetraso();

        $response->getBody()->write(json_encode($pedidosSinRetraso));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function codigoRepetido(Request $request, Response $response, array $args)
    {
        $data = $request->getParsedBody();

        if (empty($data['codigo'])) {
            $response->getBody()->write(json_encode(array('error' => 'El campo "codigo" es obligatorio')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $codigoRepetido = Comanda::codigoRepetido($data['codigo']);

        $response->getBody()->write(json_encode(array('codigoRepetido' => $codigoRepetido)));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerPedidosPorEstado(Request $request, Response $response, array $args)
    {
        $queryParams = $request->getQueryParams();
        $estadoPedido = isset($queryParams['estado_pedido']) ? $queryParams['estado_pedido'] : null;

        if ($estadoPedido === null) {
            $response->getBody()->write(json_encode(array('error' => 'Se debe proporcionar el parámetro "estado_pedido"')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $pedidosPorEstado = Comanda::TraerPedidosPorEstado($estadoPedido);

        $response->getBody()->write(json_encode($pedidosPorEstado));
        return $response->withHeader('Content-Type', 'application/json');
    }

}

?>
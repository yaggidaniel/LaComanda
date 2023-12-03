<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

require_once __DIR__ . '/../models/Caja.php'; 
require_once __DIR__ . '/../middlewares/AuthJWT.php';
require_once __DIR__ . '/../Interfaces/IApiUsable.php';

class CajaController
{
    public function CargarUno($request, $response, $args)
    {
        // Verifica el token y el acceso del usuario
        $this->verificarAcceso($request, 'Socio');

        // Obtener datos de la nueva caja desde la solicitud
        $data = $request->getParsedBody();

        // Validar que se proporcionen todos los campos necesarios
        if (empty($data['fecha']) || empty($data['valor_total'])) {
            $response->getBody()->write(json_encode(array('error' => 'Todos los campos son obligatorios')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Crear una nueva instancia de Caja con los datos proporcionados
        $nuevaCaja = new Caja();
        $nuevaCaja->fecha = $data['fecha'];
        $nuevaCaja->valor_total = $data['valor_total'];

        try {
            $idNuevaCaja = $nuevaCaja->ActualizarOInsertarValorTotal();

            $result = array('mensaje' => 'Valor total actualizado correctamente', 'idCaja' => $idNuevaCaja);

            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(array('error' => 'No se pudo actualizar el valor total')));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function TraerUno($request, $response, $args)
    {
        // Obtener la fecha de los parámetros de consulta
        $queryParams = $request->getQueryParams();
        $fecha = isset($queryParams['fecha']) ? $queryParams['fecha'] : null;

        // Verificar que se haya proporcionado el parámetro fecha
        if ($fecha === null) {
            $response->getBody()->write(json_encode(array('error' => 'Se debe proporcionar el parámetro "fecha"')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Obtener el valor total por fecha desde la base de datos
        $valorTotal = Caja::ObtenerValorTotalPorFecha($fecha);

        // Verificar si se encontró el valor total
        if (!$valorTotal) {
            $response->getBody()->write(json_encode(array('error' => 'Valor total no encontrado')));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        // Construir la respuesta
        $response->getBody()->write(json_encode($valorTotal));
        return $response->withHeader('Content-Type', 'application/json');
    }



    // Implementación de la interfaz para obtener todos los elementos
    public function TraerTodos($request, $response, $args)
    {
        // Obtener todos los registros de valor total desde la base de datos
        $valoresTotales = Caja::ObtenerTodos();

        // Construir la respuesta
        $response->getBody()->write(json_encode($valoresTotales));
        return $response->withHeader('Content-Type', 'application/json');
    }




    // Verifica el token y el acceso del usuario
    private function verificarAcceso($request, $tipoUsuario)
    {
        $header = $request->getHeaderLine('authorization');
        $response = new \Slim\Psr7\Response();

        if (empty($header)) {
            $response->getBody()->write(json_encode(array("error" => "No se ingresó el token")));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        } else {
            $token = trim(explode("Bearer", $header)[1]);

            if (AuthJWT::VerifyToken($token)) {
                $data = AuthJWT::getData($token);

                if ($data->tipo_usuario != $tipoUsuario) {
                    $response->getBody()->write(json_encode(array("error" => "No tiene acceso, no es $tipoUsuario")));
                    return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
                }
            } else {
                $response->getBody()->write(json_encode(array("error" => "Token inválido")));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }
        }
    }
}

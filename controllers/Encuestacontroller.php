<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once __DIR__ . '/../models/Encuesta.php'; 
require_once __DIR__ . '/../db/ConexionPDO.php'; 

class EncuestaController
{
    public function CrearEncuesta(Request $request, Response $response, array $args)
    {
        $data = $request->getParsedBody();

        if (empty($data['idCliente']) || empty($data['id_comanda']) || empty($data['comentario']) || empty($data['puntuacion_mesa']) || empty($data['puntuacion_restaurante']) || empty($data['puntuacion_mozo']) || empty($data['puntuacion_cocinero'])) {
            $response->getBody()->write(json_encode(array('error' => 'Todos los campos son obligatorios')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $nuevaEncuesta = new Encuesta();
        $nuevaEncuesta->idCliente = $data['idCliente'];
        $nuevaEncuesta->id_comanda = $data['id_comanda'];
        $nuevaEncuesta->comentario = $data['comentario'];
        $nuevaEncuesta->puntuacion_restaurante = $data['puntuacion_restaurante'];

        try {
            $idNuevaEncuesta = $nuevaEncuesta->InsertarEncuesta();

            $result = array('mensaje' => 'Encuesta creada correctamente', 'idEncuesta' => $idNuevaEncuesta);

            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(array('error' => 'No se pudo crear la encuesta')));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function ObtenerEncuestaPorComanda(Request $request, Response $response, array $args)
    {
        $queryParams = $request->getQueryParams();
        $idComanda = isset($queryParams['id_comanda']) ? $queryParams['id_comanda'] : null;

        if ($idComanda === null) {
            $response->getBody()->write(json_encode(array('error' => 'Se debe proporcionar el parámetro "id_comanda"')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $encuesta = Encuesta::TraerEncuestaPorComanda($idComanda);

        if (!$encuesta) {
            $response->getBody()->write(json_encode(array('error' => 'Encuesta no encontrada')));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode($encuesta));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos(Request $request, Response $response, array $args)
    {
        $encuestas = Encuesta::TraerTodos();

        $response->getBody()->write(json_encode($encuestas));
        return $response->withHeader('Content-Type', 'application/json');
    }


}
?>
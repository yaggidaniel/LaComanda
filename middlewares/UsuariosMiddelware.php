<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once './middlewares/AuthJWT.php';

class UsuariosMiddleware{

    // Middleware para verificar el acceso de un usuario con tipo "Socio"
    public function VerificaAccesoSocio(Request $request, RequestHandler $handler)
    {
        $header = $request->getHeaderLine('authorization'); 
        $response = new Response();

        if(empty($header)){
            // Si no se proporciona el token, devuelve un error
            $response->getBody()->write(json_encode(array("error" => "No se ingreso el token")));
            $response = $response->withStatus(401);
        }
        else{
            $token = trim(explode("Bearer", $header)[1]);
            // Verifica el token usando la clase AuthJWT
            if(AuthJWT::VerifyToken($token)){
                $data = AuthJWT::getData($token);
                // Si el tipo de usuario es "Socio", permite el acceso
                if($data->tipo_usuario == "Socio")
                    $response = $handler->handle($request);
                else {
                    // Si no es "Socio", devuelve un error
                    $response->getBody()->write(json_encode(array("error" => "No tiene acceso, no es socio")));
                    $response = $response->withStatus(401);
                }
            }
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Middleware para verificar el acceso de un usuario con tipo "Mozo"
    public function VerificaAccesoMozo(Request $request, RequestHandler $handler)
    {
        $header = $request->getHeaderLine('authorization'); 
        $response = new Response();

        if(empty($header)){
            $response->getBody()->write(json_encode(array("error" => "No se ingreso el token")));
            $response = $response->withStatus(401);
        }
        else{
            $token = trim(explode("Bearer", $header)[1]);
            
            if(AuthJWT::VerifyToken($token)){
                $data = AuthJWT::GetData($token);
                if($data->tipo_usuario == "Mozo")
                    $response = $handler->handle($request);
                else {
                    $response->getBody()->write(json_encode(array("error" => "No tiene acceso, no es mozo")));
                    $response = $response->withStatus(401);
                }
            }
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Middleware para verificar el acceso de un usuario con tipo "Cocinero"
    public function VerificaAccesoCocinero(Request $request, RequestHandler $handler)
    {
        $header = $request->getHeaderLine('authorization'); 
        $response = new Response();

        if(empty($header)){
            $response->getBody()->write(json_encode(array("error" => "No se ingreso el token")));
            $response = $response->withStatus(401);
        }
        else{
            $token = trim(explode("Bearer", $header)[1]);
            
            if(AuthJWT::VerifyToken($token)){
                $data = AuthJWT::GetData($token);
                if($data->tipo_usuario == "Cocinero")
                    $response = $handler->handle($request);
                else {
                    $response->getBody()->write(json_encode(array("error" => "No tiene acceso, no es cocinero")));
                    $response = $response->withStatus(401);
                }
            }
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Middleware para verificar el acceso de un usuario con tipo "Bartender"
    public function VerificaAccesoBartender(Request $request, RequestHandler $handler)
    {
        $header = $request->getHeaderLine('authorization'); 
        $response = new Response();

        if(empty($header)){
            $response->getBody()->write(json_encode(array("error" => "No se ingreso el token")));
            $response = $response->withStatus(401);
        }
        else{
            $token = trim(explode("Bearer", $header)[1]);
            
            if(AuthJWT::VerifyToken($token)){
                $data = AuthJWT::GetData($token);
                if($data->tipo_usuario == "Bartender")
                    $response = $handler->handle($request);
                else {
                    $response->getBody()->write(json_encode(array("error" => "No tiene acceso, no es bartender")));
                    $response = $response->withStatus(401);
                }
            }
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Middleware para verificar el acceso de un usuario con tipo "Cervecero"
    public function VerificaAccesoCervecero(Request $request, RequestHandler $handler)
    {
        $header = $request->getHeaderLine('authorization'); 
        $response = new Response();

        if(empty($header)){
            $response->getBody()->write(json_encode(array("error" => "No se ingreso el token")));
            $response = $response->withStatus(401);
        }
        else{
            $token = trim(explode("Bearer", $header)[1]);
            
            if(AuthJWT::VerifyToken($token)){
                $data = AuthJWT::GetData($token);
                if($data->tipo_usuario == "Cervecero")
                    $response = $handler->handle($request);
                else {
                    $response->getBody()->write(json_encode(array("error" => "No tiene acceso, no es cervecero")));
                    $response = $response->withStatus(401);
                }
            }
        }
        return $response->withHeader('Content-Type', 'application/json');
    }
}

?>

<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;


// Importa la clase Usuario y otros archivos necesarios
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../middlewares/AuthJWT.php';

class UsuarioController 
{
    // Verifica el token y el acceso del usuario
    private function verificarAcceso($request, $tipoUsuario)
    {
        $header = $request->getHeaderLine('authorization');
		$response = new \Slim\Psr7\Response();

        if (empty($header)) {
            $response->getBody()->write(json_encode(array("error" => "No se ingreso el token")));
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


	public function CargarUno(Request $request, Response $response, array $args)
	{
		// Verifica el token y el acceso del usuario
		$this->verificarAcceso($request, 'Socio');
	
		// Obtener datos del nuevo usuario desde la solicitud
		$data = $request->getParsedBody();
	
		// Validar que se proporcionen todos los campos necesarios
		if (empty($data['nombre']) || empty($data['mail']) || empty($data['clave']) || empty($data['puesto'])) {
			$response->getBody()->write(json_encode(array('error' => 'Todos los campos son obligatorios')));
			return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
		}
	
		// Validar el formato del correo electrónico
		if (!filter_var($data['mail'], FILTER_VALIDATE_EMAIL)) {
			$response->getBody()->write(json_encode(array('error' => 'Formato de correo electrónico no válido')));
			return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
		}
	
		// Verificar si el correo electrónico ya está en uso
		$usuarioExistente = Usuario::obtenerUsuarioMail($data['mail']);
		if ($usuarioExistente) {
			// Si el correo electrónico ya está en uso, devolver un error
			$response->getBody()->write(json_encode(array('error' => 'El correo electrónico ya está en uso')));
			return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
		}
	
		// Creo un array asociativo para validar que el puesto ingresado en el request sea válido
		$mapaPuestos = [
			'Bartender' => 1,
			'Cervecero' => 2,
			'Cocinero' => 3,
			'Mozo' => 4,
			'Socio' => 5,
		];
	
		if (!isset($mapaPuestos[$data['puesto']])) {
			$response->getBody()->write(json_encode(array('error' => 'Puesto no válido')));
			return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
		}
	
		// Crear una nueva instancia de Usuario con los datos proporcionados
		$nuevoUsuario = new Usuario();
		$nuevoUsuario->nombre = $data['nombre'];
		$nuevoUsuario->mail = $data['mail'];
		$nuevoUsuario->clave = password_hash($data['clave'], PASSWORD_DEFAULT); // Hashear la contraseña
		$nuevoUsuario->puesto = $data['puesto'];
		$nuevoUsuario->estado = 'Activo'; // estado predeterminado al crear
		$nuevoUsuario->idPuesto = $mapaPuestos[$data['puesto']];
		$nuevoUsuario->idEstado = 1; // estado predeterminado 1 Activo
		$nuevoUsuario->fecha_ingreso = date('Y-m-d'); // Fecha de ingreso actual
	
		// Intentar crear el nuevo usuario en la base de datos
		try {
			$idNuevoUsuario = $nuevoUsuario->crearUsuario();
	
			// Construir la respuesta
			$result = array('mensaje' => 'Usuario creado correctamente', 'idUsuario' => $idNuevoUsuario);
	
			$response->getBody()->write(json_encode($result));
			return $response->withHeader('Content-Type', 'application/json');
		} catch (Exception $e) {
			// En caso de error, construir una respuesta de error
			$response->getBody()->write(json_encode(array('error' => 'No se pudo crear el usuario')));
			return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
		}
	}
	

	public function ModificarUno(Request $request, Response $response, array $args)
    {
        // Verifica el token y el acceso del usuario
        $this->verificarAcceso($request, 'Socio');

        $idUsuario = $args['idUsuario'];

        // Verifica si el usuario existe
        $usuario = Usuario::obtenerUsuarioId($idUsuario);

        if (!$usuario) {
            $response->getBody()->write(json_encode(array('error' => 'Usuario no encontrado')));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        // suspender al usuario
        Usuario::modificarUsuario('Suspendido', 2, $idUsuario);

        // Construye la respuesta
        $result = array('mensaje' => 'Usuario suspendido correctamente');

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno(Request $request, Response $response, array $args)
    {
        // Verifica el token y el acceso del usuario
        $this->verificarAcceso($request, 'Socio');

        $idUsuario = $args['idUsuario'];

        // Verifica si el usuario existe
        $usuario = Usuario::obtenerUsuarioId($idUsuario);

        if (!$usuario) {
            $response->getBody()->write(json_encode(array('error' => 'Usuario no encontrado')));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        // Realiza la operación de dar de baja al usuario
        Usuario::modificarUsuario('Baja', 3, $idUsuario);

        // Construye la respuesta
        $result = array('mensaje' => 'Usuario dado de baja correctamente');

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }


	





    // Función para obtener días y horarios de ingreso de los empleados
    public function obtenerDiasHorariosIngreso(Request $request, Response $response, array $args)
    {
        // Verifica el token y el acceso del usuario
        $this->verificarAcceso($request, 'Socio');

        $result = array(); 

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Función para obtener la cantidad de operaciones de todos por sector
    public function obtenerCantidadOperacionesPorSector(Request $request, Response $response, array $args)
    {
        // Verifica el token y el acceso del usuario
        $this->verificarAcceso($request, 'Socio');

        $result = array(); 
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Función para obtener la cantidad de operaciones por sector, listada por cada empleado
    public function obtenerCantidadOperacionesPorEmpleado(Request $request, Response $response, array $args)
    {
        // Verifica el token y el acceso del usuario
        $this->verificarAcceso($request, 'Socio');

        $result = array(); 

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Función para obtener la cantidad de operaciones de cada empleado por separado
    public function obtenerCantidadOperacionesPorSeparado(Request $request, Response $response, array $args)
    {
        // Verifica el token y el acceso del usuario
        $this->verificarAcceso($request, 'Socio');

        $result = array(); 

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }


	public function agregarRutas(RouteCollectorProxy $group)
    {
        // Define las rutas relacionadas con el usuario 
		$group->post('/dar-de-alta-usuario', [$this, 'CargarUno']);
        $group->post('/suspender/{idUsuario}', [$this, 'ModificarUno']);
        $group->post('/dar-de-baja/{idUsuario}', [$this, 'BorrarUno']);


        $group->get('/dias-horarios', [$this, 'obtenerDiasHorariosIngreso']);
        $group->get('/operaciones/sector', [$this, 'obtenerCantidadOperacionesPorSector']);
        $group->get('/operaciones/sector-empleado', [$this, 'obtenerCantidadOperacionesPorEmpleado']);
        $group->get('/operaciones/individual', [$this, 'obtenerCantidadOperacionesPorSeparado']);

    }

}

?>

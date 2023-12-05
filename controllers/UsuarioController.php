<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Importa la clase Usuario y otros archivos necesarios
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../middlewares/AuthJWT.php';
require_once __DIR__ . '/../Interfaces/IApiUsable.php';

class UsuarioController implements IApiUsable
{

     // Implementación de la interfaz para crear un nuevo elemento
     public function CargarUno($request, $response, $args)
     {
         // Verifica el token y el acceso del usuario
         $this->verificarAcceso($request, 'Socio');
 
         // Obtener datos del nuevo usuario desde la solicitud
         $data = $request->getParsedBody();
 
         // Validar que se proporcionen todos los campos necesarios
         if (empty($data['nombre']) || empty($data['mail']) || empty($data['clave']) || empty($data['sector'])) {
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
 
         // Creo un array asociativo para chequear que el sector ingresado en el request sea válido
         $mapaSector = [
             'Bartender' => 1,
             'Cervecero' => 2,
             'Cocinero' => 3,
             'Mozo' => 4,
             'Socio' => 5,
         ];
         
         // Si se ingresa un sector que no está en el array da error.
         if (!isset($mapaSector[$data['sector']])) {
             $response->getBody()->write(json_encode(array('error' => 'Sector no válido')));
             return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
         }
 
         // Crear una nueva instancia de Usuario con los datos proporcionados
         $nuevoUsuario = new Usuario();
         $nuevoUsuario->nombre = $data['nombre'];
         $nuevoUsuario->mail = $data['mail'];
         $nuevoUsuario->clave = password_hash($data['clave'], PASSWORD_DEFAULT); // Hashear la contraseña
         $nuevoUsuario->sector = $data['sector'];
         $nuevoUsuario->estado = 'Activo'; // estado predeterminado al crear
         $nuevoUsuario->idSector = $mapaSector[$data['sector']];
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
             // Controlo el error en la creacion
             $response->getBody()->write(json_encode(array('error' => 'No se pudo crear el usuario')));
             return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
         }
     }


// Implementación de la interfaz para borrar un elemento
    public function BorrarUno($request, $response, $args)
    {
        // Verifica el token y el acceso del usuario
        $this->verificarAcceso($request, 'Socio');

        //   el idUsuario del cuerpo de la solicitud (si se envía como parte del cuerpo)
        $data = $request->getParsedBody();
        $idUsuario = isset($data['idUsuario']) ? $data['idUsuario'] : null;

        // Si no se encuentra en el cuerpo, intenta obtenerlo de $args
        if ($idUsuario === null && isset($args['idUsuario'])) {
            $idUsuario = $args['idUsuario'];
        }

        // Verifica si el usuario existe
        $usuario = Usuario::obtenerUsuarioId($idUsuario);

        if (!$usuario) {
            $response->getBody()->write(json_encode(array('error' => 'Usuario no encontrado')));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        // Verifica si el usuario ya está en estado "Baja"
        if ($usuario->estado === 'Baja') {
            $response->getBody()->write(json_encode(array('error' => 'Error, el usuario fue dado de baja previamente')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }


        // Realiza la operación de dar de baja al usuario utilizando la nueva función
        Usuario::borrarUsuario($idUsuario);

        // Construye la respuesta
        $result = array('mensaje' => 'Usuario dado de baja correctamente');

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }


    public function ModificarUno($request, $response, $args)
    {
        // Verifica el token y el acceso del usuario
        $this->verificarAcceso($request, 'Socio');
    
        //   los datos a modificar del cuerpo de la solicitud
        $data = $request->getParsedBody();
    
        // Verifica si el usuario proporcionó el idUsuario
        if (!isset($data['idUsuario'])) {
            $response->getBody()->write(json_encode(array('error' => 'Falta el parámetro idUsuario')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    
        // Obtiene el idUsuario del cuerpo
        $idUsuario = $data['idUsuario'];
    
        // Verifica si el usuario existe
        $usuario = Usuario::obtenerUsuarioId($idUsuario);
    
        if (!$usuario) {
            $response->getBody()->write(json_encode(array('error' => 'Usuario no encontrado')));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    
        // Creo un array asociativo para chequear que el sector ingresado en el request sea válido
        $mapaSector = [
            'Bartender' => 1,
            'Cervecero' => 2,
            'Cocinero' => 3,
            'Mozo' => 4,
            'Socio' => 5,
        ];
    
        // Creo un array asociativo para chequear que el estado ingresado en el request sea válido
        $mapaEstados = [
            'Activo' => 1,
            'Suspendido' => 2,
            'Baja' => 3,
        ];
    
        // Verificar y asignar el nuevo sector
        if (isset($data['sector'])) {
            if (!isset($mapaSector[$data['sector']])) {
                $response->getBody()->write(json_encode(array('error' => 'sector no válido')));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
            $usuario->sector = $data['sector'];
            $usuario->idSector = $mapaSector[$data['sector']];
        }
    
        // Verificar y asignar el nuevo estado
        if (isset($data['estado'])) {
            if (!isset($mapaEstados[$data['estado']])) {
                $response->getBody()->write(json_encode(array('error' => 'Estado no válido')));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
    
            $usuario->estado = $data['estado'];
    
            // Si el estado es Suspendido, grabar la fecha de salida
            if ($data['estado'] === 'Suspendido') {
                $fechaSalida = new DateTime();
                $usuario->fecha_salida = $fechaSalida->format('Y-m-d H:i:s');
            }
        }
    
        // Realiza la operación de modificar el usuario
        Usuario::modificarUsuario($usuario->estado, $usuario->idEstado, $idUsuario, $usuario->fecha_salida, $usuario->sector, $usuario->idSector);
    
        // Construye la respuesta
        $result = array('mensaje' => 'Usuario modificado correctamente');
    
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
    

    // Implementación de la interfaz para obtener todos los elementos
    public function TraerTodos($request, $response, $args)
    {
        //   una instancia de la conexión PDO
        $conexionPDO = ConexionPDO::obtenerInstancia();
    
        // Intenta realizar la consulta para obtener todos los elementos
        try {
            // Preparar la consulta SQL (sustituye 'tu_tabla' con el nombre real de tu tabla)
            $consulta = $conexionPDO->prepararConsulta("SELECT * FROM usuarios");
    
            // Ejecutar la consulta
            $consulta->execute();
    
            // Obtener todos los resultados como un array asociativo
            $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
    
            // Construir la respuesta
            $response->getBody()->write(json_encode($resultados));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            // En caso de error, construir una respuesta de error
            $response->getBody()->write(json_encode(array('error' => 'Error al obtener los elementos')));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }



    // Implementación de la interfaz para obtener un solo elemento
    public function TraerUno($request, $response, $args)
    {
        //   una instancia de la conexión PDO
        $conexionPDO = ConexionPDO::obtenerInstancia();

        //   el parámetro 'id' de los parámetros de consulta
        $queryParams = $request->getQueryParams();

         // Verifica si se proporcionó 'idUsuario' o 'mail'
        $idUsuario = isset($queryParams['idUsuario']) ? $queryParams['idUsuario'] : null;
        $mail = isset($queryParams['mail']) ? $queryParams['mail'] : null;

        // Verifica que se haya proporcionado al menos uno de los parámetros
        if ($idUsuario === null && $mail === null) {
            $response->getBody()->write(json_encode(array('error' => 'Se debe proporcionar al menos un parámetro: "idUsuario" o "mail"')));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Intenta realizar la consulta para obtener un solo elemento por ID o correo electrónico
        try {
            // Verifica si se proporciona el ID del usuario o el correo electrónico
            if ($idUsuario !== null) {
                // Preparar la consulta SQL para obtener por ID
                $consulta = $conexionPDO->prepararConsulta("SELECT * FROM usuarios WHERE idUsuario = :idUsuario");
                $consulta->bindValue(':idUsuario', $idUsuario, PDO::PARAM_INT);
            } elseif ($mail !== null) {
                // Preparar la consulta SQL para obtener por correo electrónico
                $consulta = $conexionPDO->prepararConsulta("SELECT * FROM usuarios WHERE mail = :mail");
                $consulta->bindValue(':mail', $mail, PDO::PARAM_STR);
            } else {
                // No se proporcionó ni ID ni correo electrónico
                $response->getBody()->write(json_encode(array('error' => 'Se debe proporcionar al menos un parámetro: "idUsuario" o "mail"')));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            // Ejecutar la consulta
            $consulta->execute();

            // Obtener el resultado como un array asociativo
            $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

            // Verificar si se encontró el usuario
            if (!$resultado) {
                $response->getBody()->write(json_encode(array('error' => 'Usuario no encontrado')));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            // Construir la respuesta
            $response->getBody()->write(json_encode($resultado));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            // En caso de error, construir una respuesta de error
            $response->getBody()->write(json_encode(array('error' => 'Error al obtener el elemento')));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
 

    // Implementación de la interfaz para obtener días y horarios de ingreso de los empleados
    public function obtenerDiasHorariosIngreso(Request $request, Response $response, array $args)
    {
        // Verifica el token y el acceso del usuario
        $this->verificarAcceso($request, 'Socio');

        $result = array();

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Implementación de la interfaz para obtener la cantidad de operaciones de todos por sector
    public function obtenerCantidadOperacionesPorSector($request, $response, array $args)
    {
        // Verifica el token y el acceso del usuario
        $this->verificarAcceso($request, 'Socio');

        $result = array();
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Implementación de la interfaz para obtener la cantidad de operaciones por sector, listada por cada empleado
    public function obtenerCantidadOperacionesPorEmpleado($request, $response, $args)
    {
        // Verifica el token y el acceso del usuario
        $this->verificarAcceso($request, 'Socio');

        $result = array();

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Implementación de la interfaz para obtener la cantidad de operaciones de cada empleado por separado
    public function obtenerCantidadOperacionesPorSeparado($request, $response, $args)
    {
        // Verifica el token y el acceso del usuario
        $this->verificarAcceso($request, 'Socio');

        $result = array();

        $response->getBody()->write(json_encode($result));
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

?>

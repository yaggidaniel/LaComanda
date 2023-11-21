<?php 

require_once __DIR__ . '/../db/ConexionPDO.php';
require_once __DIR__ . '/../controllers/UsuarioController.php';



class Usuario {
    public $idUsuario;
    public $nombre;
    public $mail;
    public $clave;
    public $puesto;
    public $estado;
    public $idPuesto;
    public $idEstado;
    public $fecha_ingreso;
    public $fecha_salida;

    // Obtener el ID del puesto
    public function GetIdPuesto() {
        return $this->idPuesto;
    }

    // Obtener el nombre del puesto
    public function GetPuesto() {
        return $this->puesto;
    }

    // Obtener el ID del usuario
    public function GetIdUsuario() {
        return $this->idUsuario;
    }

    // Crear un nuevo usuario
    public function crearUsuario() {
        // Obtener una instancia de ConexionPDO
        $objConexionPDO = ConexionPDO::obtenerInstancia();
        // Preparar la consulta SQL para insertar un nuevo usuario
        $consulta = $objConexionPDO->prepararConsulta("INSERT INTO usuarios (idUsuario, nombre, mail, clave, puesto, estado, idEstado, idPuesto, fecha_ingreso, fecha_salida) VALUES (:idUsuario, :nombre, :mail, :clave, :puesto, :estado, :idEstado, :idPuesto, :fecha_ingreso, :fecha_salida)");

        // Asignar valores a los parámetros de la consulta
        $consulta->bindValue(':idUsuario', $this->idUsuario, PDO::PARAM_INT);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':mail', $this->mail, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $this->clave, PDO::PARAM_INT);
        $consulta->bindValue(':puesto', $this->puesto, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':idPuesto', $this->idPuesto, PDO::PARAM_INT);
        $consulta->bindValue(':idEstado', $this->idEstado, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_ingreso', $this->fecha_ingreso, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_salida', $this->fecha_salida, PDO::PARAM_STR);
        $consulta->execute();

        // Devolver el último ID insertado
        return $objConexionPDO->obtenerUltimoId();
    }

    // Obtener todos los usuarios
    public static function obtenerTodos() {
        $objConexionPDO = ConexionPDO::obtenerInstancia();
        $consulta = $objConexionPDO->prepararConsulta("SELECT idUsuario, nombre, mail, clave, puesto, estado, idEstado, idPuesto, fecha_ingreso, fecha_salida FROM usuarios");
        $consulta->execute();

        // Devolver un arreglo de objetos de tipo Usuario
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }

    // Obtener un usuario por correo electrónico
    public static function obtenerUsuarioMail($mail) {
        $objConexionPDO = ConexionPDO::obtenerInstancia();
        $consulta = $objConexionPDO->prepararConsulta("SELECT idUsuario, nombre, mail, clave, puesto, estado, idEstado, idPuesto, fecha_ingreso, fecha_salida FROM usuarios WHERE mail = :mail");
        $consulta->bindValue(':mail', $mail, PDO::PARAM_STR);
        $consulta->execute();

        // Devolver un objeto de tipo Usuario
        return $consulta->fetchObject('Usuario');
    }

    // Obtener un usuario por su ID
    public static function obtenerUsuarioId($idUsuario) {
        $objConexionPDO = ConexionPDO::obtenerInstancia();
        $consulta = $objConexionPDO->prepararConsulta("SELECT idUsuario, nombre, mail, clave, puesto, estado, idPuesto, idEstado, fecha_ingreso, fecha_salida FROM usuarios WHERE idUsuario = :idUsuario");
        $consulta->bindValue(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $consulta->execute();

        // Devolver un objeto de tipo Usuario
        return $consulta->fetchObject('Usuario');
    }

    // Modificar el estado, ID del estado, fecha de salida, puesto e ID del puesto de un usuario
    public static function modificarUsuario($estado, $numEstado, $idUsuario, $fecha_salida, $puesto, $idPuesto) {
        $objConexionPDO = ConexionPDO::obtenerInstancia();

        // Construir la consulta base
        $consultaBase = "UPDATE usuarios SET estado = :estado, idEstado = :numEstado";

        // Verificar si se proporciona una fecha de salida
        if ($fecha_salida !== null) {
            $consultaBase .= ", fecha_salida = :fecha_salida";
        }

        // Verificar si se proporciona un puesto e ID del puesto
        if ($puesto !== null && $idPuesto !== null) {
            $consultaBase .= ", puesto = :puesto, idPuesto = :idPuesto";
        }

        // Agregar la condición WHERE
        $consultaBase .= " WHERE idUsuario = :idUsuario";

        // Preparar la consulta
        $consulta = $objConexionPDO->prepararConsulta($consultaBase);

        // Asignar los valores
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->bindValue(':numEstado', $numEstado, PDO::PARAM_INT);
        $consulta->bindValue(':idUsuario', $idUsuario, PDO::PARAM_INT);

        // Asignar la fecha de salida si se proporciona
        if ($fecha_salida !== null) {
            $consulta->bindValue(':fecha_salida', $fecha_salida, PDO::PARAM_STR);
        }

        // Asignar el puesto e ID del puesto si se proporciona
        if ($puesto !== null && $idPuesto !== null) {
            $consulta->bindValue(':puesto', $puesto, PDO::PARAM_STR);
            $consulta->bindValue(':idPuesto', $idPuesto, PDO::PARAM_INT);
        }

        // Ejecutar la consulta
        $consulta->execute();
    }


    // Borrar un usuario cambiando su estado, ID del estado y fecha de baja
    public static function borrarUsuario($idUsuario) {
        $fechaSalida = date('Y-m-d H:i:s');
        $estado = 'Baja';
        $idEstado = 3;

        $objConexionPDO = ConexionPDO::obtenerInstancia();
        $consulta = $objConexionPDO->prepararConsulta("UPDATE usuarios SET fecha_salida = :fechaSalida, estado = :estado, idEstado = :idEstado WHERE idUsuario = :idUsuario");
        $consulta->bindValue(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $consulta->bindValue(':fechaSalida', $fechaSalida, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->bindValue(':idEstado', $idEstado, PDO::PARAM_INT);
        $consulta->execute();
    }

    // Obtener información de un usuario por su ID
    public static function ObtenerUsuarioLogIn($idUsuario) {
        $objConexionPDO = ConexionPDO::obtenerInstancia();
        $consulta = $objConexionPDO->prepararConsulta("SELECT idUsuario, nombre, puesto, estado, fecha_ingreso, fecha_salida FROM usuarios WHERE idUsuario = :idUsuario");
        $consulta->bindValue(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $consulta->execute();

        // Devolver un objeto de tipo Usuario
        return $consulta->fetchObject('Usuario');
    }

    // Obtener un usuario al azar por su puesto
    public static function ObtenerUnUsuarioPorPuesto($puesto) {
        $lista = self::obtenerTodos();
        $array = array();
        foreach ($lista as $aux) {
            if ($aux->GetIdPuesto() == $puesto) {
                array_push($array, $aux);
            }
        }
        $random = random_int(1, count($array));
        if ($random == count($array))
            return $array[$random - 1]->GetIdUsuario();
        else
            return $array[$random]->GetIdUsuario();
    }
}



?>
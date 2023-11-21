<?php
require_once __DIR__ . '/../db/ConexionPDO.php';
require_once __DIR__ . '/../controllers/MesaController.php';


class Mesa
{
    public $mesa_id;
    public $codigo_identificacion;
    public $estado;
    public $activo;
    public $eliminada;
    
    public function InsertarMesa()
    {
        // Verifica si la mesa con el mismo código ya existe
        $mesaExistente = self::TraerUnaMesaPorCodigo($this->codigo_identificacion);
    
        if ($mesaExistente !== null) {
            // La mesa ya existe, devolver un error
            return false;
        }
    
        // La mesa no existe, procede con la inserción
        $objetoAccesoDato = ConexionPDO::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("INSERT INTO mesas (codigo_identificacion, estado, activo) VALUES (:codigo_identificacion, :estado, 1)");
        $consulta->bindValue(':codigo_identificacion', $this->codigo_identificacion, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->execute();
    
        // Retorna el ID del último producto insertado.
        return $objetoAccesoDato->obtenerUltimoId();
    }
    // Obtiene todas las mesas de la base de datos
    public static function TraerTodasLasMesas()
    {
        $objetoAccesoDato = ConexionPDO::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT * FROM mesas");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Mesa");
    }

    // Obtiene una mesa específica por su ID
    public static function TraerUnaMesa($id)
    {
        $objetoAccesoDato = ConexionPDO::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT * FROM mesas WHERE mesa_id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        $mesaBuscada = $consulta->fetchObject('Mesa');

        return $mesaBuscada;
    }

    // Modifica los datos de una mesa específica por su ID
    public function ModificarMesaParametros()
    {
        $objetoAccesoDato = ConexionPDO::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("UPDATE mesas SET codigo_identificacion = :codigo, estado = :estado WHERE mesa_id = :id");
        $consulta->bindValue(':id', $this->mesa_id, PDO::PARAM_INT);
        $consulta->bindValue(':codigo', $this->codigo_identificacion, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $resultado = $consulta->execute();

        $filasAfectadas = $consulta->rowCount();
        if ($filasAfectadas === 0 || !$resultado) {
            return false;
        }
        return $this->mesa_id;
    }

    // Elimina una mesa específica por su ID (borrado lógico)
    public function BajaLogicaMesa()
    {
        $objetoAccesoDato = ConexionPDO::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("UPDATE mesas SET eliminada = 1 WHERE mesa_id = :id");
        $consulta->bindValue(':id', $this->mesa_id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->rowCount();
    }
    
    // para validar que no se carguen dos veces la misma mesa
    public static function TraerUnaMesaPorCodigo($codigo_identificacion)
    {
        $objetoAccesoDato = ConexionPDO::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT * FROM mesas WHERE codigo_identificacion = :codigo_identificacion");
        $consulta->bindValue(':codigo_identificacion', $codigo_identificacion, PDO::PARAM_STR);
        $consulta->execute();
        
        // Agregar este bloque para depuración
        $errorInfo = $consulta->errorInfo();
        if ($errorInfo[0] != '00000') {
            die("Error en la consulta: " . json_encode($errorInfo));
        }
    
        $mesaBuscada = $consulta->fetchObject('Mesa');
    
        return $mesaBuscada;
    }

    // Obtiene mesas con un estado específico
    public static function TraerMesasPorEstado($estado)
    {
        $objetoAccesoDato = ConexionPDO::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT * FROM mesas WHERE estado = :estado");
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Mesa");
    }


    
}
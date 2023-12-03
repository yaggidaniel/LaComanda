<?php

require_once __DIR__ . '/../db/ConexionPDO.php';


class Caja {
    public $id_valor_total;
    public $fecha;
    public $valor_total;

    public function ActualizarOInsertarValorTotal() {
        // Verifica si ya existe un registro para la fecha dada
        $valorTotalExistente = self::ObtenerValorTotalPorFecha($this->fecha);
    
        $objetoAccesoDato = ConexionPDO::obtenerInstancia();
    
        if ($valorTotalExistente !== null) {
            // Ya hay un registro para esta fecha, actualiza el valor total
            $consulta = $objetoAccesoDato->prepararConsulta("UPDATE valor_total_por_fecha SET valor_total = valor_total + :valor_total WHERE fecha = :fecha");
            $consulta->bindValue(':fecha', $this->fecha, PDO::PARAM_STR);
            $consulta->bindValue(':valor_total', $this->valor_total, PDO::PARAM_INT);
            $consulta->execute();
        } else {
            // No hay un registro para esta fecha, inserta uno nuevo
            $consulta = $objetoAccesoDato->prepararConsulta("INSERT INTO valor_total_por_fecha (fecha, valor_total) VALUES (:fecha, :valor_total)");
            $consulta->bindValue(':fecha', $this->fecha, PDO::PARAM_STR);
            $consulta->bindValue(':valor_total', $this->valor_total, PDO::PARAM_INT);
            $consulta->execute();
        }
    
        // Retorna el ID del último registro insertado o actualizado.
        return $objetoAccesoDato->obtenerUltimoId();
    }

    public static function ObtenerValorTotalPorFecha($fecha) {
        $objetoAccesoDato = ConexionPDO::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT fecha, valor_total FROM valor_total_por_fecha WHERE fecha = :fecha");
        $consulta->bindValue(':fecha', $fecha, PDO::PARAM_STR);
        $consulta->execute();
        $valorTotalBuscado = $consulta->fetch(PDO::FETCH_ASSOC);

        return $valorTotalBuscado;
    }

    // Método para obtener el valor total entre dos fechas
    public static function ObtenerValorTotalEntreFechas($fechaInicio, $fechaFin) {
        $objetoAccesoDato = ConexionPDO::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT SUM(valor_total) as total FROM valor_total_por_fecha WHERE fecha BETWEEN :fechaInicio AND :fechaFin");
        $consulta->bindValue(':fechaInicio', $fechaInicio, PDO::PARAM_STR);
        $consulta->bindValue(':fechaFin', $fechaFin, PDO::PARAM_STR);
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

        // Retorna la suma total
        return $resultado['total'];
    }

    // Método para obtener todos los registros de valor total
    public static function ObtenerTodos() {
        $objetoAccesoDato = ConexionPDO::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT * FROM valor_total_por_fecha");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "ValorTotalPorFecha");
    }
}
?>

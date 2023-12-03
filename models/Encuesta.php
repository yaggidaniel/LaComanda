<?php

require_once __DIR__ . '/../db/ConexionPDO.php';

class Encuesta {
    public $id_encuesta;
    public $idCliente;
    public $id_comanda;
    public $comentario;
    public $puntuacion_restaurante;

    // Método para insertar una nueva encuesta en la base de datos
    public function InsertarEncuesta() {
        // Verifica si ya existe una encuesta para la comanda
        $encuestaExistente = self::TraerEncuestaPorComanda($this->id_comanda);

        if ($encuestaExistente !== null) {
            // Ya hay una encuesta para esta comanda, devolver un error o manejar según tu lógica
            return false;
        }

        $objetoAccesoDato = ConexionPDO::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("INSERT INTO encuestas (idCliente, id_comanda, comentario, puntuacion_mesa, puntuacion_restaurante, puntuacion_mozo, puntuacion_cocinero) VALUES (:idCliente, :id_comanda, :comentario, :puntuacion_mesa, :puntuacion_restaurante, :puntuacion_mozo, :puntuacion_cocinero)");
        $consulta->bindValue(':idCliente', $this->idCliente, PDO::PARAM_INT);
        $consulta->bindValue(':id_comanda', $this->id_comanda, PDO::PARAM_INT);
        $consulta->bindValue(':comentario', $this->comentario, PDO::PARAM_STR);
        $consulta->bindValue(':puntuacion_restaurante', $this->puntuacion_restaurante, PDO::PARAM_INT);
        $consulta->execute();

        // Retorna el ID de la última encuesta insertada.
        return $objetoAccesoDato->obtenerUltimoId();
    }

    // Método para obtener la encuesta por ID de cliente y comanda
    public static function TraerEncuestaPorComanda($id_comanda) {
        $objetoAccesoDato = ConexionPDO::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT * FROM encuestas WHERE id_comanda = :id_comanda");
        $consulta->bindValue(':id_comanda', $id_comanda, PDO::PARAM_INT);
        $consulta->execute();
        $encuestaBuscada = $consulta->fetchObject('Encuesta');

        return $encuestaBuscada;
    }

    public static function TraerTodos() {
        $objetoAccesoDato = ConexionPDO::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT * FROM encuestas");
        $consulta->execute();

        // Devuelve un array de objetos Encuesta
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Encuesta");
    }

}
?>
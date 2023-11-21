<?php

require_once __DIR__ . '/../db/ConexionPDO.php';
require_once __DIR__ . '/../controllers/ProductosController.php';


class Producto
{
    // Atributos con los nombres especificados
    public $idProducto;
    public $nombreProducto;
    public $precioProducto;
    public $categoriaProducto;

    // Inserta un nuevo producto en la base de datos.
    public function InsertarProducto()
    {
        $objetoAccesoDato = ConexionPDO::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("INSERT INTO productos (nombreProducto, precioProducto, categoriaProducto) VALUES ('$this->nombreProducto', $this->precioProducto, '$this->categoriaProducto')");
        $consulta->execute();
        
        // Retorna el ID del último producto insertado.
        return $objetoAccesoDato->obtenerUltimoId();
    }

    // Inserta un nuevo producto en la base de datos utilizando parámetros.
    public function InsertarProductoParametros()
    {
        $objetoAccesoDato = ConexionPDO::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("INSERT INTO productos (nombreProducto, precioProducto, categoriaProducto) VALUES (:nombreProducto, :precioProducto, :categoriaProducto)");
        $consulta->bindValue(':nombreProducto', $this->nombreProducto, PDO::PARAM_STR);
        $consulta->bindValue(':precioProducto', $this->precioProducto, PDO::PARAM_STR);
        $consulta->bindValue(':categoriaProducto', $this->categoriaProducto, PDO::PARAM_STR);
        $consulta->execute();

        // Retorna el ID del último producto insertado.
        return $objetoAccesoDato->obtenerUltimoId();
    }

    // Obtiene todos los productos de la base de datos.
    public static function TraerTodosLosProductos()
    {
        $objetoAccesoDato = ConexionPDO::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT idProducto, nombreProducto, precioProducto, categoriaProducto FROM productos");
        $consulta->execute();

        // Retorna un arreglo de objetos Producto.
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Producto");
    }

  // Obtiene un producto específico por su ID.
    public static function TraerUnProducto($idProducto)
    {
        $objetoAccesoDato = ConexionPDO::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT idProducto, nombreProducto, precioProducto, categoriaProducto FROM productos where idProducto = :idProducto");
        $consulta->bindValue(':idProducto', $idProducto, PDO::PARAM_INT);
        $consulta->execute();
        $productoBuscado = $consulta->fetchObject('Producto');

        // Retorna el producto encontrado o null si no existe.
        return $productoBuscado;
    }  

    //REtornar el producto por nombre
    public static function TraerUnProductoPorNombre($nombreProducto)
    {
        $objetoAccesoDato = ConexionPDO::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT idProducto, nombreProducto, precioProducto, categoriaProducto FROM productos WHERE nombreProducto = :nombreProducto");
        $consulta->bindValue(':nombreProducto', $nombreProducto, PDO::PARAM_STR);
        $consulta->execute();
        $productoBuscado = $consulta->fetchObject('Producto');

        // Retorna el producto encontrado o null si no existe.
        return $productoBuscado;
    }


    // Modifica un producto existente en la base de datos.
    public function ModificarProductoParametros()
    {
        $objetoAccesoDato = ConexionPDO::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("UPDATE productos SET nombreProducto = :nombreProducto, precioProducto = :precioProducto, categoriaProducto = :categoriaProducto WHERE id = :id");
        $consulta->bindValue(':idProducto', $this->idProducto, PDO::PARAM_INT);
        $consulta->bindValue(':nombreProducto', $this->nombreProducto, PDO::PARAM_STR);
        $consulta->bindValue(':precioProducto', $this->precioProducto, PDO::PARAM_STR);
        $consulta->bindValue(':categoriaProducto', $this->categoriaProducto, PDO::PARAM_STR);
        $resultado = $consulta->execute();

        $filasAfectadas = $consulta->rowCount();
        if ($filasAfectadas === 0 || !$resultado) {
            return false;
        }

        // Retorna el ID del producto modificado o false si falla.
        return $this->idProducto;
    }

    // Elimina un producto de la base de datos por su ID.
    public function BorrarProducto()
    {
        $objetoAccesoDato = ConexionPDO::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("DELETE FROM productos WHERE idProducto = :idProducto");
        $consulta->bindValue(':idProducto', $this->idProducto, PDO::PARAM_INT);
        $consulta->execute();
        
        // Retorna la cantidad de filas afectadas (1 si se eliminó correctamente, 0 si no se encontró).
        return $consulta->rowCount();
    }
}

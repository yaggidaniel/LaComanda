
<?php

// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../controllers/UsuarioController.php';
require_once __DIR__ . '/../controllers/ProductosController.php';
require_once __DIR__ . '/../controllers/ComandaController.php';
require_once __DIR__ . '/../controllers/CajaController.php';
require_once __DIR__ . '/../controllers/EncuestaController.php';
require_once __DIR__ . '/../controllers/ClienteController.php';


// Instantiate App
$app = AppFactory::create();
$app->setBasePath('/SPLABO3_Daniel_Yaggi/public');


// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();


// Instancia mi controlador de usuarios
$usuarioController = new UsuarioController();

// Instancia mi controlador de productos
$productoController = new ProductoController();

// Instancia mi controlador de mesas
$comandaController = new ComandaController();

// Instancia mi controlador de caja
$cajaController = new CajaController();

// Instancia mi controlador de productos
$encuestaController = new EncuestaController();

// I nstancia mi controlador de clientes
$clienteController = new ClienteController();



$app->group('/api', function (RouteCollectorProxy $group) 
use ($usuarioController, $productoController, $cajaController, $encuestaController, $comandaController, $clienteController) {
  
    // Define las rutas de usuarioController
    $group->get('/usuario/{idUsuario}', [$usuarioController, 'TraerUno']);
    $group->get('/usuarios', [$usuarioController, 'TraerTodos']);
    $group->post('/dar-de-alta-usuario', [$usuarioController, 'CargarUno']);
    $group->post('/modificar', [$usuarioController, 'ModificarUno']);
    $group->post('/dar-de-baja', [$usuarioController, 'BorrarUno']);
    $group->get('/dias-horarios', [$usuarioController, 'obtenerDiasHorariosIngreso']);
    $group->get('/operaciones/sector', [$usuarioController, 'obtenerCantidadOperacionesPorSector']);
    $group->get('/operaciones/sector-empleado', [$usuarioController, 'obtenerCantidadOperacionesPorEmpleado']);
    $group->get('/operaciones/individual', [$usuarioController, 'obtenerCantidadOperacionesPorSeparado']);

    // Define las rutas de productoController
    $group->get('/producto/{idProducto}', [$productoController, 'TraerUno']);
    $group->get('/productos', [$productoController, 'TraerTodos']);
    $group->post('/agregar-producto', [$productoController, 'CargarUno']);
    $group->post('/modificar-producto', [$productoController, 'ModificarUno']);
    $group->post('/borrar-producto/{idProducto}', [$productoController, 'BorrarUno']); 

    // Define las rutas  de cajaController
    $group->post('/actualizar-valor-total', [$cajaController, 'CargarUno']);
    $group->post('/borrar-valor-total', [$cajaController, 'BorrarUno']);
    $group->post('/modificar-valor-total', [$cajaController, 'ModificarUno']);
    $group->get('/valores-totales', [$cajaController, 'TraerTodos']);
    $group->get('/valor-total', [$cajaController, 'TraerUno']);


    // Rutas de encuestaController
    $group->post('/crear-encuesta', [$encuestaController, 'CrearEncuesta']);
    $group->get('/encuesta-por-id', [$encuestaController, 'ObtenerEncuestaPorComanda']);
    $group->get('/todas-las-encuestas', [$encuestaController, 'TraerTodos']);

    // Rutas de comandaController
    $group->post('/insertar-pedido', [$comandaController, 'InsertarPedido']);
    $group->get('/pedidos', [$comandaController, 'TraerTodos']);
    $group->get('/pedido/{id}', [$comandaController, 'TraerUnPedido']);
    $group->get('/pedido-por-codigo/{codigo_pedido}', [$comandaController, 'TraerUnPedidoPorCodigo']);
    $group->post('/modificar-pedido', [$comandaController, 'ModificarPedidoParametros']);
    $group->post('/cambiar-estado-pedido', [$comandaController, 'CambiarEstadoPedido']);
    $group->post('/asignar-empleado', [$comandaController, 'AsignarEmpleado']);
    $group->post('/insertar-productos-a-pedido', [$comandaController, 'InsertarProductosAPedido']);
    $group->post('/incrementar-cant-pedido', [$comandaController, 'IncrementarCantPedido']);
    $group->get('/productos-por-pedido/{idPedido}', [$comandaController, 'ObtenerProductosPorPedido']);
    $group->get('/pedidos-con-retraso', [$comandaController, 'ObtenerPedidosConRetraso']);
    $group->get('/pedidos-sin-retraso', [$comandaController, 'ObtenerPedidosSinRetraso']);
    $group->post('/codigo-repetido', [$comandaController, 'codigoRepetido']);
    $group->get('/pedidos-por-estado/{estado_pedido}', [$comandaController, 'TraerPedidosPorEstado']);
    $group->post('/borrar-pedido', [$comandaController, 'BorrarPedido']);

    // Rutas del ClienteController
    $group->post('/cliente/cargar', [$clienteController, 'CargarUno']);
    $group->get('/cliente/{idCliente}', [$clienteController, 'TraerUno']);
    $group->get('/clientes', [$clienteController, 'TraerTodos']);
    $group->post('/cliente/modificar', [$clienteController, 'ModificarUno']);
    $group->post('/cliente/borrar', [$clienteController, 'BorrarUno']);

});

// el entrypoint de la aplicacion
$app->run();

?>


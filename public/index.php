
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


// Instantiate App
$app = AppFactory::create();
$app->setBasePath('/MiApp/public');


// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();


// Instancia mi controlador de usuarios
$usuarioController = new UsuarioController();

// Configura los endpoint utilizando RouteCollectorProxy (en UsuarioController estan las rutas)
$app->group('/api', function (RouteCollectorProxy $group) use ($usuarioController) {
    $usuarioController->agregarRutas($group);
});





// Rutas de prueba
$app->get('[/]', function (Request $request, Response $response) {
    $payload = json_encode(array('method' => 'GET', 'msg' => "Bienvenido a SlimFramework 2023 A"));
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/test', function (Request $request, Response $response) {
    $payload = json_encode(array('method' => 'GET', 'msg' => "Bienvenido a SlimFramework 2023 / test"));
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('[/]', function (Request $request, Response $response) {
    $payload = json_encode(array('method' => 'POST', 'msg' => "Bienvenido a SlimFramework 2023 B"));
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/test', function (Request $request, Response $response) {
    $payload = json_encode(array('method' => 'POST', 'msg' => "Bienvenido a SlimFramework 2023 /testPost"));
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});


// el entrypoint de la aplicacion
$app->run();

?>


<?php
/**
 * Routes Configuration
 * Define all application routes here
 */

use AuraCore\Router;

$router = new Router();

// Welcome routes
$router->get('', 'welcome@index');
$router->get('/', 'welcome@index');
$router->get('welcome', 'welcome@index');

// Components showcase
$router->get('components', 'components@index');

// Colors system
$router->get('colors', 'colors@index');

// Documentation
$router->get('docs/framework', 'docs@index');
$router->get('docs/ownstrap', 'documentation@index');

// Demo routes - showcasing routing capabilities
$router->get('demo', 'demo@index');
$router->get('demo/user/:id', 'demo@user');
$router->get('demo/post/:id/:slug', 'demo@post');
$router->get('demo/api', 'demo@api');

// Add your routes below
// Example:
// $router->get('users', 'user@list');
// $router->get('users/:id', 'user@show');
// $router->post('users', 'user@store');
// $router->put('users/:id', 'user@update');
// $router->delete('users/:id', 'user@destroy');

// Callback example:
// $router->get('api/data', function() {
//     echo json_encode(['status' => 'ok']);
// });

return $router;
?>

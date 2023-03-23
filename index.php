<?php  
//*Mostrar errores
ini_set('display_errors', 1);
ini_set('log_errors',1);
ini_set('error_log', 'C:/wamp/www/cursos/apirest-dinamica/php_error_log');

//*CORS
//Dar permiso a otros dominios de obtener informacion de este dominio
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('content-type: application/json; charset=utf-8');

//*Requerimientos
require_once 'controllers/routes.controller.php';

//*Para comprobar la conexion:
/*require_once 'models/connection.php';
echo "<pre>";
print_r(Connection::connect());
echo "</pre>";
return;*/


$index = new RoutesController();
$index->index();
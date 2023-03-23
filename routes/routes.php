<?php
require_once "models/connection.php";
require_once 'controllers/get.controller.php';

// ($_SERVER['REQUEST_URI']) sirve para obtener el parametro
//que se le a#ade a la URL principal, por ejemplo 
//apirest.com/courses, la propiedad nos devolveria "/courses"
/*
echo ($_SERVER['REQUEST_URI']);
echo "<br>";
*/

//explore() genera arrays apartir de una cadena de texto, en
//este caso le indicaremos que a#ada un array apartir del
// caracter '/' que se encuentre en la cadena de texto,
//en este caso es parametro agregado a la URL, ejemplo "/courses"
$routesArray = explode("/", $_SERVER['REQUEST_URI']);

//limpiar los indices vacios, cuando no hay dada detras del "/" 
$routesArray= array_filter($routesArray);

/*
echo "<pre>";
print_r($routesArray);
echo "</pre>";
*/

//?========================================================
//?     Cuando no se hace ninguna peticion a la API
//?=======================================================*/

//* Verificar si no se a#ade un parametro a la URL
//* y enviar un error:
//podemos utilizar count(variable)==0 o empty() para 
//verificar si el array esta vacio 
if(count($routesArray) == 0){

    //debemos devolver la informacion en formato JSON, ya que 
    //este es el formato en que las API's devuelven la informacion
    //o tambien en formato XML, pero en este caso vamos a usar
    //el formato JSON, para ello hacemos lo siguiente
    $json= array(

        //codigo 404 : no encuentra informacion
        'status'  => 404,
        'results'  => 'Not found'
        
        );
        
        //http_response_code('propiedad donde esta el status'):
        //Sirve para manipular en los servicios HTTP el status,
        //es decir, si el codigo de estado del JSON es 404,  
        //el codigo de estado de los servicios HTTP tambien sera 404
        //! en POSTMAN se puede visualizar los cambios en los estados
        echo json_encode($json, http_response_code($json['status']));
        
        return;
   
}

//?========================================================
//?     Cuando si se hace una peticion a la API
//?=======================================================*/

if(count($routesArray) == 1 && isset($_SERVER['REQUEST_METHOD'])){
   
    //nombre de la tabla
    $table = explode("?", $routesArray[1])[0];
    
    //*===================================
    //*       Validar llave secreta
    //*===================================*/
   
    if(!isset(getallheaders()["Authorization"]) || getallheaders()["Authorization"] != Connection::apikey() ){

        if(in_array($table, Connection::publicAccess()) == 0 ){
            $json= array(

                //codigo 400: Error
                'status'  => 400,
                'results'  => 'You are not authorized to make this request'
                
            );
                
            echo json_encode($json, http_response_code($json['status']));

            return;
            
        }else{

            //*===================================
            //*       Acceso publico
            //*===================================*/

            //Acceso publico solo a las peticiones SIN FILTROS
            $response= new GetController();
            $response->getData($table, "*", null, null, null, null);
            
            return;
        }
    }

   

    //$_SERVER['REQUEST_METHOD']: Devuelve el metodo http que
    //se esta utilizando

    //?===================================
    //?           Peticiones GET
    //?===================================*/
    if($_SERVER['REQUEST_METHOD'] == "GET"){

        include "services/get.php";

    }

    //?===================================
    //?           Peticiones POST
    //?===================================*/
    if($_SERVER['REQUEST_METHOD'] == "POST"){
       
        include "services/post.php";
            
    }

    //?===================================
    //?           Peticiones PUT
    //?===================================*/
    if($_SERVER['REQUEST_METHOD'] == "PUT"){
     
        include "services/put.php";
            
    }

    //?===================================
    //?           Peticiones DELETE
    //?===================================*/
    if($_SERVER['REQUEST_METHOD'] == "DELETE"){
       /* $json= array(

            //codigo 200: todo esta bien
            'status'  => 200,
            'results'  => 'Solicitud DELETE'
            
            );
            
            
            echo json_encode($json, http_response_code($json['status']));*/

        include "services/delete.php";
        
            
    }
    
   

}
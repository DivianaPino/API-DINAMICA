<?php

require_once 'models/get.model.php';

class Connection{
   //*===================================
   //* Informacion de la base de datos
   //*===================================*/

   static public function infoDatabase(){

       $infoDB= array(
           "database" => "database-1",
           "user" => "root",
           "pass" => "1234"
       );
       return $infoDB;
   }

   //*===================================
   //* APIKEY
   //*===================================*/
   
   static public function apikey(){

    return "dasfs45hy67er88ght64351MjgTyU";

   }


    //*===================================
    //* Acceso publico
    //*===================================*/

    static public function publicAccess(){

        $tables = ["courses"];
        return $tables;

    }

   //*===================================
   //* Conexion a la base de datos
   //*===================================*/

   static public function connect(){

    try{
         $link= new PDO(
               "mysql:host=localhost;dbname=".Connection::infoDatabase()["database"],
               Connection::infoDatabase()["user"],
               Connection::infoDatabase()["pass"]
         );

         //para que respete los caracteres latinos si vienen
         //en idioma espanol (ñ,la tilde)
         $link->exec("set names utf8");

    }catch(PDOException $e){
        die("Error: ".$e->getMessage());
    }

    return $link;
   }


   //*==========================================
   //* Validar existencia de una tabla en la bd
   //*===========================================*/

   static public function getColumnsData($table, $columns){
        //* Traer el nombre de la base de datos
        $database = Connection::infoDatabase()['database'];

        //* Traer todas las columnas de una tabla
        //hacemos la conexion a la bd
        $validate = Connection::connect()
        //COLUMN_NAME: seleccionamos todas las columnas de la TABLA que se pasa por parametro
        //la cual debe estar en la base de datos donde estamos conectados ($database)

        //information_schema.columns: informacion de las columnas de la tabla de nuestra bd
        ->query("SELECT COLUMN_NAME AS item FROM information_schema.columns WHERE table_schema = '$database' AND table_name= '$table'")
        //devolvemos las columnas en formato de objeto
        ->fetchAll(PDO::FETCH_OBJ);

        //* Validamos existencia de la tabla 
        if(empty($validate)){

            return null;

        }else{

            //*Ajuste de seleccion de columnas globales
            if($columns[0] == "*"){
                array_shift($columns);
            }

            //* Validamos existencia de columnas
            $sum= 0;

            foreach ($validate as $key => $value) {
                //numero de coincidencia de las tablas solicitadas
                //con las de la bd
                $sum += in_array($value->item, $columns);
            }

            //count($columns): numero de columnas solicitadas

            //si $sum y $columns no son iguales es porque no hay
            //coincidencias o algunas de las tablas solicidas no existen
            //hacemos la siguiente ternaria si $sum y count($columns) son 
            //iguales retornamos &validate sino lo son retornamos null
            return $sum == count($columns) ? $validate : null;
        }
    }   


   //*===================================
   //* Generar TOKEN de autenticacion
   //*===================================*/

   static public function jwt($id, $email){
     
        $time = time();

        $token = array(

            "iat" => $time, //Tiempo en que inicia el token
            "exp" => $time + (60*60*24), // Tiempo en que expirara el token (1 dia)
            "data" => [
                "id" => $id,
                "email" => $email
            ]
            
        );

        
        return $token;

        // echo "<pre>"; print_r($jwt); echo "</pre>";

    }

   //*===================================
   //* Validar el token de seguridad
   //*===================================*/

   static public function tokenValidate($token, $table, $suffix){

     //*========================================
     //* Traemos el usuario de acuerdo al token
     //*========================================*/
     $user = GetModel::getDataFilter($table, "token_exp_".$suffix , "token_".$suffix, $token, null, null, null, null);
      

    //validar que el token no haya expirado
    if(!empty($user)){

        $time = time();

        if($user[0]->{"token_exp_".$suffix} > $time){

            return "ok";

        }else{

            return "expired";
        }

    }else{
        return "no-auth";
    }
  
   
   }









        

     
   


}
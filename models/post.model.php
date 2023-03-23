<?php 

require_once "connection.php";

class PostModel{

    //*===================================================
    //*  Peticion POST para crear datos de forma dinamica
    //*===================================================*/

	static public function postData($table, $data){

		$columns = "";
		$params = "";

		foreach ($data as $key => $value) {
			
			$columns .= $key.",";
			
			$params .= ":".$key.",";
			
		}

        //Quitar la ultima coma de las columnas y parametros
		$columns = substr($columns, 0, -1);
		$params = substr($params, 0, -1);


		$sql = "INSERT INTO $table ($columns) VALUES ($params)";

        
		$link = Connection::connect();

        //Preparamos sentencia SQL
		$stmt = $link->prepare($sql);

        //Enlazamos los parametros de la sentencia SQL
		foreach ($data as $key => $value) {

			$stmt->bindParam(":".$key, $data[$key], PDO::PARAM_STR);
		
		}

        //Ejecutamos la sentencia SQL
		if($stmt -> execute()){

			$response = array(

                //devuelve ultimo ID (registro recien guardado)
				"lastId" => $link->lastInsertId(),
				"comment" => "The process was successful"
			);

			return $response;
		
		}else{

			return $link->errorInfo();

		}


	}

}




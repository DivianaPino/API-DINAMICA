<?php 

require_once "connection.php";
require_once "get.model.php";

class PutModel{

    //*===================================================
    //*  Peticion PUT para editar datos de forma dinamica
    //*===================================================*/

	static public function putData($table, $data, $id, $name_Id){

        //*===================================================
        //*  Validar el ID
        //*===================================================*/

        $response= GetModel::getDataFilter($table, $name_Id, $name_Id, $id,null, null, null, null);
        

        if(empty($response)){
    
			return null;
        }

        //*===================================================
        //*  Actualizamos registro
        //*===================================================*/

        $set = "";

        foreach ($data as $key => $value) {
            $set .= $key." = :".$key.","; 
        }

        //eliminar la ultima coma de $set

        $set = substr($set, 0, -1);

        //consulta SQL
        $sql = "UPDATE $table SET $set WHERE $name_Id = :$name_Id";

        $link = Connection::connect();
        //Preparamos sentencia SQL
		$stmt = $link->prepare($sql);

         //Enlazamos los parametros de la sentencia SQL
		foreach ($data as $key => $value) {

			$stmt->bindParam(":".$key, $data[$key], PDO::PARAM_STR);
		
		}

        $stmt->bindParam(":".$name_Id, $id, PDO::PARAM_STR);


        //Ejecutamos la sentencia SQL
		if($stmt -> execute()){

			$response = array(

				"comment" => "The process was successful"
			);

			return $response;
		
		}else{

			return $link->errorInfo();

		}

    }

}




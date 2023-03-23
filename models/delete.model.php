<?php 

require_once "connection.php";
require_once "get.model.php";

class DeleteModel{

    //*=========================================================
    //*  Peticion DELETE para eliminar datos de forma dinamica
    //*=========================================================*/

	static public function deleteData($table, $id, $name_Id){

        //*===================================================
        //*  Validar el ID
        //*===================================================*/

        $response= GetModel::getDataFilter($table, $name_Id, $name_Id, $id,null, null, null, null);
        

        if(empty($response)){
    
			return null;
        }

        //*===================================================
        //*  Eliminamos registro
        //*===================================================*/


        //consulta SQL
        $sql = "DELETE FROM $table WHERE $name_Id = :$name_Id";

        $link = Connection::connect();

        //Preparamos sentencia SQL
		$stmt = $link->prepare($sql);

         //Enlazamos los parametros de la sentencia SQL
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




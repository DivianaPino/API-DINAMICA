<?php

require_once "models/delete.model.php";

class DeleteController{
    //*=======================================
    //*    Peticion DELETE para eliminar datos
    //*======================================*/
  
    static public function deleteData($table, $id, $name_Id){

        $response = DeleteModel::deleteData($table, $id, $name_Id);

        $return = new DeleteController();
        $return -> fncResponse($response);

    }


    //*======================================
    //*      Respuestas del controlador
    //*======================================*/

    
    public function fncResponse($response){

        if(!empty($response)){

            $json = array(

                'status' => 200,
                'results' => $response

            );

        }else{

            $json = array(

                'status' => 404,
                'results' => 'Not Found',
                'method' => 'delete'

            );

        }


        echo json_encode($json, http_response_code($json["status"]));
    
    }


}
  
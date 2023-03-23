<?php
require_once 'connection.php';

class GetModel{
    //*===================================================
    //*        Peticiones GET sin filtro
    //*===================================================*/

    //metodo para obtener los datos de la tabla que se 
    //ingrese por parametro en  la URL principal de la API
    static public function getData($table, $select, $orderBy, $orderMode,$startAt, $endAt){

        //*Validar existencia de una tabla y las columnas en la bd

        $selectArray = explode(",", $select);

        if(empty(Connection::getColumnsData($table, $selectArray))){
             return null;
        }
        

        //*Consultas a la base de datos
        //consulta con ordenamiento pero sin limitacion
        if($orderBy != null && $orderMode != null && $startAt == null && $endAt == null){
            $sql= "SELECT $select FROM $table ORDER BY $orderBy $orderMode";
        //consulta con ordenamiento y con limitacion
        }elseif($orderBy != null && $orderMode != null && $startAt != null && $endAt != null){
            $sql= "SELECT $select FROM $table ORDER BY $orderBy $orderMode LIMIT $startAt,$endAt";
        //consulta con limitacion pero sin ordenamiento
        }elseif($orderBy == null && $orderMode == null && $startAt != null && $endAt != null){
            $sql= "SELECT $select FROM $table LIMIT $startAt,$endAt";
        }else{
            //consulta sin filtrar, ni limitar,  ni ordenar datos   
            //consulta para seleccionar un campo en especifico
            $sql= "SELECT $select FROM $table ";
        }

        //*preparacion de la sentencia SQL:
        //nos conectamos a la BD y le pasamos la consulta
        $stmt= Connection::connect()->prepare($sql);

        //Try-catch para capturar errores
        try{
           //*lo ejecutamos
            $stmt->execute();

        }catch(PDOException $Exception){
            return null;
        }

        //*retornamos lo que responda con un fetchAll()
        //FETCH_CLASS: para que no muestre los numero de indices
        //en el objeto
        return $stmt-> fetchAll(PDO::FETCH_CLASS);
    }

    //*===================================================
    //*        Peticiones GET con filtro
    //*===================================================*/

    //metodo para obtener los datos de la tabla que se 
    //ingrese por parametro en  la URL principal de la API
    static public function getDataFilter($table, $select, $linkTo, $equalTo,$orderBy, $orderMode,$startAt, $endAt){

       //*Validar existencia de una tabla y las columnas en la bd

         //*filtrar varios valores a la consulta
         $linkToArray = explode(",", $linkTo);
         $selectArray = explode(",", $select);

         //verificar si existen las columnas en la tablas
         //seleccionadas pasadas por $linkToArray y en $selectArray,
         //hacemos foreach para agregar cada uno de los valores de 
         //$linkToArray a $selectArray
         foreach ($linkToArray as $key => $value) {
             //array_push(array donde queremos agregar informacion (primer paramettro),
             // lo que queremos agregar (segundo parametro))
             array_push($selectArray, $value);
         }

         $selectArray = array_unique($selectArray);

        if(empty(Connection::getColumnsData($table, $selectArray))){
            return null;
        }
               
        //lo hacemos con ",", ya que en es probable que en los datos
        //en la base de datos tenga comas y lo tome cuenta en la instruccion
        //por lo tanto dara error, o no imprimira el resultado esperado
        $equalToArray = explode(",", $equalTo);

        //iniciamos la variable vacia para despues concatenarle los 
        //AND de la consulta SQL
        $linktoText="";


        //comprobar si se reciben mas de un parametro y asi 
        //concatenar cada uno en la sentencia SQL
        if(count($linkToArray)>1){

            //recorremos todos AND existentes en la consulta SQL
            foreach ($linkToArray as $key => $value) {
                if($key > 0){
                    $linktoText .= "AND " .$value." = :".$value." ";
                }
            }
        }


        //*Consultas a la base de datos
        //:linkToArray: se coloca ":"para indicar que se le va a enlazar parametros
        //:linkToArray es el parametro de enlace 

        //consulta con filtros, con ordenamiento pero sin limitacion
        if($orderBy != null && $orderMode != null && $startAt == null && $endAt == null){
            $sql= "SELECT $select FROM $table WHERE $linkToArray[0] = :$linkToArray[0] $linktoText ORDER BY $orderBy $orderMode ";
        //consulta con filtros, con ordenamiento y con limitacion
        }elseif($orderBy != null && $orderMode != null && $startAt != null && $endAt != null){
            $sql= "SELECT $select FROM $table WHERE $linkToArray[0] = :$linkToArray[0] $linktoText ORDER BY $orderBy $orderMode LIMIT $startAt,$endAt";
        //consulta con filtros, con limitacion pero sin ordenamiento
        }elseif($orderBy == null && $orderMode == null && $startAt != null && $endAt != null){
            $sql= "SELECT $select FROM $table WHERE $linkToArray[0] = :$linkToArray[0] $linktoText LIMIT $startAt,$endAt";
        }else{ 
            //consulta para seleccionar un campo en especifico con filtros pero sin limitacion ni ordenamiento
            $sql= "SELECT $select FROM $table WHERE $linkToArray[0] = :$linkToArray[0] $linktoText ";
        }


        //*Preparacion de la sentencia SQL:
        //nos conectamos a la BD y le pasamos la consulta
        $stmt= Connection::connect()->prepare($sql);
        
        //* Enlazamos parametros
        // foreach para enlazar los AND tambien a la consulta SQL
        foreach ($linkToArray as $key => $value) {
            //bindParam(":".parametro_enlace, $parametro_a_enlazar): enlazar parametros
            //ejemplo binParam(":".)
            //$key= id de cada linkToArray, el cual aumenta en cada iteracion
            $stmt->bindParam(":".$value, $equalToArray[$key], PDO::PARAM_STR);
            //Ejemplo de lo anterior
            //:title_course = $equalToArray[$key]
        }
       
        //Try-catch para capturar errores
        try{
            //*lo ejecutamos
            $stmt->execute();

        }catch(PDOException $Exception){
            return null;
        }

        //*retornamos lo que responda con un fetchAll()
        //FETCH_CLASS: para que no muestre los numero de indices
        //en el objeto
        return $stmt-> fetchAll(PDO::FETCH_CLASS);
    }


    //*======================================================
    //* Peticiones GET sin filtro entre tablas relacionadas
    //*======================================================*/

    //metodo para obtener los datos de una o varias tablas 
    //relacionadas sin filtro
    static public function getRelData($rel,$type, $select, $orderBy, $orderMode,$startAt, $endAt){

        $relArray = explode(",", $rel);
        $typeArray = explode(",", $type);
        $innerJoinText="";

        //concatenar los INNER JOIN en la sentencia SQL
        if(count($relArray)>1){

            //recorremos todos AND existentes en la consulta SQL
            foreach ($relArray as $key => $value) {

                //*Validar existencia de una tabla y las columnas en la bd
                if(empty(Connection::getColumnsData($value, ["*"]))){
                 return null;
                }

                //ejecutar cuando $key sea mayor que 0, ya que la posicion cero esta la tabla
                //principal y las que necesitamos colocar luego del INNER JOIN son las otras  
                //tablas con las que se relacionara la tabla principal
                //SI QUEREMOS obtener el valor del tabla principal forzamos colocando $relArray[0]
                if($key > 0){
                   //consulta modelo para crear la consulta en "innerJoinText"
                  // "SELECT $select FROM $relArray[0] INNER JOIN $relArray[1] ON $relArray[0].id_$typeArray[1]_$typeArray[0] = $relArray[1].id_$typeArray[1] ";
                    
                    $innerJoinText .= "INNER JOIN ".$value." ON ".$relArray[0].".id_".$typeArray[$key]."_".$typeArray[0]." = ". $value.".id_".$typeArray[$key]." ";
                }
            }

                
            //*Consultas a la base de datos CON TABLAS RELACIONADAS
            //consulta con ordenamiento pero sin limitacion
            if($orderBy != null && $orderMode != null && $startAt == null && $endAt == null){
                $sql= "SELECT $select FROM $relArray[0] $innerJoinText ORDER BY $orderBy $orderMode";
            //consulta con ordenamiento y con limitacion
            }elseif($orderBy != null && $orderMode != null && $startAt != null && $endAt != null){
                $sql= "SELECT $select FROM $relArray[0] $innerJoinText ORDER BY $orderBy $orderMode LIMIT $startAt,$endAt";
            //consulta con limitacion pero sin ordenamiento
            }elseif($orderBy == null && $orderMode == null && $startAt != null && $endAt != null){
                $sql= "SELECT $select FROM $relArray[0] $innerJoinText LIMIT $startAt,$endAt";
            }else{
                //consulta sin filtrar, ni limitar,  ni ordenar datos   
                //consulta para seleccionar un campo en especifico 
                $sql= "SELECT $select FROM $relArray[0] $innerJoinText ";
            }

            //*preparacion de la sentencia SQL:
            //nos conectamos a la BD y le pasamos la consulta
            $stmt= Connection::connect()->prepare($sql);

            //Try-catch para capturar errores
            try{
                //*lo ejecutamos
                $stmt->execute();

            }catch(PDOException $Exception){
                return null;
            }
            //*retornamos lo que responda con un fetchAll()
            //FETCH_CLASS: para que no muestre los numero de indices
            //en el objeto
            return $stmt-> fetchAll(PDO::FETCH_CLASS);

        }else{
            //si no viene mas de una tabla devolvemos valor nulo
            return null;
        }
    }


    //*======================================================
    //* Peticiones GET con filtro entre tablas relacionadas
    //*======================================================*/

    //metodo para obtener los datos de una o varias tablas 
    //relacionadas sin filtro
    static public function getRelDataFilter($rel,$type, $select, $linkTo, $equalTo, $orderBy, $orderMode,$startAt, $endAt){
       
        //*-===========================
        //*  Organizamos los filtros
        //*=============================
        $linkToArray = explode(",",$linkTo);
		$equalToArray = explode(",",$equalTo);
		$linkToText = "";

		if(count($linkToArray)>1){

			foreach ($linkToArray as $key => $value) {
            
				if($key > 0){

					$linkToText .= "AND ".$value." = :".$value." ";
				}
			}

		}

        //*-=============================
        //*  Organizamos las relaciones
        //*==============================

        $relArray = explode(",", $rel);
        $typeArray = explode(",", $type);
        $innerJoinText="";

        //concatenar los INNER JOIN en la sentencia SQL
        if(count($relArray)>1){

            //recorremos todos AND existentes en la consulta SQL
            foreach ($relArray as $key => $value) {

                //*Validar existencia de una tabla en la bd
                if(empty(Connection::getColumnsData($value,["*"]))){
                    return null;
                }

                if($key > 0){
                   //consulta modelo para crear la consulta en "innerJoinText"
                  // "SELECT $select FROM $relArray[0] INNER JOIN $relArray[1] ON $relArray[0].id_$typeArray[1]_$typeArray[0] = $relArray[1].id_$typeArray[1] ";
                    
                    $innerJoinText .= "INNER JOIN ".$value." ON ".$relArray[0].".id_".$typeArray[$key]."_".$typeArray[0]." = ". $value.".id_".$typeArray[$key]." ";

                

                }
            }

                
            //*Consultas a la base de datos CON TABLAS RELACIONADAS
            //consulta con ordenamiento pero sin limitacion
            if($orderBy != null && $orderMode != null && $startAt == null && $endAt == null){
                $sql= "SELECT $select FROM $relArray[0] $innerJoinText WHERE $linkToArray[0] = :$linkToArray[0] $linktoText  ORDER BY $orderBy $orderMode";
            //consulta con ordenamiento y con limitacion
            }elseif($orderBy != null && $orderMode != null && $startAt != null && $endAt != null){
                $sql= "SELECT $select FROM $relArray[0] $innerJoinText WHERE $linkToArray[0] = :$linkToArray[0] $linktoText ORDER BY $orderBy $orderMode LIMIT $startAt,$endAt";
            //consulta con limitacion pero sin ordenamiento
            }elseif($orderBy == null && $orderMode == null && $startAt != null && $endAt != null){
                $sql= "SELECT $select FROM $relArray[0] $innerJoinText WHERE $linkToArray[0] = :$linkToArray[0] $linktoText LIMIT $startAt,$endAt";
            }else{
                //consulta sin filtrar, ni limitar,  ni ordenar datos   
                //consulta para seleccionar un campo en especifico 
                $sql = "SELECT $select FROM $relArray[0] $innerJoinText WHERE $linkToArray[0] = :$linkToArray[0] $linkToText";

            }

            //*preparacion de la sentencia SQL:
            //nos conectamos a la BD y le pasamos la consulta
            $stmt= Connection::connect()->prepare($sql);

            //* Enlazamos parametros
            // foreach para enlazar los AND tambien a la consulta SQL
            foreach ($linkToArray as $key => $value) {
                //bindParam(":".parametro_enlace, $parametro_a_enlazar): enlazar parametros
                //ejemplo binParam(":".)
                //$key= id de cada linkToArray, el cual aumenta en cada iteracion
                $stmt->bindParam(":".$value, $equalToArray[$key], PDO::PARAM_STR);
                //Ejemplo de lo anterior
                //:title_course = $equalToArray[$key]
            }
        
            //Try-catch para capturar errores
            try{
                //*lo ejecutamos
                $stmt->execute();
    
            }catch(PDOException $Exception){
                return null;
            }

            //*retornamos lo que responda con un fetchAll()
            //FETCH_CLASS: para que no muestre los numero de indices
            //en el objeto
            return $stmt-> fetchAll(PDO::FETCH_CLASS);
    

        }else{
            //si no viene mas de una tabla devolvemos valor nulo
            return null;
        }
    }

    //*======================================================
    //* Peticiones GET para el buscador sin relaciones
    //*======================================================*/

    static public function getDataSearch($table, $select, $linkTo, $search, $orderBy, $orderMode,$startAt, $endAt){
        
        //*Validar existencia de una tabla y las columnas en la bd
        //filtrar varios valores a la consulta
         $linkToArray = explode(",", $linkTo);
         $selectArray = explode(",", $select);

         foreach ($linkToArray as $key => $value) {
             //array_push(array donde queremos agregar informacion (primer paramettro),
             // lo que queremos agregar (segundo parametro))
             array_push($selectArray, $value);
         }

         $selectArray = array_unique($selectArray);

        if(empty(Connection::getColumnsData($table, $selectArray))){
            return null;
        }
           

           //$searchArray: array que obtendra el texto a buscar,
           //el cual estara en la primera posicion es decir posicion 0
           //y luego estaran los filtros
           $searchArray= explode(",", $search);
           $linktoText="";
   
           //comprobar si se reciben mas de un parametro y asi 
           //concatenar cada uno en la sentencia SQL
           if(count($linkToArray)>1){
   
               //recorremos todos AND existentes en la consulta SQL
               foreach ($linkToArray as $key => $value) {
                   if($key > 0){
                       $linktoText .= "AND " .$value." = :".$value." ";
                   }
               }
           }
        
        
        //*Consultas a la base de datos
        //consulta con ordenamiento pero sin limitacion
        if($orderBy != null && $orderMode != null && $startAt == null && $endAt == null){
            $sql= "SELECT $select FROM $table WHERE $linkToArray[0] LIKE '%$searchArray[0]%' $linktoText ORDER BY $orderBy $orderMode";
        //consulta con ordenamiento y con limitacion
        }elseif($orderBy != null && $orderMode != null && $startAt != null && $endAt != null){
            $sql= "SELECT $select FROM $table WHERE $linkToArray[0] LIKE '%$searchArray[0]%' $linktoText  ORDER BY $orderBy $orderMode LIMIT $startAt,$endAt";
        //consulta con limitacion pero sin ordenamiento
        }elseif($orderBy == null && $orderMode == null && $startAt != null && $endAt != null){
            $sql= "SELECT $select FROM $table WHERE $linkToArray[0] LIKE '%$searchArray[0]%' $linktoText LIMIT $startAt,$endAt";
        }else{
            //consulta sin filtrar, ni limitar,  ni ordenar datos   
            //consulta para seleccionar un campo en especifico
            $sql= "SELECT $select FROM $table WHERE $linkToArray[0] LIKE '%$searchArray[0]%' $linktoText";
        }

        //*preparacion de la sentencia SQL:
        //nos conectamos a la BD y le pasamos la consulta
        $stmt= Connection::connect()->prepare($sql);

        //* Enlazamos parametros
        // foreach para enlazar los AND tambien a la consulta SQL
        foreach ($linkToArray as $key => $value) {
            //bindParam(":".parametro_enlace, $parametro_a_enlazar): enlazar parametros
            //ejemplo binParam(":".)
            //$key= id de cada linkToArray, el cual aumenta en cada iteracion
            
            //en este caso lo hacemos desde la posicion 1, ya que en la
            //posicion 0 se recibe la palabra que se va a buscar
            if($key > 0){
                $stmt->bindParam(":".$value, $searchArray[$key], PDO::PARAM_STR);
                //Ejemplo de lo anterior
                //:title_course = $equalToArray[$key]
            }
        }

        //Try-catch para capturar errores
        try{
            //*lo ejecutamos
            $stmt->execute();

        }catch(PDOException $Exception){
            return null;
        }

        //*retornamos lo que responda con un fetchAll()
        //FETCH_CLASS: para que no muestre los numero de indices
        //en el objeto
        return $stmt-> fetchAll(PDO::FETCH_CLASS);
        
    }

    //*============================================================
    //* Peticiones GET para el buscador entre tablas relacionadas
    //*============================================================*/

    static public function getRelDataSearch($rel,$type, $select, $linkTo, $search, $orderBy, $orderMode,$startAt, $endAt){
       

        //*-===========================
        //*  Organizamos los filtros
        //*=============================

         //$searchArray: array que obtendra el texto a buscar,
         //el cual estara en la primera posicion es decir posicion 0
         //y luego estaran los filtros
         $searchArray= explode(",", $search);
         $linktoText="";
        //filtrar varios valores a la consulta
         $linkToArray = explode(",",$linkTo);
 
         //comprobar si se reciben mas de un parametro y asi 
         //concatenar cada uno en la sentencia SQL
         if(count($linkToArray)>1){
 
             //recorremos todos AND existentes en la consulta SQL
             foreach ($linkToArray as $key => $value) {

                if($key > 0){
                    $linktoText .= "AND " .$value." = :".$value." ";
                }
             }
         }
        //*-=============================
        //*  Organizamos las relaciones
        //*==============================

        $relArray = explode(",", $rel);
        $typeArray = explode(",", $type);
        $innerJoinText="";

        //concatenar los INNER JOIN en la sentencia SQL
        if(count($relArray)>1){

            //recorremos todos AND existentes en la consulta SQL
            foreach ($relArray as $key => $value) {

                //*Validar existencia de una tabla en la bd
                if(empty(Connection::getColumnsData($value,["*"]))){
                    return null;
                }

                if($key > 0){
                   //consulta modelo para crear la consulta en "innerJoinText"
                  // "SELECT $select FROM $relArray[0] INNER JOIN $relArray[1] ON $relArray[0].id_$typeArray[1]_$typeArray[0] = $relArray[1].id_$typeArray[1] ";
                    
                    $innerJoinText .= "INNER JOIN ".$value." ON ".$relArray[0].".id_".$typeArray[$key]."_".$typeArray[0]." = ". $value.".id_".$typeArray[$key]." ";

                }
            }

            //*Consultas a la base de datos CON TABLAS RELACIONADAS
            //consulta con ordenamiento pero sin limitacion
            if($orderBy != null && $orderMode != null && $startAt == null && $endAt == null){
                $sql= "SELECT $select FROM $relArray[0] $innerJoinText WHERE $linkToArray[0] LIKE '%$searchArray[0]%' $linktoText  ORDER BY $orderBy $orderMode";
            //consulta con ordenamiento y con limitacion
            }elseif($orderBy != null && $orderMode != null && $startAt != null && $endAt != null){
                $sql= "SELECT $select FROM $relArray[0] $innerJoinText WHERE $linkToArray[0] LIKE '%$searchArray[0]%' $linktoText ORDER BY $orderBy $orderMode LIMIT $startAt,$endAt";
            //consulta con limitacion pero sin ordenamiento
            }elseif($orderBy == null && $orderMode == null && $startAt != null && $endAt != null){
                $sql= "SELECT $select FROM $relArray[0] $innerJoinText WHERE $linkToArray[0] LIKE '%$searchArray[0]%' $linktoText LIMIT $startAt,$endAt";
            }else{
                //consulta sin filtrar, ni limitar,  ni ordenar datos   
                //consulta para seleccionar un campo en especifico 
                $sql = "SELECT $select FROM $relArray[0] $innerJoinText WHERE $linkToArray[0] LIKE '%$searchArray[0]%' $linktoText";

            }

            //*preparacion de la sentencia SQL:
            //nos conectamos a la BD y le pasamos la consulta
            $stmt= Connection::connect()->prepare($sql);
            
            //* Enlazamos parametros
            // foreach para enlazar los AND tambien a la consulta SQL
            foreach ($linkToArray as $key => $value) {
                //bindParam(":".parametro_enlace, $parametro_a_enlazar): enlazar parametros
                //ejemplo binParam(":".)
                //$key= id de cada linkToArray, el cual aumenta en cada iteracion
                
                //en este caso lo hacemos desde la posicion 1, ya que en la
                //posicion 0 se recibe la palabra que se va a buscar
                if($key > 0){
                    $stmt->bindParam(":".$value, $searchArray[$key], PDO::PARAM_STR);
                    //Ejemplo de lo anterior
                    //:title_course = $equalToArray[$key]
                }
            }
        
            //Try-catch para capturar errores
            try{
                //*lo ejecutamos
                $stmt->execute();
    
            }catch(PDOException $Exception){
                return null;
            }

            //*retornamos lo que responda con un fetchAll()
            //FETCH_CLASS: para que no muestre los numero de indices
            //en el objeto
            return $stmt-> fetchAll(PDO::FETCH_CLASS);
    

        }else{
            //si no viene mas de una tabla devolvemos valor nulo
            return null;
        }
    }

    //*=============================================
    //*Peticiones GET para seleccion de rangos
    //*=============================================*/

    static public function getDataRange($table, $select, $linkTo, $between1, $between2, $orderBy, $orderMode,$startAt, $endAt, $filterTo, $inTo){
         
       //*Validar existencia de una tabla y las columnas en la bd
        //filtrar varios valores a la consulta
        $linkToArray = explode(",",$linkTo);

        //verificar si en la consulta viene filtros y 
        //asi hacer el explode
        if($filterTo != null){
           $filterToArray = explode(",",$filterTo);
        }else{
        //si en la consulta no viene con filtro devolvemos 
        //el array nulo
            $filterToArray= array();
        }

        $selectArray = explode(",",$select);

        foreach ($linkToArray as $key => $value) {
            //array_push(array donde queremos agregar informacion (primer paramettro),
            // lo que queremos agregar (segundo parametro))
            array_push($selectArray, $value);
        }

        foreach ($filterToArray as $key => $value) {
            //array_push(array donde queremos agregar informacion (primer paramettro),
            // lo que queremos agregar (segundo parametro))
            array_push($selectArray, $value);
        }

        $selectArray = array_unique($selectArray);

        //*Validar existencia de una tabla en la bd
        if(empty(Connection::getColumnsData($table,$selectArray))){
            return null;
        }

        $filter= "";

        if($filterTo != null && $inTo != null){
            $filter= 'AND '.$filterTo.' IN ('.$inTo.')';
        }

        //*Consultas a la base de datos
        //consulta con ordenamiento pero sin limitacion
        if($orderBy != null && $orderMode != null && $startAt == null && $endAt == null){
            $sql= "SELECT $select FROM $table WHERE $linkTo BETWEEN '$between1' AND '$between2' $filter ORDER BY $orderBy $orderMode";
        //consulta con ordenamiento y con limitacion
        }elseif($orderBy != null && $orderMode != null && $startAt != null && $endAt != null){
            $sql= "SELECT $select FROM $table WHERE $linkTo BETWEEN '$between1' AND '$between2' $filter ORDER BY $orderBy $orderMode LIMIT $startAt,$endAt";
        //consulta con limitacion pero sin ordenamiento
        }elseif($orderBy == null && $orderMode == null && $startAt != null && $endAt != null){
            $sql= "SELECT $select FROM $table WHERE $linkTo BETWEEN '$between1' AND '$between2' $filter LIMIT $startAt,$endAt";
        }else{
            //consulta sin filtrar, ni limitar,  ni ordenar datos   
            //consulta para seleccionar un campo en especifico
            $sql= "SELECT $select FROM $table WHERE $linkTo BETWEEN '$between1' AND '$between2' $filter ";
        }

        //*preparacion de la sentencia SQL:
        //nos conectamos a la BD y le pasamos la consulta
        $stmt= Connection::connect()->prepare($sql);

        //Try-catch para capturar errores
        try{
            //*lo ejecutamos
            $stmt->execute();

        }catch(PDOException $Exception){
            return null;
        }

        //*retornamos lo que responda con un fetchAll()
        //FETCH_CLASS: para que no muestre los numero de indices
        //en el objeto
        return $stmt-> fetchAll(PDO::FETCH_CLASS);
    }


    //*===================================================================
    //*Peticiones GET para seleccion de rangos entre tablas relacionadas
    //*===================================================================*/

    static public function getRelDataRange($rel,$type, $select, $linkTo, $between1, $between2, $orderBy, $orderMode,$startAt, $endAt, $filterTo, $inTo){
         
        //*Validar existencia de una tabla y las columnas en la bd
        //filtrar varios valores a la consulta
        $linkToArray = explode(",",$linkTo);
        
        $filter= "";

        if($filterTo != null && $inTo != null){
            $filter= 'AND '.$filterTo.' IN ('.$inTo.')';
        }

        $relArray = explode(",", $rel);
        $typeArray = explode(",", $type);
        $innerJoinText="";

        //concatenar los INNER JOIN en la sentencia SQL
        if(count($relArray)>1){

            //recorremos todos AND existentes en la consulta SQL
            foreach ($relArray as $key => $value) {
                 
                //*Validar existencia de una tabla en la bd
                if(empty(Connection::getColumnsData($value, ["*"]))){
                    return null;
                }


                //ejecutar cuando $key sea mayor que 0, ya que la posicion cero esta la tabla
                //principal y las que necesitamos colocar luego del INNER JOIN son las otras  
                //tablas con las que se relacionara la tabla principal
                //SI QUEREMOS obtener el valor del tabla principal forzamos colocando $relArray[0]
                if($key > 0){
                   //consulta modelo para crear la consulta en "innerJoinText"
                  // "SELECT $select FROM $relArray[0] INNER JOIN $relArray[1] ON $relArray[0].id_$typeArray[1]_$typeArray[0] = $relArray[1].id_$typeArray[1] ";
                    
                    $innerJoinText .= "INNER JOIN ".$value." ON ".$relArray[0].".id_".$typeArray[$key]."_".$typeArray[0]." = ". $value.".id_".$typeArray[$key]." ";

                }
            }
        

            //*Consultas a la base de datos
            //consulta con ordenamiento pero sin limitacion
            if($orderBy != null && $orderMode != null && $startAt == null && $endAt == null){
                $sql= "SELECT $select FROM $relArray[0] $innerJoinText WHERE $linkTo BETWEEN '$between1' AND '$between2' $filter ORDER BY $orderBy $orderMode";
            //consulta con ordenamiento y con limitacion
            }elseif($orderBy != null && $orderMode != null && $startAt != null && $endAt != null){
                $sql= "SELECT $select FROM $relArray[0] $innerJoinText WHERE $linkTo BETWEEN '$between1' AND '$between2' $filter ORDER BY $orderBy $orderMode LIMIT $startAt,$endAt";
            //consulta con limitacion pero sin ordenamiento
            }elseif($orderBy == null && $orderMode == null && $startAt != null && $endAt != null){
                $sql= "SELECT $select FROM $relArray[0] $innerJoinText WHERE $linkTo BETWEEN '$between1' AND '$between2' $filter LIMIT $startAt,$endAt";
            }else{
                //consulta sin filtrar, ni limitar,  ni ordenar datos   
                //consulta para seleccionar un campo en especifico
                $sql= "SELECT $select FROM $relArray[0] $innerJoinText WHERE $linkTo BETWEEN '$between1' AND '$between2' $filter ";
            }

            //*preparacion de la sentencia SQL:
            //nos conectamos a la BD y le pasamos la consulta
            $stmt= Connection::connect()->prepare($sql);

            //Try-catch para capturar errores
            try{
                //*lo ejecutamos
                $stmt->execute();
    
            }catch(PDOException $Exception){
                return null;
            }

            //*retornamos lo que responda con un fetchAll()
            //FETCH_CLASS: para que no muestre los numero de indices
            //en el objeto
            return $stmt-> fetchAll(PDO::FETCH_CLASS);

        }else{
            return null;
        }

    }




}//FIN
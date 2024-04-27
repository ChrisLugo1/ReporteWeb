<?php
    include("../../../../../include/conexion.php");
    include("../../../../dominio_edilar.php");
    function getTiempo() 
    { 
        list($usec, $sec) = explode(" ",microtime()); 
        return ((float)$usec + (float)$sec); 
    }
    $TiempoInicial = getTiempo();
    $db=conecta_db();
    $request=array();
    $request['ERROR'] = false;
    $request['EMPRESAS']=array();
    
    //CONSULTA DE EMPRESAS
    $query="SELECT EMPRESA_ID, 
                    NOMBRE_CORTO
            FROM   CM_ESTRUCTURA_EMPRESA
            ORDER BY NOMBRE_CORTO";
    $res=OCIParse($db,$query);
    OCIExecute($res,OCI_DEFAULT);
    $arrDatos=array();
    while(OCIFetchInto($res,$arrDatos,OCI_ASSOC+OCI_RETURN_NULLS))
    {
        $arr=array();
        $arr['VALUE']=$arrDatos['EMPRESA_ID'];
        $arr['TEXT']=$arrDatos['NOMBRE_CORTO'];
        $request['EMPRESAS'][]=$arr;
    }
    OCIFreeStatement($res);
    
    //CONSULTA DE ESTADOS
    $query="SELECT ESTADO_ID, DESCRIPCION
            FROM   CM_ESTADO
            ORDER BY ESTADO_ID ASC";
    $res=OCIParse($db,$query);
    OCIExecute($res,OCI_DEFAULT);
    $arrDatos=array();
    while(OCIFetchInto($res,$arrDatos,OCI_ASSOC+OCI_RETURN_NULLS))
    {
        $arr=array();
        $arr['VALUE']=$arrDatos['ESTADO_ID'];
        $arr['TEXT']=$arrDatos['DESCRIPCION'];
        $request['ESTADOS'][]=$arr;
    }
    OCIFreeStatement($res);
    
    cerrar_db($db);
    echo json_encode($request);
?>
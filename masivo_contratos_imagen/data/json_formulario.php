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
    $request['TIPO_IMG']=array();

    $user_name = $_SESSION['GLOBAL_USER'];
    
    $queryUsu="SELECT DEPARTAMENTO
    FROM CL_SYS_USUARIO
    WHERE USUARIO_ID='".$user_name."' ";
    $resUsu=OCIParse($db,$queryUsu);
    OCIExecute($resUsu,OCI_DEFAULT);
    OCIFetchInto($resUsu,$arrDatosUsu,OCI_ASSOC+OCI_RETURN_NULLS);
    $DEPTO=$arrDatosUsu['DEPARTAMENTO'];

    if(($DEPTO=='CGS')||($DEPTO=='COB')||($DEPTO=='SIS')||($DEPTO=='TEL'))
    {
        $query="SELECT *
                        FROM CL.CM_TIPO_IMAGEN
                        WHERE CLASIFICACION='CON'
                        AND TIPO_ID LIKE 'NC%'";
        $res=OCIParse($db,$query);
        //OCIBindByName($res,":P_CANAL_VENTA",$_REQUEST['tipo_venta']);
        OCIExecute($res,OCI_DEFAULT);

        $arrDatos=array();
        while(OCIFetchInto($res,$arrDatos,OCI_ASSOC+OCI_RETURN_NULLS))
        {
            $arr=array();
            $arr['VALUE']=$arrDatos['TIPO_ID'];
            $arr['TEXT']=$arrDatos['DESCRIPCION'];
            $request['TIPO_IMG'][]=$arr;
        }
        OCIFreeStatement($res);

        //QUERY ESTADOS
        $query="SELECT ESTADO_ID, DESCRIPCION
                FROM   CM_ESTADO
                ORDER BY ESTADO_ID ASC";
        $res=OCIParse($db,$query);
        //OCIBindByName($res,":P_CANAL_VENTA",$_REQUEST['tipo_venta']);
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
    }
    echo json_encode($request);
?>
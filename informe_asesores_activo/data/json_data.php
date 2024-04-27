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
    $request = array();
    $request['ERROR'] = false;
    $request['SCRIPT'] = array();
    $request['CONSULTA'] = "";
    $request['datos'] = array();
    $request['datos']['datos_x_estado'] = array();
    $data = json_decode($_POST['data']);
    $estados = json_decode($_POST['state']);

    $request['DATOS_FORM'] = $data;

    $valores = "";
    if($data->P_EMPRESA == "SOF" || $data->P_EMPRESA == "PET" || $data->P_EMPRESA == "PDC"  || $data->P_EMPRESA == "CLD")
    {
        $valores = "START WITH CLAVE_JEFE = '5158' CONNECT BY PRIOR CLAVE_ASESOR = CLAVE_JEFE ORDER SIBLINGS BY ESTADO, PUESTO";
    }
    else if($data->P_EMPRESA == "RCD" || $data->P_EMPRESA == "RDP")
    {
        $valores = "START WITH CLAVE_JEFE = '5158CC7' CONNECT BY PRIOR CLAVE_ASESOR = CLAVE_JEFE ORDER SIBLINGS BY ESTADO, PUESTO";
    }
    else
    {
        $valores = "START WITH CLAVE_JEFE = '4188CC6' CONNECT BY PRIOR CLAVE_ASESOR = CLAVE_JEFE ORDER SIBLINGS BY ESTADO, PUESTO";
    }

    $where_estado = "";
    if($data->P_TEXT_1 != "" && $data->P_TEXT_2 != "")
    {
        $where_estado = "WHERE ESTADO BETWEEN :P_ESTADO_INICIAL AND :P_ESTADO_FINAL";
        $valores = "";
    }
    

    $query = "SELECT EMPLEADO,
                        NOMBRE,
                        PUESTO,
                        DIRECCION,
                        CIUDAD,
                        COLONIA,
                        CP,
                        ESTADO,
                        TELCASA,
                        TELRECADOS,
                        TEL_MOVIL,
                        E_MAIL
                    FROM(
                    SELECT EMPLEADO,
                        INITCAP(PATERNO||' '||MATERNO||' '||NOMBRE) NOMBRE,
                        RTRIM(LTRIM(TO_CHAR(EMPLEADO))) CLAVE_ASESOR,
                        RTRIM(LTRIM(JEFE)) CLAVE_JEFE,
                        NIVEL PUESTO,
                        EMP.DIRECCION,
                        EMP.CIUDAD,
                        EMP.COLONIA,
                        EMP.CP,
                        EMP.ESTADO,
                        EMP.TELEFONO1 TELCASA,
                        EMP.TELEFONO2 TELRECADOS,
                        EMP.TEL_MOVIL,
                        EMP.E_MAIL
                    FROM   CM_ESTRUCTURA_VENTAS_DET DET,
                        CM_EMPLEADO EMP
                    WHERE  EMP.STATUS = 'A'
                    AND    DET.EMPRESA = :P_EMPRESA
                    AND    DET.EMPLEADO = EMP.EMPLEADO_ID
                    UNION ALL
                    SELECT DISTINCT
                        KAR.EMPLEADO,
                        INITCAP(EMP.PATERNO||' '||EMP.MATERNO||' '||EMP.NOMBRE) NOMBRE,
                        KAR.EMPLEADO||KAR.CLAVE_VENDEDOR CLAVE_ASESOR,
                        KAR.JEFE||CLAVE_JEFE CLAVE_JEFE,
                        KAR.PUESTO,
                        EMP.DIRECCION,
                        EMP.CIUDAD,
                        EMP.COLONIA,
                        EMP.CP,
                        REG.ESTADO,
                        EMP.TELEFONO1 TELCASA,
                        EMP.TELEFONO2 TELRECADOS,
                        EMP.TEL_MOVIL,
                        EMP.E_MAIL
                    FROM   CM_KARDEX_ESTRUCTURA KAR,
                        CM_KARDEX_REGIONES_EMPLEADO REG,
                        CM_EMPLEADO EMP
                    WHERE  KAR.FOLIO = REG.FOLIO
                    AND    KAR.EMPLEADO = EMP.EMPLEADO_ID
                    AND    KAR.EMPRESA = REG.EMPRESA
                    AND    KAR.CLAVE_VENDEDOR = REG.CLAVE_VENDEDOR
                    AND    KAR.EMPRESA = :P_EMPRESA
                    AND    EMP.STATUS = 'A'
                    AND    KAR.FOLIO = (SELECT DISTINCT FOLIO FROM CM_ESTRUCTURA_EMPLEADO WHERE EMPRESA = :P_EMPRESA))
                    ".$where_estado."
                    ".$valores."";
    $res = OCIParse($db, $query);
    OCIBindByName($res, ":P_EMPRESA",$data->P_EMPRESA);
    if($data->P_TEXT_1 != "" && $data->P_TEXT_2 != "")
    {
        OCIBindByName($res, ":P_ESTADO_INICIAL",$data->P_TEXT_1);
        OCIBindByName($res, ":P_ESTADO_FINAL",$data->P_TEXT_2);
    }
    OCIExecute($res,OCI_DEFAULT);

    $request['datos']['campos']=array();
    $request['datos']['campos'][]=["COLUMNA" => "Clave Int", "NOMBRE"=>"EMPLEADO", "TIPO"=>"VARCHAR2"];
    $request['datos']['campos'][]=["COLUMNA" => "Puesto", "NOMBRE"=> "PUESTO", "TIPO"=> "VARCHAR2"];
    $request['datos']['campos'][]=["COLUMNA" => "Nombre", "NOMBRE"=> "NOMBRE", "TIPO"=> "VARCHAR2"];
    $request['datos']['campos'][]=["COLUMNA" => "Dirección", "NOMBRE"=> "DIRECCION", "TIPO"=> "VARCHAR2"];
    $request['datos']['campos'][]=["COLUMNA" => "Ciudad", "NOMBRE"=> "CIUDAD", "TIPO"=> "VARCHAR2"];
    $request['datos']['campos'][]=["COLUMNA" => "Colonia", "NOMBRE"=> "COLONIA", "TIPO"=> "VARCHAR2"];
    $request['datos']['campos'][]=["COLUMNA" => "CP", "NOMBRE"=> "CP", "TIPO"=> "VARCHAR2"];
    $request['datos']['campos'][]=["COLUMNA" => "Estado", "NOMBRE"=> "ESTADO", "TIPO"=> "VARCHAR2"];
    $request['datos']['campos'][]=["COLUMNA" => "Tel. Casa", "NOMBRE"=> "TELCASA", "TIPO"=> "VARCHAR2"];
    $request['datos']['campos'][]=["COLUMNA" => "Tel Recado", "NOMBRE"=> "TELRECADOS", "TIPO"=> "VARCHAR2"];
    $request['datos']['campos'][]=["COLUMNA" => "Movil", "NOMBRE"=> "TEL_MOVIL", "TIPO"=> "VARCHAR2"];
    $request['datos']['campos'][]=["COLUMNA" => "E-MAIL", "NOMBRE"=> "E_MAIL", "TIPO"=> "VARCHAR2"];
    
    while(OCIFetchInto($res,$arrDatos,OCI_ASSOC+OCI_RETURN_NULLS))
    {
        while(list($id,$valor)=each($estados))
        {
            if($arrDatos['ESTADO'] == $valor->VALUE)
            {
                $ESTADO = $valor->TEXT;
                $arrDatos['ESTADO']=$valor->TEXT;
            }
        }
        reset($estados);
        $request['datos']['datos'][]=$arrDatos;
        $request['datos']['datos_x_estado'][$ESTADO][]=$arrDatos;
    }

    $request['CONSULTA'] = $query;
    //sleep(20);
    $request['datos']['titulo']='Asesores Activos';
    OCIFreeStatement($res);
    cerrar_db($db);
    
    $TiempoFinal = getTiempo(); 
    $Tiempo = $TiempoFinal - $TiempoInicial; 
    $Tiempo = round($Tiempo,2); 
    $request['SCRIPT']['TIME'] = $Tiempo;
    echo json_encode($request);
?>
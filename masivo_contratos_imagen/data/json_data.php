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
    //$database_desarrollo=conecta_desarrollo();
    $request = array();
    $request['SCRIPT'] = array();
    // VARIABLE PRINCIPAL DE DATOS
    $datos = json_decode($_POST['data']);
    $estados = json_decode($_POST['estados']);

    $request['DATOS_ENVIADOS'] = json_decode($_POST['data']);
    $request['ESTADOS'] = $estados;

    $num_registros = count($datos->datos);
    if($datos->datos[$num_registros-1]->CONTRATO_PRIN == "")
    {
        unset($datos->datos[$num_registros-1]);
    }


    $REGISTROS = [];
    while(list($id, $info)=each($datos->datos))
    {
        $query = "SELECT CON.CONTRATO_PRIN_ID,
                        CON.AFILIADO,
                        INITCAP (AFIL.PATERNO || ' ' || AFIL.MATERNO || ' ' || AFIL.NOMBRE)
                        NOMBRE_EMPLEADO,
                        EVE.EMPLEADO,
                        EVE.FECHA,
                        EVE.EVENTO_ID,
                        EVE.ESTADO
                FROM 
                    CL.CM_CONTRATO_PRINCIPAL CON,
                    CL.CM_EVENTO EVE,
                    CL.CM_AFILIADO AFIL
                WHERE CON.EVENTO = EVE.EVENTO_ID
                AND AFIL.AFILIADO_ID = CON.AFILIADO
                AND CON.CONTRATO_PRIN_ID = :P_CONTRATO_PRIN";
            $res=OCIParse($db,$query);
        //    Agrega variables al QUERY
            OCIBindByName($res,":P_CONTRATO_PRIN",$info->CONTRATO_PRIN);
            OCIExecute($res,OCI_DEFAULT);
            $arrDatos = array();
            while(OCIFetchInto($res,$arrDatos,OCI_ASSOC+OCI_RETURN_NULLS))
            {
                $REGISTROS['CONTRATO_PRIN'] = $arrDatos['CONTRATO_PRIN_ID'];
                $REGISTROS['AFILIADO'] = $arrDatos['AFILIADO'];
                $REGISTROS['NOMBRE_EMPLEADO'] = $arrDatos['NOMBRE_EMPLEADO'];
                $REGISTROS['EMPLEADO'] = $arrDatos['EMPLEADO'];
                $REGISTROS['FECHA'] = $arrDatos['FECHA'];
                $REGISTROS['STATE'] = $arrDatos['ESTADO'];
                $REGISTROS['CORRECTOS'] = '';
                while(list($i, $valor)=each($estados))
                {
                    if($arrDatos['ESTADO'] == $valor->VALUE)
                    {
                        $REGISTROS['ESTADO'] = $valor->TEXT;
                    }
                }
                reset($estados);
                //$REGISTROS['ESTADO'] = $ESTADO;
                $REGISTROS['EVENTO'] = $info->EVENTO;
                $arrDatos = [];
            }
            OCIFreeStatement($res);
            $request['DATOS'][] = $REGISTROS;
    }
    reset($datos->datos);

    $request['campos']=array();
    $request['campos'][]=["COLUMNA" => "Contrato", "NOMBRE"=>"CONTRATO_PRIN", "TIPO"=>"VARCHAR2"];
    $request['campos'][]=["COLUMNA" => "Afiliado", "NOMBRE"=> "AFILIADO", "TIPO"=> "VARCHAR2"];
    $request['campos'][]=["COLUMNA" => "Evento", "NOMBRE"=> "EVENTO", "TIPO"=> "VARCHAR2"];
    $request['campos'][]=["COLUMNA" => "Nombre", "NOMBRE"=> "NOMBRE_EMPLEADO", "TIPO"=> "VARCHAR2"];
    $request['campos'][]=["COLUMNA" => "Estado", "NOMBRE"=> "ESTADO", "TIPO"=> "VARCHAR2"];
    $request['campos'][]=["COLUMNA" => "Estatus", "NOMBRE"=> "CORRECTOS", "TIPO"=> "IMG"];

    
    cerrar_db($db);
    $TiempoFinal = getTiempo(); 
    $Tiempo = $TiempoFinal - $TiempoInicial; 
    $Tiempo = round($Tiempo,2); 
    $request['SCRIPT']['TIME'] = $Tiempo;
    echo json_encode($request);
?>
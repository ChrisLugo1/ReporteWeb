<?php
    include("../../../../../include/conexion.php");
    include("../../../../dominio_edilar.php");
    include("../../../super_clases/clases_ftp.php");
    function getTiempo() 
    {
        list($usec, $sec) = explode(" ",microtime()); 
        return ((float)$usec + (float)$sec); 
    }
    $TiempoInicial = getTiempo();
    //$db=conecta_db();
    $db=conecta_desarrollo();
    $request = array();
    $request['ERROR'] = false;
    $request['ERROR_ARCHIVO'] = false;
    $request['SCRIPT'] = array();
    // VARIABLE PRINCIPAL DE DATOS
    $tipos_img = json_decode($_POST['tipos_imagen']);
    $datos = json_decode($_POST['data']);
    $data_img_name = $_FILES['data_img']['name'];
    $data_img_file = $_FILES['data_img']['tmp_name'];
    $user_name = $_SESSION['GLOBAL_USER'];

    /*
    //eliminar ultimo registro vacio para que no se inserte
    unset($datos[$request['ULTIMO']]);
    */

    $request['DATOS'] = $datos;
    $TIPOS_IMAGEN = array();
    $FILES = array();
    $FILES_ERROR = array();
    while(list($id, $campo)=each($datos))
    {
        //contar recorrido de datos
        $count = 0;
        while(list($num_reg, $tipo_imagen)=each($tipos_img))
        {
            $ARCHIVO='';
            $EVENTO = $campo->EVENTO;
            $Estado = $campo->STATE;
            //obtener fecha y estado del evento configurar la carpeta y archivo
            $Fecha_Evento=date("d/m/Y",strtotime($campo->FECHA));
            $Fecha_Ev = explode("/",$Fecha_Evento);
            $DIRECTORIO=$Fecha_Ev[2].'/'.$Fecha_Ev[1].'/'.$Estado.'/'.$campo->EVENTO;
            $ARCHIVO = $campo->CONTRATO_PRIN.'_'.$tipo_imagen->VALUE.'.jpg';
            
            //identificar si existe x imagen ya asignada
            $queryTipo_Img="SELECT count(*) TOTAL 
                                FROM CM_ASIGNACION_IMAGEN
                                WHERE IDENTIFICADOR = :P_CONTRATO_PRIN
                                AND TIPO = :P_TIPO";
            $resTipo=OCIParse($db,$queryTipo_Img);
            OCIBindByName($resTipo, ":P_CONTRATO_PRIN",$campo->CONTRATO_PRIN);
            OCIBindByName($resTipo, ":P_TIPO",$tipo_imagen->VALUE);
            OCIExecute($resTipo,OCI_DEFAULT);
            while(OCIFetchInto($resTipo,$arrTipo,OCI_ASSOC+OCI_RETURN_NULLS))
            {
                $ExisteTipo=$arrTipo['TOTAL'];
            }
            OCIFreeStatement($resTipo);
            $TIPOS_IMAGEN[$campo->CONTRATO_PRIN] = $ExisteTipo;

            $request['EXISTENCIAS'][] = ['CONTRATO_PRIN' => $campo->CONTRATO_PRIN, 'TOTAL' => $ExisteTipo, 'TIPO' => $tipo_imagen->VALUE];
            //$request['EXISTENCIAS'] = $TIPOS_IMAGEN;
            if($count == 0)
            {
                $conecta_ftp = new ftp_adminContratos39(); //test
                //$conecta_ftp = new ftp_adminContratos(); produccion
                if($TIPOS_IMAGEN[$campo->CONTRATO_PRIN]==0)
                {
                    $conecta_ftp->crear_directorio($DIRECTORIO);
                    $request['CORRECTOS'][] = ['CONTRATO_PRIN' => $campo->CONTRATO_PRIN, 'EVENTO' => $campo->EVENTO, 'TOTAL' => $ExisteTipo, 'TIPO' => $tipo_imagen->VALUE];
                    if($conecta_ftp->cargar_archivo($ARCHIVO,$data_img_file))
                    {
                        //move_uploaded_file($data_img_file,'../files/'.$DIRECTORIO.'/'.$ARCHIVO)
                        //FALTA VALIDAR PORQUE NO GUARDA EL ARCHIVO Y EN LA BASE DE DATOS SOLO GUARDA UNO
                        $ruta_archivo="sistemas10g".chr(92)."img".chr(92)."Contratos".chr(92)."".$Fecha_Ev[2]."".chr(92)."".$Fecha_Ev[1]."".chr(92)."".$Estado."".chr(92)."".$EVENTO."".chr(92)."".$ARCHIVO;
                        $CLASIFICACION = 'CON';
                        $ARCHIVO = chr(92)."".chr(92)."".$ruta_archivo;
                        $query_asignacion_img="INSERT INTO CM_ASIGNACION_IMAGEN 
                                                (
                                                    CLASIFICACION, 
                                                    TIPO, 
                                                    ARCHIVO, 
                                                    GRABADA, 
                                                    FECHA_ASIGNACION, 
                                                    IDENTIFICADOR
                                                )
                                                VALUES 
                                                (
                                                    :P_CLASIFICACION, 
                                                    :P_TIPO, 
                                                    :P_ARCHIVO, 
                                                    'S', 
                                                    SYSDATE,
                                                    :P_IDENTIFICADOR
                                                )";
                        $res1=OCIParse($db,$query_asignacion_img);
                        OCIBindByName($res1,":P_CLASIFICACION",$CLASIFICACION);
                        OCIBindByName($res1,":P_TIPO",$tipo_imagen->VALUE);
                        OCIBindByName($res1,":P_ARCHIVO",$ARCHIVO);
                        OCIBindByName($res1,":P_IDENTIFICADOR",$campo->CONTRATO_PRIN);
                        OCIExecute($res1,OCI_DEFAULT);
                        
                        $queryIA="SELECT  count(*) REGISTROS
                                        FROM  CM_ASIGNACION_IMAGEN_BIT
                                        WHERE   IDENTIFICADOR = ".$campo->CONTRATO_PRIN."
                                                AND TIPO = '".$tipo_imagen->VALUE."'";
                        $resIA=OCIParse($db,$queryIA);
                        OCIExecute($resIA,OCI_DEFAULT);
                        while(OCIFetchInto($resIA,$arrDatosIA,OCI_ASSOC+OCI_RETURN_NULLS))
                        {
                            if($arrDatosIA['REGISTROS']==0)
                            {
                                $T_MOV='A';
                            }
                            else
                            {
                                $T_MOV='R';
                            }
                        }
                        $CLASIFICACION = 'CON';
                        $ARCHIVO = chr(92)."".chr(92)."".$ruta_archivo;
                        $queryBit="INSERT INTO CM_ASIGNACION_IMAGEN_BIT
                                            (
                                                CLASIFICACION, 
                                                TIPO, 
                                                ARCHIVO, 
                                                USUARIO, 
                                                FECHA_MOVIMIENTO, 
                                                TIPO_MOVIMIENTO, 
                                                IDENTIFICADOR
                                            )
                                            VALUES 
                                            (
                                                :P_CLASIFICACION, 
                                                :P_TIPO, 
                                                :P_ARCHIVO, 
                                                :P_USUARIO, 
                                                SYSDATE,
                                                :P_TIPO_MOVIMIENTO,
                                                :P_IDENTIFICADOR
                                            )";
                        $resBit=OCIParse($db,$queryBit);
                        OCIBindByName($resBit,":P_CLASIFICACION",$CLASIFICACION);
                        OCIBindByName($resBit,":P_TIPO",$tipo_imagen->VALUE);
                        OCIBindByName($resBit,":P_ARCHIVO",$ARCHIVO);
                        OCIBindByName($resBit,":P_USUARIO",$user_name);
                        OCIBindByName($resBit,":P_TIPO_MOVIMIENTO",$T_MOV);
                        OCIBindByName($resBit,":P_IDENTIFICADOR",$campo->CONTRATO_PRIN);
                        OCIExecute($resBit,OCI_DEFAULT);
                        if(OCIError($resBit))
                        {
                            $request['ERROR'] = true;
                            OCIrollback($db);
                            $request['MENSAJE']='ERROR EN IMAGEN_BIT';
                        }
                        else
                        {
                            $request['ERROR'] = false;
                            //$request['PROCESO']['ID'] = $id;
                            $request['MENSAJE']='SE GUARDO CORRECTAMENTE';
                            OCICommit($db);
                        }
                        OCIFreeStatement($resBit);
                        //$request['FILE_CORRECTOS'] = $TIPOS_IMAGEN;
                    }
                    else
                    {
                        $request['ERROR_ARCHIVO'] = 'ERROR EN ARCHIVO CARGADO';
                        //$request['FILE_INCORRECTOS'] = $TIPOS_IMAGEN;
                    }
                    $conecta_ftp->cerrar_conexion();
                    $count++;
                }
                else
                {
                    if ($tipo_imagen->VALUE == "NC4")
                    {
                        $request['INCORRECTOS'][] = ['CONTRATO_PRIN' => $campo->CONTRATO_PRIN, 'EVENTO' => $campo->EVENTO, 'TOTAL' => $ExisteTipo, 'TIPO' => $tipo_imagen->VALUE];
                        $request['ERROR_ARCHIVO'][$campo->EVENTO] = 'YA EXISTE ARCHIVO Y REGISTRO';
                    }
                    else
                    {
                        $request['INCORRECTOS'] = [];
                    }
                }
            }
        }
        reset($tipos_img);
    }
    reset($datos);
    
    
    cerrar_db($db);
    $TiempoFinal = getTiempo(); 
    $Tiempo = $TiempoFinal - $TiempoInicial; 
    $Tiempo = round($Tiempo,2); 
    $request['SCRIPT']['TIME'] = $Tiempo;
    echo json_encode($request);
?>
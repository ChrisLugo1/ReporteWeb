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
    //$db=conecta_desarrollo();
    $request = array();
    $request['ERROR'] = false;
    $request['SCRIPT'] = array();
    $request['CONSULTA'] = "";
    $request['datos'] = array();
    $request['datos']['datos_x_estado'] = array();
    $datos_form = json_decode($_POST['data']);
    $estados = json_decode($_POST['state']);

    $request['DATOS_FORM'] = $datos_form;

    $query = "SELECT REG.ESTADO_ID,
                        REGION_ID,
                        (SELECT NVL(SUM(TOTAL_APAGAR),0)
                        FROM   CM_CONTRATO_PRINCIPAL CON,
                                CM_EVENTO EVE,
                                CM_STATUS_VTA ST,
                                CS_VIGENCIAS_COMISION VIG
                        WHERE  CON.EVENTO = EVE.EVENTO_ID
                        AND    CON.STATUS = ST.STATUS_ID
                        AND    CON.COMISION = VIG.COMISION_ID
                        AND    ST.COBRANZA = 'S'
                        AND    VIG.MEDICION <> 'DIF'
                        AND    CON.SUBSIDIO IS NULL
                        AND    EVE.ESTADO = REG.ESTADO_ID
                        AND    EVE.REGION = REGION_ID
                        AND    CON.TIPO_VENTA = :P_TIPO_VENTA
                                                AND    EVE.EMPRESA = :P_EMPRESA
                        AND    EVE.FECHA BETWEEN TO_DATE(:P_FECHA_INICIAL,'DD/MM/RRRR') AND TO_DATE(:P_FECHA_FINAL,'DD/MM/RRRR')) COMISION_CONTADO,
                        (SELECT NVL(SUM(TOTAL_APAGAR),0)
                        FROM   CM_CONTRATO_PRINCIPAL CON,
                                CM_EVENTO EVE,
                                CM_STATUS_VTA ST,
                                CS_VIGENCIAS_COMISION VIG
                        WHERE  CON.EVENTO = EVE.EVENTO_ID
                        AND    CON.STATUS = ST.STATUS_ID
                        AND    CON.COMISION = VIG.COMISION_ID
                        AND    ST.COBRANZA = 'S'
                        AND    VIG.MEDICION <> 'DIF'
                        AND    CON.SUBSIDIO = 'S'
                        AND    EVE.ESTADO = REG.ESTADO_ID
                        AND    EVE.REGION = REGION_ID
                        AND    CON.TIPO_VENTA = :P_TIPO_VENTA
                                                AND    EVE.EMPRESA = :P_EMPRESA
                        AND    EVE.FECHA BETWEEN TO_DATE(:P_FECHA_INICIAL,'DD/MM/RRRR') AND TO_DATE(:P_FECHA_FINAL,'DD/MM/RRRR')) COMISION_SUBSIDIO,
                        (SELECT NVL(SUM(DECODE(CON.TIPO_VENTA,'PCM',DECODE(CON.CLASIFICACION,'R',CON.CAPITAL_DISPERSAR,CON.ENGANCHE),TOTAL_APAGAR)),0)
                        FROM   CM_CONTRATO_PRINCIPAL CON,
                                CM_EVENTO EVE,
                                CM_STATUS_VTA ST,
                                CS_VIGENCIAS_COMISION VIG
                        WHERE  CON.EVENTO = EVE.EVENTO_ID
                        AND    CON.STATUS = ST.STATUS_ID
                        AND    CON.COMISION = VIG.COMISION_ID
                        AND    ST.COBRANZA = 'S'
                        AND    VIG.MEDICION <> 'DIF'
                        AND    EVE.ESTADO = REG.ESTADO_ID
                        AND    EVE.REGION = REGION_ID
                        AND    CON.TIPO_VENTA = :P_TIPO_VENTA
                                                AND    EVE.EMPRESA = :P_EMPRESA
                        AND    EVE.FECHA BETWEEN TO_DATE(:P_FECHA_INICIAL,'DD/MM/RRRR') AND TO_DATE(:P_FECHA_FINAL,'DD/MM/RRRR')) COMISION_CONTADO_NUEVO,
                        (SELECT NVL(SUM(TOTAL_APAGAR),0)
                        FROM   CM_CONTRATO_PRINCIPAL CON,
                                CM_EVENTO EVE,
                                CM_STATUS_VTA ST,
                                CS_VIGENCIAS_COMISION VIG
                        WHERE  CON.EVENTO = EVE.EVENTO_ID
                        AND    CON.STATUS = ST.STATUS_ID
                        AND    CON.COMISION = VIG.COMISION_ID
                        AND    ST.COBRANZA = 'S'
                        AND    VIG.MEDICION = 'DIF'
                        AND    EVE.ESTADO = REG.ESTADO_ID
                        AND    EVE.REGION = REGION_ID
                        AND    CON.TIPO_VENTA = :P_TIPO_VENTA
                                                AND    EVE.EMPRESA = :P_EMPRESA
                        AND    EVE.FECHA BETWEEN TO_DATE(:P_FECHA_INICIAL,'DD/MM/RRRR') AND TO_DATE(:P_FECHA_FINAL,'DD/MM/RRRR')
                        AND    CON.NUM_PERIODOS IN (72,108)) COMISION_DIF_72_108,
                        (SELECT NVL(SUM(CON.CUOTA*54),0)
                        FROM   CM_CONTRATO_PRINCIPAL CON,
                                CM_EVENTO EVE,
                                CM_STATUS_VTA ST,
                                CS_VIGENCIAS_COMISION VIG
                        WHERE  CON.EVENTO = EVE.EVENTO_ID
                        AND    CON.STATUS = ST.STATUS_ID
                        AND    CON.COMISION = VIG.COMISION_ID
                        AND    ST.COBRANZA = 'S'
                        AND    VIG.MEDICION = 'DIF'
                        AND    EVE.ESTADO = REG.ESTADO_ID
                        AND    EVE.REGION = REGION_ID
                        AND    CON.TIPO_VENTA = :P_TIPO_VENTA
                                                AND   EVE.EMPRESA = :P_EMPRESA
                        AND    EVE.FECHA BETWEEN TO_DATE(:P_FECHA_INICIAL,'DD/MM/RRRR') AND TO_DATE(:P_FECHA_FINAL,'DD/MM/RRRR')
                        AND    CON.NUM_PERIODOS = 90) COMISION_DIF_54,
                        (SELECT NVL(SUM(CON.CUOTA*36),0)
                        FROM   CM_CONTRATO_PRINCIPAL CON,
                                CM_EVENTO EVE,
                                CM_STATUS_VTA ST,
                                CS_VIGENCIAS_COMISION VIG
                        WHERE  CON.EVENTO = EVE.EVENTO_ID
                        AND    CON.STATUS = ST.STATUS_ID
                        AND    CON.COMISION = VIG.COMISION_ID
                        AND    ST.COBRANZA = 'S'
                        AND    VIG.MEDICION = 'DIF'
                        AND    EVE.ESTADO = REG.ESTADO_ID
                        AND    EVE.REGION = REGION_ID
                        AND    CON.TIPO_VENTA = :P_TIPO_VENTA
                                                AND    EVE.EMPRESA = :P_EMPRESA
                        AND    EVE.FECHA BETWEEN TO_DATE(:P_FECHA_INICIAL,'DD/MM/RRRR') AND TO_DATE(:P_FECHA_FINAL,'DD/MM/RRRR')
                        AND    CON.NUM_PERIODOS = 90) COMISION_DIF_36,
                        (SELECT NVL(SUM(MONTO),0)
                        FROM(SELECT EVE.ESTADO, EVE.REGION, CS.CONTRATO_PRIN, CON.TOTAL_APAGAR, CRED.NOTA_ID, CRED.MONTO
                            FROM   CS_COMISIONES_VTA CS,
                                        CM_CONTRATO_PRINCIPAL CON,
                                        CS_VIGENCIAS_COMISION VIG,
                                        CM_EVENTO EVE,
                                        CM_NOTA_CREDITO CRED
                            WHERE  CS.CONTRATO_PRIN = CON.CONTRATO_PRIN_ID
                            AND    CON.EVENTO = EVE.EVENTO_ID
                            AND    CON.COMISION = VIG.COMISION_ID
                            AND    CS.NOTA_CREDITO = CRED.NOTA_ID
                            AND    VIG.MEDICION <> 'DIF'
                            AND    CON.TIPO_VENTA = :P_TIPO_VENTA
                                                                AND    EVE.EMPRESA = :P_EMPRESA
                            AND    CS.FECHA_PAGO BETWEEN TO_DATE (:P_FECHA_PAGO_INI,'DD/MM/RRRR HH24:MI:SS') AND TO_DATE (:P_FECHA_PAGO_FIN,'DD/MM/RRRR HH24:MI:SS')
                            AND    CS.TIPO_COMISION = 'S'
                            AND    CS.NOTA_CREDITO IS NOT NULL
                            GROUP BY EVE.ESTADO, EVE.REGION, CS.CONTRATO_PRIN, CON.TOTAL_APAGAR, CRED.NOTA_ID, CRED.MONTO)
                        WHERE  ESTADO = REG.ESTADO_ID
                        AND    REGION = REGION_ID) NOTAS,
                        (SELECT NVL(SUM(MONTO),0)
                        FROM(SELECT EVE.ESTADO, EVE.REGION, CS.CONTRATO_PRIN, CON.TOTAL_APAGAR, CRED.NOTA_ID, CRED.MONTO
                            FROM   CS_COMISIONES_VTA CS,
                                        CM_CONTRATO_PRINCIPAL CON,
                                        CS_VIGENCIAS_COMISION VIG,
                                        CM_EVENTO EVE,
                                        CM_NOTA_CREDITO CRED
                            WHERE  CS.CONTRATO_PRIN = CON.CONTRATO_PRIN_ID
                            AND    CON.EVENTO = EVE.EVENTO_ID
                            AND    CON.COMISION = VIG.COMISION_ID
                            AND    CS.NOTA_CREDITO = CRED.NOTA_ID
                            AND    VIG.MEDICION = 'DIF'
                            AND    CON.TIPO_VENTA = :P_TIPO_VENTA
                                                                AND    EVE.EMPRESA = :P_EMPRESA
                            AND    CS.FECHA_PAGO BETWEEN TO_DATE (:P_FECHA_PAGO_INI,'DD/MM/RRRR HH24:MI:SS') AND TO_DATE (:P_FECHA_PAGO_FIN,'DD/MM/RRRR HH24:MI:SS')
                            AND    CS.TIPO_COMISION = 'S'
                            AND    CS.NOTA_CREDITO IS NOT NULL
                            GROUP BY EVE.ESTADO, EVE.REGION, CS.CONTRATO_PRIN, CON.TOTAL_APAGAR, CRED.NOTA_ID, CRED.MONTO)
                        WHERE  ESTADO = REG.ESTADO_ID
                        AND    REGION = REGION_ID) NOTAS_DIF,
                        (SELECT NVL(SUM(DECODE(CS.TIPO_COMISION,'S',-MONTO_COMISION, MONTO_COMISION)),0)
                        FROM   CS_COMISIONES_VTA CS,
                                    CM_CONTRATO_PRINCIPAL CON,
                                    CM_EVENTO EVE,
                                    CS_VIGENCIAS_COMISION VIG
                        WHERE  CS.CONTRATO_PRIN = CON.CONTRATO_PRIN_ID
                        AND    CON.EVENTO = EVE.EVENTO_ID
                        AND    CON.COMISION = VIG.COMISION_ID
                        AND    CON.TIPO_VENTA = :P_TIPO_VENTA
                                                AND    EVE.EMPRESA = :P_EMPRESA
                        AND    CS.FECHA_PAGO BETWEEN TO_DATE (:P_FECHA_PAGO_INI,'DD/MM/RRRR HH24:MI:SS') AND TO_DATE (:P_FECHA_PAGO_FIN,'DD/MM/RRRR HH24:MI:SS')
                        AND    EVE.ESTADO = REG.ESTADO_ID
                        AND    EVE.REGION = REGION_ID
                        AND    CONSECUTIVO > 1
                        AND    CS.TIPO_COMISION = 'E'
                        AND    CS.STATUS = 'PGD'
                        AND    VIG.MEDICION = 'DIF'
                        AND    CS.CONCEPTO <> 'Q') COMISION_VTAS_DIF,
                        (SELECT NVL(SUM(DECODE(CS.TIPO_COMISION,'S',-MONTO_COMISION, MONTO_COMISION)),0)
                        FROM   CS_COMISIONES_VTA CS,
                                    CM_CONTRATO_PRINCIPAL CON,
                                    CM_EVENTO EVE
                        WHERE  CS.CONTRATO_PRIN = CON.CONTRATO_PRIN_ID
                        AND    CON.EVENTO = EVE.EVENTO_ID
                        AND    CON.TIPO_VENTA = :P_TIPO_VENTA
                                                AND    EVE.EMPRESA = :P_EMPRESA
                        AND    CS.FECHA_PAGO BETWEEN TO_DATE (:P_FECHA_PAGO_INI,'DD/MM/RRRR HH24:MI:SS') AND TO_DATE (:P_FECHA_PAGO_FIN,'DD/MM/RRRR HH24:MI:SS')
                        AND    EVE.ESTADO = REG.ESTADO_ID
                        AND    EVE.REGION = REGION_ID
                        AND    CS.CONCEPTO = 'Q') BONO,
                        (SELECT NVL(SUM(DECODE(CS.TIPO_COMISION,'S',-MONTO_COMISION, MONTO_COMISION)),0)
                        FROM   CS_COMISIONES_VTA CS,
                                    CM_EVENTO EVE
                        WHERE  CS.EVENTO = EVE.EVENTO_ID
                        AND    EVE.TIPO_VENTA = :P_TIPO_VENTA
                                                AND    EVE.EMPRESA = :P_EMPRESA
                        AND    CS.FECHA_PAGO BETWEEN TO_DATE (:P_FECHA_PAGO_INI,'DD/MM/RRRR HH24:MI:SS') AND TO_DATE (:P_FECHA_PAGO_FIN,'DD/MM/RRRR HH24:MI:SS')
                        AND    CS.STATUS = 'PGD'
                        AND    EVE.ESTADO = REG.ESTADO_ID
                        AND    EVE.REGION = REGION_ID) COMISION_BRUTA
                    FROM   CM_REGION REG,
                        CM_ESTADO EST
                    WHERE  REG.ESTADO_ID = EST.ESTADO_ID
                    AND    EST.SE_VENDE = 'S'
                    ORDER BY ESTADO_ID, REGION_ID";
    $res = OCIParse($db, $query);
    OCIBindByName($res, ":P_FECHA_INICIAL",$datos_form->P_FECHA_1->FORMAT);
    OCIBindByName($res, ":P_FECHA_FINAL",$datos_form->P_FECHA_2->FORMAT);
    OCIBindByName($res, ":P_FECHA_PAGO_INI",$datos_form->P_FECHA_3->FORMAT);
    OCIBindByName($res, ":P_FECHA_PAGO_FIN",$datos_form->P_FECHA_4->FORMAT);
    OCIBindByName($res, ":P_TIPO_VENTA",$datos_form->P_TEXT_1);
    OCIBindByName($res, ":P_EMPRESA",$datos_form->P_TEXT_2);
    OCIExecute($res,OCI_DEFAULT);

    $RegDatos = array();
    $arrContenido = array();
    $arrTabla = array();
    $CF_VENTAS_OBJETIVO = 0;
    while(OCIFetchInto($res,$arrDatos,OCI_ASSOC+OCI_RETURN_NULLS))
    {
        while(list($id,$valor)=each($estados))
        {
            if($valor->VALUE == $arrDatos['ESTADO_ID'])
            {
                $arrContenido[$valor->TEXT]['TOTAL'][] = $arrDatos['COMISION_BRUTA'];
                $RegDatos[$valor->TEXT] = array();
                if(isset($RegDatos[$valor->TEXT]))
                {
                    $arrContenido[$valor->TEXT]['CONTENIDO'][] = ["REGION_ID" => $arrDatos['REGION_ID'], "COMISION_CONTADO" => $arrDatos['COMISION_CONTADO'], 
                    "COMISION_CONTADO_NUEVO" => $arrDatos['COMISION_CONTADO_NUEVO'], "COMISION_SUBSIDIO" => $arrDatos['COMISION_SUBSIDIO'], "NOTAS" => $arrDatos['NOTAS'], "NOTAS_DIF" => $arrDatos['NOTAS_DIF'],
                    "COMISION_BRUTA" => $arrDatos['COMISION_BRUTA']];
                }

                if($datos_form->P_TEXT_1 == "PCM")
                {
                    $CF_TITULO = "Total Dispersado";
                }
                else
                {
                    $CF_TITULO = "Total Vtas. Para Com.";
                }

                //$CF_COMISION_COBRANZA = 0;
                if ($datos_form->P_TEXT_2 == "OMP")
                {
                    $Result = array();
                    $query_comision = "SELECT NVL(SUM(DECODE(TIPO_MOVIMIENTO,'S', -MONTO, MONTO)),0) LMONTO
                                        FROM   CS_DETALLE_LIQ LIQ,
                                               CM_EMPLEADO EMP
                                        WHERE  LIQ.EMPLEADO = EMP.EMPLEADO_ID
                                        AND    LIQ.CONCEPTO IN ('COMCO','COSCO')
                                        AND    FECHA_PAGO BETWEEN TO_DATE (NVL (:P_FECHA_PAGO_INI,FECHA_PAGO),'DD/MM/RRRR HH24:MI:SS') AND TO_DATE (NVL (:P_FECHA_PAGO_FIN, FECHA_PAGO),'DD/MM/RRRR HH24:MI:SS')
                                        AND    EMP.TIPO_ESTRUCTURA IS NULL
                                        AND    EMP.ESTADO = :ESTADO_ID";
                    $res2 = OCIParse($db, $query_comision);
                    OCIBindByName($res2, ":P_FECHA_PAGO_INI",$datos_form->P_FECHA_3->FORMAT);
                    OCIBindByName($res2, ":P_FECHA_PAGO_FIN",$datos_form->P_FECHA_4->FORMAT);
                    OCIBindByName($res2, ":ESTADO_ID",$arrDatos['ESTADO_ID']);
                    OCIExecute($res2,OCI_DEFAULT);
                    while(OCIFetchInto($res2,$Result,OCI_ASSOC+OCI_RETURN_NULLS))
                    {
                        $CF_COMISION_COBRANZA = $Result['LMONTO'];
                    }
                    OCIFreeStatement($res2);
                }
                elseif($datos_form->P_TEXT_2 == "MCA")
                {
                    $Result = array();
                    $query_comision = "SELECT NVL(SUM(DECODE(TIPO_MOVIMIENTO,'S', -MONTO, MONTO)),0) LMONTO
                                        FROM   CS_DETALLE_LIQ LIQ,
                                               CM_EMPLEADO EMP
                                        WHERE  LIQ.EMPLEADO = EMP.EMPLEADO_ID
                                        AND    LIQ.CONCEPTO IN ('COMCO','COSCO','CCMD')
                                        AND    FECHA_PAGO BETWEEN TO_DATE (NVL (:P_FECHA_PAGO_INI, FECHA_PAGO),'DD/MM/RRRR HH24:MI:SS') AND TO_DATE (NVL (:P_FECHA_PAGO_FIN, FECHA_PAGO),'DD/MM/RRRR HH24:MI:SS')
                                        AND    EMP.TIPO_ESTRUCTURA = 'N'
                                        AND    F_ULTIMA_ESTRUCTURA(LIQ.EMPLEADO) = :P_EMPRESA
                                        AND    EMP.ESTADO = :ESTADO_ID";
                    $res2 = OCIParse($db, $query_comision);
                    OCIBindByName($res2, ":P_FECHA_PAGO_INI",$datos_form->P_FECHA_3->FORMAT);
                    OCIBindByName($res2, ":P_FECHA_PAGO_FIN",$datos_form->P_FECHA_4->FORMAT);
                    OCIBindByName($res2, ":P_EMPRESA",$datos_form->P_TEXT_2);
                    OCIBindByName($res2, ":ESTADO_ID",$arrDatos['ESTADO_ID']);
                    OCIExecute($res2,OCI_DEFAULT);
                    while(OCIFetchInto($res2,$Result,OCI_ASSOC+OCI_RETURN_NULLS))
                    {
                        $CF_COMISION_COBRANZA = $Result['LMONTO'];
                    }
                    OCIFreeStatement($res2);
                }
                elseif($datos_form->P_TEXT_2 == "RPOT")
                {
                    $Result = array();
                    $query_comision = "SELECT NVL(SUM(DECODE(TIPO_MOVIMIENTO,'S', -MONTO, MONTO)),0) LMONTO
                                        FROM   CS_DETALLE_LIQ LIQ,
                                               CM_EMPLEADO EMP
                                        WHERE  LIQ.EMPLEADO = EMP.EMPLEADO_ID
                                        AND    LIQ.CONCEPTO IN ('COMCO','COSCO')
                                        AND    FECHA_PAGO BETWEEN TO_DATE (NVL (:P_FECHA_PAGO_INI, FECHA_PAGO),'DD/MM/RRRR HH24:MI:SS') AND TO_DATE (NVL (:P_FECHA_PAGO_FIN, FECHA_PAGO),'DD/MM/RRRR HH24:MI:SS')
                                        AND    EMP.TIPO_ESTRUCTURA = 'N'
                                        AND    F_ULTIMA_ESTRUCTURA(LIQ.EMPLEADO) = :P_EMPRESA
                                        AND    EMP.ESTADO = :ESTADO_ID";
                    $res2 = OCIParse($db, $query_comision);
                    OCIBindByName($res2, ":P_FECHA_PAGO_INI",$datos_form->P_FECHA_3->FORMAT);
                    OCIBindByName($res2, ":P_FECHA_PAGO_FIN",$datos_form->P_FECHA_4->FORMAT);
                    OCIBindByName($res2, ":P_EMPRESA",$datos_form->P_TEXT_2);
                    OCIBindByName($res2, ":ESTADO_ID",$arrDatos['ESTADO_ID']);
                    OCIExecute($res2,OCI_DEFAULT);
                    while(OCIFetchInto($res2,$Result,OCI_ASSOC+OCI_RETURN_NULLS))
                    {
                        $CF_COMISION_COBRANZA = $Result['LMONTO'];
                    }
                    OCIFreeStatement($res2);
                }
                else
                {
                    $CF_COMISION_COBRANZA = 0;
                }

                //CF_OTROS_INGRESOS
                if($datos_form->P_TEXT_1 == "PNBM")
                {
                    $Result = array();
                    $query_comision = "SELECT NVL(SUM(DECODE(TIPO_MOVIMIENTO,'S', -MONTO, MONTO)),0) LMONTO
                                        FROM   CS_DETALLE_LIQ LIQ,
                                               CM_EMPLEADO EMP
                                        WHERE  LIQ.EMPLEADO = EMP.EMPLEADO_ID
                                        AND    LIQ.CONCEPTO IN (SELECT CONCEPTO_ID
                                                                       FROM   CS_CONCEPTO_DESC
                                                                       WHERE  CATEGORIA = 'O'
                                                                       --AND  CONCEPTO <> 'COMCO'
                                                                       AND    EMPRESA = :P_EMPRESA)
                                        AND    FECHA_PAGO BETWEEN TO_DATE (NVL (:P_FECHA_PAGO_INI, FECHA_PAGO),'DD/MM/RRRR HH24:MI:SS') AND TO_DATE (NVL (:P_FECHA_PAGO_FIN, FECHA_PAGO),'DD/MM/RRRR HH24:MI:SS')
                                        AND    NVL(EMP.TIPO_ESTRUCTURA,'N') = 'N'
                                        AND    EMP.ESTADO = :ESTADO_ID";
                    $res2 = OCIParse($db, $query_comision);
                    OCIBindByName($res2, ":P_FECHA_PAGO_INI",$datos_form->P_FECHA_3->FORMAT);
                    OCIBindByName($res2, ":P_FECHA_PAGO_FIN",$datos_form->P_FECHA_4->FORMAT);
                    OCIBindByName($res2, ":P_EMPRESA",$datos_form->P_TEXT_2);
                    OCIBindByName($res2, ":ESTADO_ID",$arrDatos['ESTADO_ID']);
                    OCIExecute($res2,OCI_DEFAULT);
                    while(OCIFetchInto($res2,$Result,OCI_ASSOC+OCI_RETURN_NULLS))
                    {
                        $CF_OTROS_INGRESOS = $Result['LMONTO'];
                    }
                    OCIFreeStatement($res2);
                }
                elseif($datos_form->P_TEXT_1 == "PCM" || $datos_form->P_TEXT_1 == "RPOT")
                {
                    $Result = array();
                    $query_comision = "SELECT NVL(SUM(DECODE(TIPO_MOVIMIENTO,'S', -MONTO, MONTO)),0) LMONTO
                                        FROM   CS_DETALLE_LIQ LIQ,
                                               CM_EMPLEADO EMP
                                        WHERE  LIQ.EMPLEADO = EMP.EMPLEADO_ID
                                        AND    LIQ.CONCEPTO IN (SELECT CONCEPTO_ID
                                                                       FROM   CS_CONCEPTO_DESC
                                                                       WHERE  CATEGORIA = 'O'
                                                                       --AND  CONCEPTO <> 'COMCO'
                                                                       AND    EMPRESA = :P_EMPRESA)
                                        AND    FECHA_PAGO BETWEEN TO_DATE (NVL (:P_FECHA_PAGO_INI, FECHA_PAGO),'DD/MM/RRRR HH24:MI:SS') AND TO_DATE (NVL (:P_FECHA_PAGO_FIN, FECHA_PAGO),'DD/MM/RRRR HH24:MI:SS')
                                        AND    EMP.TIPO_ESTRUCTURA = 'N'
                                        AND    EMP.ESTADO = :ESTADO_ID
                                        AND    LIQ.EMPLEADO IN (SELECT EMPLEADO FROM CM_ESTRUCTURA_VENTAS_DET WHERE EMPRESA = :P_EMPRESA
                                                                        UNION ALL
                                                                SELECT EMPLEADO FROM CM_ESTRUCTURA_VENTAS_DET_HIST WHERE EMPRESA = :P_EMPRESA)";
                    $res2 = OCIParse($db, $query_comision);
                    OCIBindByName($res2, ":P_FECHA_PAGO_INI",$datos_form->P_FECHA_3->FORMAT);
                    OCIBindByName($res2, ":P_FECHA_PAGO_FIN",$datos_form->P_FECHA_4->FORMAT);
                    OCIBindByName($res2, ":P_EMPRESA",$datos_form->P_TEXT_2);
                    OCIBindByName($res2, ":ESTADO_ID",$arrDatos['ESTADO_ID']);
                    OCIExecute($res2,OCI_DEFAULT);
                    while(OCIFetchInto($res2,$Result,OCI_ASSOC+OCI_RETURN_NULLS))
                    {
                        $CF_OTROS_INGRESOS = $Result['LMONTO'];
                    }
                    OCIFreeStatement($res2);
                }
                elseif($datos_form->P_TEXT_1 == "PDCM")
                {
                    $Result = array();
                    $query_comision = "SELECT NVL(SUM(DECODE(TIPO_MOVIMIENTO,'S', -MONTO, MONTO)),0) LMONTO
                                        FROM   CS_DETALLE_LIQ LIQ,
                                               CM_EMPLEADO EMP
                                        WHERE  LIQ.EMPLEADO = EMP.EMPLEADO_ID
                                        AND    LIQ.CONCEPTO IN (SELECT CONCEPTO_ID
                                                                  FROM   CS_CONCEPTO_DESC
                                                                  WHERE  CATEGORIA = 'O'
                                                                  --AND  CONCEPTO <> 'COMCO'
                                                                  AND    EMPRESA = :P_EMPRESA)
                                        AND    FECHA_PAGO BETWEEN TO_DATE (NVL (:P_FECHA_PAGO_INI, FECHA_PAGO),'DD/MM/RRRR HH24:MI:SS') AND TO_DATE (NVL (:P_FECHA_PAGO_FIN, FECHA_PAGO),'DD/MM/RRRR HH24:MI:SS')
                                        --AND  EMP.TIPO_ESTRUCTURA = 'N'
                                        AND    EMP.ESTADO = :ESTADO_ID";
                    $res2 = OCIParse($db, $query_comision);
                    OCIBindByName($res2, ":P_FECHA_PAGO_INI",$datos_form->P_FECHA_3->FORMAT);
                    OCIBindByName($res2, ":P_FECHA_PAGO_FIN",$datos_form->P_FECHA_4->FORMAT);
                    OCIBindByName($res2, ":P_EMPRESA",$datos_form->P_TEXT_2);
                    OCIBindByName($res2, ":ESTADO_ID",$arrDatos['ESTADO_ID']);
                    OCIExecute($res2,OCI_DEFAULT);
                    while(OCIFetchInto($res2,$Result,OCI_ASSOC+OCI_RETURN_NULLS))
                    {
                        $CF_OTROS_INGRESOS = $Result['LMONTO'];
                    }
                    OCIFreeStatement($res2);
                }
                else
                {
                    $CF_OTROS_INGRESOS = 0;
                }

                //CF_IVA
                if($datos_form->P_TEXT_2 == "COP")
                {
                    $Result = array();
                    $query_comision = "SELECT NVL(SUM(DECODE(TIPO_MOVIMIENTO,'S', -MONTO, MONTO)),0) LMONTO
                                        FROM   CS_DETALLE_LIQ LIQ,
                                               CM_EMPLEADO EMP
                                        WHERE  LIQ.EMPLEADO = EMP.EMPLEADO_ID
                                        AND    LIQ.CONCEPTO = 'IVA'
                                        AND    FECHA_PAGO BETWEEN TO_DATE (NVL (:P_FECHA_PAGO_INI,FECHA_PAGO),'DD/MM/RRRR HH24:MI:SS') AND TO_DATE (NVL (:P_FECHA_PAGO_FIN,FECHA_PAGO),'DD/MM/RRRR HH24:MI:SS')
                                        AND    EMP.TIPO_ESTRUCTURA = 'N'
                                        AND    EMP.ESTADO = :ESTADO_ID";
                    $res2 = OCIParse($db, $query_comision);
                    OCIBindByName($res2, ":P_FECHA_PAGO_INI",$datos_form->P_FECHA_3->FORMAT);
                    OCIBindByName($res2, ":P_FECHA_PAGO_FIN",$datos_form->P_FECHA_4->FORMAT);
                    OCIBindByName($res2, ":ESTADO_ID",$arrDatos['ESTADO_ID']);
                    OCIExecute($res2,OCI_DEFAULT);
                    while(OCIFetchInto($res2,$Result,OCI_ASSOC+OCI_RETURN_NULLS))
                    {
                        $CF_IVA = $Result['LMONTO'];
                    }
                    OCIFreeStatement($res2);
                }
                else
                {
                    $CF_IVA = 0;
                }
                
                if($datos_form->P_TEXT_2 == "OPC")
                {
                    $Result = array();
                    $query_comision = "SELECT NVL(SUM(DECODE(TIPO_MOVIMIENTO,'S', -MONTO, MONTO)),0) LMONTO
                                        FROM    CS_DETALLE_LIQ LIQ, CM_EMPLEADO EMP
                                        WHERE   LIQ.EMPLEADO = EMP.EMPLEADO_ID
                                        AND     LIQ.CONCEPTO = 'COSCO'
                                        AND     FECHA_PAGO BETWEEN TO_DATE (NVL (:P_FECHA_PAGO_INI,FECHA_PAGO),'DD/MM/RRRR HH24:MI:SS') AND TO_DATE (NVL (:P_FECHA_PAGO_FIN,FECHA_PAGO),'DD/MM/RRRR HH24:MI:SS')
                                        AND     EMP.ESTADO = :ESTADO_ID";
                    $res2 = OCIParse($db, $query_comision);
                    OCIBindByName($res2, ":P_FECHA_PAGO_INI",$datos_form->P_FECHA_3->FORMAT);
                    OCIBindByName($res2, ":P_FECHA_PAGO_FIN",$datos_form->P_FECHA_4->FORMAT);
                    OCIBindByName($res2, ":ESTADO_ID",$arrDatos['ESTADO_ID']);
                    OCIExecute($res2,OCI_DEFAULT);
                    while(OCIFetchInto($res2,$Result,OCI_ASSOC+OCI_RETURN_NULLS))
                    {
                        $CF_COMISION_S_COBRANZA = $Result['LMONTO'];
                    }
                    OCIFreeStatement($res2);
                }
                else
                {
                    $CF_COMISION_S_COBRANZA = 0;
                }
                
                $arrContenido[$valor->TEXT]['COMISION_DIF_36'] = $arrDatos['COMISION_DIF_36'];
                $arrContenido[$valor->TEXT]['COMISION_DIF_54'] = $arrDatos['COMISION_DIF_54'];
                $arrContenido[$valor->TEXT]['COMISION_DIF_72_108'] = $arrDatos['COMISION_DIF_72_108'];
                //$arrContenido[$valor->TEXT]['COMISION_CONTADO_NUEVO'] = $arrDatos['COMISION_CONTADO_NUEVO'];
                $arrContenido[$valor->TEXT]['COMISION_VTAS_DIF'] = $arrDatos['COMISION_VTAS_DIF'];
                $arrContenido[$valor->TEXT]['BONO'] = $arrDatos['BONO'];
                $arrContenido[$valor->TEXT]['CF_COMISION_S_COBRANZA'] = $CF_COMISION_S_COBRANZA;
                $arrContenido[$valor->TEXT]['CF_COMISION_COBRANZA'] = $CF_COMISION_COBRANZA;
                $arrContenido[$valor->TEXT]['CF_OTROS_INGRESOS'] = $CF_OTROS_INGRESOS;
                $arrContenido[$valor->TEXT]['CF_IVA'] = $CF_IVA;

                //DATOS DE TABLA PRINCIPAL
                $arrTabla['ESTADO_ID'] = $valor->TEXT;
                $arrTabla['REGION_ID'] = $arrDatos['REGION_ID'];
                $arrTabla['COMISION_CONTADO'] = $arrDatos['COMISION_CONTADO'];
                $arrTabla['COMISION_SUBSIDIO'] = $arrDatos['COMISION_SUBSIDIO'];
                $arrTabla['NOTAS'] = $arrDatos['NOTAS'];
                $arrTabla['NOTAS_DIF'] = $arrDatos['NOTAS_DIF'];
                $arrTabla['CF_VENTAS_OBJETIVO'] = 0;
                //$arrTabla['CF_TITULO'] = $CF_TITULO;
                $arrTabla['COMISION_VTAS_DIF'] = $arrDatos['COMISION_VTAS_DIF'];
                $arrTabla['BONO'] = $arrDatos['BONO'];
                $arrTabla['CF_COMISION_COBRANZA'] = $CF_COMISION_COBRANZA;
                $arrTabla['CF_OTROS_INGRESOS'] = $CF_OTROS_INGRESOS;
                $arrTabla['CF_IVA'] = $CF_IVA;
                $arrTabla['COMISION_BRUTA'] = $arrDatos['COMISION_BRUTA'];
            }
        }
        reset($estados);
        $request['datos']['datos'][]=$arrTabla;

        //TOTAL
        while(list($id,$valor)=each($estados))
        {
            $TOTAL = 0;
            if($valor->VALUE == $arrDatos['ESTADO_ID'])
            {
                $CF_VENTAS_OBJETIVO = (array_sum(array_column($arrContenido[$valor->TEXT]['CONTENIDO'],'COMISION_CONTADO')) + $arrContenido[$valor->TEXT]['COMISION_DIF_72_108'] + $arrContenido[$valor->TEXT]['COMISION_DIF_54'] + $arrContenido[$valor->TEXT]['COMISION_DIF_36']) - (array_sum(array_column($arrContenido[$valor->TEXT]['CONTENIDO'],'NOTAS')) + array_sum(array_column($arrContenido[$valor->TEXT]['CONTENIDO'],'NOTAS_DIF')));
                $arrContenido[$valor->TEXT]['CF_VENTAS_OBJETIVO'] = $CF_VENTAS_OBJETIVO;
                $CF_VENTAS_COMISION = (array_sum(array_column($arrContenido[$valor->TEXT]['CONTENIDO'],'COMISION_CONTADO_NUEVO')) + ($arrContenido[$valor->TEXT]['COMISION_DIF_72_108']/2) + $arrContenido[$valor->TEXT]['COMISION_DIF_54']) - (array_sum(array_column($arrContenido[$valor->TEXT]['CONTENIDO'],'NOTAS')) + array_sum(array_column($arrContenido[$valor->TEXT]['CONTENIDO'],'NOTAS_DIF')));
                $arrContenido[$valor->TEXT]['CF_VENTAS_COMISION'] = $CF_VENTAS_COMISION;
                $arrTabla['CF_VENTAS_COMISION'] = $CF_VENTAS_COMISION;
                $TOTAL = $arrContenido[$valor->TEXT]['CF_OTROS_INGRESOS'] + $arrContenido[$valor->TEXT]['CF_IVA'] + $arrContenido[$valor->TEXT]['CF_COMISION_COBRANZA'] + array_sum(array_column($arrContenido[$valor->TEXT]['CONTENIDO'],'COMISION_BRUTA')) + $arrContenido[$valor->TEXT]['CF_COMISION_S_COBRANZA'];
                $arrContenido[$valor->TEXT]['COMISION_BRUTA'] = $TOTAL;
            }
        }
        reset($estados);
    }
    $request['datos']['datos_x_estado']=$arrContenido;

    $request['datos']['campos']=array();
    $request['datos']['campos'][]=["COLUMNA" => "Estado", "NOMBRE"=>"ESTADO_ID", "TIPO"=>"VARCHAR2"];
    $request['datos']['campos'][]=["COLUMNA" => "Reg.", "NOMBRE"=> "REGION_ID", "TIPO"=> "VARCHAR2"];
    $request['datos']['campos'][]=["COLUMNA" => "Com. Única", "NOMBRE"=> "COMISION_CONTADO", "TIPO"=> "MONEDA"];
    $request['datos']['campos'][]=["COLUMNA" => "Subsidios", "NOMBRE"=> "COMISION_SUBSIDIO", "TIPO"=> "MONEDA"];
    $request['datos']['campos'][]=["COLUMNA" => "Canc. Cont.", "NOMBRE"=> "NOTAS", "TIPO"=> "MONEDA"];
    $request['datos']['campos'][]=["COLUMNA" => "Canc. Dif.", "NOMBRE"=> "NOTAS_DIF", "TIPO"=> "MONEDA"];
    $request['datos']['campos'][]=["COLUMNA" => "Total Vtas. Para Objetivo", "NOMBRE"=> "CF_VENTAS_OBJETIVO", "TIPO"=> "MONEDA"];
    $request['datos']['campos'][]=["COLUMNA" => $CF_TITULO, "NOMBRE"=> "CF_VENTAS_COMISION", "TIPO"=> "MONEDA"];
    $request['datos']['campos'][]=["COLUMNA" => "Comisiones Vtas. Dif.", "NOMBRE"=> "COMISION_VTAS_DIF", "TIPO"=> "MONEDA"];
    $request['datos']['campos'][]=["COLUMNA" => "Bonos", "NOMBRE"=> "BONO", "TIPO"=> "MONEDA"];
    $request['datos']['campos'][]=["COLUMNA" => "Comisiones Cobranza", "NOMBRE"=> "CF_COMISION_COBRANZA", "TIPO"=> "MONEDA"];
    $request['datos']['campos'][]=["COLUMNA" => "Otros Ingresos", "NOMBRE"=> "CF_OTROS_INGRESOS", "TIPO"=> "MONEDA"];
    $request['datos']['campos'][]=["COLUMNA" => "IVA", "NOMBRE"=> "CF_IVA", "TIPO"=> "MONEDA"];
    $request['datos']['campos'][]=["COLUMNA" => "Total", "NOMBRE"=> "COMISION_BRUTA", "TIPO"=> "MONEDA"];

    $request['CONSULTA'] = $query;
    //sleep(20);
    $request['datos']['titulo']='Concentrado Comisiones';
    OCIFreeStatement($res);
    cerrar_db($db);
    
    $TiempoFinal = getTiempo(); 
    $Tiempo = $TiempoFinal - $TiempoInicial; 
    $Tiempo = round($Tiempo,2); 
    $request['SCRIPT']['TIME'] = $Tiempo;
    echo json_encode($request);
?>
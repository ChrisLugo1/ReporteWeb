<?php
    include("../../../super_clases/PHPExcel.php");
    include("../../../../dominio_edilar.php");
    set_time_limit(1900);
    function getTiempo()
    {
        list($usec, $sec) = explode(' ', microtime());
        return (float) $usec + (float) $sec;
    }
    $TiempoInicial = getTiempo();
    $datos = json_decode($_POST['data']);
    $tipos_imagen = json_decode($_POST['tipos_imagen']);

    $request=array();
    $request['ERR']='';
    $request['LINK']='';
    $request['DATOS'] = $datos;
    $request['tipos_imagen'] = $tipos_imagen;

    if(isset($datos->CORRECTOS) && isset($datos->INCORRECTOS))
    {
        $total_registros = count($datos->CORRECTOS);
        if($total_registros==0)
        {
            $total_registros = count($datos->INCORRECTOS);
        }
    }
    else
    {
        $total_registros = 0;
    }
    

    if(false)
    {
        $request['ERR']="ERROR DE SEGURIDAD";
    }
    else
    {
        
        $objPHPExcel = new PHPExcel();
        // Estableciendo Propiedades del documento
       
        $objPHPExcel->getProperties()
        ->setCreator("Sistema Edilar")
        ->setLastModifiedBy("Sistema Edilar");
        
        $objPHPExcel->getActiveSheet()->setTitle('Contratos Masivo');

        //cabecera de tabla
        $celda_x="A";
        $celda_y=2;
        while(list($i,$columna)=each($datos->campos))
        {
            $objPHPExcel->setActiveSheetIndex(0)->getStyle($celda_x.$celda_y)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
            $objPHPExcel->setActiveSheetIndex(0)->getStyle($celda_x.$celda_y)->getFont()->applyFromArray(array('bold' => PHPExcel_Style_Font::UNDERLINE_SINGLE, 'color'=> array('rgb' => '000000')));
            $objPHPExcel->setActiveSheetIndex(0)->getStyle($celda_x.$celda_y)->applyFromArray(array('borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')))));
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($celda_x.$celda_y,str_replace('_',' ', $columna->COLUMNA));
            $celda_x++;
        }
        reset($datos->campos);

        //registros de datos
        //si existe registros con estatus correctos
        if($total_registros > 0)
        {
            $celda_y=3;
            while(list($id,$valor)=each($datos->datos))
            {
                $celda_x="A";
                while(list($i,$columna)=each($datos->campos))
                {
                    if($columna->NOMBRE == "CORRECTOS")
                    {
                        while(list($num_reg,$evento_c)=each($datos->CORRECTOS))
                        {
                            if($valor->EVENTO == $evento_c->EVENTO)
                            {
                                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($celda_x.$celda_y,'✓');
                                $objPHPExcel->setActiveSheetIndex(0)->getStyle($celda_x.$celda_y)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
                                $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($celda_x)->setAutoSize(true);
                            }
                        }
                        reset($datos->CORRECTOS);
                        while(list($num_reg,$evento_i)=each($datos->INCORRECTOS))
                        {
                            if($valor->EVENTO == $evento_i->EVENTO)
                            {
                                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($celda_x.$celda_y,'X');
                                $objPHPExcel->setActiveSheetIndex(0)->getStyle($celda_x.$celda_y)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
                                $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($celda_x)->setAutoSize(true);
                            }
                        }
                        reset($datos->INCORRECTOS);
                    }
                    else
                    {
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($celda_x.$celda_y,$valor->{$columna->NOMBRE});
                        $objPHPExcel->setActiveSheetIndex(0)->getStyle($celda_x.$celda_y)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
                        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($celda_x)->setAutoSize(true);
                        $celda_x++;
                    }
                }
                reset($datos->campos);
                $celda_y++;
            }
            reset($datos->datos);
        }
        else
        {
            //si no existe valores con estatus
            $celda_y=3;
            while(list($id,$valor)=each($datos->datos))
            {
                $celda_x="A";
                while(list($i,$columna)=each($datos->campos))
                {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($celda_x.$celda_y,$valor->{$columna->NOMBRE});
                    $objPHPExcel->setActiveSheetIndex(0)->getStyle($celda_x.$celda_y)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
                    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($celda_x)->setAutoSize(true);
                    $celda_x++;
                }
                reset($datos->campos);
                $celda_y++;
            }
            reset($datos->datos);
        }
        $request['TOTAL'] = $total_registros;
    }
    $objPHPExcel->setActiveSheetIndex(0);
    
    // Save Excel 2007 file
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('informe'.$_SESSION['GLOBAL_USER'].'.xlsx');
    $request['LINK']='data/informe'.$_SESSION['GLOBAL_USER'].'.xlsx';

    $TiempoFinal = getTiempo();
    $Tiempo = $TiempoFinal - $TiempoInicial;
    $Tiempo = round($Tiempo, 2);
    $request['SCRIPT']['TIME'] = $Tiempo;
    echo json_encode($request);
?>
<?php
    include("../../../super_clases/PHPExcel.php");
    include("../../../../dominio_edilar.php");
    $datos = json_decode($_POST['data']);
    $datos_form = json_decode($_POST['form']);
    
    $request=array();
    $request['ERR']='';
    $request['LINK']='';
    $request['DATOS']=$datos;
    
    if(false)
    {
        $request['ERR']="ERROR DE SEGURIDAD";
    }
    else
    {
        $objPHPExcel = new PHPExcel();
        // Estableciendo Propiedades del documento
        $objPHPExcel->getProperties()->setCreator("Sistema Edilar")
                                    ->setLastModifiedBy("Sistema Edilar");
        if(true)
        {
            // estableciendo el titulo de la hoja
            //$myWorkSheet = new PHPExcel_Worksheet($objPHPExcel, $res_form->titulo);
            //$objPHPExcel->addSheet($myWorkSheet, 0);
            $objPHPExcel->getActiveSheet()->setTitle($datos->titulo);
            
            $celda_x="A";
            $celda_y=2;
            while(list($id,$campo)=each($datos->campos))
            {
                $objPHPExcel->setActiveSheetIndex(0)->getStyle($celda_x.$celda_y)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
                $objPHPExcel->setActiveSheetIndex(0)->getStyle($celda_x.$celda_y)->getFont()->applyFromArray(array('bold' => PHPExcel_Style_Font::UNDERLINE_SINGLE, 'color'=> array('rgb' => '000000')));
                $objPHPExcel->setActiveSheetIndex(0)->getStyle($celda_x.$celda_y)->applyFromArray(array('borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000')))));
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($celda_x.$celda_y,str_replace('_',' ', $campo->COLUMNA));
                $celda_x++;
            }
            reset($datos->campos);
            
            $celda_y=3;
            while(list($id,$dato)=each($datos->datos))
            {
                $celda_x="A";
                //obtener el residuo de la fila para tomar los 0
                while(list($campo,$valor)=each($datos->campos))
                {
                    //Valores impresos celda
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($celda_x.$celda_y,$dato->{$valor->NOMBRE});
                    $objPHPExcel->setActiveSheetIndex(0)->getStyle($celda_x.$celda_y)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
                    $celda_x++;
                }
                $celda_y++;
                reset($datos->campos);
            }
            reset($datos->datos);
            
        }
        $objPHPExcel->setActiveSheetIndex(0);
        // Save Excel 2007 file
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('../files/informe'.$_SESSION['GLOBAL_USER'].'.xlsx');
        $request['LINK']='files/informe'.$_SESSION['GLOBAL_USER'].'.xlsx';
    }
    echo json_encode($request);
?>
<?php
    include("../../../../../include/conexion.php");
    include("../../../../dominio_edilar.php");
    require('../../../super_clases/tcpdf/examples/tcpdf_include.php');
    require('../../../super_clases/tcpdf/tcpdf.php');
    set_time_limit(1900);
    function getTiempo()
    {
        list($usec, $sec) = explode(' ', microtime());
        return (float) $usec + (float) $sec;
    }
    $TiempoInicial = getTiempo();
    //$db = conecta_db();
    $datos_form = json_decode($_POST['form']);
    $datos=json_decode($_REQUEST['data']);
    $empresas = json_decode($_POST['empresas']);
    $ventas = json_decode($_POST['ventas']);
    
    $request=array();
    $request['ERROR']='';
    $request['LINK']='';
    $request['SCRIPT'] = [];
    $request['FORM']=$datos_form;
    $request['DATOS']=$datos;
    $request['DATOS_EMPRESAS']=$empresas;
    $request['DATOS_VENTAS']=$ventas;

    if(false)
    {
        $request['ERROR']="ERROR DE SEGURIDAD";
    }
    else
    {
        class PDF extends TCPDF
        {
            public $contrato; //SIRVE PARA PASAR EL CONTRATO QUE ESTARA RELACIONADO AL CÓDIGO DE BARRAS
            public $img;
            public $datos_form;
            public $empresas;
            public $ventas;
            public function Header() 
            {
                /*PONEMOS COLOR AL TEXTO Y A LAS LINEAS */
                $this->SetTextColor(0,0,0);
                $this->SetDrawColor(0,0,0);
                $DESC_EMPRESA = '';
                while(list($id,$valor)=each($this->empresas))
                {
                    if($this->datos_form->P_TEXT_2 == $valor->VALUE)
                    {
                        $DESC_EMPRESA = $valor->TEXT;
                    }
                }
                reset($this->empresas);

                $DESC_VENTA = '';
                while(list($id,$valor)=each($this->ventas))
                {
                    if($this->datos_form->P_TEXT_1 == $valor->VALUE)
                    {
                        $DESC_VENTA = $valor->TEXT;
                    }
                }
                reset($this->ventas);

                //ENCABEZADO DE LA PÁGINA
                $enc='<table style="border-style: solid; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px">
                        <tr>
                            <td rowspan="3" style="width:150px; height:45px; text-align: center;"><br><br><img src="'.$this->img.'" height="20px"></td>
                            <td style="color:mediumblue; font-size:9px; width:180px;"><u><b>'.$DESC_EMPRESA.'</b></u></td>
                            <td style="color:black; font-size:9px; width:180px;">De la Fecha Pago: '.$this->datos_form->P_FECHA_3->FORMAT.'</td>
                            <td style="color:black; font-size:9px; width:200px;">A la Fecha Pago: '.$this->datos_form->P_FECHA_4->FORMAT.'</td>
                        </tr>
                        <tr>
                            <td style="color:black; font-size:9px; width:180px;">Reporte Concentrado Comisiones</td>
                            <td style="color:black; font-size:9px; width:180px;">De la Fecha Venta: '.$this->datos_form->P_FECHA_1->FORMAT.'</td>
                            <td style="color:black; font-size:9px; width:200px;">A la Fecha Venta: '.$this->datos_form->P_FECHA_2->FORMAT.'</td>
                        </tr>
                        <tr>
                            <td style="color:black; font-size:9px; width:180px;"></td>
                            <td style="color:black; font-size:9px; width:180px;">Tipo Venta: '.$DESC_VENTA.'</td>
                            <td style="color:black; font-size:9px; width:200px;">Fuerza de Ventas: '.$DESC_EMPRESA.'</td>
                        </tr>
                    </table>';
                $this->writeHTML($enc, true, true, true, false, '');
                //$this->writeHTMLCell(0,0,1, 1, $html,0,1, 0, false,false,'left', true);
                $this->SetFont('helvetica', 'I', 2);
                 //$this->SetTopMargin($this->GetY());
            }
            //PIE DE PÁGINA 
            public function Footer() 
            {
                $time = date("d/m/Y H:i:s");
                /* establecemos el color del texto */
                $this->SetTextColor(0,0,145);
                //$this->Line(6,282,195,282);
                /* insertamos numero de pagina y total de paginas*/
                $this->Cell(0, 2, 'Fecha de impresión '.$time ,0, false, 'L', 0, '', 0, false, 'T', 'M');
                $this->Cell(1, 2, 'Pagina '.$this->getAliasNumPage().
                                    ' de '.
                                    $this-> getAliasNbPages(),
                                    0, false, 'R', 0, '', 0, false, 'T', 'M');
                $this->SetDrawColor(0,0,0);
                /* dibujamos una linea roja delimitadora del pie de página */
                //$this->Line(1,28,20,28);
                //STYLE para el código de barras
                $style = array(
                        'position' => 'R',
                        'align' => 'C',
                        'stretch' => true,
                        'fitwidth' => true,
                        'cellfitalign' => '',
                        'border' => false,
                        //'hpadding' => 'auto',
                        //'vpadding' => 'auto',
                        'fgcolor' => array(0,0,0),
                        'bgcolor' => false, //array(255,255,255),
                        'text' => false,
                        'font' => 'helvetica',
                        'fontsize' => 7,
                        'stretchtext' => 4
                    );
                // posicion
                $this->SetY(-7);
                // fuente
                $this->SetFont('helvetica', 'I', 8);
                // numero de pagina
                /*$this->Ln(2);
                $this->Cell(0, 6, 'Blvd. Manuel Ávila Camacho 1994-103,Col. San Lucas Tepetlacalco,', 0, false, 'C', 0, '', 0, false, 'T', 'M');
                $this->Ln(0.3);
                $this->Cell(0, 6, 'Tlalnepantla, Edo. México, C.P. 540055, Torre Ejecutiva Satélite.', 0, false, 'C', 0, '', 0, false, 'T', 'M');
                $this->Ln(0.3);
                $this->Cell(0, 6, 'Tels: 5362-3431 / 5361-9611', 0, false, 'C', 0, '', 0, false, 'T', 'M');
                $this->Ln(0.3);
                $this->Cell(0, 6, 'Lada sin costo 01 800 31 222 00 - www.edilar.com', 0, false, 'C', 0, '', 0, false, 'T', 'M');
                $this->Ln(4);
                $this->write1DBarcode($this->contrato, 'C128', '', '', '', '1', 0.05, $style, 'N');//Aqui se inserta el código de barras
                $this->write2DBarcode('www.edilar.com', 'QRCODE,H','','','1', 2, $style, 'N');
                */
            }
        }
        //DISEÑO DE PÁGINA 
        $pdf = new PDF('P', 'cm', 'A4', true, 'UTF-8', false);
        // orientacion de hoja P=vertical L=horizontal
        
        //config de cabecera
        $pdf->img = "../../../imagenes/logoedilar.jpg";
        $pdf->datos_form = $datos_form;
        $pdf->empresas = $empresas;
        $pdf->ventas = $ventas;
        $pdf->setPageOrientation('L');
        $pdf->SetAutoPageBreak(true,1);
        $pdf->setHeaderMargin(0.5); 
        $pdf->setFooterMargin(1.5);
        $pdf->setHeaderFont(Array('times', '', 8)); 
        $pdf->setFooterFont(Array('helvetica', '', 8));
        $pdf->SetAuthor($_SESSION['GLOBAL_USER']);
        $pdf->SetTitle($datos->titulo);
        $pdf->SetSubject($datos->titulo);
        $pdf->AddPage();
        $pdf->Ln(1);
        $pdf->SetTopMargin(3);
        
        //color texto rgb
        $pdf->setFont('','B',9);
        //$pdf->SetTextColor(3,150,179);
        //Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')
        //$pdf->Cell(0, 0, 'Tabla de '.$datos->titulo, 0, false, 'C', 0, '', 0, false, '', '');
        $pdf->SetTextColor(0,0,0);
        $pdf->setFont('','',13);
        
        //CABECERA
        $html = '';
        $html.='<table cellpadding="1" border="1px;"><tr>';
        while(list($id,$columna)=each($datos->campos))
        {
            switch ($columna->NOMBRE)
            {
                case 'ESTADO_ID':
                    $html.= '<td rowspan="2" style="text-align: center;font-size: 7px;width:90px;"><b>'.$columna->COLUMNA.'</b></td>';
                break;
                case 'REGION_ID':
                    $html.= '<td rowspan="2" style="text-align: center;font-size: 7px;width:30px;"><b>'.$columna->COLUMNA.'</b></td>';
                break;
                case 'COMISION_CONTADO':
                    $html.= '<td colspan="2" style="text-align: center;font-size: 7px;width:100px;"><b>Ventas</b></td>';
                break;
                case 'NOTAS':
                    $html.= '<td colspan="2" style="text-align: center;font-size: 7px;width:100px;"><b>Cancelaciones</b></td>';
                break;
                case 'CF_VENTAS_OBJETIVO':
                    $html.= '<td rowspan="2" style="text-align: center;font-size: 7px;width:60px;"><b>'.$columna->COLUMNA.'</b></td>';
                break;
                case 'CF_VENTAS_COMISION':
                    $html.= '<td rowspan="2" style="text-align: center;font-size: 7px;width:60px;"><b>'.$columna->COLUMNA.'</b></td>';
                break;
                case 'COMISION_VTAS_DIF':
                    $html.= '<td rowspan="2" style="text-align: center;font-size: 7px;width:60px;"><b>'.$columna->COLUMNA.'</b></td>';
                break;
                case 'BONO':
                    $html.= '<td rowspan="2" style="text-align: center;font-size: 7px;width:50px;"><b>'.$columna->COLUMNA.'</b></td>';
                break;
                case 'CF_COMISION_COBRANZA':
                    $html.= '<td rowspan="2" style="text-align: center;font-size: 7px;width:50px;"><b>'.$columna->COLUMNA.'</b></td>';
                break;
                case 'CF_OTROS_INGRESOS':
                    $html.= '<td rowspan="2" style="text-align: center;font-size: 7px;width:50px;"><b>'.$columna->COLUMNA.'</b></td>';
                break;
                case 'CF_IVA':
                    $html.= '<td rowspan="2" style="text-align: center;font-size: 7px;width:50;"><b>'.$columna->COLUMNA.'</b></td>';
                break;
                case 'COMISION_BRUTA':
                    $html.= '<td rowspan="2" style="text-align: center;font-size: 7px;width:55px;"><b>'.$columna->COLUMNA.'</b></td>';
                break;
            }
        }
        reset($datos->campos);
        $html.='</tr><tr>';
        $html.= '<td style="text-align: center;font-size: 7px;"><b>Com. Unica</b></td>';
        $html.= '<td style="text-align: center;font-size: 7px;"><b>Subsidios</b></td>';
        $html.= '<td style="text-align: center;font-size: 7px;"><b>Canc. Cont.</b></td>';
        $html.= '<td style="text-align: center;font-size: 7px;"><b>Canc. Dif.</b></td>';
        $html.='</tr></table>';

        //REGISTROS
        $COMISION_CONTADO = 0;
        $COMISION_SUBSIDIO = 0;
        $NOTAS = 0;
        $NOTAS_DIF = 0;
        $TOTAL_COMISION_COBRANZA = 0;
        $TOTAL_VTAS_OBJETIVO = 0;
        $TOTAL_VTAS_DIF = 0;
        $TOTAL_BONO = 0;
        $TOTAL_COMISION_COBRANZA = 0;
        $TOTAL_OTROS_INGRESOS = 0;
        $TOTAL_IVA = 0;
        $TOTAL_COMISION_BRUTA = 0;
        $html.='<table cellpadding="1" border="1px;">';
        while(list($estado,$valores)=each($datos->datos_x_estado))
        {
            $total = count($valores->CONTENIDO)+1;
            $html.= '<tr>';
            $html.= '<td rowspan="'.$total.'" style="text-align: left;font-size: 7px;width:90px;">'.$estado.'</td>';
            $html.= '<td style="text-align: center;font-size: 7px;width:230px;"></td>';
            $html.= '<td style="text-align: center;font-size: 7px;width:60px;">$'.number_format($valores->CF_VENTAS_OBJETIVO,2).'</td>';
            $html.= '<td style="text-align: center;font-size: 7px;width:60px;">$'.number_format($valores->CF_VENTAS_COMISION,2).'</td>';
            $html.= '<td style="text-align: center;font-size: 7px;width:60px;">$'.number_format($valores->COMISION_VTAS_DIF,2).'</td>';
            $html.= '<td style="text-align: center;font-size: 7px;width:50px;">$'.number_format($valores->BONO,2).'</td>';
            $html.= '<td style="text-align: center;font-size: 7px;width:50px;">$'.number_format($valores->CF_COMISION_COBRANZA,2).'</td>';
            $html.= '<td style="text-align: center;font-size: 7px;width:50px;">$'.number_format($valores->CF_OTROS_INGRESOS,2).'</td>';
            $html.= '<td style="text-align: center;font-size: 7px;width:50px;">$'.number_format($valores->CF_IVA,2).'</td>';
            $html.= '<td style="text-align: center;font-size: 7px;width:55px;">$'.number_format($valores->COMISION_BRUTA,2).'</td>';
            $html.= '</tr>';
            while(list($id,$campos)=each($valores->CONTENIDO))
            {
                $html.= '<tr>';
                $html.= '<td style="text-align: center;font-size: 7px;width:30px;">'.$campos->REGION_ID.'</td>';
                $html.= '<td style="text-align: center;font-size: 7px;width:50px;">$'.number_format($campos->COMISION_CONTADO,2).'</td>';
                $html.= '<td style="text-align: center;font-size: 7px;width:50px;">$'.number_format($campos->COMISION_SUBSIDIO,2).'</td>';
                $html.= '<td style="text-align: center;font-size: 7px;width:50px;">$'.number_format($campos->NOTAS,2).'</td>';
                $html.= '<td style="text-align: center;font-size: 7px;width:50px;">$'.number_format($campos->NOTAS_DIF,2).'</td>';
                $html.= '</tr>';

                $COMISION_CONTADO += $campos->COMISION_CONTADO;
                $COMISION_SUBSIDIO += $campos->COMISION_SUBSIDIO;
                $NOTAS += $campos->NOTAS;
                $NOTAS_DIF += $campos->NOTAS_DIF;
            }
            reset($valores->CONTENIDO);

            $TOTAL_VTAS_OBJETIVO += $valores->CF_VENTAS_OBJETIVO;
            $TOTAL_VTAS_COMISION += $valores->CF_VENTAS_COMISION;
            $TOTAL_VTAS_DIF += $valores->COMISION_VTAS_DIF;
            $TOTAL_BONO += $valores->BONO;
            $TOTAL_COMISION_COBRANZA += $valores->CF_COMISION_COBRANZA;
            $TOTAL_OTROS_INGRESOS += $valores->CF_OTROS_INGRESOS;
            $TOTAL_IVA += $valores->CF_IVA;
            $TOTAL_COMISION_BRUTA += $valores->COMISION_BRUTA;
        }
        reset($datos->datos_x_estado);
        $html.='</table>';

        $html.='<table border="1"><tr>';
        $html.='<td style="width:120px; text-align:right;font-size: 8px;">Totales: </td>';
        $html.='<td style="text-align: center;font-size: 7px;width:50px;">$'.number_format($COMISION_CONTADO,2).'</td>';
        $html.='<td style="text-align: center;font-size: 7px;width:50px;">$'.number_format($COMISION_SUBSIDIO,2).'</td>';
        $html.='<td style="text-align: center;font-size: 7px;width:50px;">$'.number_format($NOTAS,2).'</td>';
        $html.='<td style="text-align: center;font-size: 7px;width:50px;">$'.number_format($NOTAS_DIF,2).'</td>';
        $html.='<td style="text-align: center;font-size: 7px;width:60px;">$'.number_format($TOTAL_VTAS_OBJETIVO,2).'</td>';
        $html.='<td style="text-align: center;font-size: 7px;width:60px;">$'.number_format($TOTAL_VTAS_COMISION,2).'</td>';
        $html.='<td style="text-align: center;font-size: 7px;width:60px;">$'.number_format($TOTAL_VTAS_DIF,2).'</td>';
        $html.='<td style="text-align: center;font-size: 7px;width:50px;">$'.number_format($TOTAL_BONO,2).'</td>';
        $html.='<td style="text-align: center;font-size: 7px;width:50px;">$'.number_format($TOTAL_COMISION_COBRANZA,2).'</td>';
        $html.='<td style="text-align: center;font-size: 7px;width:50px;">$'.number_format($TOTAL_OTROS_INGRESOS,2).'</td>';
        $html.='<td style="text-align: center;font-size: 7px;width:50px;">$'.number_format($TOTAL_IVA,2).'</td>';
        $html.='<td style="text-align: center;font-size: 7px;width:55px;">$'.number_format($TOTAL_COMISION_BRUTA,2).'</td>';
        $html.='</tr></table>'; 
        
        $request['table'] = $html;
        $pdf->lastPage();
        $pdf->writeHTML($html, true, false, true, false, '');

        //Cerramos y damos salida al fichero PDF
        ob_end_clean ();
        $pdf->Output('../files/reporte'.$_SESSION['GLOBAL_USER'].'.pdf', 'F'); 
        $request['LINK']='files/reporte'.$_SESSION['GLOBAL_USER'].'.pdf';
        //I -- ABRE EN NAVEGADOR
        //F -- GUARDA ARCHIVO EN SERVIDOR LOCAL
    }
    
    $TiempoFinal = getTiempo();
    $Tiempo = $TiempoFinal - $TiempoInicial;
    $Tiempo = round($Tiempo, 2);
    $request['SCRIPT']['TIME'] = $Tiempo;
    echo json_encode($request);
?>

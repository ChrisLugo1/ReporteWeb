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
    $datos=json_decode($_REQUEST['data']);
    $datos_x_estado=json_decode($_POST['datos_x_estado']);
    $datos_form = json_decode($_POST['form']);
    $empresas = json_decode($_POST['empresas']);
    $estados = json_decode($_POST['estados']);
    
    $request=array();
    $request['ERROR']='';
    $request['LINK']='';
    $request['SCRIPT'] = [];
    $request['DATOS']=$datos;
    $request['FORM']=$datos_form;
    $request['DATOS_ESTADO']=$datos_x_estado;
    $request['ESTADOS']=$estados;

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
            public $estados;
            public function Header() 
            {
                /*PONEMOS COLOR AL TEXTO Y A LAS LINEAS */
                $this->SetTextColor(0,0,0);
                $this->SetDrawColor(0,0,0);
                $DESC_EMPRESA = '';
                while(list($id,$valor)=each($this->empresas))
                {
                    if($this->datos_form->P_EMPRESA == $valor->VALUE)
                    {
                        $DESC_EMPRESA = $valor->TEXT;
                    }
                }
                reset($this->empresas);

                $del_estado = '';
                $al_estado = '';
                while(list($id,$valor)=each($this->estados))
                {
                    if($this->datos_form->P_TEXT_1 == $valor->VALUE)
                    {
                        $del_estado = $valor->TEXT;
                    }
                    if($this->datos_form->P_TEXT_2 == $valor->VALUE)
                    {
                        $al_estado = $valor->TEXT;
                    }
                }
                reset($this->estados);

                //ENCABEZADO DE LA PÁGINA
                $enc='<table style="border-style: solid; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px">
                        <tr>
                            <td rowspan="3" style="width:120px; text-align: center;"><br><br><img src="'.$this->img.'" height="15px"></td>
                            <td style="color:mediumblue; font-size:9px; width:190px;"><u><b>EDILAR, S.A. DE C.V.</b></u></td>
                            <td style="color:black; font-size:8px; text-align:right; width:230px;">Fuerza de Ventas: '.$DESC_EMPRESA.'</td>
                        </tr>
                        <tr>
                            <td style="color:black; font-size:9px; width:190px;">Asesores Activos</td>
                            <td style="color:black; font-size:8px; width:115px;text-align:right;">Del Estado: '.$del_estado.'</td>
                            <td style="color:black; font-size:8px; width:115px;text-align:right;">Al Estado: '.$al_estado.'</td>
                        </tr>
                        <tr>
                            <td style="color:black; font-size:9px; width:210px;"></td>
                            <td style="color:black; font-size:9px; width:210px;"></td>
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
        $pdf->estados = $estados;
        $pdf->setPageOrientation('P');
        $pdf->SetAutoPageBreak(true,1);
        $pdf->setHeaderMargin(1); 
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
        
        $html = '';
        while(list($id,$campo)=each($estados))
        {
            while(list($estado,$id_registro)=each($datos_x_estado))
            {
                if($campo->TEXT == $estado)
                {
                    $html.='<h6>'.$estado.'</h6>';
                    
                    //cabecera de la tabla
                    $html.='<table cellpadding="1" border="1px;"><tr>';
                    while(list($id,$columna)=each($datos->campos))
                    {
                        switch ($columna->NOMBRE)
                        {
                            case 'EMPLEADO':
                                $html.= '<td style="text-align: center;font-size: 7px;width:30px;"><b>'.$columna->COLUMNA.'</b></td>';
                            break;
                            case 'PUESTO':
                                $html.= '<td style="text-align: center;font-size: 7px;width:30px;"><b>'.$columna->COLUMNA.'</b></td>';
                            break;
                            case 'NOMBRE':
                                $html.= '<td style="text-align: center;font-size: 7px;width:100px;"><b>'.$columna->COLUMNA.'</b></td>';
                            break;
                            case 'DIRECCION':
                                $html.= '<td style="text-align: center;font-size: 7px;width:100px;"><b>'.$columna->COLUMNA.'</b></td>';
                            break;
                            case 'CIUDAD':
                                $html.= '<td style="text-align: center;font-size: 7px;width:40px;"><b>'.$columna->COLUMNA.'</b></td>';
                            break;
                            case 'COLONIA':
                                $html.= '<td style="text-align: center;font-size: 7px;width:40px;"><b>'.$columna->COLUMNA.'</b></td>';
                            break;
                            case 'CP':
                                $html.= '<td style="text-align: center;font-size: 7px;width:20px;"><b>'.$columna->COLUMNA.'</b></td>';
                            break;
                            case 'TELCASA':
                                $html.= '<td style="text-align: center;font-size: 7px;width:40px;"><b>'.$columna->COLUMNA.'</b></td>';
                            break;
                            case 'TELRECADOS':
                                $html.= '<td style="text-align: center;font-size: 7px;width:40px;"><b>'.$columna->COLUMNA.'</b></td>';
                            break;
                            case 'TEL_MOVIL':
                                $html.= '<td style="text-align: center;font-size: 7px;width:40px;"><b>'.$columna->COLUMNA.'</b></td>';
                            break;
                            case 'E_MAIL':
                                $html.= '<td style="text-align: center;font-size: 7px;width:70px;"><b>'.$columna->COLUMNA.'</b></td>';
                            break;
                        }
                    }
                    reset($datos->campos);
                    $html.='</tr>';

                    //Registros de la tabla
                    while(list($id,$reg)=each($id_registro))
                    {
                        $html.='<tr>';
                        while(list($id,$columna)=each($datos->campos))
                        {
                            switch ($columna->NOMBRE)
                            {
                                case 'EMPLEADO':
                                    $html.= '<td style="text-align: center;font-size: 6px;width:30px;">'.$reg->{$columna->NOMBRE}.'</td>';
                                break;
                                case 'PUESTO':
                                    $html.= '<td style="text-align: center;font-size: 6px;width:30px;">'.$reg->{$columna->NOMBRE}.'</td>';
                                break;
                                case 'NOMBRE':
                                    $html.= '<td style="text-align: center;font-size: 6px;width:100px;">'.$reg->{$columna->NOMBRE}.'</td>';
                                break;
                                case 'DIRECCION':
                                    $html.= '<td style="text-align: center;font-size: 6px;width:100px;">'.$reg->{$columna->NOMBRE}.'</td>';
                                break;
                                case 'CIUDAD':
                                    $html.= '<td style="text-align: center;font-size: 5px;width:40px;">'.$reg->{$columna->NOMBRE}.'</td>';
                                break;
                                case 'COLONIA':
                                    $html.= '<td style="text-align: center;font-size: 5px;width:40px;">'.$reg->{$columna->NOMBRE}.'</td>';
                                break;
                                case 'CP':
                                    $html.= '<td style="text-align: center;font-size: 6px;width:20px;">'.$reg->{$columna->NOMBRE}.'</td>';
                                break;
                                case 'TELCASA':
                                    $html.= '<td style="text-align: center;font-size: 6px;width:40px;">'.$reg->{$columna->NOMBRE}.'</td>';
                                break;
                                case 'TELRECADOS':
                                    $html.= '<td style="text-align: center;font-size: 6px;width:40px;">'.$reg->{$columna->NOMBRE}.'</td>';
                                break;
                                case 'TEL_MOVIL':
                                    $html.= '<td style="text-align: center;font-size: 6px;width:40px;">'.$reg->{$columna->NOMBRE}.'</td>';
                                break;
                                case 'E_MAIL':
                                    $html.= '<td style="text-align: center;font-size: 6px;width:70px;">'.$reg->{$columna->NOMBRE}.'</td>';
                                break;
                            }
                        }
                        reset($datos->campos);
                        $html.='</tr>';
                    }
                    reset($id_registro);
                    $html.='</table>';
                }
            }
            reset($datos_x_estado);
        }
        reset($estados);

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

<?php
include("../../../../include/conexion.php");
include("../../../dominio_edilar.php");
?>
<!DOCTYPE html>
<html lang="es" ng-app="MyAplicacion">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta http-equiv="Cache-Control" content="no-store"/>
        <meta http-equiv="Cache-Control" content="no-cache"/>
        <meta http-equiv="Pragma" content="no-cache"/>
        <title>Titulo Informe</title>
        <link rel="shortcut icon" href="../../../img/edilar.ico">
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.7.6/angular.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.7.6/angular-animate.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.7.6/angular-aria.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.7.6/angular-messages.min.js"></script>
        <!-- Angular Material Library -->
        <script src="https://ajax.googleapis.com/ajax/libs/angular_material/1.1.12/angular-material.min.js"></script>
        <!-- Angular Google Chart -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/angular-google-chart/1.0.0-beta.1/ng-google-chart.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.js"></script>
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/angular_material/1.1.12/angular-material.min.css">
        <link rel="stylesheet" href="css/edi_md.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,400italic">
        <script type="text/javascript" src="js/control.js"></script>
        <style type="text/css">
            a {
                padding: 10px;
                text-decoration: none;
                color: white;
                font-weight: bold;
                display: block;

                border-right: 30px solid transparent;
                border-bottom: 30px solid #4c4c4c; 

                height: 0;
                line-height: 50px;
            }
        </style>
        <script>
            
        </script>
    </head>
    <body ng-controller="ctrlPrincipal">   
        <edi-toolbar-principal></edi-toolbar-principal>
        <edi-menu-principal></edi-menu-principal>
        <md-content layout-padding class="parametros" ng-hide="OCULTA_ARCHIVO">
            <section layout-align="center center" layout-wrap="">
                <archivo-csv edi-archivocsv='DatosArchivo' delimitador='|'></archivo-csv>
                <archivo-img edi-archivo='ArchivoIMG' nombre="'ArchivoIMG'" clase="'archivo_info'"></archivo-img>
            </section>
        </md-content>
        <edi-grid></edi-grid>
    </body>
</html>
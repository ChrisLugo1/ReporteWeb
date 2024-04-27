var app = angular.module("MyAplicacion",['ngMaterial','googlechart']);
app.config(function($mdDateLocaleProvider) 
{
    $mdDateLocaleProvider.formatDate = function(date) {
      return moment(date).format('DD/MM/YYYY');
    };

    $mdDateLocaleProvider.parseDate = function(dateString) {
      var m = moment(dateString, 'DD/MM/YYYY', true);
      return m.isValid() ? m.toDate() : new Date(NaN);
    };
});
app.directive('ediArchivo',function($parse){
    function link(scope,element,attrs)
    {
        element.on("change",function(e)
        {
            $parse(attrs.ediArchivo).assign(scope,element[0].files);
        });
    };
    return{
        restrict: "A",
        link: link
    }
});
app.directive('archivoImg',function($parse){
    function link(scope,element,attrs)
    {
        function Archivo()
        {
            this.inputarchivo = {};
            this.info = {};
            this.status = 'Sin Archivo';
            this.contenido = {};
            this.muestra = [];
            this.registros = {};
            this.num_registros = 0;
            this.datos = [];
            this.lector = new FileReader();
        };
        scope.gridArchivoImg = new Archivo();
        element.on("change",function(e)
        {
            scope.gridArchivoImg.info = e.target.files[0];
            scope.gridArchivoImg.status = 'Cargando...';
            scope.$apply();
            scope.gridArchivoImg.lector.addEventListener('load',function(e){scope.muestra_imagen(e);},false);
            scope.gridArchivoImg.lector.readAsDataURL(scope.gridArchivoImg.info);
        });
        scope.muestra_imagen = function(e)
        {
            var target = document.querySelector("#"+scope.nombre);
            target.setAttribute('src',e.target.result);
            scope.gridArchivoImg.status = 'Listo';
            scope.$apply();
        };
        
    };
    return{
        restrict: 'E',
        scope : {
            clase : '=clase',
            nombre : '=nombre'
        },
        templateUrl: "template/archivoimg.html",
        link: link
    }
});
app.directive('archivoCsv',function($parse){
    function link(scope,element,attrs)
    {
        function Archivo()
        {
            this.inputarchivo = {};
            this.info = {};
            this.status = 'Sin Archivo';
            this.contenido = {};
            this.muestra = [];
            this.registros = {};
            this.cabecera = [];
            this.datos = [];
            this.lector = new FileReader();
        };
        scope.gridArchivo = new Archivo();
        scope.delimitador = '|';
        element.on("change",function(e)
        {
            scope.gridArchivo.info = e.target.files[0];
            scope.gridArchivo.status = 'Cargando...';
            scope.$apply();
            scope.gridArchivo.lector.addEventListener('load',function(e){scope.carga_contenido(e);},false);
            scope.gridArchivo.lector.readAsText(scope.gridArchivo.info);
        });
        scope.carga_contenido = function(e)
        {
            scope.gridArchivo.status = 'Listo';
            scope.gridArchivo.contenido = e.target.result;
            scope.gridArchivo.registros = scope.gridArchivo.contenido.split(/\n/);
            scope.gridArchivo.info.num_registros = scope.gridArchivo.registros.length;
            var datos = scope.gridArchivo.registros[0].split(scope.delimitador);
            for(var j=0; j<datos.length; j++)
            {
                var d = {
                    NOMBRE : datos[j].replace(/"/g,'').trim(),
                    TIPO : 'VARCHAR2'
                };
//                d = d.trim();
                scope.gridArchivo.cabecera[j]=d;
            }
            for(var i=1; i<scope.gridArchivo.info.num_registros;i++)
            {
                scope.gridArchivo.datos[i-1] = {};
                if(i<10)
                {
                    scope.gridArchivo.muestra[i-1] = {};
                }
                var datos = scope.gridArchivo.registros[i].split(scope.delimitador);
                for(var j=0; j<datos.length; j++)
                
                {
                    var d = datos[j].replace(/"/g,'');
                    d = d.trim();
                    scope.gridArchivo.datos[i-1][scope.gridArchivo.cabecera[j].NOMBRE]=d;
                    if(i<10)
                    {
                        scope.gridArchivo.muestra[i-1][scope.gridArchivo.cabecera[j].NOMBRE]=d;
                    }
                }
            }
            //console.log(scope.gridArchivo);
            // Se usa para llevar los datos al scope del control con el nombre de el atributo edi-archivocsv
            $parse(attrs.ediArchivocsv).assign(scope,scope.gridArchivo);
            scope.$apply();
        };
    };
    return{
        restrict: 'E',
        templateUrl: "template/archivocsv.html",
        link: link
    }
});
app.directive("ediMenuPrincipal",function(){
    function link(scope,element,attrs)
    {
        
    }
    return {
        restrict: 'E',
        templateUrl: "template/menu_principal.html",
        link: link
    }
});
app.directive("ediToolbarPrincipal",function(){
    function link(scope,element,attrs)
    {
        
    }
    return {
        restrict: 'E',
        templateUrl: "template/toolbar_principal.html",
        link: link
    }
});
app.directive("ediFormParametros",function(){
    function link(scope,element,attrs)
    {
        
    }
    return {
        restrict: 'E',
        templateUrl: "template/form_parametros.html",
        link: link
    }
});
app.directive("ediGrid",function(){
    function link(scope,element,attrs)
    {
        scope.grid = {
            CARGANDO : false,
            NUMREG : '20',
            opFILTROSGRID : [],
            FILTRO : '',
            informacion : {
                    titulo : '',
                    campos : [],
                    datos : [],
                    CORRECTOS:[],
                    INCORRECTOS:[]
                },
            SELECT : 0,
            SELECCIONADO : {},
            TOTALES : {},
            sumas : 'NO',
            clase : 'table_azul',
            paginas : {
                TOTAL : 0,
                ACTUAL : 1,
                MIN : 0,
                MAX : 0
            },
            opNUMREG : [
                {TEXT:'10', VALUE:'10'},
                {TEXT:'20', VALUE:'20'},
                {TEXT:'50', VALUE:'50'},
                {TEXT:'100', VALUE:'100'}
            ],
            selecciona_registro : function(i, d)
            {
                scope.grid.SELECT=i;
                scope.grid.SELECCIONADO=JSON.parse(JSON.stringify(d));
            },
            crea_paginas : function()
                            {
                                if(scope.grid.informacion.datos.length>0)
                                {
                                    scope.grid.paginas.TOTAL = Math.ceil(scope.grid.informacion.datos.length/Number(scope.grid.NUMREG));
                                }
                            },
            controles_paginas : function()
                                {
                                    if(scope.grid.paginas.ACTUAL>scope.grid.paginas.TOTAL)
                                    {
                                        scope.grid.paginas.ACTUAL=scope.grid.paginas.TOTAL;
                                    }
                                    if(scope.grid.paginas.ACTUAL<=0)
                                    {
                                        scope.grid.paginas.ACTUAL=1;
                                    }
                                    if(scope.grid.paginas.ACTUAL==1)
                                    {
                                        scope.grid.paginas.MIN=0;
                                        scope.grid.paginas.MAX=Number(scope.grid.NUMREG)-1;
                                    }
                                    else
                                    {
                                        scope.grid.paginas.MIN=((scope.grid.paginas.ACTUAL-1)*Number(scope.grid.NUMREG));
                                        scope.grid.paginas.MAX=(scope.grid.paginas.ACTUAL*Number(scope.grid.NUMREG))-1;
                                    }
                                }
        }
        scope.$watch('grid.paginas.ACTUAL',function(newValue, oldValue)
        {
            scope.grid.controles_paginas();
        });
        scope.$watch('grid.NUMREG',function(newValue, oldValue)
        {
            scope.grid.paginas.ACTUAL=1;
            scope.grid.crea_paginas();
            scope.grid.controles_paginas();
        });
        scope.$watch('grid.informacion',function(newValue, oldValue)
        {
            if(newValue.datos.length>0)
            {
                scope.grid.crea_paginas();
                scope.grid.TOTALES = {};
                scope.grid.informacion.campos.forEach(function(item,index){
                    var op = {VALUE : item.NOMBRE, TEXT:item.NOMBRE};
                    scope.grid.opFILTROSGRID.push(op);
                    if(index>0)
                    {
                        if((item.TIPO=='NUMBER')||(item.TIPO=='MONEDA'))
                        {
                            scope.grid.TOTALES[item.NOMBRE]=0;
                        }
                        else
                        {
                            scope.grid.TOTALES[item.NOMBRE]='';
                        }
                    }
                    else
                    {
                        scope.grid.TOTALES[item.NOMBRE]='Total:';
                    }
                });
                if(scope.grid.sumas=='SI')
                {
                    scope.grid.informacion.datos.forEach(function(item,index)
                    {
                        scope.grid.informacion.campos.forEach(function(i,key){
                            if(key>0)
                            {
                                if((i.TIPO=='NUMBER')||(i.TIPO=='MONEDA'))
                                {
                                    scope.grid.TOTALES[i.NOMBRE]+=parseFloat(item[i.NOMBRE]);
                                }
                            }
                        });
                    });
                }
//                console.log(scope.grid);
            }
        });
    }
    return {
        restrict: 'E',
//        scope : {
//            informacion : '=info',
//            sumas : '=sumas',
//            clase : '=clase'
//        },
        templateUrl: "template/grid.html",
        link: link
    }
});
app.directive("ediInforme",function(){
    function link(scope,element,attrs)
    {
        
    }
    return {
        restrict: 'E',
        templateUrl: "template/informe.html",
        link: link
    }
});
app.directive("ediCargarArchivo",function(){
    function link(scope,element,attrs)
    {
        
    }
    return {
        restrict: 'E',
        templateUrl: "template/cargar_archivo.html",
        link: link
    }
});
app.directive("ediVisualizarImagen",function(){
    function link(scope,element,attrs)
    {
        
    }
    return {
        restrict: 'E',
        templateUrl: "template/visualizar_imagen.html",
        link: link
    }
});
app.filter("campos", function()
{
    return function(text)
    {
        return String(text).replace(/_/g," ");
    }
})
app.controller("ctrlPrincipal",function($scope, $http, $compile, $mdSidenav, $mdDialog)
    {
        $scope.titulo_informe="Masivo Memorandum Contratos";
        $scope.seccion="Captura";
        // Variable de control de loading
        $scope.CARGA_ARCHIVO = false;
        $scope.OCULTA_ARCHIVO = false;
        //$scope.CERRAR_PROCESO = false;
        $scope.PROCESAR=false;
        $scope.ESTRUCTURA={};
        $scope.CAMPOS={};
        $scope.form={
            P_TIPO_IMAGEN: ''
        };
        $scope.datos_fila=[];
        $scope.datos_seleccionados=[];

        //Varible para el estado de notificaciones
        $scope.notificaciones = {};
        
        //$scope.grid = JSON.parse(JSON.stringify($scope.datos));
        
        $scope.iniciar_app = function()
        {
            document.title=$scope.titulo_informe;
            /* Activar Notificaciones */
            /* Activar el Chrome sólo envia notificaciones en sitios https o a traves de localhost */
            if(!("Notification" in window)) 
            {
            alert("Este navegador no soporta Notificaciones");
                $scope.notificaciones.status=false;
            }
            else
            {
                $scope.notificaciones.status=true;
                Notification.requestPermission().then(function(permission) 
                {
                    $scope.$apply(function()
                    {
                        if (permission === 'granted') 
                        {
                            $scope.notificaciones.permiso=true;
                            //$scope.enviar_notificacion("Notificaciones Activadas!");
                        }
                        else
                        {
                            $scope.notificaciones.permiso=false;
                            //$scope.enviar_notificacion("Notificaciones Denegadas!");
                        }
                    });
                });
            }
            $scope.$watchCollection('DatosArchivo', function(newCollection, oldCollection) 
            {
                if (newCollection === oldCollection) {
                return;
                }
                //$scope.grid.informacion.campos = $scope.DatosArchivo.cabecera;
                //$scope.grid.informacion.titulo = 'Información del Archivo';
                $scope.grid.informacion.datos = $scope.DatosArchivo.datos;
            $scope.cargar_datos($scope.DatosArchivo);
            });
            $scope.cargar_formulario();
        };

        $scope.cargar_datos = function(informacion)
        {
            $scope.CARGA_ARCHIVO = true;
            $scope.CARGANDO = true;
            var formData = new FormData();
            formData.append("data",JSON.stringify(informacion));
            formData.append('estados',JSON.stringify($scope.opEstados));
            $http.post("data/json_data.php",
                        formData,
                        {
                            headers: {
                                "Content-Type": undefined
                            },
                            transformRequest: angular.identity
                        })
            .then(function($response)
                {
                    console.log($response.data);
                    if(!$response.data.ERROR)
                    {
                        $scope.grid.informacion.datos =$response.data.DATOS;
                        $scope.grid.informacion.campos = $response.data.campos;
                        //console.log($scope.ESTRUCTURA.length);
                    }
                    else
                    {
                        $scope.enviar_notificacion("Ha ocurrido un error en el servicio!");
                    }
                    $scope.CARGANDO = false;
                }
                ,function($response)
                {
                console.log($response.status);
                alert("Error: " + $response.status);
            });
        }

        $scope.valida_campos = function()
        {
            var Imagen_FILE = document.getElementById("ArchivoImg").files.length;
            //var Imagen_NAME = Imagen_FILE.files[0].name;
            //console.log(Imagen_NAME);
            if(Imagen_FILE == 0) return false;
            return true;
        };
        
        $scope.exportar_excel = function()
        {
            //console.log($scope.grid.informacion);
            $scope.cargando = true;
            var formData = new FormData();
            formData.append("tipos_imagen",JSON.stringify($scope.opTIPO_IMAGEN));
            formData.append("data",JSON.stringify($scope.grid.informacion));
            $http.post("data/crear_xlsx.php",
                        formData,
                        {
                            headers: {
                                "Content-Type": undefined
                            },
                            transformRequest: angular.identity
                        })
            .then(function($response)
                {
                    console.log($response.data);
                    if($response.data.LINK.length>0)
                    {
                        window.open($response.data.LINK);
                    }
                    else
                    {
                        
                    }
                    $scope.cargando = false;
                }
                ,function($response)
                {
                    console.log($response.status);
                    alert("Error: " + $response.status);
                });
                
        }

        $scope.procesar_info = function()
        {
            if($scope.valida_campos())
            {
                $scope.CARGANDO = true;
                var Imagen_FILE = document.getElementById("ArchivoImg");
                var Imagen_DATA = Imagen_FILE.files[0];
                //console.log(Imagen_DATA);
                
                var formData = new FormData();
                formData.append("tipos_imagen",JSON.stringify($scope.opTIPO_IMAGEN));
                //formData.append("tipo_img",JSON.stringify($scope.form.P_TIPO_IMAGEN));
                formData.append("data",JSON.stringify($scope.grid.informacion.datos));
                formData.append("data_img",Imagen_DATA);
                $http.post("data/json_procesar_info.php",
                            formData,
                            {
                                headers: {
                                    "Content-Type": undefined
                                },
                                transformRequest: angular.identity
                            })
                .then(function($response)
                    {
                        console.log($response.data);
                        if(!$response.data.ERROR)
                        {
                            for (let index = 0; index < $response.data.EXISTENCIAS.length; index++)
                            {
                                if($response.data.EXISTENCIAS[index]['TOTAL']==1)
                                {
                                    //si ya existe se va a los archivos incorrectos
                                    $scope.grid.informacion.INCORRECTOS = $response.data.INCORRECTOS;
                                }
                                else
                                {
                                    $scope.grid.informacion.CORRECTOS = $response.data.CORRECTOS;
                                    //$scope.enviar_notificacion($response.data.MENSAJE);
                                }
                            }
                        }
                        else
                        {
                            $scope.enviar_notificacion("Ha ocurrido un error en el servicio!");
                        }
                        $scope.CARGANDO = false;
                    }
                    ,function($response)
                    {
                    console.log($response.status);
                    alert("Error: " + $response.status);
                });
            }
            else
            {
                $scope.enviar_notificacion("Por favor completar todos los campos para continuar");
            }
        }

        $scope.salir = function()
        {
            $scope.enviar_notificacion("No se ha generado las hojas de cobranza");
            setTimeout(() => {  window.location.reload(); }, 5000);
        }
        
        $scope.cargar_formulario = function()
        {
            $scope.cargando=true;
            $http.get("data/json_formulario.php")
            .then(function($response)
                {
                    $scope.opTIPO_IMAGEN=$response.data.TIPO_IMG;
                    $scope.opEstados=$response.data.ESTADOS;
                    $scope.cargando=false;
                },function($response)
                {
                    console.log($response);
                    $scope.cargando=false;
                });
        }
        
        $scope.switch = function(id_menu)
        {
            $mdSidenav(id_menu).toggle();
        };
        
        // Controles de Notificaciones
        $scope.enviar_notificacion = function(mensaje)
        {
            console.log("Notificación: " + mensaje);
//            console.log($scope.notificaciones);
//            if(($scope.notificaciones.status===true)&&($scope.notificaciones.permiso===true))
//            {
//                var options = {
//                                body: mensaje,
//                                icon: "../../../img/edilar.ico",
//                                tag: 'SisEdilar'
//                            };
//                var titulo = "SisEdilar";
//                var notification = new Notification(titulo,options);
//                notification.onclick = function(e){
//                    window.focus();
//                    this.close();
//                };
//            }
//            else
//            {
                $mdDialog.show(
                  $mdDialog.alert()
                    .parent(angular.element(document.body))
                    .clickOutsideToClose(true)
                    .title('Notificacion')
                    .textContent(mensaje)
                    .ariaLabel('Notificacion')
                    .ok('Aceptar')
                    .targetEvent()
                );
//            }
        };
        $scope.toggleNotificaciones = function(ev)
        {
            console.log(ev);
            $scope.notificaciones.permiso = !$scope.notificaciones.permiso;
//            var texto = $scope.notificaciones.permiso ? 'Desactivar' : 'Activar';
//            var confirm = $mdDialog.confirm()
//                    .title('Mensaje')
//                    .textContent('Desea ' + texto + 'las notificaciones?' )
//                    .ariaLabel('Pregunta')
//                    .targetEvent(ev)
//                    .ok('Si')
//                    .cancel('No');
//            $mdDialog.show(confirm).then(function()
//                {
//                    $scope.notificaciones.permiso = !$scope.notificaciones.permiso;
//                }, function()
//                {
//                    $scope.enviar_notificacion("Operacion Cancelada!");
//                });
        }
        
        /* Control de Información */
        $scope.mostrar_informacion = function(ev)
        {
            $mdDialog.show(
                    {
                        controller: $scope.ctrlDialogIinformacion,
                        templateUrl: 'template/info_app.html',
                        parent: angular.element(document.body),
                        targetEvent: ev,
                        clickOutsideToClose:true
                    })
                    .then(function(answer) 
                    {
                        console.log(answer);
                    }, function() 
                    {
                        console.log('Has cancelado el diálogo!');
                    });
        };
        $scope.ctrlDialogIinformacion = function($scope,$mdDialog)
        {
            $scope.cerrar_dialogo = function()
            {
                $mdDialog.hide("Dialogo Cerrado");
            }
            $scope.cancel = function() 
            {
                $mdDialog.cancel();
            };
            $scope.responder = function(answer) 
            {
                $mdDialog.hide(answer);
            };
        };
        
        $scope.iniciar_app();
        
});
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
                    datos : []
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
app.filter("campos", function()
{
    return function(text)
    {
        return String(text).replace(/_/g," ");
    }
})
app.controller("ctrlPrincipal",function($scope, $http, $compile, $mdSidenav, $mdDialog)
    {
        $scope.titulo_informe="Informe";
        $scope.seccion="Concentrado Comisiones";
        // Variable de control de loading
        $scope.cargando=false;
        $scope.limpiar=true;

        // Varible para el estado de notificaciones
        $scope.notificaciones = {};
        $scope.limpiar_campos = function(){
            $scope.form = {
                P_TEXT_1 : '',
                P_TEXT_2 : '',
                P_FECHA_1 : {
                    DATE : '',
                    FORMAT : ''
                },
                P_FECHA_2 : {
                    DATE : '',
                    FORMAT : ''
                },
                P_FECHA_3 : {
                    DATE : '',
                    FORMAT : ''
                },
                P_FECHA_4 : {
                    DATE : '',
                    FORMAT : ''
                }
            };
            $scope.grid.informacion = {
                campos : [],
                datos : []
            };
        };

        $scope.form = {
            P_TEXT_1 : '',
            P_TEXT_2 : '',
            P_FECHA_1 : {
                DATE : '',
                FORMAT : ''
            },
            P_FECHA_2 : {
                DATE : '',
                FORMAT : ''
            },
            P_FECHA_3 : {
                DATE : '',
                FORMAT : ''
            },
            P_FECHA_4 : {
                DATE : '',
                FORMAT : ''
            }
        };

        $scope.$watch('form.P_TEXT_1', function($new,$old)
        {
            //console.log($new+" y "+$old);
            if($new == undefined || $new == "")
            {
                $scope.consulta = true;
                $scope.limpiar = false;
            }
            else if($new != "" || $new != "undefined")
            {
                $scope.consulta = false;
                $scope.limpiar = true;
            }
        });

        $scope.$watch('form.P_TEXT_2', function($new,$old)
        {
            //console.log($new+" y "+$old);
            if($new == undefined || $new == "")
            {
                $scope.consulta = true;
                $scope.limpiar = false;
            }
            else if($new != "" || $new != "undefined")
            {
                $scope.consulta = false;
                $scope.limpiar = true;
            }
        });

        $scope.$watch('form.P_FECHA_1.DATE', function($new,$old)
        {
            if($new!==$old)
            {
                $scope.form.P_FECHA_1.FORMAT=moment($new).format('DD/MM/YYYY');
                //console.log($scope.form.P_FECHA_1);
                $scope.consulta = false;
                $scope.limpiar = true;
            }
        });

        $scope.$watch('form.P_FECHA_2.DATE', function($new,$old)
        {
            if($new!==$old)
            {
                $scope.form.P_FECHA_2.FORMAT=moment($new).format('DD/MM/YYYY');
                //console.log($scope.form.P_FECHA_1);
                $scope.consulta = false;
                $scope.limpiar = true;
            }
        });

        $scope.$watch('form.P_FECHA_3.DATE', function($new,$old)
        {
            if($new!==$old)
            {
                $scope.form.P_FECHA_3.FORMAT=moment($new).format('DD/MM/YYYY HH:mm:ss');
                //console.log($scope.form.P_FECHA_1);
                $scope.consulta = false;
                $scope.limpiar = true;
            }
        });

        $scope.$watch('form.P_FECHA_4.DATE', function($new,$old)
        {
            if($new!==$old)
            {
                $scope.form.P_FECHA_4.FORMAT=moment($new).format('DD/MM/YYYY HH:mm:ss');
                //console.log($scope.form.P_FECHA_1);
                $scope.consulta = false;
                $scope.limpiar = true;
            }
        });
        
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
//                            $scope.enviar_notificacion("Notificaciones Activadas!");
                        }
                        else
                        {
                            $scope.notificaciones.permiso=false;
//                            $scope.enviar_notificacion("Notificaciones Denegadas!");
                        }
                    });
                });
            }
            $scope.cargar_formulario();
            //$scope.cargar_datos();
        };
        
        $scope.valida_form = function()
        {
            if($scope.form.P_TEXT_1=="") return false;
            if($scope.form.P_TEXT_2=="") return false;
            return true;
        }
        
        $scope.cargar_datos = function()
        {
            if($scope.valida_form())
            {
                $scope.cargando = true;
                var formData = new FormData();
                formData.append("data",JSON.stringify($scope.form));
                formData.append("state",JSON.stringify($scope.opESTADOS));
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
                            // Para el Grid los datos se deben de incluir en la información del grid.
                            $scope.grid.informacion=$response.data.datos;
                            // Se puede cambiar el estilo de la tabla cambiando la clase del grid
                            //$scope.grid.clase='table_verde';
                        }
                        else
                        {
                            $scope.enviar_notificacion("Ha ocurrido un error en el servicio!");
                        }
                        $scope.cargando = false;
                        $scope.consulta = true;
                        $scope.limpiar = false;
                    }
                    ,function($response)
                    {
                    console.log($response.status);
                    alert("Error: " + $response.status);
                });
            }
            else
            {
                $scope.enviar_notificacion("Favor de completar el campo!");
            }
        }

        $scope.exportar_excel = function()
        {
            $scope.cargando = true;
            var formData = new FormData();
            formData.append("form",JSON.stringify($scope.form));
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

        $scope.exportar_pdf = function()
        {
            $scope.cargando = true;
            var formData = new FormData();
            formData.append("form",JSON.stringify($scope.form));
            formData.append("data",JSON.stringify($scope.grid.informacion));
            formData.append('empresas',JSON.stringify($scope.opEMPRESAS));
            formData.append('ventas',JSON.stringify($scope.opVENTAS));
            $http.post("data/crear_pdf.php",
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

        $scope.cargar_formulario = function()
        {
            $scope.cargando=true;
            $http.get("data/json_formulario.php")
            .then(function($response)
                {
                    $scope.opVENTAS=$response.data.VENTAS;
                    $scope.opEMPRESAS=$response.data.EMPRESAS;
                    $scope.opESTADOS=$response.data.ESTADOS;
                    $scope.cargando=false;
                    console.log($response);
                },function($response)
                {
                    console.log($response);
                    $scope.cargando=false;
                });
        }

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
<?php
session_start();
require_once 'clases/usuario.php';
require_once 'clases/region.php';
require_once 'clases/vm_grafico_alarmas.php';
require_once 'vista/vw_alarmas_region.php';
require_once 'vista/modal/modal_informativo.php';

$Fecha=getdate();
$Anio=$Fecha["year"];

$cod_region = $_GET['region'];
$mi_region = region::traer_region($cod_region);

$mi_usuario = cl_usuario::traer_usuario($_SESSION["username"]);
$mi_area = $mi_usuario->getCod_area();
$mi_rol = $mi_usuario->getCod_rol();

$alarmas = vm_grafico_alarmas::traer_alarmas_region($cod_region);
foreach($alarmas as $rows):
    $data_alarmas[] = array($rows['comuna'],$rows['sitios'],$rows['alarmas']);
endforeach;

$alarmas_mapa = vm_grafico_alarmas::traer_alarmas_region_mapa($cod_region);
foreach($alarmas_mapa as $rows):
    $string = str_replace(' ', '-', $rows['nombre']); // Replaces all spaces with hyphens.
    $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    $nombre = preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.

    $data_alarmas_mapa[] = array($nombre ,$rows['CELDAS_ALARMADAS'], $rows['lat_google'], $rows['lon_google'], $rows['id'], $rows['tipo_nodo']);
endforeach;

$centro_mapa = vm_grafico_alarmas::traer_centro_mapa_region($cod_region);
foreach ($centro_mapa as $rows_centro):
    $lat = $rows_centro['lat'];
    $lon = $rows_centro['lon'];
endforeach;

// Condicional mejora nombre de Región
if ($mi_region->getRegion() == "RM") {
    $region = 'Región Metropolitana';
}
else {
$region = $mi_region->getRegion()." Región";
}

?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-6">
        <h2>Alarma eléctrica a nivel regional - <?php echo $region; ?></h2>
        <ol class="breadcrumb">

            <?php
            // Vista de todas las regiones
            vw_alarmas_region::lista_regiones($lista);
            ?> 

        </ol>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInDown">
    <div class="row">

        <div class="col-lg-4">
            <?php
            // Vista de tabla por regiones
            vw_alarmas_region::lista_regional($alarmas);
            ?>
        </div>

        <!-- Mapa -->
        <div class="col-lg-8">
            <div class="ibox float-e-margins animated fadeInDown">
                <div class="ibox-title">
                    <h5>Alarmas electricas de la <?php echo $region; ?></h5>
                    <div ibox-tools></div>
                </div>
                <div class="ibox-content" id="mapa" style="height: 623px">
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Alarmas eléctricas por comuna</h5>
                </div>
                <div class="ibox-content">
                    <div style="text-align: center">
                        <p class="label label-plain">Sitios</p>
                        <p class="label label-danger">Alarmas</p>
                    </div>
                    <div>
                        <canvas class="center-block" id="barChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>

        

    </div>

</div>

<!-- Mainly scripts -->
<script src="js/jquery-2.1.1.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

<script src="https://maps.google.com/maps/api/js?sensor=false"></script>

<!-- Flot -->
<script src="js/plugins/flot/jquery.flot.js"></script>
<script src="js/plugins/flot/jquery.flot.tooltip.min.js"></script>
<script src="js/plugins/flot/jquery.flot.spline.js"></script>
<script src="js/plugins/flot/jquery.flot.resize.js"></script>
<script src="js/plugins/flot/jquery.flot.pie.js"></script>
<script src="js/plugins/flot/jquery.flot.symbol.js"></script>
<script src="js/plugins/flot/curvedLines.js"></script>

<!-- Peity -->
<script src="js/plugins/peity/jquery.peity.min.js"></script>
<script src="js/demo/peity-demo.js"></script>

<!-- Custom and plugin javascript -->
<script src="js/inspinia.js"></script>
<script src="js/plugins/pace/pace.min.js"></script>

<!-- jQuery UI -->
<script src="js/plugins/jquery-ui/jquery-ui.min.js"></script>

<!-- Jvectormap -->
<script src="js/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="js/plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>

<!-- Sparkline -->
<script src="js/plugins/sparkline/jquery.sparkline.min.js"></script>

<!-- Sparkline demo data  -->
<script src="js/demo/sparkline-demo.js"></script>

<!-- ChartJS-->
<script src="js/plugins/chartJs/Chart.min.js"></script>

<!-- Toastr script -->
<script src="js/plugins/toastr/toastr.min.js"></script>

<!-- Data Tables >
<script src="js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="js/plugins/dataTables/dataTables.responsive.js"></script>
<script src="js/plugins/dataTables/dataTables.tableTools.min.js"></script-->
<script src="js/plugins/dataTables/datatables.min.js"></script>

<script>
    var barData = {
        labels: [
            <?php $i=0;
                foreach($data_alarmas as $datos){ $i++;
            ?>
            <?php echo "'$datos[0]'"; ?> ,
            <?php
                if(count($data_alarmas) == $i-1){
            ?>
            <?php echo "'$datos[0]'"; ?>
            <?php
                }
            } ?>
        ],
        datasets: [
            {
                label: "Sitios",
                fillColor: "rgba(220,220,220,0.5)",
                strokeColor: "rgba(220,220,220,0.8)",
                highlightFill: "rgba(220,220,220,0.75)",
                highlightStroke: "rgba(220,220,220,1)",
                data: [
                    <?php
                    $i=0;
                    foreach($data_alarmas as $datos)
                    {
                        $i++;
                        echo $datos[1]; ?> ,
                    <?php
                        if(count($data_alarmas) == $i-1){
                            echo $datos[1];
                        }
                    }
                ?>
                ]
            },
            {
                label: "Alarmas",
                fillColor: "rgba(236, 71, 88,0.5)",
                strokeColor: "rgba(236, 71, 88,0.8)",
                highlightFill: "rgba(236, 71, 88,0.75)",
                highlightStroke: "rgba(236, 71, 88,1)",
                data: [
                    <?php
                    $i=0;
                    foreach($data_alarmas as $datos)
                    {
                        $i++;
                        echo $datos[2]; ?> ,
                    <?php
                        if(count($data_alarmas) == $i-1){
                            echo $datos[2];
                        }
                    }
                ?>
                ]
            }
        ]
    };

    var barOptions = {
        scaleBeginAtZero: true,
        scaleShowGridLines: true,
        scaleGridLineColor: "rgba(0,0,0,.05)",
        scaleGridLineWidth: 1,
        barShowStroke: true,
        barStrokeWidth: 1,
        barValueSpacing: 5,
        barDatasetSpacing: 1,
        responsive: true,
    }


    var ctx = document.getElementById("barChart").getContext("2d");
    var myNewChart = new Chart(ctx).Bar(barData, barOptions);
</script>

<script>
    var mapa;
    function initialize() {
        // las coordenadas

        var sitios = [
            <?php $i=0;
                foreach($data_alarmas_mapa as $datos){ $i++;
            ?> {title: <?php echo "'$datos[0]'"; ?> , pin: <?php if ($datos[5] == 1 && $datos[1] >= 1){
                $pin = 'pink-dot';
            }
            else if ($datos[5] == 1 && $datos[1] < 1) {
                $pin = 'blue-dot';
             }
             else if ($datos[5] <> 1 && $datos[1] >= 1) {
                $pin = 'red-dot';
             }
             else {
                $pin = 'green-dot';
             }
             echo "'$pin'"; ?> , lat:<?php echo $datos[2]; ?> , lng:<?php echo $datos[3]; ?> , sigla:<?php echo "'$datos[4]'"; ?>},
            <?php
                if(count($data_alarmas_mapa) == $i-1){
                ?> {title:<?php echo " '$datos[0]'";?> , pin:
                <?php if ($datos[1] > 0){
                    $pin = 'red-dot';
                }
                 else {
                    $pin = 'green-dot';
                 }
                 echo "'$pin'"; ?> , lat:<?php echo $datos[2]; ?> , lng:<?php echo $datos[3]; ?>, sigla:<?php echo "'$datos[4]'"; ?>
            <?php
            }
        }
    ?>
        ];

        var centroMapa = new google.maps.LatLng(<?php echo $lat; ?>, <?php echo $lon; ?>);

        var opcionesDeMapa = {
            zoom: 8,
            center: centroMapa,
            mapTypeId: google.maps.MapTypeId.ROADMAP //SATELITE, HYBRID, ROADMAP, TERRAIN
        };
        // instancia un nuevo objeto Map
        mapa = new google.maps.Map(document.getElementById("mapa"), opcionesDeMapa);

        // instancia unos nuevos marcadores ( chinchetas )
        var marcador, pin	;

        var url = "http:\/\/maps.google.com/mapfiles/ms/micons/";
        for( var i = 0; i < sitios.length; i++){
            pin = url + sitios[i].pin + ".png";
            marcador = new google.maps.Marker({
                position: new google.maps.LatLng(sitios[i].lat, sitios[i].lng),
                map: mapa,
                title: sitios[i].title,
                icon: pin
            });
            (function(marker,i){
                google.maps.event.addListener(marker, 'click', function(){
                    $('#sitio'+sitios[i].sigla).modal('show');
                });
            }(marcador,i));
        }
    }
    // inicializa el mapa cuando la ventana se haya cargado:
    google.maps.event.addDomListener(window, "load", initialize);

</script>

<!-- Script para exportar a Excel -->
<script>
    $("button").click(function(){
        $("#editable").table2excel({
            // exclude CSS class
            exclude: ".noExl",
            name: "Busqueda OT",
            filename: "Busqueda de OT"

        });
    });
</script>

<!-- Page-Level Scripts -->
<script>
    $(document).ready(function(){
        $('.dataTables-example').DataTable({
            dom: '<"html5buttons"B>lTfgitp',
            buttons: [
            {extend: 'copy'},
            {extend: 'csv'},
            {extend: 'excel', title: 'ExampleFile'},
            {extend: 'pdf', title: 'ExampleFile'},

            {extend: 'print',
            customize: function (win){
                $(win.document.body).addClass('white-bg');
                $(win.document.body).css('font-size', '10px');

                $(win.document.body).find('table')
                .addClass('compact')
                .css('font-size', 'inherit');
            }
        }
        ]

    });

        /* Init DataTables */
        var oTable = $('#editable').DataTable();

        /* Apply the jEditable handlers to the table */
        oTable.$('td').editable( '../example_ajax.php', {
            "callback": function( sValue, y ) {
                var aPos = oTable.fnGetPosition( this );
                oTable.fnUpdate( sValue, aPos[0], aPos[1] );
            },
            "submitdata": function ( value, settings ) {
                return {
                    "row_id": this.parentNode.getAttribute('id'),
                    "column": oTable.fnGetPosition( this )[2]
                };
            },

            "width": "90%",
            "height": "100%"
        } );


    });

    function fnClickAddRow() {
        $('#editable').dataTable().fnAddData( [
            "Custom row",
            "New row",
            "New row",
            "New row",
            "New row" ] );

    }
</script>


<!-- Page-Level Scripts -->
    <!--script>
        $(document).ready(function() {

            $('.footable').footable();
            $('.footable2').footable();

        });

    </script-->


<style>
    body.DTTT_Print {
        background: #fff;

    }
    .DTTT_Print #page-wrapper {
        margin: 0;
        background:#fff;
    }

    button.DTTT_button, div.DTTT_button, a.DTTT_button {
        border: 1px solid #e7eaec;
        background: #fff;
        color: #676a6c;
        box-shadow: none;
        padding: 6px 8px;
    }
    button.DTTT_button:hover, div.DTTT_button:hover, a.DTTT_button:hover {
        border: 1px solid #d2d2d2;
        background: #fff;
        color: #676a6c;
        box-shadow: none;
        padding: 6px 8px;
    }

    .dataTables_filter label {
        margin-right: 5px;

    }
</style>
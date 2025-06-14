<?php
$xmlUrl = 'http://localhost:8080/api/cinepolis/cartelera/obtenerCarteleras';
$xmlContent = file_get_contents($xmlUrl);
$xmlContent = ltrim($xmlContent);
$xml = simplexml_load_string($xmlContent);
if ($xml === false) {
    die("Error al cargar Carteleras");
}

$carteleraAgrupada = [];
$cinesUnicos = [];
if ($xml && $xml->cartelera) {
    foreach ($xml->cartelera as $cartelera) {
        $cineNombre = trim((string)$cartelera->cine); // Asegúrate de castear a string
        $peliculaTitulo = trim((string)$cartelera->pelicula); // Asegúrate de castear a string
        $horario = trim((string)$cartelera->horario);
        $rutaPoster = trim((string)$cartelera->ruta_poster);
        $fecha = trim((string)$cartelera->fecha);
        $idPelicula = trim((string)$cartelera->id_pelicula);
        $idCartelera = trim((string)$cartelera->id_cartelera);
        $idSala = trim((string)$cartelera->id_sala); // Asegúrate de que tu XML tenga el id_pelicula

        if (!in_array($cineNombre, $cinesUnicos)) {
            $cinesUnicos[] = $cineNombre;
        }

        if (!isset($carteleraAgrupada[$cineNombre])) {
            $carteleraAgrupada[$cineNombre] = [];
        }
        if (!isset($carteleraAgrupada[$cineNombre][$peliculaTitulo])) {
            $carteleraAgrupada[$cineNombre][$peliculaTitulo] = [
                'ruta_poster' => $rutaPoster,
                'horarios' => [],
                'fecha' => $fecha,
                'id_pelicula' => $idPelicula, 
            ];
        }
        $carteleraAgrupada[$cineNombre][$peliculaTitulo]['horarios'][] = [
            'horario_completo' => $horario,
            'id_sala' => $idSala,
            'id_cartelera' => $idCartelera
        ];
    }
    sort($cinesUnicos); 
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carteleras</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="estilos.css">
    <style>
        .horario-boton {
            display: inline-block;
            border: 1px solid #007bff;
            background-color: white;
            color: #007bff;
            padding: 5px 8px;
            margin-right: 5px;
            margin-bottom: 5px;
            border-radius: 5px;
            font-size: 0.9em;
            cursor: pointer;
        }
        .horario-boton:hover {
            background-color: #007bff;
            color: white;
        }
        .card-wrapper {
            width: 220px;
            margin-bottom: 20px;
        }
        .card-img-top {
            max-height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Carteleras</h1>
        <?php if (!empty($cinesUnicos)): ?>
            <div class="mb-3">
                <select class="form-control" id="selectCine">
                    <option value="">Seleccionar Todos los Cines </option>
                    <?php foreach ($cinesUnicos as $cine): ?>
                        <option value="<?php echo htmlspecialchars($cine); ?>"><?php echo htmlspecialchars($cine); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <a href="/Cinepolis-Front/pelicula/views/peliculasView.php" class="btn btn-primary">Ver peliculas disponibles</a>
            </div>
        <?php endif; ?>
        <div class="row">
            <?php if (!empty($carteleraAgrupada)): ?>
                <?php foreach ($carteleraAgrupada as $cineNombre => $peliculas): ?>
                    <div class="col-12 mb-3 cine-section" id="<?php echo htmlspecialchars(str_replace(' ', '-', $cineNombre)); ?>">
                        <h2 class="mb-2"><?php echo htmlspecialchars($cineNombre); ?></h2>
                        <hr class="mb-3">
                    </div>
                    <div class="d-flex flex-wrap">
                        <?php foreach ($peliculas as $peliculaTitulo => $peliculaData): ?>
                            <div class="card card-wrapper mr-3 movie-card" data-cine="<?php echo htmlspecialchars($cineNombre); ?>">
                                <?php if (!empty($peliculaData['ruta_poster'])): ?>
                                    <img src="<?php echo trim($peliculaData['ruta_poster']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($peliculaTitulo); ?>">
                                <?php else: ?>
                                    <div class="bg-light text-center p-4">Imagen no disponible</div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($peliculaTitulo); ?></h5>
                                    <p class="card-text"><strong>Horarios:</strong></p>
                                    <div>
                                        <?php foreach ($peliculaData['horarios'] as $horarioData): ?>
                                        <?php
                                            $horarioFormateado = date("H:i", strtotime($horarioData['horario_completo']));
                                        ?>
                                        <button class="horario-boton"
                                                onclick="abrirSeleccionBoletos(
                                                    '<?php echo htmlspecialchars($peliculaData['id_pelicula']); ?>',
                                                    '<?php echo htmlspecialchars(str_replace(' ', '-', $cineNombre)); ?>',
                                                    '<?php echo htmlspecialchars($peliculaTitulo); ?>',
                                                    '<?php echo htmlspecialchars($horarioData['horario_completo']); ?>',
                                                    '<?php echo htmlspecialchars($horarioData['id_sala']); ?>',        /* <-- YA PUEDE PASAR EL ID REAL */
                                                    '<?php echo htmlspecialchars($horarioData['id_cartelera']); ?>'   /* <-- YA PUEDE PASAR EL ID REAL */
                                                )">
                                            <?php echo htmlspecialchars($horarioFormateado); ?>
                                        </button>
                                    <?php endforeach; ?>

                                    <script>
                                        function abrirSeleccionBoletos(idPelicula, idCine, tituloPelicula, horario, idSala, idCartelera) { // <--- Nuevos parámetros
                                            var url = '/Cinepolis-Front/boletos/mostrarBoletos.php?' +
                                                    'id_sala=' + encodeURIComponent(idSala) + '&' +             // <--- USA LOS PARÁMETROS REALES
                                                    'id_cartelera=' + encodeURIComponent(idCartelera) + '&' +   // <--- USA LOS PARÁMETROS REALES
                                                    'idPelicula=' + encodeURIComponent(idPelicula) + '&' +
                                                    'cine=' + encodeURIComponent(idCine) + '&' +
                                                    'pelicula=' + encodeURIComponent(tituloPelicula) + '&' +
                                                    'horario=' + encodeURIComponent(horario);
                                            window.open(url, '_blank', 'width=800,height=600');
                                        }
                                    </script>
                                    </div>
                                    <p class="card-text mt-2"><strong>Fecha:</strong> <?php echo htmlspecialchars($peliculaData['fecha']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">No se encontraron carteleras.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#selectCine').change(function() {
                var selectedCine = $(this).val();
                $('.cine-section').hide();
                $('.movie-card').hide();

                if (selectedCine === '') {
                    $('.cine-section').show();
                    $('.movie-card').show();
                } else {
                    var cineId = selectedCine.replace(/ /g, '-');
                    $('#' + cineId).show();
                    $('.movie-card[data-cine="' + selectedCine + '"]').show();
                }
            });
        });
    </script>
</body>
</html>
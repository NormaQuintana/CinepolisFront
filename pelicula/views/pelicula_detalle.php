<?php
$apiUrlBase = 'http://localhost:8080/api/cinepolis/peliculas'; 

$peliculaDetalle = null;
$mensajeError = null;

if (isset($_GET['id'])) {
    $peliculaId = $_GET['id'];
    $apiUrl = $apiUrlBase . '/obtenerPelicula/' . $peliculaId; 

    $response = @file_get_contents($apiUrl);

    if ($response === false) {
        $mensajeError = "Error al obtener los detalles de la película desde la API.";
    } else {
        $xml = simplexml_load_string($response);
        if ($xml === false) {
            $mensajeError = "Error al cargar la respuesta XML de la API.";
        } else {
            $peliculaDetalle = $xml; // Asignamos el SimpleXMLElement directamente
        }
    }
} else {
    $mensajeError = "ID de película no proporcionado.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($peliculaDetalle ? $peliculaDetalle->titulo : 'Detalle de Película'); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <style>
        .movie-detail-container {
            margin-top: 30px;
        }
        .movie-card {
            margin-bottom: 20px;
        }
        .movie-poster {
            max-width: 300px;
            height: auto;
            margin-bottom: 20px;
            align-self: center;
        }
    </style>
</head>
<body>
    <div class="container movie-detail-container">
        <h1 class="text-center mb-4"><?php echo htmlspecialchars($peliculaDetalle ? $peliculaDetalle->titulo : 'Detalle de Película'); ?></h1>

        <?php if ($peliculaDetalle): ?>
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="card movie-card">
                        <?php if (!empty($peliculaDetalle->ruta_poster)): ?>
                            <img src="<?php echo trim($peliculaDetalle->ruta_poster); ?>" class="card-img-top movie-poster" alt="<?php echo htmlspecialchars($peliculaDetalle->titulo); ?>">
                        <?php else: ?>
                            <div class="bg-light text-center p-4">Imagen no disponible</div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($peliculaDetalle->titulo); ?></h5>
                            <p class="card-text"><small class="text-muted">Director: <?php echo htmlspecialchars($peliculaDetalle->director); ?></small></p>
                            <p class="card-text">Género: <?php echo htmlspecialchars($peliculaDetalle->genero); ?></p>
                            <p class="card-text">Clasificación: <?php echo htmlspecialchars($peliculaDetalle->clasificacion); ?></p>
                            <p class="card-text"><strong>Sinopsis:</strong> <?php echo htmlspecialchars($peliculaDetalle->sinopsis); ?></p>
                            <p class="card-text"><small class="text-muted">Duración: <?php echo htmlspecialchars($peliculaDetalle->duracion); ?> minutos</small></p>
                            <p class="card-text"><small class="text-muted">Reparto: <?php echo htmlspecialchars($peliculaDetalle->reparto); ?></small></p>
                            <a href="javascript:history.back()" class="btn btn-secondary mt-3">Regresar</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $mensajeError; ?>
            </div>
            <a href="javascript:history.back()" class="btn btn-secondary">Regresar</a>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
</body>
</html>
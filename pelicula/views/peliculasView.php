<?php
$xmlUrl = 'http://localhost:8080/api/cinepolis/peliculas/obtenerPeliculas';
$xmlContent = file_get_contents($xmlUrl);
$xml = simplexml_load_string($xmlContent);
if ($xml === false) {
    die("Error al cargar Peliculas");
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cartelera Cinepolis Coquette</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <style>
        .movie-card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1 class="text-center mb-4">Nuestra Cartelera</h1>
        <div class="row">
            <?php if ($xml && $xml->pelicula): ?>
                <?php foreach ($xml->pelicula as $pelicula): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card movie-card">
                            <?php if (!empty($pelicula->ruta_poster)): ?>
                                <img src="<?php echo trim($pelicula->ruta_poster); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($pelicula->titulo); ?>">
                            <?php else: ?>
                                <div class="bg-light text-center p-4">Imagen no disponible</div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($pelicula->titulo); ?></h5>
                                <p class="card-text"><small class="text-muted">Director: <?php echo htmlspecialchars($pelicula->director); ?></small></p>
                                <p class="card-text">Género: <?php echo htmlspecialchars($pelicula->genero); ?></p>
                                <p class="card-text">Clasificación: <?php echo htmlspecialchars($pelicula->clasificacion); ?></p>
                                <p class="card-text"><?php echo htmlspecialchars(substr($pelicula->sinopsis, 0, 100)); ?>...</p>
                                <p class="card-text"><small class="text-muted">Duración: <?php echo htmlspecialchars($pelicula->duracion); ?> minutos</small></p>
                                <p class="card-text"><small class="text-muted">Reparto: <?php echo htmlspecialchars($pelicula->reparto); ?></small></p>
                                <a href="#" class="btn btn-primary">Ver más</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">No se encontraron películas.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
</body>
</html>

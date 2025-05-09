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

        <div class="mb-3">
            <input type="text" id="searchInput" class="form-control" placeholder="Buscar película por nombre">
        </div>

        <div class="row" id="moviesContainer">
            <?php if ($xml && $xml->pelicula): ?>
                <?php foreach ($xml->pelicula as $pelicula): ?>
                    <div class="col-md-4 mb-4 movie-item">
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
                                <a href="pelicula_detalle.php?id=<?php echo htmlspecialchars($pelicula->id_pelicula); ?>" class="btn btn-primary">Ver más</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">No se encontraron películas.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#searchInput').on('input', function() {
                var searchText = $(this).val().toLowerCase();
                $('.movie-item').each(function() {
                    var movieTitle = $(this).find('.card-title').text().toLowerCase();
                    if (movieTitle.includes(searchText)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        });
    </script>
</body>
</html>
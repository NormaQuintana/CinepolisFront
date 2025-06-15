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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        .movie-card {
            margin-bottom: 20px;
        }

        .btn-group-custom {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .btn-group-custom .btn {
            flex: 1;
            margin-right: 5px;
        }

        .btn-group-custom .btn:last-child {
            margin-right: 0;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1 class="text-center mb-4">Películas Disponibles</h1>

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

                                <div class="btn-group-custom">
                                    <a href="pelicula_detalle.php?id=<?php echo htmlspecialchars($pelicula->id_pelicula); ?>" class="btn btn-primary btn-sm">Ver más</a>
                                    <a href="editar_pelicula.php?id=<?php echo htmlspecialchars($pelicula->id_pelicula); ?>" class="btn btn-warning btn-sm">Editar</a>

                                    <button class="btn btn-danger btn-sm"
                                        onclick="borrarPelicula('<?php echo $pelicula->id_pelicula; ?>', this)">
                                        Borrar
                                    </button>



                                </div>
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

        function borrarPelicula(id, button) {
            if (!confirm('¿Estás seguro de que deseas borrar esta película?')) {
                return;
            }

            fetch(`http://localhost:8080/api/cinepolis/peliculas/eliminarPelicula/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/xml'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('No se pudo eliminar la película');
                    }
                    alert('Película eliminada correctamente');

                    // Opcional: Eliminar el card visualmente sin recargar
                    const card = button.closest('.movie-item');
                    if (card) {
                        card.remove();
                    }
                })
                .catch(error => {
                    console.error('Error al borrar la película:', error);
                    alert('Error al borrar la película');
                });
        }
    </script>
</body>

</html>
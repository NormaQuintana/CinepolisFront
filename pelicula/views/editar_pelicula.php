<?php
// obtener el id por GET
if (!isset($_GET['id'])) {
    die("No se especificó la película");
}
$id = $_GET['id'];

// Función auxiliar para obtener datos XML de una URL
function getXmlDataFromUrl($url) {
    $xmlContent = file_get_contents($url);
    if ($xmlContent === false) {
        return false;
    }
    return simplexml_load_string($xmlContent);
}

// --- Obtener todos los géneros ---
$urlGeneros = "http://localhost:8080/api/cinepolis/generos/obtenerGeneros";
$generosXml = getXmlDataFromUrl($urlGeneros);
$generos = [];
if ($generosXml !== false && isset($generosXml->genero)) {
    foreach ($generosXml->genero as $genero) {
        // CORREGIDO: Acceder a 'nombre' en lugar de 'nombre_genero'
        $generos[intval($genero->id_genero)] = (string)$genero->nombre; 
    }
} else {
    $generos = [
        1 => "Acción (Default)",
        2 => "Animación (Default)",
        3 => "Comedia (Default)",
        4 => "Drama (Default)",
    ];
    error_log("Error: No se pudieron obtener los géneros desde la API: " . $urlGeneros);
}

// --- Obtener todas las clasificaciones ---
$urlClasificaciones = "http://localhost:8080/api/cinepolis/clasificaciones/obtenerClasificaciones";
$clasificacionesXml = getXmlDataFromUrl($urlClasificaciones);
$clasificaciones = [];
if ($clasificacionesXml !== false && isset($clasificacionesXml->clasificacion)) {
    foreach ($clasificacionesXml->clasificacion as $clasificacion) {
        // CORREGIDO: Acceder a 'nombre' en lugar de 'tipo_clasificacion'
        $clasificaciones[intval($clasificacion->id_clasificacion)] = (string)$clasificacion->nombre;
    }
} else {
    $clasificaciones = [
        1 => "A (Default)",
        2 => "B (Default)",
        3 => "C (Default)",
    ];
    error_log("Error: No se pudieron obtener las clasificaciones desde la API: " . $urlClasificaciones);
}


// Obtener datos actuales de la película para mostrar en el formulario
$urlGetPelicula = "http://localhost:8080/api/cinepolis/peliculas/obtenerPelicula/" . $id;
$pelicula = getXmlDataFromUrl($urlGetPelicula);
if ($pelicula === false) {
    die("Error al cargar los datos de la película desde la API.");
}

$id_genero_actual = intval($pelicula->id_genero ?? 1);
$id_clasificacion_actual = intval($pelicula->id_clasificacion ?? 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos del formulario
    $titulo = $_POST['titulo'] ?? '';
    $director = $_POST['director'] ?? '';
    $id_genero = $_POST['id_genero'] ?? '';
    $id_clasificacion = $_POST['id_clasificacion'] ?? '';
    $sinopsis = $_POST['sinopsis'] ?? '';
    $duracion = $_POST['duracion'] ?? '';
    $reparto = $_POST['reparto'] ?? '';
    $ruta_poster = $_POST['ruta_poster'] ?? '';

    // Actualizar variables para mantener seleccionadas en caso de error o recarga
    $id_genero_actual = intval($id_genero);
    $id_clasificacion_actual = intval($id_clasificacion);

    // Construir XML para enviar al API
    $xmlData = "<pelicula>
        <id_genero>" . intval($id_genero) . "</id_genero>
        <id_clasificacion>" . intval($id_clasificacion) . "</id_clasificacion>
        <titulo>" . htmlspecialchars($titulo) . "</titulo>
        <director>" . htmlspecialchars($director) . "</director>
        <sinopsis>" . htmlspecialchars($sinopsis) . "</sinopsis>
        <duracion>" . htmlspecialchars($duracion) . "</duracion>
        <reparto>" . htmlspecialchars($reparto) . "</reparto>
        <ruta_poster>" . htmlspecialchars($ruta_poster) . "</ruta_poster>
    </pelicula>";

    // URL para actualizar película
    $urlPut = "http://localhost:8080/api/cinepolis/peliculas/editarPelicula/" . $id;

    // Hacer la petición PUT con cURL
    $ch = curl_init($urlPut);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/xml',
        'Content-Length: ' . strlen($xmlData)
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200 || $httpCode == 204) {
        header("Location: peliculasView.php");
        exit();
    } else {
        $mensaje = "Error al actualizar la película. Código HTTP: $httpCode. Respuesta: $response";
    }

    // Actualizar el objeto $pelicula con los nuevos datos en caso de error para que el formulario los muestre
    $pelicula->titulo = $titulo;
    $pelicula->director = $director;
    $pelicula->sinopsis = $sinopsis;
    $pelicula->duracion = $duracion;
    $pelicula->reparto = $reparto;
    $pelicula->ruta_poster = $ruta_poster;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Editar Película</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
</head>

<body>
    <div class="container">
        <h1 class="mt-4 mb-4">Editar Película</h1>

        <?php if (isset($mensaje)): ?>
            <div class="alert <?php echo strpos($mensaje, 'Error') === false ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="titulo">Título:</label>
                <input type="text" name="titulo" id="titulo" class="form-control" value="<?php echo htmlspecialchars($pelicula->titulo); ?>" required />
            </div>
            <div class="form-group">
                <label for="director">Director:</label>
                <input type="text" name="director" id="director" class="form-control" value="<?php echo htmlspecialchars($pelicula->director); ?>" />
            </div>

            <div class="form-group">
                <label for="id_genero">Género:</label>
                <select name="id_genero" id="id_genero" class="form-control" required>
                    <option value="">-- Selecciona género --</option>
                    <?php foreach ($generos as $idG => $nombreG): ?>
                        <option value="<?= $idG ?>" <?= $idG == $id_genero_actual ? 'selected' : '' ?>>
                            <?= htmlspecialchars($nombreG) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="id_clasificacion" class="mt-3">Clasificación:</label>
                <select name="id_clasificacion" id="id_clasificacion" class="form-control" required>
                    <option value="">-- Selecciona clasificación --</option>
                    <?php foreach ($clasificaciones as $idC => $nombreC): ?>
                        <option value="<?= $idC ?>" <?= $idC == $id_clasificacion_actual ? 'selected' : '' ?>>
                            <?= htmlspecialchars($nombreC) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="sinopsis">Sinopsis:</label>
                <textarea name="sinopsis" id="sinopsis" class="form-control"><?php echo htmlspecialchars($pelicula->sinopsis); ?></textarea>
            </div>
            <div class="form-group">
                <label for="duracion">Duración (minutos):</label>
                <input type="number" name="duracion" id="duracion" class="form-control" value="<?php echo htmlspecialchars($pelicula->duracion); ?>" />
            </div>
            <div class="form-group">
                <label for="reparto">Reparto:</label>
                <input type="text" name="reparto" id="reparto" class="form-control" value="<?php echo htmlspecialchars($pelicula->reparto); ?>" />
            </div>
            <div class="form-group">
                <label for="ruta_poster">URL Poster:</label>
                <input type="text" name="ruta_poster" id="ruta_poster" class="form-control" value="<?php echo htmlspecialchars($pelicula->ruta_poster); ?>" />
            </div>

            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>
    </div>
</body>

</html>
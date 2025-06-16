<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Película</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
      
        pre {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 5px;
            white-space: pre-wrap; 
            word-wrap: break-word;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Agregar Nueva Película</h2>

    <?php
    // --- Lógica para obtener géneros ---
    $generos = [];
    // Usamos @ para suprimir warnings directos si la URL no responde (útil en desarrollo)
    $generosData = @file_get_contents("http://localhost:8080/api/cinepolis/generos/obtenerGeneros");

    if ($generosData !== false) {
        // Intentamos cargar el string como XML
        $xmlGeneros = simplexml_load_string($generosData);
        if ($xmlGeneros !== false) { // Verificamos si la carga fue exitosa
            foreach ($xmlGeneros->genero as $item) {
                $generos[] = [
                    'id_genero' => (string) $item->id_genero,
                    'nombre' => (string) $item->nombre
                ];
            }
        } else {
            // Manejo de error si el XML de géneros es inválido
            echo '<div class="alert alert-danger" role="alert">Error: La API de géneros devolvió un XML inválido.</div>';
            error_log("Error XML inválido de API de géneros: " . $generosData);
        }
    } else {
        // Manejo de error si no se pudo acceder a la API de géneros
        echo '<div class="alert alert-warning" role="alert">Advertencia: No se pudieron cargar los géneros. Verifique la disponibilidad de la API.</div>';
        error_log("Error al obtener datos de API de géneros.");
    }

    // --- Lógica para obtener clasificaciones ---
    $clasificaciones = [];
    $clasificacionesData = @file_get_contents("http://localhost:8080/api/cinepolis/clasificaciones/obtenerClasificaciones");

    if ($clasificacionesData !== false) {
        // Intentamos cargar el string como XML
        $xmlClasificaciones = simplexml_load_string($clasificacionesData);
        if ($xmlClasificaciones !== false) { // Verificamos si la carga fue exitosa
            foreach ($xmlClasificaciones->clasificacion as $item) {
                $clasificaciones[] = [
                    'id_clasificacion' => (string) $item->id_clasificacion,
                    'nombre' => (string) $item->nombre
                ];
            }
        } else {
            // Manejo de error si el XML de clasificaciones es inválido
            echo '<div class="alert alert-danger" role="alert">Error: La API de clasificaciones devolvió un XML inválido.</div>';
            error_log("Error XML inválido de API de clasificaciones: " . $clasificacionesData);
        }
    } else {
        // Manejo de error si no se pudo acceder a la API de clasificaciones
        echo '<div class="alert alert-warning" role="alert">Advertencia: No se pudieron cargar las clasificaciones. Verifique la disponibilidad de la API.</div>';
        error_log("Error al obtener datos de API de clasificaciones.");
    }

    // --- Lógica para el envío del formulario (POST) ---
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Creamos un objeto SimpleXMLElement con un elemento raíz para la película
        $xmlData = new SimpleXMLElement('<pelicula/>');

        // Agregamos cada campo del formulario como un nodo hijo en el XML
        $xmlData->addChild('id_genero', htmlspecialchars($_POST['id_genero']));
        $xmlData->addChild('id_clasificacion', htmlspecialchars($_POST['id_clasificacion']));
        $xmlData->addChild('titulo', htmlspecialchars($_POST['pelicula']));
        $xmlData->addChild('director', htmlspecialchars($_POST['director']));
        $xmlData->addChild('sinopsis', htmlspecialchars($_POST['sinopsis']));
        $xmlData->addChild('duracion', htmlspecialchars($_POST['duracion']));
        $xmlData->addChild('reparto', htmlspecialchars($_POST['reparto']));
        $xmlData->addChild('ruta_poster', htmlspecialchars($_POST['ruta_poster']));

        // Convertimos el objeto SimpleXMLElement a una cadena XML
        $xmlString = $xmlData->asXML();

        // Inicializamos cURL para enviar la solicitud POST al backend
        $ch = curl_init('http://localhost:8080/api/cinepolis/peliculas/crearPelicula');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); // Método HTTP POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlString); // El cuerpo de la solicitud es el string XML
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Queremos que cURL devuelva la respuesta como string
        // Establecemos los encabezados HTTP, ¡es crucial especificar Content-Type: application/xml!
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/xml',
            'Content-Length: ' . strlen($xmlString) // Se recomienda enviar el Content-Length
        ]);

        // Ejecutamos la solicitud cURL
        $response = curl_exec($ch);
        // Capturamos cualquier error de cURL
        $curl_error = curl_error($ch);
        curl_close($ch); // Cerramos la sesión cURL

        // Mostramos la respuesta del servidor o cualquier error de cURL
        //echo "<h3 class='mt-4'>Resultado del Envío:</h3>";
        if ($curl_error) {
            echo '<div class="alert alert-danger" role="alert">Error al conectar con la API: ' . htmlspecialchars($curl_error) . '</div>';
        } else {
           // echo "<p>La solicitud POST fue enviada con éxito.</p>";
         //   echo "<pre>Respuesta del servidor:\n" . htmlspecialchars($response) . "</pre>";
        }
    }
    ?>

    <form method="POST">
        <div class="form-group">
            <label for="pelicula">Título de la Película</label>
            <input type="text" class="form-control" id="pelicula" name="pelicula" required>
        </div>
        <div class="form-group">
            <label for="id_genero">Género</label>
            <select class="form-control" id="id_genero" name="id_genero" required>
                <option value="">Seleccione un género</option>
                <?php foreach ($generos as $genero): ?>
                    <option value="<?= htmlspecialchars($genero['id_genero']) ?>"><?= htmlspecialchars($genero['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="id_clasificacion">Clasificación</label>
            <select class="form-control" id="id_clasificacion" name="id_clasificacion" required>
                <option value="">Seleccione una clasificación</option>
                <?php foreach ($clasificaciones as $clasificacion): ?>
                    <option value="<?= htmlspecialchars($clasificacion['id_clasificacion']) ?>"><?= htmlspecialchars($clasificacion['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="director">Director</label>
            <input type="text" class="form-control" id="director" name="director" required>
        </div>

        <div class="form-group">
            <label for="sinopsis">Sinopsis</label>
            <textarea class="form-control" id="sinopsis" name="sinopsis" rows="3" required></textarea>
            </div>

<div class="form-group">
    <label for="duracion">Duración (minutos)</label>
    <input type="number" class="form-control" id="duracion" name="duracion" required>
</div>

<div class="form-group">
    <label for="reparto">Reparto</label>
    <input type="text" class="form-control" id="reparto" name="reparto" required>
</div>

<div class="form-group">
    <label for="ruta_poster">URL del Póster</label>
    <input type="url" class="form-control" id="ruta_poster" name="ruta_poster" required>
</div>

<button type="submit" class="btn btn-primary">Agregar Película</button>
<a href="../../pelicula/views/peliculasView.php" class="btn btn-secondary">Volver</a>
</form>
</div>
</body>
</html>
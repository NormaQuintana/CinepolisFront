<?php
require_once (__DIR__ . '/../models/boletoModel.php');
require_once (__DIR__ . '/../../pelicula/models/peliculaModel.php'); // Esta ruta ya la habías corregido previamente, está bien.

class BoletoController{
    private $boletoModel;
    private $peliculaModel; // Ensure this is correctly initialized if used elsewhere

    public function __construct(){
        $this->boletoModel = new BoletoModel();
        $this->peliculaModel = new PeliculaModel(); // Assuming this is correct
    }

   public function apartarBoletos() {
        // La respuesta de este controlador al frontend será JSON
        header('Content-Type: application/json'); 

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rawXmlData = file_get_contents("php://input"); // <-- OBTENER EL XML EN CRUDO DEL NAVEGADOR

            // --- DEPURACIÓN: Log del XML recibido del frontend ---
            error_log("DEBUG (BoletoController Front): XML recibido del frontend: " . $rawXmlData);

            // Intentar parsear el XML recibido del frontend
            libxml_use_internal_errors(true); // Habilita manejo de errores para simplexml
            $xmlRequest = simplexml_load_string($rawXmlData);
            
            if ($xmlRequest === false) {
                $errors = libxml_get_errors();
                $error_message = "Error al parsear XML del frontend: ";
                foreach ($errors as $error) {
                    $error_message .= trim($error->message) . "; ";
                }
                libxml_clear_errors(); // Limpia los errores de libxml
                http_response_code(400);
                echo json_encode(['error' => 'XML mal formado enviado por el frontend. Detalles: ' . $error_message, 'raw_xml_received' => $rawXmlData]);
                return;
            }

            // --- DEPURACIÓN: Log de los datos extraídos del XML del frontend ---
            error_log("DEBUG (BoletoController Front): Datos extraídos del XML (del frontend):");
            error_log("  id_cartelera: " . (string)$xmlRequest->id_cartelera);
            error_log("  id_sala: " . (string)$xmlRequest->id_sala);
            error_log("  cantidad: " . (string)$xmlRequest->cantidad);
            error_log("  metodo_pago: " . (string)$xmlRequest->metodo_pago);
            error_log("  precio_total: " . (string)$xmlRequest->precio_total);
            if (isset($xmlRequest->num_asientos->asiento[0])) {
                error_log("  primer_asiento_recibido: " . (string)$xmlRequest->num_asientos->asiento[0]);
            } else {
                error_log("  num_asientos no encontrado o vacío.");
            }
            // ----------------------------------------------------

            // Extracción y cast de los datos del XML
            $idCartelera = (int)$xmlRequest->id_cartelera;
            $idSala = (int)$xmlRequest->id_sala;
            $cantidad = (int)$xmlRequest->cantidad;
            $metodoPago = (string)$xmlRequest->metodo_pago;
            $precioTotal = (float)$xmlRequest->precio_total;
            
            // Los asientos seleccionados son un array de <asiento> dentro de <num_asientos>
            $selectedSeats = [];
            if (isset($xmlRequest->num_asientos) && $xmlRequest->num_asientos->asiento) {
                foreach ($xmlRequest->num_asientos->asiento as $asientoNode) {
                    $selectedSeats[] = (string)$asientoNode; // Obtiene el valor del asiento (ej. "B2")
                }
            }

            // Validar que los datos estén presentes y sean válidos
            // Aquí usamos los valores parseados y casteados
            if (
                !$idCartelera || 
                !$idSala || 
                $cantidad <= 0 || 
                empty($metodoPago) || 
                $precioTotal <= 0 || 
                empty($selectedSeats) // Aseguramos que se haya seleccionado al menos un asiento
            ) {
                http_response_code(400);
                echo json_encode([
                    'error' => 'Faltan parámetros requeridos o son inválidos en la solicitud (validación interna).',
                    'debug_info' => [ // Añadir datos recibidos para depuración
                        'idCartelera' => $idCartelera,
                        'idSala' => $idSala,
                        'cantidad' => $cantidad,
                        'metodoPago' => $metodoPago,
                        'precioTotal' => $precioTotal,
                        'selectedSeatsCount' => count($selectedSeats),
                        'rawXml' => $rawXmlData // También puedes incluir el XML original para una depuración completa
                    ]
                ]);
                return;
            }

            try {
                // Llamada al modelo para apartar boletos (el modelo es quien habla con la API XML real)
                // Se eliminó $idUsuario de los argumentos del modelo
                $responseXml = $this->boletoModel->apartarBoletos(
                    $idCartelera,
                    $idSala,
                    $cantidad,
                    $selectedSeats, // Pasar el array de nombres de asientos
                    $metodoPago,
                    $precioTotal
                );

                // --- DEPURACIÓN: Log de la respuesta XML de tu boletoModel ---
                error_log("DEBUG (BoletoController Front): Respuesta XML del BoletoModel (API Externa): " . $responseXml);

                // Procesamiento de la respuesta XML de tu boletoModel (API externa)
                libxml_use_internal_errors(true); 
                $xmlResponse = simplexml_load_string($responseXml);

                if ($xmlResponse === false) {
                    $errors = [];
                    foreach (libxml_get_errors() as $error) {
                        $errors[] = $error->message;
                    }
                    libxml_clear_errors();
                    http_response_code(500); 
                    echo json_encode(['error' => 'Error al procesar la respuesta XML de la API externa (mal formado). Detalles: ' . implode(', ', $errors), 'raw_response_from_api' => $responseXml]);
                    return;
                }

                if (isset($xmlResponse->message)) {
                    http_response_code(200); 
                    echo json_encode([
                        'success' => true,
                        'message' => (string)$xmlResponse->message,
                        'boleto_id' => (string)$xmlResponse->boleto_id ?? null 
                    ]);
                } elseif (isset($xmlResponse->error)) {
                    http_response_code(400); // Usar 400 Bad Request si el error viene de la API externa
                    echo json_encode(['success' => false, 'error' => (string)$xmlResponse->error]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Respuesta inesperada de la API de boletos externa. No contiene message ni error.', 'raw_response_from_api' => $responseXml]);
                }

            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Error de conexión o procesamiento en el controlador: ' . $e->getMessage()]);
            }

        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['error' => 'Método no permitido. Solo se acepta POST.']);
        }
    }

    /**
     * Displays the seat selection view, fetching initial seat status.
     * This method will be called when the user navigates to the seat selection page.
     */
    public function mostrarSeleccionBoletos() {
        // Asume que id_sala y id_cartelera se pasan a través de parámetros GET
        $idSala = isset($_GET['id_sala']) ? (int)$_GET['id_sala'] : 0;
        $idCartelera = isset($_GET['id_cartelera']) ? (int)$_GET['id_cartelera'] : 0;

        $asientosData = [];
        if ($idSala > 0 && $idCartelera > 0) {
            $xmlAsientos = $this->boletoModel->obtenerEstadoAsientos($idSala, $idCartelera);

            // --- LÍNEAS DE DEBUGGING TEMPORALES: COLOCA ESTO AQUÍ ---
            echo '<pre>DEBUG: Valor de $xmlAsientos: ';
            var_dump($xmlAsientos);
            echo '</pre>';
            if ($xmlAsientos instanceof SimpleXMLElement) {
                 echo '<pre>DEBUG: Número de elementos <asiento> encontrados: ' . $xmlAsientos->asiento->count() . '</pre>';
            } else {
                echo '<pre>DEBUG: $xmlAsientos NO es una instancia de SimpleXMLElement o es nulo/falso.</pre>';
            }
            // --- FIN DE LÍNEAS DE DEBUGGING TEMPORALES ---

            if ($xmlAsientos instanceof SimpleXMLElement) { // Asegúrate de que es un objeto SimpleXMLElement válido
                // Iterar sobre los nodos 'asiento'
                foreach ($xmlAsientos->asiento as $asiento) {
                    $asientosData[] = [
                        'id_asiento' => (string)$asiento->id_asiento,
                        'fila' => (string)$asiento->fila,
                        'numero' => (string)$asiento->numero,
                        'tipo' => (string)$asiento->tipo,
                        'nombre_asiento' => (string)$asiento->nombre_asiento,
                        'estado' => (string)$asiento->estado
                    ];
                }
            } else {
                error_log("No se pudo obtener el estado de los asientos o el XML está mal formado para Sala: $idSala, Cartelera: $idCartelera");
            }
        } else {
            error_log("Falta id_sala o id_cartelera para la vista de selección de asientos.");
        }

        // --- LÍNEAS DE DEBUGGING TEMPORALES: Y ESTO AQUÍ TAMBIÉN ---
        echo '<pre>DEBUG: Contenido final de $asientosData antes de pasar a la vista: ';
        var_dump($asientosData);
        echo '</pre>';
        // --- FIN DE LÍNEAS DE DEBUGGING TEMPORALES ---

        // Prepara todos los datos necesarios
        $viewData = [ // Renombrado a $viewData para evitar confusión si $data se usaba localmente
            'idPelicula' => isset($_GET['idPelicula']) ? htmlspecialchars($_GET['idPelicula']) : '',
            'cine' => isset($_GET['cine']) ? htmlspecialchars(str_replace('-', ' ', $_GET['cine'])) : '',
            'pelicula' => isset($_GET['pelicula']) ? htmlspecialchars($_GET['pelicula']) : '',
            'horario' => isset($_GET['horario']) ? htmlspecialchars(date("H:i", strtotime($_GET['horario']))) : '',
            'idSala' => $idSala,
            'idCartelera' => $idCartelera,
            'asientos_sala' => $asientosData
        ];

        // Extrae el array $viewData en variables individuales para la vista.
        extract($viewData);

        require_once (__DIR__ . '/../views/seleccionarBoletosView.php');
    }
}
?>
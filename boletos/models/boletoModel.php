<?php
// BoletoModel.php (en el lado del Front)

class BoletoModel {
    // Asegúrate de que esta URL sea la correcta para tu API
    private $apiBoletos = 'http://localhost:8080/api/cinepolis/boletos/'; 

    public function sendRequest($method, $url, $data = null) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/xml']);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $response = curl_exec($ch);
        if ($response === false) {
            // Log the error for debugging, but don't expose curl_error directly to the user
            error_log("cURL Error: " . curl_error($ch) . " URL: " . $url . " Data: " . ($data ?? ''));
            throw new Exception("Error al conectar con el servidor de la API de boletos.");
        }
        curl_close($ch); // Close the cURL handle
        return $response;
    }

    // MÉTODO APARTARBOLETOS - MODIFICADO
    // Se eliminó $idUsuario de los parámetros
    public function apartarBoletos($idCartelera, $idSala, $cantidad, $numAsientos, $metodoPago, $precioTotal) {
        $url = $this->apiBoletos . 'apartarBoletos';

        $xmlData = "<boleto>";
        $xmlData .= "<id_cartelera>" . $idCartelera . "</id_cartelera>";
        // YA NO SE NECESITA id_usuario: ELIMINAR O COMENTAR LA SIGUIENTE LÍNEA
        // $xmlData .= "<id_usuario>" . $idUsuario . "</id_usuario>"; 
        $xmlData .= "<id_sala>" . $idSala . "</id_sala>";
        $xmlData .= "<cantidad>" . $cantidad . "</cantidad>";
        $xmlData .= "<metodo_pago>" . htmlspecialchars($metodoPago) . "</metodo_pago>";
        $xmlData .= "<precio_total>" . $precioTotal . "</precio_total>";
        if (!empty($numAsientos)) {
            $xmlData .= "<num_asientos>";
            foreach ($numAsientos as $asiento) {
                // Ensure seat names (e.g., 'A1') are sent as strings
                $xmlData .= "<asiento>" . htmlspecialchars($asiento) . "</asiento>";
            }
            $xmlData .= "</num_asientos>";
        }
        $xmlData .= "</boleto>";

        $response = $this->sendRequest('POST', $url, $xmlData);
        return $response;
    }

    // Este método no necesita cambios ya que no usa id_usuario
    public function obtenerEstadoAsientos($idSala, $idCartelera) {
        $url = $this->apiBoletos . 'sala/' . $idSala . '/cartelera/' . $idCartelera . '/asientos';

        try {
            error_log("DEBUG (Model): Intentando GET a URL: " . $url); // <--- Esta es importante
            $responseXml = $this->sendRequest('GET', $url);
            error_log("DEBUG (Model): Raw response de la API: " . $responseXml); // <--- ESTA ES LA MÁS CRÍTICA AHORA

            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($responseXml);
            if ($xml === false) {
                $errors = [];
                foreach (libxml_get_errors() as $error) {
                    $errors[] = $error->message;
                }
                libxml_clear_errors();
                error_log("Error parsing XML response from API: " . implode(", ", $errors) . " Raw XML: " . $responseXml); // <--- Y esta también
                throw new Exception("Error al procesar la respuesta de la API de asientos.");
            }
            return $xml;
        } catch (Exception $e) {
            error_log("Error capturado en obtenerEstadoAsientos: " . $e->getMessage());
            return false;
        }
    }
}
?>
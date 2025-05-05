<?php
class PeliculaModel {
    private $apiPeliculas = 'http://localhost:8080/api/cinepolis/peliculas/';

    public function sendRequest($method, $url, $data = null) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/xml']);
        if ($xmlData) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
        }
        $response = curl_exec($ch);
        if ($response === false) {
            die("Error: " . curl_error($ch));
        }
        return $response;
    }

    public function getPeliculas() {
        $xmlContent = file_get_contents(this->apiUrl . "obtenerPeliculas");
        $xml = simplexml_load_string($xmlContent);
        if($xml === false) {
            die("Error al cargar Peliculas");
        }
        return $xml;
    }
}

?>
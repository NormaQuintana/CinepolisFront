<?php
class CarteleraModel {
    private $apiUrl = 'http://localhost:8080/api/cinepolis/cartelera/';

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
            die("Error: " . curl_error($ch));
        }
        return $response;
    }

    public function getCartelera() {
        $xmlContent = file_get_contents($this->apiUrl . "obtenerCartelera");
        $xml = simplexml_load_string($xmlContent);
        if ($xml === false) {
            die("Error al cargar Cartelera");
        }
        return $xml;
    }
}
?>
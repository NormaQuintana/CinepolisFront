<?php
class PeliculaModel
{
    private $apiPeliculas = 'http://localhost:8080/api/cinepolis/peliculas/';

    public function sendRequest($method, $url, $data = null)
    {
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

        curl_close($ch);
        return $response;
    }

    public function getPeliculas()
    {
        $url = $this->apiPeliculas . "obtenerPeliculas";
        $xmlContent = $this->sendRequest('GET', $url);

        $xml = simplexml_load_string($xmlContent);
        if ($xml === false) {
            die("Error al cargar Peliculas");
        }

        return $xml;
    }

    public function getPeliculaPorId($id)
    {
        $url = $this->apiPeliculas . "obtenerPelicula/" . $id;
        $xmlContent = $this->sendRequest('GET', $url);

        $xml = simplexml_load_string($xmlContent);
        if ($xml === false) {
            die("Error al cargar la película con ID $id");
        }

        return $xml;
    }

    public function editarPelicula($id, $xmlData)
    {
        $url = $this->apiPeliculas . "editarPelicula/" . $id;
        $response = $this->sendRequest('PUT', $url, $xmlData);
        // Puedes verificar que la respuesta sea exitosa (según tu API)
        return ($response !== false);
    }

    public function borrarPelicula($id)
    {
        $url = $this->apiPeliculas . "eliminarPelicula/" . $id;
        $response = $this->sendRequest('DELETE', $url);
        return ($response !== false);
    }
}

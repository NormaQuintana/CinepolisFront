<?php
require_once (__DIR__ . '/../models/peliculaModel.php');

class PeliculaController {
    public function __construct() {
        $this->peliculaModel = new PeliculaModel();
    }

    public function getPeliculas() {
        $peliculas = $this->peliculaModel->getPeliculas();
        if ($peliculas === false) {
            die("Error al cargar Peliculas");
        }
        return $peliculas;
    }
}
?>
<?php
require_once (__DIR__ . '/../models/PeliculaModel.php');

class PeliculaController {
    private $peliculaModel;

    public function __construct() {
        $this->peliculaModel = new PeliculaModel();
    }

    public function getPeliculas() {
        try {
            $peliculas = $this->peliculaModel->getPeliculas();
            if ($peliculas === false) {
                throw new Exception("Error al cargar películas");
            }
            return $peliculas;
        } catch (Exception $e) {
            die("Error en el controlador: " . $e->getMessage());
        }
    }

    public function getPeliculaPorId($id) {
        try {
            return $this->peliculaModel->getPeliculaPorId($id);
        } catch (Exception $e) {
            die("Error al obtener la película: " . $e->getMessage());
        }
    }

    public function editarPelicula($id, $xmlData) {
        try {
            return $this->peliculaModel->editarPelicula($id, $xmlData);
        } catch (Exception $e) {
            die("Error al editar la película: " . $e->getMessage());
        }
    }

    public function borrarPelicula($id) {
        try {
            return $this->peliculaModel->borrarPelicula($id);
        } catch (Exception $e) {
            die("Error al borrar la película: " . $e->getMessage());
        }
    }
}
?>

<?php
require_once (__DIR__ . '/../models/carteleraModel.php');

class CarteleraController {
    public function __construct() {
        $this->carteleraModel = new CarteleraModel();
    }

    public function getCartelera() {
        $cartelera = $this->carteleraModel->getCartelera();
        if ($cartelera === false) {
            die("Error al cargar Cartelera");
        }
        return $cartelera;
    }
}
?>
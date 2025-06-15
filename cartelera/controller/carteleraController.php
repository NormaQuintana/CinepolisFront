<?php
require_once (__DIR__ . '/../models/carteleraModel.php');

class CarteleraController {
    public static function getCartelera() {
        $carteleraModel = new CarteleraModel();  // instancia local
        $cartelera = $carteleraModel->getCartelera();

        if ($cartelera === false) {
            http_response_code(500);
            echo json_encode(["error" => "Error al cargar Cartelera"]);
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode($cartelera);
    }
}

?>
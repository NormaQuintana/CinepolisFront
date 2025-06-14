<?php
ini_set('display_errors', 'Off'); // Desactiva la visualización de errores en la salida
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
require_once (__DIR__ . '/controller/boletoController.php'); // Nota la 'b' minúscula si tu archivo es 'boletoController.php'

$controller = new BoletoController();

$controller->mostrarSeleccionBoletos();

?>
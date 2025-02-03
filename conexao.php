<?php
include_once('config.php');
function conectar() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("ConexПлоПлкo falhou: " . $conn->connect_error);
    }
    return $conn;
}
?>
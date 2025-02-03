<?php
include_once('conexao.php');
include_once('auth.php'); // Inclui o arquivo auth.php onde a função verificaLogin está definida

verificaLogin();

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_movimento']) && isset($_POST['enviado'])) {
    $id_movimento = $_POST['id_movimento'];
    $enviado = $_POST['enviado'];

    $conn = conectar();
    $stmt = $conn->prepare("UPDATE movimento SET enviado = ? WHERE id_movimento = ?");
    $stmt->bind_param("ii", $enviado, $id_movimento);

    if ($stmt->execute()) {
        $response['success'] = true;
    }
}

echo json_encode($response);
?>
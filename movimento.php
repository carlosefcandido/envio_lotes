<?php
include_once('conexao.php');
include_once('auth.php'); // Inclui o arquivo auth.php onde a função verificaLogin está definida

verificaLogin();

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_tipo = $_POST['id_tipo'] ?? '';
    $lote = $_POST['lote'] ?? '';
    $valor = $_POST['valor'] ?? '';
    $id_banco = $_POST['id_banco'] ?? '';

    if (empty($id_tipo) || empty($lote) || empty($valor) || empty($id_banco)) {
        $message = "Todos os campos são obrigatórios.";
    } else {
        if (salvarMovimento($id_tipo, $lote, $valor, $id_banco)) {
            echo "<script>alert('Lote salvo com sucesso!'); window.location.replace('operador.php');</script>";
        } else {
            echo "<script>alert('Erro ao salvar o Lote!');</script>";
            header("location: operador.php");
        }
    }
}

function salvarMovimento($id_tipo, $lote, $valor, $id_banco) {
    $conn = conectar();
    $stmt = $conn->prepare("INSERT INTO movimento (id_tipo, lote, valor, id_usuario, id_banco) VALUES (?, ?, ?, ?, ?)");
    $id_usuario = $_SESSION['usuario']['id_usuario'];
    $stmt->bind_param("isdis", $id_tipo, $lote, $valor, $id_usuario, $id_banco); // Corrigido para "isdis"
    return $stmt->execute();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Movimento</title>
    <link rel="stylesheet" href="./styles/style.css">
    <script>
        function showAlert(message) {
            alert(message);
        }
    </script>
</head>
<body>
    <?php if ($message): ?>
        <script>
            showAlert('<?php echo $message; ?>');
        </script>
    <?php endif; ?>
    <!-- Conteúdo da página -->
</body>
</html>
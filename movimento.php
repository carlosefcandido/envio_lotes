<?php
include_once('conexao.php');
include_once('auth.php'); // Inclui o arquivo auth.php onde a função verificaLogin está definida

verificaLogin();

$id_usuario = $_SESSION['usuario']['id_usuario'];

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_tipo = $_POST['id_tipo'] ?? '';
    $lote = $_POST['lote'] ?? '';
    $valor = $_POST['valor'] ?? '';
    $id_banco = $_POST['id_banco'] ?? '';
    $id_movimento = $_POST['id_movimento'] ?? '';
    echo $id_movimento;
    echo $id_usuario;

    /*if (empty($id_tipo) || empty($lote) || empty($valor) || empty($id_banco)) {
        $message = "Todos os campos são obrigatórios.";
    } else {
        if (salvarMovimento($id_tipo, $lote, $valor, $id_banco)) {
            echo "<script>alert('Lote salvo com sucesso!'); window.location.replace('operador.php');</script>";
        } else {
            echo "<script>alert('Erro ao salvar o Lote!');</script>";
            header("location: operador.php");
        }
    }*/
    
    if (empty($id_tipo) || empty($lote) || empty($valor) || empty($id_banco)) {
        $message = "Todos os campos são obrigatórios.";
        $messageType = 'error';
    } else {
        if ($id_movimento || isLoteUnico($id_tipo, $lote)) {
            if (salvarMovimento($id_tipo, $lote, $valor, $id_usuario, $id_movimento, $id_banco)) {
                //$message = "Movimento salvo com sucesso.";
                //$messageType = 'success';
                echo "<script>alert('Lote salvo com sucesso!'); window.location.replace('operador.php');</script>";
            } else {
                //$message = "Erro ao salvar o movimento.";
                //$messageType = 'error';
                echo "<script>alert('Erro ao salvar o Lote!'); window.location.replace('operador.php');</script>";
                header("location: operador.php");
            }
        } else {
            echo "<script>alert('O número do lote deve ser único!!!.'); window.location.replace('operador.php');</script>";
            
        }
    }
}

function isLoteUnico($id_tipo, $lote) {
    $conn = conectar();
    $stmt = $conn->prepare("SELECT COUNT(*) FROM movimento WHERE lote = ?");
    $stmt->bind_param("s", $lote);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    return $count == 0;
}

/*function salvarMovimento($id_tipo, $lote, $valor, $id_banco) {
    $conn = conectar();
    $stmt = $conn->prepare("INSERT INTO movimento (id_tipo, lote, valor, id_usuario, id_banco) VALUES (?, ?, ?, ?, ?)");
    $id_usuario = $_SESSION['usuario']['id_usuario'];
    $stmt->bind_param("isdis", $id_tipo, $lote, $valor, $id_usuario, $id_banco); // Corrigido para "isdis"
    return $stmt->execute();
}*/

function salvarMovimento($id_tipo, $lote, $valor, $id_usuario, $id_movimento, $id_banco) {
    $conn = conectar();
    if ($id_movimento) {
        $sql = "UPDATE movimento SET id_tipo = ?, lote = ?, valor = ?, id_banco = ? WHERE id_movimento = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isdii", $id_tipo, $lote, $valor, $id_banco, $id_movimento);
    } else {
        $sql = "INSERT INTO movimento (id_tipo, lote, valor, id_usuario, id_banco) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isdii", $id_tipo, $lote, $valor, $id_usuario, $id_banco);
    }
    return $stmt->execute();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Movimento</title>
    <link rel="stylesheet" href="./styles/style.css">
    <script>
        /*function showAlert(message) {
            alert(message);
        }*/
    </script>
</head>
<body>
    <!--<?php if ($message): ?>
        <script>
            showAlert('<?php echo $message; ?>');
        </script>
    <?php endif; ?>--!>
    <!-- Conteúdo da página -->
</body>
</html>
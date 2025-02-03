<?php
require_once('conexao.php');

function gerarCodigoRecuperacao($login) {
    $conn = conectar();
    $codigo = rand(100000, 999999);
    $expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Obter o e-mail do usuário com base no login
    $stmt = $conn->prepare("SELECT login FROM usuario WHERE login = ?");
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $email = $row['login'];
        
        $stmt = $conn->prepare("UPDATE usuario SET codigo_recuperacao = ?, expiracao_codigo = ? WHERE login = ?");
        $stmt->bind_param("sss", $codigo, $expiracao, $login);
        
        if($stmt->execute()) {
            // Enviar o código de recuperação por e-mail
            $assunto = "Código de Recuperação de Senha";
            $mensagem = "Seu código de recuperação é: $codigo";
            $headers = "From: no-reply@acir.mprado.info";
            
            if (mail($email, $assunto, $mensagem, $headers)) {
                header("Location: recuperar_senha.php?success=Código de recuperação enviado com sucesso.");
                exit();
            } else {
                header("Location: recuperar_senha.php?error=Erro ao enviar o e-mail.");
                exit();
            }
        }
    }
    header("Location: recuperar_senha.php?error=Erro ao gerar código de recuperação.");
    exit();
}

function alterarSenha($login, $codigo, $nova_senha) {
    $conn = conectar();
    
    $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE login = ? AND codigo_recuperacao = ? AND expiracao_codigo > NOW()");
    $stmt->bind_param("ss", $login, $codigo);
    $stmt->execute();
    
    if($stmt->get_result()->num_rows == 1) {
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuario SET senha = ?, codigo_recuperacao = NULL, expiracao_codigo = NULL WHERE login = ?");
        $stmt->bind_param("ss", $senha_hash, $login);
        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => 'Erro ao atualizar a senha.'];
        }
    }
    return ['success' => false, 'error' => 'Código de recuperação inválido ou expirado.'];
}

// Exemplo de uso das funções
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login']) && isset($_POST['acao'])) {
        $login = $_POST['login'];
        if ($_POST['acao'] == 'gerar_codigo') {
            gerarCodigoRecuperacao($login);
        } elseif ($_POST['acao'] == 'alterar_senha' && isset($_POST['codigo']) && isset($_POST['nova_senha'])) {
            $codigo = $_POST['codigo'];
            $nova_senha = $_POST['nova_senha'];
            $resultado = alterarSenha($login, $codigo, $nova_senha);
            if ($resultado['success']) {
                header("Location: recuperar_senha.php?success=Senha alterada com sucesso.");
            } else {
                header("Location: recuperar_senha.php?error=" . $resultado['error']);
            }
            exit();
        }
    } else {
        header("Location: recuperar_senha.php?error=Dados incompletos.");
        exit();
    }
}
?>
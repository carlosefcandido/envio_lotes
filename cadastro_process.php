<?php
require_once 'config.php';
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'] ?? '';
    $login = $_POST['login'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirma_senha = $_POST['confirma_senha'] ?? '';
    $nivel_usuario = $_POST['nivel_usuario'] ?? '';

    // Validações
    if (empty($nome) || empty($login) || empty($senha) || empty($confirma_senha) || empty($nivel_usuario)) {
        header("Location: cadastro.php?erro=campos_obrigatorios");
        exit;
    }

    if ($senha !== $confirma_senha) {
        header("Location: cadastro.php?error=As senhas não coincidem");
        exit();
    }

    $conn = conectar();
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO usuario (nome, login, senha, nivel_usuario) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nome, $login, $senha_hash, $nivel_usuario);

    if ($stmt->execute()) {
        header("Location: cadastro.php?success=Cadastro realizado com sucesso");
    } else {
        header("Location: cadastro.php?error=Erro ao realizar o cadastro");
    }
    exit();
}

function cadastrarUsuario($nome, $login, $senha, $nivel_usuario) {
    $conn = conectar();
    
    // Verifica se login já existe
    $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE login = ?");
    $stmt->bind_param("s", $login);
    $stmt->execute();
    if($stmt->get_result()->num_rows > 0) {
        return ['success' => false, 'message' => 'Login já existe'];
    }
    
    // Cadastra novo usuário
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO usuario (nome, login, senha, nivel_usuario) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nome, $login, $senha_hash, $nivel_usuario);
    
    if($stmt->execute()) {
        return ['success' => true];
    }
    return ['success' => false, 'message' => 'Erro ao cadastrar'];
}
?>
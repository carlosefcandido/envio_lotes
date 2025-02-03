<?php
require_once('conexao.php');
session_start();

function verificaLogin() {
    if (!isset($_SESSION['usuario'])) {
        header("Location: index.php");
        exit;
    }
}

function processaLogin() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $login = $_POST['login'];
        $senha = $_POST['senha'];

        if (empty($login) || empty($senha)) {
            header("Location: index.php?error=Login e senha são obrigatórios");
            exit();
        } else {
            login($login, $senha);
        }
    }
}

function login($login, $senha) {
    $conn = conectar();
    $stmt = $conn->prepare("SELECT id_usuario, nome, nivel_usuario, senha FROM usuario WHERE login = ?");
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $usuario = $result->fetch_assoc();
        if (password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario'] = $usuario;
            
            if ($usuario['nivel_usuario'] == 'supervisor') {
                header("Location: supervisor.php");
            } else {
                header("Location: operador.php");
            }
            exit();
        } else {
            header("Location: index.php?error=Senha incorreta");
            exit();
        }
    } else {
        header("Location: index.php?error=Usuário não encontrado");
        exit();
    }
    return false;
}

// Chama a função processaLogin se o script for acessado diretamente
if (basename($_SERVER['PHP_SELF']) == 'auth.php') {
    processaLogin();
}
?>
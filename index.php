<!DOCTYPE html>
<html>
<head>
    <title>Controle de Lotes</title>
    <link rel="stylesheet" href="./styles/style.css">
</head>
<body>
    <div class="login-container">
        <?php if (isset($_GET['error'])): ?>
            <div class="alert error">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="auth.php">
            <h2>Login</h2>
            <input type="text" name="login" placeholder="E-mail" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit">Entrar</button>
        </form>
        <div class="links">
            <a href="cadastro.php">Cadastrar-se</a>
            <a href="recuperar_senha.php">Esqueci minha senha</a>
        </div>
    </div>
</body>
</html>
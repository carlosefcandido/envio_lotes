<!DOCTYPE html>
<html>
<head>
    <title>Cadastro de Lotess - Recuperação de Senha</title>
    <link rel="stylesheet" href="./styles/style.css">
</head>
<body>
    <div class="container">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert error">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>
        <h2>Recuperação de Senha</h2>
        <form method="POST" action="recuperar_senha_process.php" class="form-recuperacao">
            <input type="hidden" name="acao" value="gerar_codigo">
            <div class="form-group">
                <label for="login">Login:</label>
                <input type="text" id="login" name="login" required>
            </div>
            <button type="submit" class="btn">Gerar Código de Recuperação</button>
        </form>

        <h2>Alterar Senha</h2>
        <form method="POST" action="recuperar_senha_process.php" class="form-alterar-senha">
            <input type="hidden" name="acao" value="alterar_senha">
            <div class="form-group">
                <label for="login">Login:</label>
                <input type="text" id="login" name="login" required>
            </div>
            <div class="form-group">
                <label for="codigo">Código de Recuperação:</label>
                <input type="text" id="codigo" name="codigo" required>
            </div>
            <div class="form-group">
                <label for="nova_senha">Nova Senha:</label>
                <input type="password" id="nova_senha" name="nova_senha" required>
            </div>
            <button type="submit" class="btn">Alterar Senha</button>
        </form>
    </div>
</body>
</html>
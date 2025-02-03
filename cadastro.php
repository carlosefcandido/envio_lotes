<!DOCTYPE html>
<html>
<head>
    <title>Controle de Lotes - Cadastro Usuário</title>
    <link rel="stylesheet" href="./styles/style.css">
</head>
<body>
    <div class="login-container">
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
        <form method="POST" action="cadastro_process.php">
            <h2>Cadastro</h2>
            <input type="text" name="nome" placeholder="Nome completo" required>
            <input type="text" name="login" placeholder="E-mail" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <input type="password" name="confirma_senha" placeholder="Confirme a senha" required>
            <select name="nivel_usuario" required>
                <option value="operador">Operador</option>
                <option value="supervisor">Supervisor</option>
            </select>
            <button type="submit">Cadastrar</button>
        </form>
    </div>
</body>
</html>
<?php
// filepath: /c:/wamp64/www/envio_lotes/provisao.php

include_once('conexao.php');
include_once('auth.php');

verificaLogin();

$id_usuario = $_SESSION['usuario']['id_usuario'];
$nome_usuario = $_SESSION['usuario']['nome'];

// Processa o lançamento se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo_provisao'])) {
    // Agora o valor de tipo_provisao será o ID vindo da tabela tipo_provisao
    $tipo_provisao = $_POST['tipo_provisao'];
    $id_banco = $_POST['id_banco'];
    $valor = $_POST['valor'];
    // O campo data_folha é opcional, podendo vir vazio
    $data_folha = !empty($_POST['data_folha']) ? $_POST['data_folha'] : null;
    
    $conn = conectar();
    // Se o tipo for "Folha de Pagamento" (assumindo que seu id na tabela tipo_provisao seja 3)
    if ($tipo_provisao == 3) {
        $stmt = $conn->prepare('INSERT INTO provisao (id_banco, valor, id_usuario, id_tipo_provisao, data_folha) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('idiss', $id_banco, $valor, $id_usuario, $tipo_provisao, $data_folha);
    } else {
        $stmt = $conn->prepare('INSERT INTO provisao (id_banco, valor, id_usuario, id_tipo_provisao) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('idis', $id_banco, $valor, $id_usuario, $tipo_provisao);
    }

    if ($stmt->execute()) {
        $message = 'Provisão salva com sucesso!';
        $messageType = 'success';
    } else {
        $message = 'Erro ao salvar provisão.';
        $messageType = 'error';
    }
    
    $stmt->close();
    $conn->close();
}

// Define as datas de início e fim para o filtro
$data_inicio = $_POST['data_inicio'] ?? date('Y-m-d');
$data_fim = $_POST['data_fim'] ?? date('Y-m-d');

// Conecta ao banco de dados
$conn = conectar();

// Atualize a consulta dos totais por tipo de provisão:
$query_totais = "SELECT t.nome_tipo AS tipo_provisao, b.nome_banco, SUM(p.valor) AS total_valor
                 FROM provisao p
                 JOIN banco b ON p.id_banco = b.id_banco
                 JOIN tipo_provisao t ON p.id_tipo_provisao = t.id_tipo_provisao
                 WHERE p.id_usuario = ? 
                 AND t.nome_tipo != 'Folha'
                 AND DATE(p.data_salvo) BETWEEN ? AND ?
                 GROUP BY t.nome_tipo, b.nome_banco";
$stmt_totais = $conn->prepare($query_totais);
$stmt_totais->bind_param('iss', $id_usuario, $data_inicio, $data_fim);
$stmt_totais->execute();
$totais = $stmt_totais->get_result();

// Atualize a consulta das provisões individuais:
$query_individuais = "SELECT p.data_salvo, p.data_folha, t.nome_tipo AS tipo_provisao, b.nome_banco, p.valor
                      FROM provisao p
                      JOIN banco b ON p.id_banco = b.id_banco
                      JOIN tipo_provisao t ON p.id_tipo_provisao = t.id_tipo_provisao
                      WHERE p.id_usuario = ? 
                      AND DATE(p.data_salvo) BETWEEN ? AND ?";
$stmt_individuais = $conn->prepare($query_individuais);
$stmt_individuais->bind_param('iss', $id_usuario, $data_inicio, $data_fim);
$stmt_individuais->execute();
$individuais = $stmt_individuais->get_result();

// Fecha a conexão com o banco de dados
$stmt_totais->close();
$stmt_individuais->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Controle de Provisões</title>
    <link rel="stylesheet" href="styles/style.css">
    <script>
        // Função para exibir a mensagem de alerta
        function showAlert(message, type) {
            const alertBox = document.createElement('div');
            alertBox.className = `alert ${type}`;
            alertBox.textContent = message;
            document.body.appendChild(alertBox);
            setTimeout(() => {
                alertBox.remove();
            }, 3000); // A mensagem desaparecerá após 3 segundos
        }
    </script>
    <style>
        .alert {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 10px 20px;
            border-radius: 5px;
            color: #fff;
            z-index: 1000;
        }
        .alert.success {
            background-color: #4CAF50;
        }
        .alert.error {
            background-color: #f44336;
        }
    </style>
</head>
<body>
    <!-- Exibe a mensagem de sucesso ou erro -->
    <?php if (isset($message)): ?>
        <script>
            showAlert('<?php echo $message; ?>', '<?php echo $messageType; ?>');
        </script>
    <?php endif; ?>

    <!-- Menu de navegação -->
    <div class="menu">
        <a href="operador.php">Lote</a>
        <a href="provisao.php">Provisão</a>
    </div>

    <div class="container">
        <!-- Mensagem de boas-vindas -->
        <div class="login-message">
            Bem-vindo, <?php echo htmlspecialchars(is_string($nome_usuario) ? $nome_usuario : ''); ?>! Você está logado como operador.
            <a href="logout.php" class="logout-link">Deslogar</a>
        </div>

        <!-- Formulário para lançamento da provisão -->
        <h2>Inserir Provisão</h2>
        <form method="POST" action="provisao.php">
            <label for="tipo_provisao">Tipo de Provisão:</label>
            <select name="tipo_provisao" id="tipo_provisao" required>
                <option value="">Selecione a provisão</option>
                <?php
                $conn = conectar();
                $tipos = $conn->query('SELECT * FROM tipo_provisao');
                while ($tipo = $tipos->fetch_assoc()) {
                    echo "<option value='{$tipo['id_tipo_provisao']}'>{$tipo['nome_tipo']}</option>";
                }
                $conn->close();
                ?>
            </select>
            
            <label for="id_banco">Banco:</label>
            <select name="id_banco" id="id_banco" required>
                <option value="">Selecione o banco</option>
                <?php
                $conn = conectar();
                $bancos = $conn->query('SELECT * FROM banco WHERE nome_banco IN ("Itaú", "Bradesco")');
                while ($banco = $bancos->fetch_assoc()) {
                    echo "<option value='{$banco['id_banco']}'>{$banco['nome_banco']}</option>";
                }
                $conn->close();
                ?>
            </select>
            
            <label for="valor">Valor:</label>
            <input type="number" step="0.01" name="valor" id="valor" placeholder="Valor" required>
            
            <!-- Campo de data que será exibido apenas para "Folha de Pagamento" -->
            <div id="campoDataFolha" style="display: none;">
                <label for="data_folha">Data da Folha:</label>
                <input type="date" name="data_folha" id="data_folha">
            </div>
            
            <button type="submit">Salvar</button>
        </form>

        <script>
        // Exibe o campo de data quando o tipo selecionado for "Folha de Pagamento"
        document.getElementById('tipo_provisao').addEventListener('change', function() {
            var selectedText = this.options[this.selectedIndex].text;
            if (selectedText.trim() === 'Folha de Pagamento') {
                document.getElementById('campoDataFolha').style.display = 'block';
            } else {
                document.getElementById('campoDataFolha').style.display = 'none';
                document.getElementById('data_folha').value = '';
            }
        });
        </script>

        <!-- Formulário para filtrar lançamentos -->
        <h2>Filtrar Lançamentos</h2>
        <form method="POST" action="provisao.php">
            <input type="date" name="data_inicio" value="<?php echo $data_inicio; ?>">
            <input type="date" name="data_fim" value="<?php echo $data_fim; ?>">
            <button type="submit">Filtrar</button>
        </form>

        <!-- Tabela de totais por tipo de provisão -->
        <h2>Totais por Tipo de Provisão</h2>
        <table>
            <thead>
                <tr>
                    <th>Tipo de Provisão</th>
                    <th>Banco</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $totais->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['tipo_provisao']); ?></td>
                        <td><?php echo htmlspecialchars($row['nome_banco']); ?></td>
                        <td>R$ <?php echo number_format($row['total_valor'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Tabela de provisões individuais -->
        <h2>Provisões Individuais</h2>
        <table>
            <thead>
                <tr>
                    <th>Data e Hora</th>
                    <th>Data da Folha</th>
                    <th>Tipo de Provisão</th>
                    <th>Banco</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $individuais->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($row['data_salvo']))); ?></td>
                        <td><?php echo htmlspecialchars($row['data_folha'] ? date('d/m/Y', strtotime($row['data_folha'])) : ''); ?></td>
                        <td><?php echo htmlspecialchars($row['tipo_provisao']); ?></td>
                        <td><?php echo htmlspecialchars($row['nome_banco']); ?></td>
                        <td>R$ <?php echo number_format($row['valor'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
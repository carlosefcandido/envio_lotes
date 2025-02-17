<?php
// filepath: /c:/wamp64/www/envio_lotes/provisao.php

include_once('conexao.php');
include_once('auth.php');

verificaLogin();

// Inicia a sessão se ainda não estiver iniciada (caso o auth.php não o faça)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Recupera mensagem de flash, se existir
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['messageType'];
    unset($_SESSION['message'], $_SESSION['messageType']);
}

$id_usuario = $_SESSION['usuario']['id_usuario'];
$nome_usuario = $_SESSION['usuario']['nome'];

// Inicializa variáveis para edição
$editing = false;
$editData = [
    'id_provisao'      => '',
    'id_tipo_provisao' => '',
    'id_banco'         => '',
    'valor'            => '',
    'data_folha'       => ''
];

// Se houver parâmetro "edit" na URL, carrega os dados para edição
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $conn = conectar();
    $stmt_edit = $conn->prepare("SELECT * FROM provisao WHERE id_provisao = ? AND id_usuario = ?");
    $stmt_edit->bind_param("ii", $edit_id, $id_usuario);
    $stmt_edit->execute();
    $result_edit = $stmt_edit->get_result();
    if ($row = $result_edit->fetch_assoc()) {
        $editing = true;
        $editData = [
            'id_provisao'      => $row['id_provisao'],
            'id_tipo_provisao' => $row['id_tipo_provisao'],
            'id_banco'         => $row['id_banco'],
            'valor'            => $row['valor'],
            'data_folha'       => $row['data_folha']
        ];
    }
    $stmt_edit->close();
    $conn->close();
}

// Processa o formulário de inserção/edição
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo_provisao'])) {
    $tipo_provisao = $_POST['tipo_provisao'];
    $id_banco = $_POST['id_banco'];
    $valor = $_POST['valor'];
    $data_folha = !empty($_POST['data_folha']) ? $_POST['data_folha'] : null;
    
    $conn = conectar();
    
    if (isset($_POST['id_provisao']) && !empty($_POST['id_provisao'])) {
        // Atualização
        $id_provisao = $_POST['id_provisao'];
        if ($tipo_provisao == 3) {
            $stmt = $conn->prepare("UPDATE provisao SET id_banco = ?, valor = ?, id_tipo_provisao = ?, data_folha = ? WHERE id_provisao = ? AND id_usuario = ?");
            $stmt->bind_param("idssii", $id_banco, $valor, $tipo_provisao, $data_folha, $id_provisao, $id_usuario);
        } else {
            $stmt = $conn->prepare("UPDATE provisao SET id_banco = ?, valor = ?, id_tipo_provisao = ?, data_folha = NULL WHERE id_provisao = ? AND id_usuario = ?");
            $stmt->bind_param("idiii", $id_banco, $valor, $tipo_provisao, $id_provisao, $id_usuario);
        }
    } else {
        // Inserção
        if ($tipo_provisao == 3) {
            $stmt = $conn->prepare("INSERT INTO provisao (id_banco, valor, id_usuario, id_tipo_provisao, data_folha) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("idiss", $id_banco, $valor, $id_usuario, $tipo_provisao, $data_folha);
        } else {
            $stmt = $conn->prepare("INSERT INTO provisao (id_banco, valor, id_usuario, id_tipo_provisao) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("idis", $id_banco, $valor, $id_usuario, $tipo_provisao);
        }
    }
    
    if ($stmt->execute()) {
        $_SESSION['message'] = isset($id_provisao) ? "Provisão atualizada com sucesso!" : "Provisão salva com sucesso!";
        $_SESSION['messageType'] = "success";
    } else {
        $_SESSION['message'] = isset($id_provisao) ? "Erro ao atualizar provisão." : "Erro ao salvar provisão.";
        $_SESSION['messageType'] = "error";
    }
    
    $stmt->close();
    $conn->close();
    
    // Redireciona para evitar reenvio em caso de refresh (POST-Redirect-GET)
    header("Location: provisao.php");
    exit;
}

// Define as datas para filtrar os lançamentos
$data_inicio = $_POST['data_inicio'] ?? date('Y-m-d');
$data_fim = $_POST['data_fim'] ?? date('Y-m-d');

// Consulta dos totais por tipo de provisão (excetuando "Folha")
$conn = conectar();
$query_totais = "SELECT t.nome_tipo AS tipo_provisao, b.nome_banco, SUM(p.valor) AS total_valor
                 FROM provisao p
                 JOIN banco b ON p.id_banco = b.id_banco
                 JOIN tipo_provisao t ON p.id_tipo_provisao = t.id_tipo_provisao
                 WHERE p.id_usuario = ? 
                 AND t.nome_tipo != 'Folha'
                 AND DATE(p.data_salvo) BETWEEN ? AND ?
                 GROUP BY t.nome_tipo, b.nome_banco";
$stmt_totais = $conn->prepare($query_totais);
$stmt_totais->bind_param("iss", $id_usuario, $data_inicio, $data_fim);
$stmt_totais->execute();
$totais = $stmt_totais->get_result();

// Consulta das provisões individuais
$query_individuais = "SELECT p.id_provisao, p.data_salvo, p.data_folha, t.nome_tipo AS tipo_provisao, b.nome_banco, p.valor
                      FROM provisao p
                      JOIN banco b ON p.id_banco = b.id_banco
                      JOIN tipo_provisao t ON p.id_tipo_provisao = t.id_tipo_provisao
                      WHERE p.id_usuario = ? 
                      AND DATE(p.data_salvo) BETWEEN ? AND ?";
$stmt_individuais = $conn->prepare($query_individuais);
$stmt_individuais->bind_param("iss", $id_usuario, $data_inicio, $data_fim);
$stmt_individuais->execute();
$individuais = $stmt_individuais->get_result();

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
            }, 3000);
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
        .btn-editar {
            padding: 5px 10px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 3px;
            transition: background-color 0.3s ease;
        }
        .btn-editar:hover {
            background-color: #0056b3;
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

        <!-- Formulário para lançamento/edição da provisão -->
        <h2><?php echo $editing ? 'Editar Provisão' : 'Inserir Provisão'; ?></h2>
        <form method="POST" action="provisao.php">
            <?php if ($editing): ?>
                <input type="hidden" name="id_provisao" value="<?php echo $editData['id_provisao']; ?>">
            <?php endif; ?>
            
            <label for="tipo_provisao">Tipo de Provisão:</label>
            <select name="tipo_provisao" id="tipo_provisao" required>
                <option value="">Selecione a provisão</option>
                <?php
                $conn = conectar();
                $tipos = $conn->query("SELECT * FROM tipo_provisao");
                while ($tipo = $tipos->fetch_assoc()) {
                    $selected = ($editing && $editData['id_tipo_provisao'] == $tipo['id_tipo_provisao']) ? 'selected' : '';
                    echo "<option value='{$tipo['id_tipo_provisao']}' {$selected}>{$tipo['nome_tipo']}</option>";
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
                    $selected = ($editing && $editData['id_banco'] == $banco['id_banco']) ? 'selected' : '';
                    echo "<option value='{$banco['id_banco']}' {$selected}>{$banco['nome_banco']}</option>";
                }
                $conn->close();
                ?>
            </select>
            
            <label for="valor">Valor:</label>
            <input type="number" step="0.01" name="valor" id="valor" placeholder="Valor" required value="<?php echo $editing ? $editData['valor'] : ''; ?>">
            
            <!-- Campo de data que será exibido apenas para "Folha de Pagamento" -->
            <div id="campoDataFolha" style="display: <?php echo ($editing && $editData['id_tipo_provisao'] == 3) ? 'block' : 'none'; ?>;">
                <label for="data_folha">Data da Folha:</label>
                <input type="date" name="data_folha" id="data_folha" value="<?php echo $editing ? $editData['data_folha'] : ''; ?>">
            </div>
            
            <button type="submit"><?php echo $editing ? 'Atualizar' : 'Salvar'; ?></button>
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
                    <th>Ação</th>
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
                        <td>
                            <a href="provisao.php?edit=<?php echo $row['id_provisao']; ?>" class="btn-editar">Editar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
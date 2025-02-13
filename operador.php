<?php
include_once('conexao.php');
include_once('auth.php'); // Inclui o arquivo auth.php onde a função verificaLogin está definida

verificaLogin();

$id_usuario = $_SESSION['usuario']['id_usuario'];
$nome_usuario = $_SESSION['usuario']['nome'];

$message = '';
$messageType = ''; // Variável para armazenar o tipo de mensagem (sucesso ou erro)

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_tipo = $_POST['id_tipo'] ?? '';
    $lote = $_POST['lote'] ?? '';
    $valor = $_POST['valor'] ?? '';
    $id_banco = $_POST['id_banco'] ?? '';
    $id_movimento = $_POST['id_movimento'] ?? '';

    if (empty($id_tipo) || empty($lote) || empty($valor) || empty($id_banco)) {
        $message = "Todos os campos são obrigatórios.";
        $messageType = 'error';
    } else {
        if ($id_movimento || isLoteUnico($id_tipo, $lote)) {
            if (salvarMovimento($id_tipo, $lote, $valor, $id_usuario, $id_movimento, $id_banco)) {
                $message = "Movimento salvo com sucesso.";
                $messageType = 'success';
            } else {
                $message = "Erro ao salvar o movimento.";
                $messageType = 'error';
            }
        } else {
            $message = "O número do lote deve ser único, exceto para tipos PIX e Qr Code -PIX.";
            $messageType = 'error';
        }
    }
}

function isLoteUnico($id_tipo, $lote) {
    if ($id_tipo == 10 || $id_tipo == 11) {
        return true;
    }
    $conn = conectar();
    $stmt = $conn->prepare("SELECT COUNT(*) FROM movimento WHERE lote = ?");
    $stmt->bind_param("s", $lote);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    return $count == 0;
}

function salvarMovimento($id_tipo, $lote, $valor, $id_usuario, $id_banco, $id_movimento = null) {
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

// Consulta para obter os totais por tipo de pagamento digitados pelo operador
function getTotaisPorTipo($id_usuario, $data_inicio, $data_fim) {
    $conn = conectar();
    $stmt = $conn->prepare("SELECT t.nome_tipo, SUM(m.valor) as total_valor 
                            FROM movimento m 
                            JOIN tipo_pagamento t ON m.id_tipo = t.id_tipo 
                            WHERE m.id_usuario = ? AND DATE(m.data_salvo) BETWEEN ? AND ? 
                            GROUP BY t.nome_tipo");
    $stmt->bind_param("iss", $id_usuario, $data_inicio, $data_fim);
    $stmt->execute();
    return $stmt->get_result();
}

$data_inicio = $_POST['data_inicio'] ?? date('Y-m-d');
$data_fim = $_POST['data_fim'] ?? date('Y-m-d');
$totais = getTotaisPorTipo($id_usuario, $data_inicio, $data_fim);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Controle de Lotes - Operador</title>
    <link rel="stylesheet" href="./styles/style.css">
    <script>
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
        .login-message {
            text-align: center;
            margin-bottom: 20px;
            font-size: 18px;
            color: #333;
        }
        .logout-link {
            margin-left: 10px;
            font-size: 14px;
            color: #007bff;
            text-decoration: none;
        }
        .logout-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php if ($message): ?>
        <script>
            showAlert('<?php echo $message; ?>', '<?php echo $messageType; ?>');
        </script>
    <?php endif; ?>
    <div class="menu">
        <a href="operador.php">Lote</a>
        <a href="provisao.php">Provisão</a>
    </div>
    <div class="container">
        <div class="login-message">
            Bem-vindo, <?php echo htmlspecialchars($nome_usuario); ?>! Você está logado como operador.
            <a href="logout.php" class="logout-link">Deslogar</a>
        </div>
        
        <h2>Digitação de Lotes</h2>
        <form method="POST" action="movimento.php">
            <input type="hidden" name="id_movimento" value="<?php echo $_POST['id_movimento'] ?? ''; ?>">
            <select name="id_tipo" required>
                <?php
                $conn = conectar();
                $tipos = $conn->query("SELECT * FROM tipo_pagamento");
                while($tipo = $tipos->fetch_assoc()) {
                    $selected = (isset($_POST['id_tipo']) && $_POST['id_tipo'] == $tipo['id_tipo']) ? 'selected' : '';
                    echo "<option value='".$tipo['id_tipo']."' $selected>".$tipo['nome_tipo']."</option>";
                }
                ?>
            </select>
            <select name="id_banco" required>
                <option value="">Selecione o banco</option>
                <?php
                $bancos = $conn->query("SELECT * FROM banco");
                while($banco = $bancos->fetch_assoc()) {
                    $selected = (isset($_POST['id_banco']) && $_POST['id_banco'] == $banco['id_banco']) ? 'selected' : '';
                    echo "<option value='".$banco['id_banco']."' $selected>".$banco['nome_banco']."</option>";
                }
                ?>
            </select>
            <input type="text" name="lote" placeholder="Número do Lote" value="<?php echo $_POST['lote'] ?? ''; ?>" required>
            <input type="number" step="0.01" name="valor" placeholder="Valor" value="<?php echo $_POST['valor'] ?? ''; ?>" required>
            <button type="submit">Salvar</button>
        </form>
        
        <h2>Filtrar Lançamentos</h2>
        <form method="POST" action="operador.php">
            <input type="date" name="data_inicio" value="<?php echo $data_inicio; ?>">
            <input type="date" name="data_fim" value="<?php echo $data_fim; ?>">
            <button type="submit">Filtrar</button>
        </form>

        <h2>Totais por Tipo de Pagamento</h2>
        <table>
            <thead>
                <tr>
                    <th>Tipo de Pagamento</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $totais->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nome_tipo']); ?></td>
                        <td>R$ <?php echo number_format($row['total_valor'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h2>Lançamentos do Dia</h2>
        <table id="tabelaLancamentos">
            <thead>
                <tr>
                    <th>Data e Hora</th>
                    <th>Lote</th>
                    <th>Tipo</th>
                    <th>Banco</th>
                    <th>Valor</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $query = "SELECT m.id_movimento, m.data_salvo, m.lote, t.id_tipo, t.nome_tipo, m.valor, m.id_banco, b.nome_banco 
                          FROM movimento m 
                          JOIN tipo_pagamento t ON m.id_tipo = t.id_tipo 
                          LEFT JOIN banco b ON m.id_banco = b.id_banco 
                          WHERE m.id_usuario = ? AND DATE(m.data_salvo) BETWEEN ? AND ? 
                          ORDER BY m.data_salvo DESC";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("iss", $id_usuario, $data_inicio, $data_fim);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    $data_hora_formatada = date('d/m/Y H:i:s', strtotime($row['data_salvo']));
                    $nome_banco = $row['nome_banco'] ?? 'N/A'; // Tratar valores nulos para o banco
                    echo "<tr>";
                    echo "<td>" . $data_hora_formatada . "</td>";
                    echo "<td>" . $row['lote'] . "</td>";
                    echo "<td>" . $row['nome_tipo'] . "</td>";
                    echo "<td>" . $nome_banco . "</td>"; // Nova coluna
                    echo "<td>R$ " . number_format($row['valor'], 2, ',', '.') . "</td>";
                    echo "<td><form method='POST' action='operador.php'>
                            <input type='hidden' name='id_movimento' value='".$row['id_movimento']."'>
                            <input type='hidden' name='id_tipo' value='".$row['id_tipo']."'>
                            <input type='hidden' name='lote' value='".$row['lote']."'>
                            <input type='hidden' name='valor' value='".$row['valor']."'>
                            <input type='hidden' name='id_banco' value='".$row['id_banco']."'>
                            <button type='submit'>Editar</button>
                        </form></td>";
                    echo "</tr>";
                }
            ?>
            </tbody>
        </table>
    </div>
</body>
</html>
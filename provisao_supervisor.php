<?php
require_once 'auth.php';
require_once 'conexao.php'; // Inclui o arquivo de conexão com o banco de dados
require_once 'provisao_relatorio.php'; // Inclui o arquivo provisao_relatorio.php onde as funções getTotaisProvisao e getTotaisFolha estão definidas

verificaLogin();

$id_usuario = $_SESSION['usuario']['id_usuario'];
$nome_usuario = $_SESSION['usuario']['nome'];

// Initialize data_inicio and data_fim with default values
$data_inicio = date('Y-m-d');
$data_fim = date('Y-m-d');

// Check if the form is submitted and the date values are set
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['data_inicio']) && isset($_POST['data_fim'])) {
    // Use the values from the form, or keep the default values if they are not set
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
}

$totaisProvisao = getTotaisProvisao($data_inicio, $data_fim);
$totaisFolha = getTotaisFolha($data_inicio, $data_fim);
$provisoesDoDia = getProvisoesDoDia($data_inicio, $data_fim);

$response = [
    'totais' => '',
    'provisoes' => ''
];

// Exibir totais de provisões
if ($totaisProvisao->num_rows > 0) {
    $response['totais'] .= "<h3>Totais de Provisões</h3>";
    $response['totais'] .= "<table id='tabelaTotaisProvisao'>";
    $response['totais'] .= "<thead><tr><th>Tipo de Provisão</th><th>Total</th></tr></thead>";
    $response['totais'] .= "<tbody>";
    while ($row = $totaisProvisao->fetch_assoc()) {
        $response['totais'] .= "<tr>";
        $response['totais'] .= "<td>" . htmlspecialchars($row['nome_tipo']) . "</td>";
        $response['totais'] .= "<td>R$ " . number_format($row['total_valor'], 2, ',', '.') . "</td>";
        $response['totais'] .= "</tr>";
    }
    $response['totais'] .= "</tbody></table>";
}

// Exibir totais de folha de pagamento
if ($totaisFolha->num_rows > 0) {
    $response['totais'] .= "<h3>Totais de Folha de Pagamento</h3>";
    $response['totais'] .= "<table id='tabelaTotaisFolha'>";
    $response['totais'] .= "<thead><tr><th>Data da Folha</th><th>Total</th></tr></thead>";
    $response['totais'] .= "<tbody>";
    while ($row = $totaisFolha->fetch_assoc()) {
        $response['totais'] .= "<tr>";
        $response['totais'] .= "<td>" . date('d/m/Y', strtotime($row['data_folha'])) . "</td>";
        $response['totais'] .= "<td>R$ " . number_format($row['total_valor'], 2, ',', '.') . "</td>";
        $response['totais'] .= "</tr>";
    }
    $response['totais'] .= "</tbody></table>";
}

// Exibir provisões do dia
if ($provisoesDoDia->num_rows > 0) {
    $response['provisoes'] .= "<h3>Provisões do Dia</h3>";
    $response['provisoes'] .= "<table id='tabelaProvisoesDoDia'>";
    $response['provisoes'] .= "<thead><tr><th>Usuário</th><th>Tipo de Provisão</th><th>Valor</th><th>Data</th><th>Data da Folha</th><th>Banco</th></tr></thead>";
    $response['provisoes'] .= "<tbody>";
    while ($row = $provisoesDoDia->fetch_assoc()) {
        $response['provisoes'] .= "<tr>";
        $response['provisoes'] .= "<td>" . htmlspecialchars($row['nome_usuario']) . "</td>";
        $response['provisoes'] .= "<td>" . htmlspecialchars($row['tipo_provisao']) . "</td>";
        $response['provisoes'] .= "<td>R$ " . number_format($row['valor'], 2, ',', '.') . "</td>";
        $response['provisoes'] .= "<td>" . date('d/m/Y H:i:s', strtotime($row['data_salvo'])) . "</td>";
        $response['provisoes'] .= "<td>" . date('d/m/Y', strtotime($row['data_folha'])) . "</td>";
        $response['provisoes'] .= "<td>" . htmlspecialchars($row['nome_banco']) . "</td>";
        $response['provisoes'] .= "</tr>";
    }
    $response['provisoes'] .= "</tbody></table>";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Provisões</title>
    <link rel="stylesheet" href="styles/style.css"> <!-- Inclui o arquivo CSS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function atualizarTabela() {
            const data_inicio = $('input[name="data_inicio"]').val();
            const data_fim = $('input[name="data_fim"]').val();

            $.post('provisao_supervisor.php', { data_inicio: data_inicio, data_fim: data_fim }, function(data) {
                $('#totaisContainer').html(data.totais);
                $('#provisoesContainer').html(data.provisoes);
            }, 'json');
        }

        $(document).ready(function() {
            $('form').on('submit', function(e) {
                e.preventDefault();
                atualizarTabela();
            });

            // Atualizar a tabela ao carregar a página
            atualizarTabela();
        });
    </script>
</head>
<body>
    <div class="menu">
        <a href="supervisor.php">Lote</a>
        <a href="provisao_supervisor.php">Provisão</a>
    </div>
    <div class="container">
        <div class="login-message">
            Bem-vindo, <?php echo htmlspecialchars($nome_usuario); ?>! Você está logado como supervisor.
            <a href="logout.php" class="logout-link">Deslogar</a>
        </div>
        <h2>Relatório de Provisões</h2>
        <form method="POST" action="provisao_supervisor.php">
            <input type="date" name="data_inicio" value="<?php echo $data_inicio; ?>">
            <input type="date" name="data_fim" value="<?php echo $data_fim; ?>">
            <button type="submit">Filtrar</button>
        </form>
        <div id="totaisContainer">
            <?php echo $response['totais']; ?>
        </div>
        <div id="provisoesContainer">
            <?php echo $response['provisoes']; ?>
        </div>
    </div>
</body>
</html>

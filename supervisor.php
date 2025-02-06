<?php
require_once 'auth.php';
require_once 'supervisor_relatorio.php'; // Inclui o arquivo supervisor_relatorio.php onde as funções getRelatorio e getTotaisPorTipo estão definidas
verificaLogin();

$id_usuario = $_SESSION['usuario']['id_usuario'];
$nome_usuario = $_SESSION['usuario']['nome'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['data_inicio']) && isset($_POST['data_fim'])) {
    $data_inicio = $_POST['data_inicio'] ?? null;
    $data_fim = $_POST['data_fim'] ?? null;

    $totaisPorTipo = getTotaisPorTipo($data_inicio, $data_fim);
    $totaisPorTipoEBanco = getTotaisPorTipoEBanco($data_inicio, $data_fim);
    $result = getRelatorio($data_inicio, $data_fim);

    $response = [
        'totais' => '',
        'movimentos' => ''
    ];

    // Exibir totais por tipo de pagamento
    if ($totaisPorTipo->num_rows > 0) {
        $response['totais'] .= "<h3>Totais por Tipo de Pagamento</h3>";
        $response['totais'] .= "<table id='tabelaTotais'>";
        $response['totais'] .= "<thead><tr><th>Tipo de Pagamento</th><th>Total</th></tr></thead>";
        $response['totais'] .= "<tbody>";
        while ($row = $totaisPorTipo->fetch_assoc()) {
            $response['totais'] .= "<tr>";
            $response['totais'] .= "<td>" . $row['nome_tipo'] . "</td>";
            $response['totais'] .= "<td>R$ " . number_format($row['total_valor'], 2, ',', '.') . "</td>";
            $response['totais'] .= "</tr>";
        }
        $response['totais'] .= "</tbody></table>";
    }

    // Exibir totais por tipo de pagamento e banco
    if ($totaisPorTipoEBanco->num_rows > 0) {
        $response['totais'] .= "<h3>Totais por Tipo de Pagamento e Banco</h3>";
        $response['totais'] .= "<table id='tabelaTotaisPorBanco'>";
        $response['totais'] .= "<thead><tr><th>Tipo de Pagamento</th><th>Banco</th><th>Total</th></tr></thead>";
        $response['totais'] .= "<tbody>";
        while ($row = $totaisPorTipoEBanco->fetch_assoc()) {
            $response['totais'] .= "<tr>";
            $response['totais'] .= "<td>" . $row['nome_tipo'] . "</td>";
            $response['totais'] .= "<td>" . $row['nome_banco'] . "</td>";
            $response['totais'] .= "<td>R$ " . number_format($row['total_valor'], 2, ',', '.') . "</td>";
            $response['totais'] .= "</tr>";
        }
        $response['totais'] .= "</tbody></table>";
    }

    // Exibir relatório de movimentos
    while ($row = $result->fetch_assoc()) {
        $data_hora_formatada = date('d/m/Y H:i:s', strtotime($row['data_salvo']));
        $enviado = $row['enviado'] ? 'Sim' : 'Não';
        $response['movimentos'] .= "<tr>";
        $response['movimentos'] .= "<td>" . $data_hora_formatada . "</td>";
        $response['movimentos'] .= "<td>" . $row['lote'] . "</td>";
        $response['movimentos'] .= "<td>" . $row['nome_tipo'] . "</td>";
        $response['movimentos'] .= "<td>" . $row['nome_banco'] . "</td>"; // Nova coluna
        $response['movimentos'] .= "<td>R$ " . number_format($row['valor'], 2, ',', '.') . "</td>";
        $response['movimentos'] .= "<td>" . $row['nome_usuario'] . "</td>";
        $response['movimentos'] .= "<td>" . $enviado . "</td>";
        $response['movimentos'] .= "<td><input type='checkbox' class='marcar-enviado' data-id='" . $row['id_movimento'] . "' " . ($row['enviado'] ? 'checked' : '') . "></td>";
        $response['movimentos'] .= "</tr>";
    }

    echo json_encode($response);
    exit;
} else {
    $data_inicio = date('Y-m-d');
    $data_fim = date('Y-m-d');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Movimentos</title>
    <link rel="stylesheet" href="styles/style.css"> <!-- Inclui o arquivo CSS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function atualizarTabela() {
            const data_inicio = $('input[name="data_inicio"]').val();
            const data_fim = $('input[name="data_fim"]').val();

            $.post('supervisor.php', { data_inicio: data_inicio, data_fim: data_fim }, function(data) {
                $('#totaisContainer').html(data.totais);
                $('#tabelaRelatorio tbody').html(data.movimentos);
                bindMarcarEnviado();
            }, 'json');
        }

        function bindMarcarEnviado() {
            $('.marcar-enviado').on('change', function() {
                const id_movimento = $(this).data('id');
                const enviado = $(this).is(':checked') ? 1 : 0;
                $.post('marcar_enviado.php', { id_movimento: id_movimento, enviado: enviado }, function(response) {
                    if (response.success) {
                        alert('Status atualizado com sucesso.');
                        atualizarTabela(); // Atualiza a tabela após a confirmação
                    } else {
                        alert('Erro ao atualizar status.');
                    }
                }, 'json');
            });
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
    <div class="container">
        <div class="login-message">
            Bem-vindo, <?php echo htmlspecialchars($nome_usuario); ?>! Você está logado como supervisor.
            <a href="logout.php" class="logout-link">Deslogar</a>
        </div>
        <h2>Relatório de Movimentos</h2>
        <form method="POST" action="supervisor.php">
            <input type="date" name="data_inicio" value="<?php echo date('Y-m-d'); ?>">
            <input type="date" name="data_fim" value="<?php echo date('Y-m-d'); ?>">
            <button type="submit">Filtrar</button>
            <a href="exportar_lancamentos.php?data_inicio=<?php echo $data_inicio; ?>&data_fim=<?php echo $data_fim; ?>" class="btn">Exportar para XLS</a>
        </form>
        <div id="totaisContainer">
            <!-- Tabela de totais será inserida aqui -->
        </div>
        <br>
        <br>
        <table id="tabelaRelatorio">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Lote</th>
                    <th>Tipo</th>
                    <th>Banco</th>
                    <th>Valor</th>
                    <th>Operador</th>
                    <th>Enviado</th>
                    <th>Marcar Enviado</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $relatorio = getRelatorio($data_inicio, $data_fim);
                while ($row = $relatorio->fetch_assoc()) {
                    $data_formatada = date('d/m/Y', strtotime($row['data_salvo']));
                    echo "<tr>";
                    echo "<td>" . $data_formatada . "</td>";
                    echo "<td>" . htmlspecialchars($row['lote']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nome_tipo']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nome_banco'] ?? 'N/A') . "</td>"; // Exibir 'N/A' se o banco não estiver definido
                    echo "<td>R$ " . number_format($row['valor'], 2, ',', '.') . "</td>";
                    echo "<td>" . htmlspecialchars($row['nome_usuario']) . "</td>";
                    echo "<td>" . ($row['enviado'] ? 'Sim' : 'Não') . "</td>";
                    echo "<td><form method='POST' action='marcar_enviado.php'>
                            <input type='hidden' name='id_movimento' value='" . $row['id_movimento'] . "'>
                            <button type='submit'>Marcar Enviado</button>
                          </form></td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <script src="./js/script.js"></script>
</body>
</html>
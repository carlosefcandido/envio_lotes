<?php
require_once('conexao.php');
require_once('auth.php'); // Inclui o arquivo auth.php onde a função verificaLogin está definida

verificaLogin();

$id_usuario = $_SESSION['usuario']['id_usuario'];
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-d');
$data_fim = $_GET['data_fim'] ?? date('Y-m-d');

$conn = conectar();

// Consulta para obter os totais por tipo de pagamento
$query_totais = "SELECT t.nome_tipo, SUM(m.valor) as total_valor 
                 FROM movimento m 
                 JOIN tipo_pagamento t ON m.id_tipo = t.id_tipo 
                 WHERE DATE(m.data_salvo) BETWEEN ? AND ?
                 AND m.enviado = 0
                 GROUP BY t.nome_tipo";
$stmt_totais = $conn->prepare($query_totais);
$stmt_totais->bind_param("ss", $data_inicio, $data_fim);
$stmt_totais->execute();
$result_totais = $stmt_totais->get_result();

// Consulta para obter os lançamentos diários
$query_lancamentos = "SELECT m.data_salvo, m.lote, t.nome_tipo, m.valor, u.nome as nome_usuario, b.nome_banco 
                      FROM movimento m 
                      JOIN tipo_pagamento t ON m.id_tipo = t.id_tipo 
                      JOIN usuario u ON m.id_usuario = u.id_usuario 
                      LEFT JOIN banco b ON m.id_banco = b.id_banco 
                      WHERE DATE(m.data_salvo) BETWEEN ? AND ? 
                      AND m.enviado = 0
                      ORDER BY m.data_salvo DESC";
$stmt_lancamentos = $conn->prepare($query_lancamentos);
$stmt_lancamentos->bind_param("ss", $data_inicio, $data_fim);
$stmt_lancamentos->execute();
$result_lancamentos = $stmt_lancamentos->get_result();

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=lancamentos_diarios.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Escrever os totais por tipo de pagamento
echo mb_convert_encoding("Totais por Tipo de Pagamento\n", 'UTF-8');
echo mb_convert_encoding("Tipo de Pagamento\tTotal\n", 'UTF-8');
while ($row = $result_totais->fetch_assoc()) {
    echo mb_convert_encoding("{$row['nome_tipo']}\tR$ " . number_format($row['total_valor'], 2, ',', '.') . "\n", 'UTF-8');
}
echo mb_convert_encoding("\n", 'UTF-8');

// Escrever os lançamentos diários
echo mb_convert_encoding("Lancamentos Diarios\n", 'UTF-8');
echo mb_convert_encoding("Data e Hora\tLote\tTipo\tBanco\tValor\tOperador\n", 'UTF-8');
while ($row = $result_lancamentos->fetch_assoc()) {
    $data_hora_formatada = date('d/m/Y H:i:s', strtotime($row['data_salvo']));
    $nome_banco = $row['nome_banco'] ?? 'N/A'; // Tratar valores nulos para o banco
    echo mb_convert_encoding("{$data_hora_formatada}\t{$row['lote']}\t{$row['nome_tipo']}\t{$nome_banco}\tR$ " . number_format($row['valor'], 2, ',', '.') . "\t{$row['nome_usuario']}\n", 'UTF-8');
}
?>
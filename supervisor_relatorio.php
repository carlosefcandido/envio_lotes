<?php
include_once('conexao.php');

function getRelatorio($data_inicio = null, $data_fim = null) {
    $conn = conectar();
    $sql = "SELECT m.*, t.nome_tipo, u.nome as nome_usuario, b.nome_banco 
            FROM movimento m 
            JOIN tipo_pagamento t ON m.id_tipo = t.id_tipo 
            JOIN usuario u ON m.id_usuario = u.id_usuario
            LEFT JOIN banco b ON m.id_banco = b.id_banco"; // Usar LEFT JOIN para incluir lançamentos sem banco
    
    if ($data_inicio && $data_fim) {
        $sql .= " WHERE DATE(m.data_salvo) BETWEEN ? AND ?";
    }
    
    $sql .= " ORDER BY m.enviado ASC, m.data_salvo DESC";
    
    $stmt = $conn->prepare($sql);
    if ($data_inicio && $data_fim) {
        $stmt->bind_param("ss", $data_inicio, $data_fim);
    }
    $stmt->execute();
    return $stmt->get_result();
}

function getTotaisPorTipo($data_inicio = null, $data_fim = null) {
    $conn = conectar();
    $sql = "SELECT t.nome_tipo, SUM(m.valor) as total_valor 
            FROM movimento m 
            JOIN tipo_pagamento t ON m.id_tipo = t.id_tipo";
    
    if ($data_inicio && $data_fim) {
        $sql .= " WHERE DATE(m.data_salvo) BETWEEN ? AND ?";
    }
    
    $sql .= " GROUP BY t.nome_tipo";
    
    $stmt = $conn->prepare($sql);
    if ($data_inicio && $data_fim) {
        $stmt->bind_param("ss", $data_inicio, $data_fim);
    }
    $stmt->execute();
    return $stmt->get_result();
}

// Lida com a solicitação AJAX para atualizar a tabela
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['data_inicio']) && isset($_POST['data_fim'])) {
    $data_inicio = $_POST['data_inicio'] ?? null;
    $data_fim = $_POST['data_fim'] ?? null;

    $totais = getTotaisPorTipo($data_inicio, $data_fim);
    $result = getRelatorio($data_inicio, $data_fim);

    $response = [
        'totais' => '',
        'movimentos' => ''
    ];

    // Exibir totais por tipo de pagamento
    if ($totais->num_rows > 0) {
        $response['totais'] .= "<h3>Totais por Tipo de Pagamento</h3>";
        $response['totais'] .= "<table id='tabelaTotais'>";
        $response['totais'] .= "<thead><tr><th>Tipo de Pagamento</th><th>Total</th></tr></thead>";
        $response['totais'] .= "<tbody>";
        while ($row = $totais->fetch_assoc()) {
            $response['totais'] .= "<tr>";
            $response['totais'] .= "<td>" . $row['nome_tipo'] . "</td>";
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
        $response['movimentos'] .= "<td>" . $row['nome_banco'] . "</td>";
        $response['movimentos'] .= "<td>R$ " . number_format($row['valor'], 2, ',', '.') . "</td>";
        $response['movimentos'] .= "<td>" . $row['nome_usuario'] . "</td>";
        $response['movimentos'] .= "<td>" . $enviado . "</td>";
        $response['movimentos'] .= "<td><input type='checkbox' class='marcar-enviado' data-id='" . $row['id_movimento'] . "' " . ($row['enviado'] ? 'checked' : '') . "></td>";
        $response['movimentos'] .= "</tr>";
    }

    echo json_encode($response);
    exit;
}
?>
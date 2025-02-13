<?php
require_once 'conexao.php'; // Inclui o arquivo de conexão com o banco de dados

function getTotaisProvisao($data_inicio, $data_fim) {
    $conn = conectar();

    $sql = "SELECT tipo_provisao AS nome_tipo, SUM(valor) AS total_valor
            FROM provisao
            WHERE data_salvo BETWEEN ? AND ?
            GROUP BY tipo_provisao";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $data_inicio, $data_fim);
    $stmt->execute();
    $result = $stmt->get_result();

    $stmt->close();
    $conn->close();

    return $result;
}

function getTotaisFolha($data_inicio, $data_fim) {
    $conn = conectar();

    $sql = "SELECT data_folha, SUM(valor) AS total_valor
            FROM provisao
            WHERE tipo_provisao = 'Folha' AND data_salvo BETWEEN ? AND ?
            GROUP BY data_folha";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $data_inicio, $data_fim);
    $stmt->execute();
    $result = $stmt->get_result();

    $stmt->close();
    $conn->close();

    return $result;
}

function getTotaisProvisaoPorTipoEBanco($data_inicio, $data_fim) {
    $conn = conectar();

    $sql = "SELECT tipo_provisao, b.nome_banco, SUM(p.valor) AS total_valor
            FROM provisao p
            JOIN banco b ON p.id_banco = b.id_banco
            WHERE p.data_salvo BETWEEN ? AND ?
            GROUP BY tipo_provisao, b.nome_banco";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $data_inicio, $data_fim);
    $stmt->execute();
    $result = $stmt->get_result();

    $stmt->close();
    $conn->close();

    return $result;
}

function getProvisoesPorUsuario($data_inicio, $data_fim) {
    $conn = conectar();

    $sql = "SELECT u.nome AS nome_usuario, SUM(p.valor) AS total_valor
            FROM provisao p
            JOIN usuario u ON p.id_usuario = u.id_usuario
            WHERE p.data_salvo BETWEEN ? AND ?
            GROUP BY u.nome";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $data_inicio, $data_fim);
    $stmt->execute();
    $result = $stmt->get_result();

    $stmt->close();
    $conn->close();

    return $result;
}

function getProvisoesDoDia($data_inicio, $data_fim) {
    $conn = conectar();

    $sql = "SELECT u.nome AS nome_usuario, p.tipo_provisao, p.valor, p.data_salvo, p.data_folha, b.nome_banco
            FROM provisao p
            JOIN usuario u ON p.id_usuario = u.id_usuario
            LEFT JOIN banco b ON p.id_banco = b.id_banco
            WHERE p.data_salvo BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $data_inicio, $data_fim);
    $stmt->execute();
    $result = $stmt->get_result();

    $stmt->close();
    $conn->close();

    return $result;
}
?>

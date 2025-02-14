<?php
require_once 'conexao.php'; // Inclui o arquivo de conexão com o banco de dados

function getTotaisProvisao($data_inicio, $data_fim) {
    $conn = conectar();

    $sql = "SELECT t.nome_tipo, SUM(p.valor) AS total_valor
            FROM provisao p
            JOIN tipo_provisao t ON p.id_tipo_provisao = t.id_tipo_provisao
            WHERE DATE(p.data_salvo) BETWEEN ? AND ?
            GROUP BY t.nome_tipo";
    
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

    $sql = "SELECT p.data_folha, SUM(p.valor) AS total_valor
            FROM provisao p
            JOIN tipo_provisao t ON p.id_tipo_provisao = t.id_tipo_provisao
            WHERE t.id_tipo_provisao = '3' AND date(data_salvo) BETWEEN ? AND ?
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

    $sql = "SELECT t.nome_tipo, b.nome_banco, SUM(p.valor) AS total_valor
            FROM provisao p
            JOIN tipo_provisao t ON p.id_tipo_provisao = t.id_tipo_provisao
            JOIN banco b ON p.id_banco = b.id_banco
            WHERE DATE(p.data_salvo) BETWEEN ? AND ?
            GROUP BY t.nome_tipo, b.nome_banco";
    
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
            JOIN tipo_provisao t ON p.id_tipo_provisao = t.id_tipo_provisao
            JOIN usuario u ON p.id_usuario = u.id_usuario
            WHERE DATE(p.data_salvo) BETWEEN ? AND ?
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

    $sql = "SELECT u.nome AS nome_usuario, t.nome_tipo AS tipo_provisao, p.valor, p.data_salvo, p.data_folha, b.nome_banco
            FROM provisao p
            JOIN tipo_provisao t ON p.id_tipo_provisao = t.id_tipo_provisao
            JOIN usuario u ON p.id_usuario = u.id_usuario
            LEFT JOIN banco b ON p.id_banco = b.id_banco
            WHERE DATE(p.data_salvo) BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $data_inicio, $data_fim);
    $stmt->execute();
    $result = $stmt->get_result();

    $stmt->close();
    $conn->close();

    return $result;
}
?>

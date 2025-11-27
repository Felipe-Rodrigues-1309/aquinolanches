<?php
include "conexao.php";

$id   = $_GET['id'] ?? 0;
$data = $_GET['data'] ?? '';

if (!$id || !$data) {
    die("Dados incompletos.");
}

$sql = "UPDATE pedidos SET pago = 1, data_pagamento = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $data, $id);

if ($stmt->execute()) {
    // Atualiza a MESMA página
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
} else {
    // Também apenas recarrega a página
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

<?php
include "conexao.php";

date_default_timezone_set('America/Sao_Paulo');

$nome       = $_POST['nome'];
$id_usuario = $_POST['id_usuario'];  // ID enviado na sessão
$quantidade = $_POST['quantidade'];
$valor      = $_POST['valor'];
$pagamento  = $_POST['pagamento'];

$data_pedido = date("Y-m-d H:i:s");

$sql = "INSERT INTO pedidos (id_usuario, nome, quantidade, valor, pagamento, data_pedido)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isdsss", $id_usuario, $nome, $quantidade, $valor, $pagamento, $data_pedido);

if ($stmt->execute()) {
    header("Location: pagina_inicial.php");
    exit;
} else {
    echo "Erro ao salvar pedido.";
}

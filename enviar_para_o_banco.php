<?php
include "conexao.php"; // Arquivo com conexão ao MySQL


date_default_timezone_set('America/Sao_Paulo');

$nome = $_POST['nome'];
$quantidade = $_POST['quantidade'];
$valor = $_POST['valor'];
$pagamento = $_POST['pagamento'];

// DATA AUTOMÁTICA
$data_pedido = date("Y-m-d H:i:s"); // Formato ideal para MySQL

$sql = "INSERT INTO pedidos (nome, quantidade, valor, pagamento, data_pedido) 
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sdsss", $nome, $quantidade, $valor, $pagamento, $data_pedido);

if ($stmt->execute()) {
    header("Location: pagina_inicial.html");
    exit;
} else {
    echo "Erro ao salvar pedido.";
}
?>

<?php
include "conexao.php";

$id = $_GET['id'];

$sql = "UPDATE pedidos SET pago = 1 WHERE id = $id";
$conn->query($sql);

header("Location: listar_pedidos.php");
exit;

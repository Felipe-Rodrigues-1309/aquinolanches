<?php
include "conexao.php";

$id = $_GET['id'];

$sql = "UPDATE pedidos SET pago = 0 WHERE id = $id";
$conn->query($sql);

header("Location: listar_pedidos.php");
exit;

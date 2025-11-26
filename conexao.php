<?php
$host = "localhost";
$user = "root";
$pass = "";
$banco = "lanches";

$conn = new mysqli($host, $user, $pass, $banco);

if ($conn->connect_errno) {
    die("Erro ao conectar: " . $conn->connect_error);
}
?>

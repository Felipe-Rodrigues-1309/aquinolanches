<?php
include "conexao.php";

$id = $_POST['id'];
$acao = $_POST['acao'];

// Senha mestre
$senha_correta = "1234";

if ($acao === "desmarcar") {
    $senha = $_POST['senha'];

    if ($senha !== $senha_correta) {
        echo "senha_errada";
        exit;
    }

    $sql = "UPDATE pedidos SET pago = 0 WHERE id = $id";
}

if ($acao === "marcar") {
    $sql = "UPDATE pedidos SET pago = 1 WHERE id = $id";
}

if ($conn->query($sql)) {
    echo "ok";
} else {
    echo "erro";
}

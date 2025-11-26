<?php
include "conexao.php";

const ADMIN_PASSWORD = '130915';

$acao  = $_GET['acao'] ?? '';
$id    = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$senha = $_GET['senha'] ?? '';

if ($id <= 0 || $acao === '') {
    header("Location: listar_pedidos.php");
    exit;
}

$sql = "";
$precisaSenha = false;

switch ($acao) {
    case 'marcar': // marcar como pago (sem senha)
        $sql = "UPDATE pedidos SET pago = 1 WHERE id = ?";
        $precisaSenha = false;
        break;

    case 'desmarcar': // voltar para pendente (com senha)
        $sql = "UPDATE pedidos SET pago = 0 WHERE id = ?";
        $precisaSenha = true;
        break;

    case 'excluir': // excluir registro (com senha)
        $sql = "DELETE FROM pedidos WHERE id = ?";
        $precisaSenha = true;
        break;

    default:
        header("Location: listar_pedidos.php");
        exit;
}

// Verifica senha se precisar
if ($precisaSenha) {
    if ($senha !== ADMIN_PASSWORD) {
        die("Senha incorreta.");
    }
}

// Executa
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: listar_pedidos.php");
exit;

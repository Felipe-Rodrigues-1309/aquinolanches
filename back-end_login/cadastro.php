<?php
include "../conexao.php";

if (!isset($_POST['usuario'], $_POST['senha'])) {
    echo "<script>alert('Dados incompletos!'); window.history.back();</script>";
    exit();
}

$usuario = trim($_POST['usuario']);
$senhaPlain = $_POST['senha'];

if ($usuario === "" || $senhaPlain === "") {
    echo "<script>alert('Usuário e senha não podem estar vazios.'); window.history.back();</script>";
    exit();
}

$senha = password_hash($senhaPlain, PASSWORD_DEFAULT);

// 1. Verificar se usuário já existe
$sqlCheck = "SELECT id FROM usuarios WHERE usuario = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("s", $usuario);
$stmtCheck->execute();
$stmtCheck->store_result();

if ($stmtCheck->num_rows > 0) {
    echo "<script>alert('Esse usuário já existe! Escolha outro nome.'); window.history.back();</script>";
    exit();
}

// 2. Inserir usuário
$sql = "INSERT INTO usuarios (usuario, senha) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $usuario, $senha);

if ($stmt->execute()) {
    echo "<script>alert('Usuário cadastrado com sucesso!'); window.location.href='../pagina_de_login.html';</script>";
} else {
    $erro = addslashes($conn->error);
    echo "<script>alert('Erro ao cadastrar usuário: {$erro}'); window.history.back();</script>";
}

$stmtCheck->close();
$stmt->close();
$conn->close();
?>

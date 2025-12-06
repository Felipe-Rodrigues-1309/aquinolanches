<?php
session_start();
session_unset();
session_destroy();
session_start();

include "../conexao.php";  // AJUSTE SE O CAMINHO FOR OUTRO

if (!isset($_POST['usuario'], $_POST['senha'])) {
    die("Dados incompletos.");
}

$usuario = $_POST['usuario'];
$senhaDigitada = $_POST['senha'];

$sql = "SELECT * FROM usuarios WHERE usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();

$resultado = $stmt->get_result();
$user = $resultado->fetch_assoc();

if ($user && password_verify($senhaDigitada, $user['senha'])) {

    // SALVA NA SESSÃO
    $_SESSION['id_usuario'] = $user['id'];
    $_SESSION['usuario']    = $user['usuario'];

    header("Location: ../pagina_inicial.php");
    exit();

} else {
    echo "<script>alert('Usuário ou Senha Incorretos!'); window.location.href='../pagina_de_login.html';</script>";
}

$stmt->close();
$conn->close();

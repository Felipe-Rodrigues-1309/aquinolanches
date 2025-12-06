<?php
session_start();
include "../conexao.php";

$mensagem = "";

if (isset($_POST['usuario'])) {
    $usuario = $_POST['usuario'];

    // Verifica se o usuário existe
    $sql = "SELECT id FROM usuarios WHERE usuario=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $user = $resultado->fetch_assoc();

    if ($user) {
        // Gera token seguro
        $token = bin2hex(random_bytes(32));
        $expiracao = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Salva no banco
        $sqlToken = "INSERT INTO redefinir_senha (id_usuario, token, expiracao) VALUES (?, ?, ?)";
        $stmtToken = $conn->prepare($sqlToken);
        $stmtToken->bind_param("iss", $user['id'], $token, $expiracao);
        $stmtToken->execute();

        // Aqui você pode redirecionar direto para redefinir senha usando token
        // ou exibir um link na tela
        $link = "http://localhost/aquinolanches/back-end_login/redefinir_senha.php?token=$token";
        $mensagem = "Usuário encontrado! <a href='$link'>Clique aqui para redefinir sua senha</a>.";
    } else {
        $mensagem = "Usuário não encontrado.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Esqueci minha senha</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-4">

            <?php if ($mensagem != ""): ?>
                <div class="alert alert-info"><?php echo $mensagem; ?></div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Esqueci minha senha</h3>
                    <form method="post" action="">
                        <div class="mb-3">
                            <input type="text" name="usuario" class="form-control" placeholder="Digite seu usuário" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Continuar</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>

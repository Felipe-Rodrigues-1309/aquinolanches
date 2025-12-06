<?php
session_start();
include "../conexao.php";

$mensagem = "";
$mostrarForm = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verifica token válido e não expirado
    $sql = "SELECT id_usuario FROM redefinir_senha WHERE token=? AND expiracao>=NOW() AND usado=0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $registro = $resultado->fetch_assoc();

    if ($registro) {
        $mostrarForm = true;

        if (isset($_POST['senha'], $_POST['confirma_senha'])) {
            $senha = $_POST['senha'];
            $confirma = $_POST['confirma_senha'];

            if ($senha === $confirma) {
                $hash = password_hash($senha, PASSWORD_DEFAULT);

                // Atualiza senha no usuário
                $sqlUpdate = "UPDATE usuarios SET senha=? WHERE id=?";
                $stmtUpdate = $conn->prepare($sqlUpdate);
                $stmtUpdate->bind_param("si", $hash, $registro['id_usuario']);
                $stmtUpdate->execute();

                // Marca token como usado
                $sqlToken = "UPDATE redefinir_senha SET usado=1 WHERE token=?";
                $stmtToken = $conn->prepare($sqlToken);
                $stmtToken->bind_param("s", $token);
                $stmtToken->execute();

                $mensagem = "Senha redefinida com sucesso! <a href='../pagina_de_login.html'>Faça login</a>.";
                $mostrarForm = false;
            } else {
                $mensagem = "As senhas não coincidem!";
            }
        }

    } else {
        $mensagem = "Token inválido ou expirado.";
    }

    $stmt->close();
    $conn->close();
} else {
    $mensagem = "Token não fornecido.";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Redefinir senha</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-4">

            <?php if ($mensagem != ""): ?>
                <div class="alert alert-info"><?php echo $mensagem; ?></div>
            <?php endif; ?>

            <?php if ($mostrarForm): ?>
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">Redefinir senha</h3>
                        <form method="post" action="">
                            <div class="mb-3">
                                <input type="password" name="senha" class="form-control" placeholder="Nova senha" required>
                            </div>
                            <div class="mb-3">
                                <input type="password" name="confirma_senha" class="form-control" placeholder="Confirme a senha" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Redefinir senha</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
</body>
</html>

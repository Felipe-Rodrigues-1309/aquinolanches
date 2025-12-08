<?php
session_start();
include "../conexao.php";

$mensagem = "";

if (isset($_POST['usuario'])) {
    $usuario = $_POST['usuario'];

    $sql = "SELECT id FROM usuarios WHERE usuario=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $user = $resultado->fetch_assoc();

    if ($user) {

        $token = bin2hex(random_bytes(32));
        $expiracao = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $sqlToken = "INSERT INTO redefinir_senha (id_usuario, token, expiracao) VALUES (?, ?, ?)";
        $stmtToken = $conn->prepare($sqlToken);
        $stmtToken->bind_param("iss", $user['id'], $token, $expiracao);
        $stmtToken->execute();

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-image: linear-gradient(to left bottom, #000000, #084b26ff);
            background-size: cover;
            background-position: center;
            height: 100vh;
            margin: 0;
            padding-top: 80px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            overflow: hidden;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 18px;
            padding: 35px;
            color: white;
            box-shadow: 0 0 30px rgba(0, 0, 0, .4);
            border: 1px solid rgba(255, 255, 255, 0.25);
        }

        .icon-top {
            font-size: 65px;
            color: #00ff88;
            display: block;
            text-align: center;
            margin-bottom: 15px;
            animation: spin 4s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        input.form-control {
            height: 48px;
            border-radius: 12px;
            border: none;
            padding-left: 12px;
        }

        button.btn-primary {
            height: 48px;
            border-radius: 12px;
            border: none;
            background-color: #00cc66;
            font-weight: bold;
        }

        button.btn-primary:hover {
            background-color: #00994d;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">

            <div class="col-md-4">

                <?php if ($mensagem != ""): ?>
                    <div class="alert alert-info text-center">
                        <?php echo $mensagem; ?>
                    </div>
                <?php endif; ?>

                <div class="glass-card text-center">

                    <i class="bi bi-ui-checks-grid icon-top"></i>

                    <h3 class="mb-4">Esqueci minha senha</h3>

                    <form method="post">

                        <div class="mb-3">
                            <input type="text" name="usuario" class="form-control" placeholder="Digite seu usuário" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Continuar
                        </button>

                    </form>

                </div>

            </div>
        </div>
    </div>

</body>

</html>

<?php
session_start();
include "../conexao.php";

$mensagem = "";
$mostrarForm = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

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

                $sqlUpdate = "UPDATE usuarios SET senha=? WHERE id=?";
                $stmtUpdate = $conn->prepare($sqlUpdate);
                $stmtUpdate->bind_param("si", $hash, $registro['id_usuario']);
                $stmtUpdate->execute();

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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Redefinir senha</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
  background-image: linear-gradient(to left bottom, #000000, #084b26ff);
  height: 100vh;
  margin: 0;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding-top: 80px;
  overflow: hidden;
}

/* CARD */
.card-glass {
    background: rgba(255, 255, 255, 0);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-radius: 22px;
    border: 1px solid rgba(255, 255, 255, 0.25);
    box-shadow: 0 8px 25px rgba(0,0,0,0.4);
    padding: 28px;
    width: 90%;
    max-width: 450px;
    animation: fadeIn 0.8s ease;
}

/* Ícone de cadeado girando */
.lock-icon {
    font-size: 50px;
    color: white;
    display: flex;
    justify-content: center;
    margin-bottom: 15px;
    animation: spin 2.5s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}

/* Inputs */
.card-glass input {
    background: rgba(255,255,255,0.2);
    border: none;
    border-radius: 12px;
    color: #fff;
}

.card-glass input::placeholder {
    color: #e0e0e0;
}

/* Barra força da senha */
.strength-bar {
    height: 7px;
    border-radius: 4px;
    margin-top: -7px;
    margin-bottom: 10px;
    transition: 0.3s;
}

.strength-label {
    color: #fff;
    font-size: 14px;
}

/* Botão */
.card-glass button {
    border-radius: 12px;
    font-weight: bold;
}

/* Fade */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-15px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>

</head>
<body>

<div class="card-glass">

    <!-- Ícone animado -->
    <div class="lock-icon">
        <img src="../img/redefinir-senha.png" alt="" width="90px">
    </div>

    <?php if ($mensagem != ""): ?>
        <div class="alert alert-info text-center"><?php echo $mensagem; ?></div>
    <?php endif; ?>

    <?php if ($mostrarForm): ?>

        <h3 class="text-center text-white mb-4">Redefinir senha</h3>

        <form method="post" action="">

            <input type="password" id="senha" name="senha" class="form-control form-control-lg" placeholder="Nova senha" required>

            <div class="strength-bar" id="strengthBar"></div>
            <div class="strength-label" id="strengthLabel"></div>

            <div class="mb-3 mt-3">
                <input type="password" name="confirma_senha" class="form-control form-control-lg" placeholder="Confirme a senha" required>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-light fw-bold">Redefinir senha</button>
            </div>

        </form>

    <?php endif; ?>

</div>

<script>
const senhaInput = document.getElementById("senha");
const bar = document.getElementById("strengthBar");
const label = document.getElementById("strengthLabel");

senhaInput.addEventListener("input", function() {
    let senha = senhaInput.value;
    let força = 0;

    if (senha.length >= 6) força++;
    if (/[A-Z]/.test(senha)) força++;
    if (/[0-9]/.test(senha)) força++;
    if (/[^A-Za-z0-9]/.test(senha)) força++;

    bar.style.width = força * 25 + "%";

    if (força === 0) {
        bar.style.background = "transparent";
        label.textContent = "";
    }
    else if (força === 1) {
        bar.style.background = "red";
        label.textContent = "Senha fraca";
    }
    else if (força === 2) {
        bar.style.background = "orange";
        label.textContent = "Senha média";
    }
    else if (força === 3) {
        bar.style.background = "yellow";
        label.textContent = "Senha boa";
    }
    else if (força === 4) {
        bar.style.background = "limegreen";
        label.textContent = "Senha forte";
    }
});
</script>

</body>
</html>

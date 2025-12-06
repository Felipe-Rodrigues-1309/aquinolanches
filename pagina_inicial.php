<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}

$nomeLogado = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Pedido</title>

<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Dutra Lanches</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
      <div class="navbar-nav">
        <a class="nav-link active" aria-current="page" href="listar_seus_pedidos.php">Seus Pedidos</a>
        <a class="nav-link active" href="https://wa.me/5588997443499?text=Ol%C3%A1%2C%20tenho%20uma%20Duvida !">Duvidas ? </a>
        <a class="nav-link active" href="pagina_de_login.html">Sair</a>
      </div>
    </div>
  </div>
</nav>
    <style>
        body{ background-color: #000; }
        .card {
            background-image: linear-gradient(to left bottom, #000000, #14084b);
            color: white;
            text-align: center;
            border: solid 1px rgb(255, 255, 255);
            max-width: 380px;
            margin: 25px auto;
            padding: 8px;
            font-family: 'Times New Roman', Times, serif;
            font-size: large
        }
        input{ font-size: large; }
        .botao {
            margin-top: 30px;
            margin-bottom: 10px;
            width: 220px;
            height: 50px;
            border: none;
            outline: none;
            color: #fff;
            background: #111;
            cursor: pointer;
            font-size: x-large;
            position: relative;
            z-index: 0;
            border-radius: 15px;
        }
        .botao:before{
            content: '';
            background: linear-gradient(45deg, #00ff00, #2704ec5d, #00ff00);
            position: absolute;
            top: -2px;
            left: -2px;
            background-size: 400%;
            z-index: -1;
            filter: blur(1px);
            width: calc(100% + 4px);
            height: calc(100% + 4px);
            animation: glowing 8s linear infinite;
            opacity: 1;
            border-radius: 10px;
        }
        .card:before {
            content: '';
            background: linear-gradient(45deg, #ff0000, #0400ff, #00ff00);
            position: absolute;
            top: -2px;
            left: -2px;
            background-size: 400%;
            z-index: -1;
            filter: blur(20px);
            width: calc(100% + 10px);
            height: calc(100% + 10px);
            animation: glowing 8s linear infinite;
            opacity: 1;
            border-radius: 10px;
        }
        .botao:after {
            z-index: -2;
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: #111;
            left: 0;
            top: 0;
            border-radius: 10px;
        }
        @keyframes glowing {
            0% { background-position: 0 0; }
            50% { background-position: 400% 0; }
            100% { background-position: 0 0; }
        }
        .pix-box {
            background: #f1f1f1;
            padding: 10px;
            border-radius: 8px;
            margin-top: 10px;
            display: none;
        }
        .copy-btn {
            margin-top: 5px;
            background-color: #000;
            padding: 2px 8px;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-left: 5px;
        }
    </style>
</head>
<body>

<div class="card shadow">
<h1>Salgado</h1>
<h3>Valor: R$ 3,50</h3>

    <form id="formPedido" action="enviar_para_o_banco.php" method="POST">

        <label class="form-label">Nome:</label>
        <input 
            type="text" 
            id="nome" 
            name="nome" 
            class="form-control" 
            value="<?php echo $nomeLogado; ?>" 
            readonly
        >
        <input type="hidden" name="id_usuario" value="<?php echo $_SESSION['id_usuario']; ?>">


        <label class="form-label mt-3">Quantidade:</label>
        <input type="number" id="quantidade" name="quantidade" class="form-control" required>

        <label class="form-label mt-3">Valor:</label>
        <input type="number" id="valor" name="valor" class="form-control" readonly required>

        <label class="form-label mt-3">Forma de Pagamento:</label>
        <select id="pagamento" name="pagamento" class="form-select" required>
            <option value="">Selecione</option>
            <option value="pix">PIX</option>
            <option value="dinheiro">Dinheiro</option>
            <option value="promissoria">Promissória</option>
        </select>

        <div id="pixArea" class="pix-box">
            <p><strong class="text-dark">Chave PIX (Celular):</strong> 
                <span id="cpfPix" class="text-dark">88997443499</span>
                <button type="button" class="copy-btn" onclick="copiarPix()">Copiar</button>
            </p>
            <p class="text-danger"><strong>⚠ Envie o comprovante pelo WhatsApp após concluir o PIX.</strong></p>
            <input type="hidden" name="comprovante_msg" value="Aguardando comprovante PIX enviado pelo WhatsApp">
        </div>

        <button type="button" class="botao" onclick="enviarWhats()">Enviar</button>
    </form>
</div>

<script>
    document.getElementById("quantidade").addEventListener("input", function () {
        let q = parseFloat(this.value);
        document.getElementById("valor").value = q > 0 ? (q * 3.5).toFixed(2) : "";
    });

    document.getElementById("pagamento").addEventListener("change", function () {
        document.getElementById("pixArea").style.display = (this.value === "pix") ? "block" : "none";
    });

    function copiarPix() {
        navigator.clipboard.writeText("88997443499");
        alert("Chave PIX copiada!");
    }

    function enviarWhats() {
        if (!document.getElementById("formPedido").checkValidity()) {
            document.getElementById("formPedido").reportValidity();
            return;
        }

        let nome = document.getElementById("nome").value;
        let quantidade = document.getElementById("quantidade").value;
        let valor = document.getElementById("valor").value;
        let pagamento = document.getElementById("pagamento").value;

        let msg =
`*Nova Compra de ${quantidade} Salgado(s)*  
Nome: ${nome}
Quantidade: ${quantidade}
Valor Total: R$ ${valor}
Pagamento: ${pagamento.toUpperCase()}

${pagamento === "pix" ? "⚠ Envie o comprovante do PIX após o pagamento." : ""}`;

        window.open("https://wa.me/5588997443499?text=" + encodeURIComponent(msg), "_blank");

        setTimeout(() => {
            document.getElementById("formPedido").submit();
        }, 500);
    }
</script>

<!-- Scripts do Bootstrap no final do body -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // seu JS continua aqui
</script>

</body>
</html>

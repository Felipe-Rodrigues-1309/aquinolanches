<?php
session_start();
include "conexao.php";

// Verifica se usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: pagina_de_login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// --------- FILTROS ---------
$data_inicio = $_GET['inicio'] ?? '';
$data_fim    = $_GET['fim'] ?? '';
$pagamento   = $_GET['pagamento'] ?? '';
$nome        = $_GET['nome'] ?? '';

// Monta condições de filtro
$condicoes = ["id_usuario = {$id_usuario}"];
if ($data_inicio !== '') $condicoes[] = "data_pedido >= '{$data_inicio} 00:00:00'";
if ($data_fim !== '') $condicoes[] = "data_pedido <= '{$data_fim} 23:59:59'";
if ($pagamento !== '') $condicoes[] = "pagamento = '{$pagamento}'";
if ($nome !== '') $condicoes[] = "nome LIKE '%" . $conn->real_escape_string($nome) . "%'";

$where = 'WHERE ' . implode(' AND ', $condicoes);

// --------- BUSCA PRINCIPAL ---------
$sql = "SELECT * FROM pedidos {$where} ORDER BY data_pedido DESC";
$result = $conn->query($sql);

// --------- TOTAIS FILTRADOS ---------
$sqlTotais = "
SELECT
    SUM(valor) AS total_geral,
    SUM(CASE WHEN pago = 1 THEN valor END) AS total_pago,
    SUM(CASE WHEN pago = 0 THEN valor END) AS total_pendente
FROM pedidos {$where}";

$totais = $conn->query($sqlTotais)->fetch_assoc();
$totalGeral    = $totais['total_geral'] ?? 0;
$totalPago     = $totais['total_pago'] ?? 0;
$totalPendente = $totais['total_pendente'] ?? 0;

// Chave PIX fixa
$chavePix = "88997443499";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Meus Pedidos</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Dutra Lanches</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
      <div class="navbar-nav">
        <a class="nav-link active" aria-current="page" href="pagina_inicial.php">Fazer Pedido</a>
        <a class="nav-link active" href="https://wa.me/5588997443499?text=Ol%C3%A1%2C%20Segue%20Comprovante%20Pix !">Enviar Comprovante</a>
        <a class="nav-link active" href="https://wa.me/5588997443499?text=Ol%C3%A1%2C%20tenho%20uma%20Duvida !">Duvidas ? </a>
        <a class="nav-link active" href="pagina_de_login.html">Sair</a>
      </div>
    </div>
  </div>
</nav>
<style>
body {
    background-image: linear-gradient(to left bottom, #051937, #004d7a, #051937, #004d7a, #090220ff);
    color: white;
}
h2 { color:white; }
body label { color:white; }
.container { max-width: 1200px; margin-top: 30px; }

/* Totais */
.total-box {
    background: #000000cc;
    padding: 15px;
    border-radius: 10px;
    border: 2px solid #00ff00cc;
    margin-bottom: 20px;
}

/* Cards */
.card-pedido {
    background: #000000cc;
    border: 2px solid #00ff00cc;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 15px;
}
.card-pedido h5 { margin-bottom: 10px; }
.card-pedido p { margin: 0; }
.card-actions { margin-top: 10px; }

/* Botão copiar PIX */
.btn-pix {
    background-color: #00ff00;
    color: black;
    font-weight: bold;
}

/* Responsivo */
@media (max-width:768px){
    .card-pedido{ font-size: 0.9rem; padding: 10px; }
}
</style>

<script>
function copiarPix(chave){
    navigator.clipboard.writeText(chave)
        .then(() => alert("Chave PIX (Celular) copiada, Por favor enviar o comprovante!"))
        .catch(err => alert("Erro ao copiar: "+err));
}

</script>
</head>
<body>
<div class="container">
<h2 class="text-center mb-4">Meus Pedidos</h2>



<!-- FILTROS -->
<form method="GET" class="row g-2 mb-3 align-items-end p-2 bg-darck rounded shadow-sm">
    <div class="col-auto">
        <label class="form-label small text-light">Data Início:</label>
        <input type="date" name="inicio" class="form-control form-control-sm" value="<?=htmlspecialchars($data_inicio)?>">
    </div>
    <div class="col-auto">
        <label class="form-label small text-light">Data Fim:</label>
        <input type="date" name="fim" class="form-control form-control-sm" value="<?=htmlspecialchars($data_fim)?>">
    </div>
    <div class="col-auto">
        <label class="form-label small text-light">Pagamento:</label>
        <select name="pagamento" class="form-select form-select-sm">
            <option value="">Todos</option>
            <option value="pix" <?= $pagamento==='pix'?'selected':''?>>PIX</option>
            <option value="dinheiro" <?= $pagamento==='dinheiro'?'selected':''?>>Dinheiro</option>
            <option value="promissoria" <?= $pagamento==='promissoria'?'selected':''?>>Promissória</option>
        </select>
    </div>
    <div class="col-auto">
        <button class="btn btn-warning btn-sm w-100" type="submit">Filtrar</button>
    </div>
</form>
<style>
    form.row {
        border: 1px solid #00ff00;       /* Borda suave */
        background-color: #000000ff;    /* Fundo claro */
    }
    form.row .form-control, 
    form.row .form-select {
        border-radius: 0.25rem;       /* Inputs arredondados */
    }
    form.row .btn {
        border-radius: 0.25rem;
    }
    form.row label {
        margin-bottom: 0.25rem;
    }
</style>

<!-- TOTAIS -->
<div class="d-flex justify-content-between text-center total-box">
    <div class="flex-fill mx-2">
        <h5>Total Geral</h5>
        <strong>R$ <?=number_format($totalGeral,2,',','.')?></strong>
    </div>
    <div class="flex-fill mx-2">
        <h5>Pago</h5>
        <strong class="text-success">R$ <?=number_format($totalPago,2,',','.')?></strong>
    </div>
    <div class="flex-fill mx-2">
        <h5>Pendente</h5>
        <strong class="text-danger">R$ <?=number_format($totalPendente,2,',','.')?></strong>
    </div>
</div>




<!-- CARDS -->
<?php if($result && $result->num_rows>0): ?>
    <?php while($row=$result->fetch_assoc()): ?>
        <div class="card-pedido">
            <h5><?=htmlspecialchars($row['nome'])?></h5>
            <p><strong>ID:</strong> <?=$row['id_usuario']?> | <strong>Qtd:</strong> <?=$row['quantidade']?> | <strong>Valor:</strong> R$ <?=number_format($row['valor'],2,',','.')?></p>
            <p><strong>Pagamento:</strong> <?=ucfirst($row['pagamento'])?> | <strong>Emissão:</strong> <?=date("d/m/Y H:i", strtotime($row['data_pedido']))?></p>
            <p><strong>Status:</strong> <?=$row['pago']==1?'<span class="badge bg-success">Pago</span>':'<span class="badge bg-danger">Pendente</span>'?></p>
            <p><strong>Data Pgto:</strong> <?=$row['data_pagamento']?date("d/m/Y", strtotime($row['data_pagamento'])):'-'?></p>
            <div class="card-actions">
                <?php if($row['pago']==0 && $row['pagamento']=='promissoria'): ?>
                    <button class="btn btn-pix btn-sm" onclick="copiarPix('<?=$chavePix?>')">Pagar Agora ? </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p class="text-center text-danger">Nenhum pedido encontrado.</p>
<?php endif; ?>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

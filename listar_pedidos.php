<?php
include "conexao.php";

// --------- FILTROS ---------
$data_inicio = $_GET['inicio'] ?? '';
$data_fim    = $_GET['fim'] ?? '';
$pagamento   = $_GET['pagamento'] ?? '';
$nome        = $_GET['nome'] ?? '';

// Monta condições de filtro
$condicoes = [];

if ($data_inicio !== '') {
    $condicoes[] = "data_pedido >= '{$data_inicio} 00:00:00'";
}

if ($data_fim !== '') {
    $condicoes[] = "data_pedido <= '{$data_fim} 23:59:59'";
}

if ($pagamento !== '') {
    $condicoes[] = "pagamento = '{$pagamento}'";
}

if ($nome !== '') {
    $nomeEsc = $conn->real_escape_string($nome);
    $condicoes[] = "nome LIKE '%{$nomeEsc}%'";
}

// Monta cláusula WHERE única
$where = '';
if (count($condicoes) > 0) {
    $where = 'WHERE ' . implode(' AND ', $condicoes);
}

// --------- BUSCA PRINCIPAL ---------
$sql = "SELECT * FROM pedidos {$where} ORDER BY data_pedido DESC";
$result = $conn->query($sql);

// --------- TOTAIS FILTRADOS ---------
$sqlTotais = "
    SELECT
        SUM(valor) AS total_geral,
        SUM(CASE WHEN pago = 1 THEN valor END) AS total_pago,
        SUM(CASE WHEN pago = 0 THEN valor END) AS total_pendente
    FROM pedidos
    {$where}
";

$totais = $conn->query($sqlTotais)->fetch_assoc();

$totalGeral     = $totais['total_geral']     ?? 0;
$totalPago      = $totais['total_pago']      ?? 0;
$totalPendente  = $totais['total_pendente']  ?? 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Pedidos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />

    <style>
        body{
            background-image: linear-gradient(to left bottom, #051937, #004d7a, #051937, #004d7a, #090220ff);
        }
        h2{
            color:white;
        }
        body
        label{
            color:white;
            
        }
        .container { max-width: 1400px; margin-top: 30px; }
        .total-box {
            background: #000000ff;
            padding: 15px;
            border-radius: 10px;
            border: 3px solid #00ff00ff;
            color: white;
        }
    </style>

    <script>
        let acaoAtual = "";
        let idAtual = 0;

        function abrirModalSenha(acao, id) {
            acaoAtual = acao; // 'desmarcar' ou 'excluir'
            idAtual = id;
            document.getElementById("senha").value = "";
            const modal = new bootstrap.Modal(document.getElementById('modalSenha'));
            modal.show();
        }

        function confirmarAcao() {
            const senha = document.getElementById("senha").value;

            if (!senha) {
                alert("Digite a senha.");
                return;
            }

            window.location.href = "acao_pedido.php?acao=" + encodeURIComponent(acaoAtual) +
                                   "&id=" + encodeURIComponent(idAtual) +
                                   "&senha=" + encodeURIComponent(senha);
        }
    </script>
</head>
<body>

<div class="container">
    <h2 class="text-center mb-4">Contas a Receber</h2>

    <!-- TOTAIS FILTRADOS -->
    <div class="row text-center total-box mb-4">
        <div class="col-md-4">
            <h5>Total Geral</h5>
            <strong>R$ <?= number_format($totalGeral, 2, ',', '.') ?></strong>
        </div>
        <div class="col-md-4">
            <h5>Total Pago</h5>
            <strong class="text-success">R$ <?= number_format($totalPago, 2, ',', '.') ?></strong>
        </div>
        <div class="col-md-4">
            <h5>Total Pendente</h5>
            <strong class="text-danger">R$ <?= number_format($totalPendente, 2, ',', '.') ?></strong>
        </div>
    </div>
    <!-- FILTROS -->
    <form method="GET" class="row g-3 mb-4">

        <div class="col-md-3">
            <label class="form-label">Data Início:</label>
            <input type="date" name="inicio" class="form-control" value="<?= htmlspecialchars($data_inicio) ?>">
        </div>

        <div class="col-md-3">
            <label class="form-label">Data Fim:</label>
            <input type="date" name="fim" class="form-control" value="<?= htmlspecialchars($data_fim) ?>">
        </div>

        <div class="col-md-3">
            <label class="form-label">Forma de Pagamento:</label>
            <select name="pagamento" class="form-select">
                <option value="">Todos</option>
                <option value="pix"        <?= $pagamento === 'pix' ? 'selected' : '' ?>>PIX</option>
                <option value="dinheiro"   <?= $pagamento === 'dinheiro' ? 'selected' : '' ?>>Dinheiro</option>
                <option value="promissoria"<?= $pagamento === 'promissoria' ? 'selected' : '' ?>>Promissória</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Nome:</label>
            <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($nome) ?>" placeholder="Buscar por nome">
        </div>

        <div class="col-12">
            <button class="btn btn-warning w-100" type="submit">Filtrar</button>
        </div>

    </form>

    <!-- TABELA -->
    <table class="table table-hover">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Qtd</th>
                <th>Valor</th>
                <th>Pagamento</th>
                <th>Data</th>
                <th>Status</th>
                <th style="width: 220px;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['nome']) ?></td>
                        <td><?= $row['quantidade'] ?></td>
                        <td>R$ <?= number_format($row['valor'], 2, ',', '.') ?></td>
                        <td><?= ucfirst($row['pagamento']) ?></td>
                        <td><?= date("d/m/Y H:i", strtotime($row['data_pedido'])) ?></td>

                        <td>
                            <?php if ($row['pago'] == 1): ?>
                                <span class="badge bg-success">Pago</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Pendente</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if ($row['pago'] == 0): ?>
                                <!-- Marcar como pago (sem senha) -->
                                <a href="acao_pedido.php?acao=marcar&id=<?= $row['id'] ?>"
                                   class="btn btn-success btn-sm mb-1">
                                   Marcar Pago
                                </a>
                            <?php else: ?>
                                <!-- Desmarcar (com senha) -->
                                <button type="button"
                                        class="btn btn-secondary btn-sm mb-1"
                                        onclick="abrirModalSenha('desmarcar', <?= $row['id'] ?>)">
                                    Desmarcar
                                </button>
                            <?php endif; ?>

                            <!-- Excluir (com senha) -->
                            <button type="button"
                                    class="btn btn-danger btn-sm"
                                    onclick="abrirModalSenha('excluir', <?= $row['id'] ?>)">
                                Excluir
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center text-danger">Nenhum pedido encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>

<!-- MODAL DE SENHA -->
<div class="modal fade" id="modalSenha" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Confirmação</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <label class="form-label">Digite a senha de administrador:</label>
        <input type="password" id="senha" class="form-control">
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-danger" onclick="confirmarAcao()">Confirmar</button>
      </div>

    </div>
  </div>
</div>
<br><br>

    <!-- FORMULÁRIO P/ ADICIONAR MANUAL -->


    <form action="enviar_para_o_banco_listar.php" method="POST" class="row g-3 mb-4 p-3 border rounded">
        <div class="col-md-4">
            <label class="form-label">Nome:</label>
            <input type="text" name="nome" class="form-control" required>
        </div>

        <div class="col-md-2">
            <label class="form-label">Quantidade:</label>
            <input type="number" name="quantidade" class="form-control" min="1" required>
        </div>

        <div class="col-md-2">
            <label class="form-label">Valor (R$):</label>
            <input type="number" name="valor" class="form-control" min="0.01" step="0.01" required>
        </div>

        <div class="col-md-2">
            <label class="form-label">Pagamento:</label>
            <select name="pagamento" class="form-select" required>
                <option value="pix">PIX</option>
                <option value="dinheiro">Dinheiro</option>
                <option value="promissoria">Promissória</option>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Data do Pedido:</label>
            <input type="datetime-local" name="data_pedido" class="form-control">
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary w-100">Adicionar Pedido</button>
        </div>
    </form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

/* $mensagem = $erro = '';
$conn = getDB();

$agendamentos = $conn->query("
    SELECT a.id, a.data_hora, c.nome AS cliente, s.nome AS servico, a.preco_cobrado
    FROM agendamento a
    JOIN cliente c ON c.id = a.cliente_id
    JOIN servico s ON s.id = a.servico_id
    LEFT JOIN pagamento p ON p.agendamento_id = a.id
    WHERE a.status IN ('confirmado','concluido') AND p.id IS NULL
    ORDER BY a.data_hora DESC
    LIMIT 50
")->fetch_all(MYSQLI_ASSOC); */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agendamento_id = $_POST['agendamento_id'] ?? '';
    $valor          = $_POST['valor']          ?? '';
    $metodo         = $_POST['metodo']         ?? '';

    if (!$agendamento_id || !$valor || !$metodo) {
        $erro = 'Todos os campos são obrigatórios.';
    } elseif (!is_numeric($valor) || $valor <= 0) {
        $erro = 'Valor inválido.';
    } else {
        $valor = (float)$valor;
        $stmt  = $conn->prepare("INSERT INTO pagamento (id, agendamento_id, valor, metodo, status, pago_em) VALUES (UUID(),?,?,'pago', NOW())");
        // metodo + valor
        $stmt  = $conn->prepare("INSERT INTO pagamento (id, agendamento_id, valor, metodo, status, pago_em) VALUES (UUID(),?,?,?,'pago',NOW())");
        $stmt->bind_param('sds', $agendamento_id, $valor, $metodo);
        if ($stmt->execute()) {
            $mensagem = 'Pagamento registado com sucesso!';
            $_POST = [];
        } else {
            $erro = str_contains($conn->error, 'Duplicate') ? 'Este agendamento já tem pagamento.' : 'Erro: ' . $conn->error;
        }
        $stmt->close();
    }
}
?>

<div class="page-header">
  <h1>Registar Pagamento</h1>
  <p>Confirma o pagamento de um agendamento</p>
</div>

<?php if ($mensagem): ?><div class="alert alert-success">✓ <?= htmlspecialchars($mensagem) ?></div><?php endif; ?>
<?php if ($erro):     ?><div class="alert alert-error">✕ <?= htmlspecialchars($erro) ?></div><?php endif; ?>

<div class="card">
  <div class="card-title">Dados do pagamento</div>
  <form method="POST">
    <div class="form-grid">
      <div class="field full">
        <label>Agendamento *</label>
        <select name="agendamento_id" required id="sel-ag">
          <option value="">— Seleccione —</option>
          <?php foreach ($agendamentos as $a): ?>
          <option value="<?= $a['id'] ?>"
            data-valor="<?= $a['preco_cobrado'] ?>"
            <?= ($_POST['agendamento_id']??'')===$a['id']?'selected':'' ?>>
            <?= date('d/m/Y H:i', strtotime($a['data_hora'])) ?> — <?= htmlspecialchars($a['cliente']) ?> — <?= htmlspecialchars($a['servico']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Valor (MZN) *</label>
        <input type="number" name="valor" id="inp-valor" value="<?= htmlspecialchars($_POST['valor'] ?? '') ?>" required min="0.01" step="0.01" placeholder="0.00">
      </div>
      <div class="field">
        <label>Método de Pagamento *</label>
        <select name="metodo" required>
          <option value="">— Seleccione —</option>
          <?php foreach (['dinheiro'=>'Dinheiro','cartao_debito'=>'Cartão Débito','cartao_credito'=>'Cartão Crédito','transferencia'=>'Transferência','mpesa'=>'M-Pesa','emola'=>'e-Mola'] as $v=>$l): ?>
          <option value="<?= $v ?>" <?= ($_POST['metodo']??'')===$v?'selected':'' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💰 Confirmar Pagamento</button>
      <a href="index.php" class="btn btn-ghost">← Voltar</a>
    </div>
  </form>
</div>

<script>
document.getElementById('sel-ag').addEventListener('change', function() {
    const v = this.options[this.selectedIndex]?.dataset?.valor;
    if (v) document.getElementById('inp-valor').value = parseFloat(v).toFixed(2);
});
</script>

<?php require_once 'includes/footer.php'; ?>

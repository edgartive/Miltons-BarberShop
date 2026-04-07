<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$mensagem = $erro = '';
$conn = getDB();

$clientes  = $conn->query("SELECT id, nome, telefone FROM cliente ORDER BY nome")->fetch_all(MYSQLI_ASSOC);
$barbeiros = $conn->query("SELECT id, nome FROM barbeiro WHERE ativo=1 ORDER BY nome")->fetch_all(MYSQLI_ASSOC);
$servicos  = $conn->query("SELECT id, nome, preco, duracao_min FROM servico WHERE ativo=1 ORDER BY nome")->fetch_all(MYSQLI_ASSOC);
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id  = $_POST['cliente_id']  ?? '';
    $barbeiro_id = $_POST['barbeiro_id'] ?? '';
    $servico_id  = $_POST['servico_id']  ?? '';
    $data_hora   = str_replace('T', ' ', $_POST['data_hora'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '') ?: null;

    if (!$cliente_id || !$barbeiro_id || !$servico_id || !$data_hora) {
        $erro = 'Preenche todos os campos obrigatórios.';
    } else {
        // Busca duração e preço do serviço
        $svc = $conn->prepare("SELECT duracao_min, preco FROM servico WHERE id=?");
        $svc->bind_param('s', $servico_id);
        $svc->execute();
        $row = $svc->get_result()->fetch_assoc();
        $svc->close();

        $data_hora_fim = date('Y-m-d H:i:s', strtotime($data_hora) + $row['duracao_min'] * 60);
        $preco         = (float)$row['preco'];

        // Verifica conflito de horário
        $conf = $conn->prepare("
            SELECT COUNT(*) AS total FROM agendamento
            WHERE barbeiro_id=?
              AND status NOT IN ('cancelado','nao_compareceu')
              AND data_hora < ? AND data_hora_fim > ?
        ");
        $conf->bind_param('sss', $barbeiro_id, $data_hora_fim, $data_hora);
        $conf->execute();
        $conflitos = $conf->get_result()->fetch_assoc()['total'];
        $conf->close();

        if ($conflitos > 0) {
            $erro = 'O barbeiro já tem um agendamento neste horário.';
        } else {
            $stmt = $conn->prepare("
                INSERT INTO agendamento (id, cliente_id, barbeiro_id, servico_id, data_hora, data_hora_fim, preco_cobrado, observacoes)
                VALUES (UUID(),?,?,?,?,?,?,?)
            ");
            $stmt->bind_param('sssssds', $cliente_id, $barbeiro_id, $servico_id, $data_hora, $data_hora_fim, $preco, $observacoes);
            if ($stmt->execute()) {
                $mensagem = 'Agendamento criado com sucesso!';
                $_POST = [];
            } else {
                $erro = 'Erro ao guardar: ' . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>

<div class="page-header">
  <h1>Novo Agendamento</h1>
  <p>Marca uma consulta para um cliente</p>
</div>

<?php if ($mensagem): ?><div class="alert alert-success">✓ <?= htmlspecialchars($mensagem) ?></div><?php endif; ?>
<?php if ($erro):     ?><div class="alert alert-error">✕ <?= htmlspecialchars($erro) ?></div><?php endif; ?>

<div class="card">
  <div class="card-title">Detalhes do agendamento</div>
  <form method="POST">
    <div class="form-grid">
      <div class="field full">
        <label>Cliente *</label>
        <select name="cliente_id" required>
          <option value="">— Seleccione um cliente —</option>
          <?php foreach ($clientes as $c): ?>
          <option value="<?= $c['id'] ?>" <?= ($_POST['cliente_id']??'')===$c['id']?'selected':'' ?>>
            <?= htmlspecialchars($c['nome']) ?><?= $c['telefone'] ? ' — '.$c['telefone'] : '' ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Barbeiro *</label>
        <select name="barbeiro_id" required>
          <option value="">— Seleccione —</option>
          <?php foreach ($barbeiros as $b): ?>
          <option value="<?= $b['id'] ?>" <?= ($_POST['barbeiro_id']??'')===$b['id']?'selected':'' ?>><?= htmlspecialchars($b['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Serviço *</label>
        <select name="servico_id" required id="sel-servico">
          <option value="">— Seleccione —</option>
          <?php foreach ($servicos as $s): ?>
          <option value="<?= $s['id'] ?>"
            data-preco="<?= $s['preco'] ?>"
            data-dur="<?= $s['duracao_min'] ?>"
            <?= ($_POST['servico_id']??'')===$s['id']?'selected':'' ?>>
            <?= htmlspecialchars($s['nome']) ?> — <?= $s['duracao_min'] ?>min — <?= number_format($s['preco'],2) ?> MZN
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Data e hora *</label>
        <input type="datetime-local" name="data_hora" id="data-hora"
          value="<?= htmlspecialchars($_POST['data_hora'] ?? '') ?>" required>
      </div>

      <div class="full" id="resumo" style="display:none;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:14px 18px;font-size:13.5px;color:var(--muted)">
        ⏱ Fim previsto: <strong id="r-fim" style="color:var(--text)">—</strong> &nbsp;|&nbsp;
        💰 Valor: <strong id="r-preco" style="color:var(--gold)">—</strong>
      </div>

      <div class="field full">
        <label>Observações</label>
        <textarea name="observacoes" placeholder="Notas adicionais para este agendamento..."><?= htmlspecialchars($_POST['observacoes'] ?? '') ?></textarea>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">📅 Confirmar Agendamento</button>
      <a href="index.php" class="btn btn-ghost">← Voltar</a>
    </div>
  </form>
</div>

<script>
const selServico = document.getElementById('sel-servico');
const inputData  = document.getElementById('data-hora');
const resumo     = document.getElementById('resumo');

function actualizarResumo() {
    const opt   = selServico.options[selServico.selectedIndex];
    const dur   = parseInt(opt?.dataset?.dur);
    const preco = parseFloat(opt?.dataset?.preco);
    const dt    = inputData.value;
    if (!dur || !dt) { resumo.style.display = 'none'; return; }
    const fim = new Date(new Date(dt).getTime() + dur * 60000);
    document.getElementById('r-fim').textContent   = fim.toLocaleTimeString('pt', {hour:'2-digit',minute:'2-digit'});
    document.getElementById('r-preco').textContent = preco.toFixed(2) + ' MZN';
    resumo.style.display = 'block';
}
selServico.addEventListener('change', actualizarResumo);
inputData.addEventListener('change',  actualizarResumo);
actualizarResumo();
</script>

<?php require_once 'includes/footer.php'; ?>

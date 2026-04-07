<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
requireRole('admin', 'barbeiro');

$mensagem = $erro = '';
$conn = getDB();

$agendamentos = $conn->query("
    SELECT a.id, a.data_hora, c.nome AS cliente, c.telefone, c.email, b.nome AS barbeiro
    FROM agendamento a
    JOIN cliente  c ON c.id = a.cliente_id
    JOIN barbeiro b ON b.id = a.barbeiro_id
    WHERE a.status IN ('pendente','confirmado')
    ORDER BY a.data_hora ASC LIMIT 50
")->fetch_all(MYSQLI_ASSOC); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agendamento_id = $_POST['agendamento_id'] ?? '';
    $tipo           = $_POST['tipo']           ?? '';
    $canal          = $_POST['canal']          ?? '';
    $destinatario   = trim($_POST['destinatario']  ?? '');
    $agendado_para  = str_replace('T', ' ', $_POST['agendado_para'] ?? '');

    if (!$agendamento_id || !$tipo || !$canal || !$destinatario || !$agendado_para) {
        $erro = 'Preenche todos os campos obrigatórios.';
    } else {
        $stmt = $conn->prepare("INSERT INTO notificacao (id, agendamento_id, tipo, canal, destinatario, agendado_para) VALUES (UUID(),?,?,?,?,?)");
        $stmt->bind_param('sssss', $agendamento_id, $tipo, $canal, $destinatario, $agendado_para);
        if ($stmt->execute()) {
            $mensagem = 'Notificação agendada com sucesso!';
            $_POST = [];
        } else {
            $erro = 'Erro: ' . $conn->error;
        }
        $stmt->close();
    }
}

$tipos  = ['confirmacao'=>'Confirmação','lembrete_24h'=>'Lembrete 24h','lembrete_1h'=>'Lembrete 1h','cancelamento'=>'Cancelamento','reagendamento'=>'Reagendamento'];
$canais = ['email'=>'Email','sms'=>'SMS','push'=>'Push','whatsapp'=>'WhatsApp'];
?>

<div class="page-header">
  <h1>Nova Notificação</h1>
  <p>Agenda o envio de uma notificação ao cliente</p>
</div>

<?php if ($mensagem): ?><div class="alert alert-success">✓ <?= htmlspecialchars($mensagem) ?></div><?php endif; ?>
<?php if ($erro):     ?><div class="alert alert-error">✕ <?= htmlspecialchars($erro) ?></div><?php endif; ?>

<div class="card">
  <div class="card-title">Configurar notificação</div>
  <form method="POST">
    <div class="form-grid">
      <div class="field full">
        <label>Agendamento *</label>
        <select name="agendamento_id" required id="sel-ag">
          <option value="">— Seleccione —</option>
          <?php foreach ($agendamentos as $a): ?>
          <option value="<?= $a['id'] ?>"
            data-email="<?= htmlspecialchars($a['email'] ?? '') ?>"
            data-tel="<?= htmlspecialchars($a['telefone'] ?? '') ?>"
            data-dt="<?= $a['data_hora'] ?>"
            <?= ($_POST['agendamento_id']??'')===$a['id']?'selected':'' ?>>
            <?= date('d/m/Y H:i', strtotime($a['data_hora'])) ?> — <?= htmlspecialchars($a['cliente']) ?> / <?= htmlspecialchars($a['barbeiro']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Tipo *</label>
        <select name="tipo" required id="sel-tipo">
          <option value="">— Seleccione —</option>
          <?php foreach ($tipos as $v=>$l): ?>
          <option value="<?= $v ?>" <?= ($_POST['tipo']??'')===$v?'selected':'' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Canal *</label>
        <select name="canal" required id="sel-canal">
          <option value="">— Seleccione —</option>
          <?php foreach ($canais as $v=>$l): ?>
          <option value="<?= $v ?>" <?= ($_POST['canal']??'')===$v?'selected':'' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field full">
        <label>Destinatário *</label>
        <input type="text" name="destinatario" id="inp-dest" value="<?= htmlspecialchars($_POST['destinatario'] ?? '') ?>" required placeholder="Email ou número de telefone">
      </div>
      <div class="field full">
        <label>Enviar em *</label>
        <input type="datetime-local" name="agendado_para" id="inp-agendado" value="<?= htmlspecialchars($_POST['agendado_para'] ?? '') ?>" required>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">🔔 Agendar Envio</button>
      <a href="index.php" class="btn btn-ghost">← Voltar</a>
    </div>
  </form>
</div>

<script>
const selAg    = document.getElementById('sel-ag');
const selCanal = document.getElementById('sel-canal');
const selTipo  = document.getElementById('sel-tipo');
const inpDest  = document.getElementById('inp-dest');
const inpAg    = document.getElementById('inp-agendado');

function preencherDestinatario() {
    const opt   = selAg.options[selAg.selectedIndex];
    const canal = selCanal.value;
    if (!opt?.dataset?.email) return;
    if (canal === 'email')                          inpDest.value = opt.dataset.email;
    else if (canal === 'sms' || canal === 'whatsapp') inpDest.value = opt.dataset.tel;
}

function preencherHoraEnvio() {
    const opt  = selAg.options[selAg.selectedIndex];
    const tipo = selTipo.value;
    if (!opt?.dataset?.dt) return;
    const dt = new Date(opt.dataset.dt);
    if      (tipo === 'lembrete_24h') dt.setHours(dt.getHours() - 24);
    else if (tipo === 'lembrete_1h')  dt.setHours(dt.getHours() - 1);
    else if (tipo === 'confirmacao')  { inpAg.value = new Date().toISOString().slice(0,16); return; }
    inpAg.value = dt.toISOString().slice(0,16);
}

selAg.addEventListener('change',    () => { preencherDestinatario(); preencherHoraEnvio(); });
selCanal.addEventListener('change', preencherDestinatario);
selTipo.addEventListener('change',  preencherHoraEnvio);
</script>

<?php require_once 'includes/footer.php'; ?>

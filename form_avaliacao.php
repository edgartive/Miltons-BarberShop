<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

 $mensagem = $erro = '';
$conn = getDB();

$agendamentos = $conn->query("
    SELECT a.id, a.data_hora, c.nome AS cliente, b.nome AS barbeiro, s.nome AS servico
    FROM agendamento a
    JOIN cliente  c ON c.id = a.cliente_id
    JOIN barbeiro b ON b.id = a.barbeiro_id
    JOIN servico  s ON s.id = a.servico_id
    LEFT JOIN avaliacao av ON av.agendamento_id = a.id
    WHERE a.status = 'concluido' AND av.id IS NULL
    ORDER BY a.data_hora DESC LIMIT 50
")->fetch_all(MYSQLI_ASSOC); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agendamento_id = $_POST['agendamento_id'] ?? '';
    $nota           = (int)($_POST['nota']     ?? 0);
    $comentario     = trim($_POST['comentario'] ?? '') ?: null;

    if (!$agendamento_id || $nota < 1 || $nota > 5) {
        $erro = 'Seleccione o agendamento e uma nota de 1 a 5.';
    } else {
        // Busca cliente_id e barbeiro_id
        $ag = $conn->prepare("SELECT cliente_id, barbeiro_id FROM agendamento WHERE id=?");
        $ag->bind_param('s', $agendamento_id);
        $ag->execute();
        $row = $ag->get_result()->fetch_assoc();
        $ag->close();

        $stmt = $conn->prepare("INSERT INTO avaliacao (id, agendamento_id, cliente_id, barbeiro_id, nota, comentario) VALUES (UUID(),?,?,?,?,?)");
        $stmt->bind_param('sssss', $agendamento_id, $row['cliente_id'], $row['barbeiro_id'], $nota, $comentario);
        if ($stmt->execute()) {
            $mensagem = 'Avaliação registada com sucesso!';
            $_POST = [];
        } else {
            $erro = str_contains($conn->error,'Duplicate') ? 'Este agendamento já foi avaliado.' : 'Erro: '.$conn->error;
        }
        $stmt->close();
    }
}
?>

<div class="page-header">
  <h1>Nova Avaliação</h1>
  <p>Regista o feedback do cliente após o atendimento</p>
</div>

<?php if ($mensagem): ?><div class="alert alert-success">✓ <?= htmlspecialchars($mensagem) ?></div><?php endif; ?>
<?php if ($erro):     ?><div class="alert alert-error">✕ <?= htmlspecialchars($erro) ?></div><?php endif; ?>

<div class="card">
  <div class="card-title">Feedback</div>
  <form method="POST">
    <div class="form-grid">
      <div class="field full">
        <label>Agendamento *</label>
        <select name="agendamento_id" required>
          <option value="">— Seleccione —</option>
          <?php foreach ($agendamentos as $a): ?>
          <option value="<?= $a['id'] ?>" <?= ($_POST['agendamento_id']??'')===$a['id']?'selected':'' ?>>
            <?= date('d/m/Y', strtotime($a['data_hora'])) ?> — <?= htmlspecialchars($a['cliente']) ?> com <?= htmlspecialchars($a['barbeiro']) ?> — <?= htmlspecialchars($a['servico']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field full">
        <label>Nota *</label>
        <div style="display:flex;gap:8px;margin-top:4px" id="stars">
          <?php for ($i=1;$i<=5;$i++): ?>
          <label style="cursor:pointer;font-size:28px;line-height:1;opacity:0.25;transition:opacity .1s;color:var(--gold)">
            <input type="radio" name="nota" value="<?= $i ?>" style="display:none"
              <?= (int)($_POST['nota']??0)===$i?'checked':'' ?>>
            ★
          </label>
          <?php endfor; ?>
        </div>
      </div>

      <div class="field full">
        <label>Comentário</label>
        <textarea name="comentario" placeholder="O que o cliente achou do atendimento?"><?= htmlspecialchars($_POST['comentario'] ?? '') ?></textarea>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">⭐ Guardar Avaliação</button>
      <a href="index.php" class="btn btn-ghost">← Voltar</a>
    </div>
  </form>
</div>

<script>
const labels = document.querySelectorAll('#stars label');
labels.forEach((lbl, i) => {
    lbl.addEventListener('mouseover', () => labels.forEach((l,j) => l.style.opacity = j<=i?'1':'0.25'));
    lbl.addEventListener('mouseout',  () => highlightSelected());
    lbl.addEventListener('click',     () => highlightSelected());
});
function highlightSelected() {
    const checked = document.querySelector('#stars input:checked');
    const idx = checked ? parseInt(checked.value) - 1 : -1;
    labels.forEach((l,j) => l.style.opacity = j<=idx?'1':'0.25');
}
highlightSelected();
</script>

<?php require_once 'includes/footer.php'; ?>

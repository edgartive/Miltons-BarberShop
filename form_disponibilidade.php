<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
requireRole('admin', 'barbeiro');

 $mensagem = $erro = '';
$conn      = getDB(); 
$barbeiros = $conn->query("SELECT id, nome FROM barbeiro WHERE ativo=1 ORDER BY nome")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barbeiro_id = $_POST['barbeiro_id'] ?? '';
    $dias        = $_POST['dia_semana']  ?? [];
    $hora_inicio = $_POST['hora_inicio'] ?? '';
    $hora_fim    = $_POST['hora_fim']    ?? '';

    if (!$barbeiro_id || !$dias || !$hora_inicio || !$hora_fim) {
        $erro = 'Preenche todos os campos obrigatórios.';
    } elseif ($hora_fim <= $hora_inicio) {
        $erro = 'A hora de fim deve ser posterior à hora de início.';
    } else {
        $stmt      = $conn->prepare("INSERT IGNORE INTO disponibilidade (id, barbeiro_id, dia_semana, hora_inicio, hora_fim) VALUES (UUID(),?,?,?,?)");
        $inseridos = 0;
        foreach ($dias as $dia) {
            $dia = (int)$dia;
            $stmt->bind_param('sisss', $barbeiro_id, $dia, $barbeiro_id, $hora_inicio, $hora_fim);
            // bind correcto: barbeiro_id(s), dia(i), hora_inicio(s), hora_fim(s)
            $stmt->bind_param('siss', $barbeiro_id, $dia, $hora_inicio, $hora_fim);
            $stmt->execute();
            $inseridos += $stmt->affected_rows;
        }
        $stmt->close();
        $mensagem = "$inseridos horário(s) definido(s) com sucesso!";
        $_POST = [];
    }
}

$dias_semana = ['0'=>'Domingo','1'=>'Segunda','2'=>'Terça','3'=>'Quarta','4'=>'Quinta','5'=>'Sexta','6'=>'Sábado'];
?>

<div class="page-header">
  <h1>Disponibilidade</h1>
  <p>Define o horário semanal de um barbeiro</p>
</div>

<?php if ($mensagem): ?><div class="alert alert-success">✓ <?= htmlspecialchars($mensagem) ?></div><?php endif; ?>
<?php if ($erro):     ?><div class="alert alert-error">✕ <?= htmlspecialchars($erro) ?></div><?php endif; ?>

<div class="card">
  <div class="card-title">Horário semanal</div>
  <form method="POST">
    <div class="form-grid">
      <div class="field full">
        <label>Barbeiro *</label>
        <select name="barbeiro_id" required>
          <option value="">— Seleccione —</option>
          <?php foreach ($barbeiros as $b): ?>
          <option value="<?= $b['id'] ?>" <?= ($_POST['barbeiro_id']??'')===$b['id']?'selected':'' ?>><?= htmlspecialchars($b['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field full">
        <label>Dias da semana *</label>
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:4px">
          <?php foreach ($dias_semana as $val => $nome): ?>
          <label style="display:flex;align-items:center;gap:6px;padding:7px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:6px;cursor:pointer;font-size:13px;font-weight:400;text-transform:none;letter-spacing:0;color:var(--text)">
            <input type="checkbox" name="dia_semana[]" value="<?= $val ?>"
              <?= in_array($val, (array)($_POST['dia_semana'] ?? [])) ? 'checked' : '' ?>
              style="accent-color:var(--gold)">
            <?= $nome ?>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="field">
        <label>Hora de início *</label>
        <input type="time" name="hora_inicio" value="<?= htmlspecialchars($_POST['hora_inicio'] ?? '08:00') ?>" required>
      </div>
      <div class="field">
        <label>Hora de fim *</label>
        <input type="time" name="hora_fim" value="<?= htmlspecialchars($_POST['hora_fim'] ?? '18:00') ?>" required>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Guardar</button>
      <a href="index.php" class="btn btn-ghost">← Voltar</a>
    </div>
  </form>
</div>

<?php require_once 'includes/footer.php'; ?>

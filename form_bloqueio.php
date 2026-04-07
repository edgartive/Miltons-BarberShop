<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
requireRole('admin', 'barbeiro');

 $mensagem = $erro = '';
$conn      = getDB(); 

 $barbeiros = $conn->query("SELECT id, nome FROM barbeiro WHERE ativo=1 ORDER BY nome")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barbeiro_id = $_POST['barbeiro_id'] ?? '';
    $data_inicio = $_POST['data_inicio'] ?? '';
    $data_fim    = $_POST['data_fim']    ?? '';
    $motivo      = trim($_POST['motivo'] ?? '') ?: null;

    if (!$barbeiro_id || !$data_inicio || !$data_fim) {
        $erro = 'Barbeiro, data de início e de fim são obrigatórios.';
    } elseif ($data_fim <= $data_inicio) {
        $erro = 'A data/hora de fim deve ser posterior ao início.';
    } else {
        // Converter datetime-local para formato MySQL
        $data_inicio = str_replace('T', ' ', $data_inicio);
        $data_fim    = str_replace('T', ' ', $data_fim);

        $stmt = $conn->prepare("INSERT INTO bloqueio (id, barbeiro_id, data_inicio, data_fim, motivo) VALUES (UUID(),?,?,?,?)");
        $stmt->bind_param('ssss', $barbeiro_id, $data_inicio, $data_fim, $motivo);
        if ($stmt->execute()) {
            $mensagem = 'Bloqueio registado com sucesso!';
            $_POST = [];
        } else {
            $erro = 'Erro ao guardar: ' . $conn->error;
        }
        $stmt->close();
    }
}
?>

<div class="page-header">
  <h1>Bloqueio de Agenda</h1>
  <p>Regista férias, folgas ou pausas pontuais</p>
</div>

<?php if ($mensagem): ?><div class="alert alert-success">✓ <?= htmlspecialchars($mensagem) ?></div><?php endif; ?>
<?php if ($erro):     ?><div class="alert alert-error">✕ <?= htmlspecialchars($erro) ?></div><?php endif; ?>

<div class="card">
  <div class="card-title">Período bloqueado</div>
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
      <div class="field">
        <label>Início *</label>
        <input type="datetime-local" name="data_inicio" value="<?= htmlspecialchars($_POST['data_inicio'] ?? '') ?>" required>
      </div>
      <div class="field">
        <label>Fim *</label>
        <input type="datetime-local" name="data_fim" value="<?= htmlspecialchars($_POST['data_fim'] ?? '') ?>" required>
      </div>
      <div class="field full">
        <label>Motivo</label>
        <input type="text" name="motivo" value="<?= htmlspecialchars($_POST['motivo'] ?? '') ?>" placeholder="Ex: Férias, consulta médica, formação...">
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Guardar</button>
      <a href="index.php" class="btn btn-ghost">← Voltar</a>
    </div>
  </form>
</div>

<?php require_once 'includes/footer.php'; ?>

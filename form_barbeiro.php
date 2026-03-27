<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$mensagem = $erro = '';
$conn = getDB();

$res = $conn->query("
    SELECT u.id, u.email FROM usuario u
    WHERE u.papel = 'barbeiro'
    AND u.id NOT IN (SELECT usuario_id FROM barbeiro)
");
$usuarios = $res->fetch_all(MYSQLI_ASSOC); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id     = $_POST['usuario_id']     ?? '';
    $nome           = trim($_POST['nome']      ?? '');
    $bio            = trim($_POST['bio']       ?? '') ?: null;
    $especialidades = trim($_POST['especialidades'] ?? '');

    if (!$nome) {
        $erro = 'O nome é obrigatório.';
    } elseif (!$usuario_id) {
        $erro = 'Seleccione um utilizador.';
    } else {
        $esp_json = '[]';
        if ($especialidades) {
            $lista    = array_values(array_filter(array_map('trim', explode(',', $especialidades))));
            $esp_json = json_encode($lista);
        }
        $stmt = $conn->prepare("INSERT INTO barbeiro (id, usuario_id, nome, bio, especialidades) VALUES (UUID(),?,?,?,?)");
        $stmt->bind_param('ssss', $usuario_id, $nome, $bio, $esp_json);
        if ($stmt->execute()) {
            $mensagem = "Barbeiro \"$nome\" criado com sucesso!";
            $_POST = [];
        } else {
            $erro = 'Erro ao guardar: ' . $conn->error;
        }
        $stmt->close();
    }
}
?>

<div class="page-header">
  <h1>Novo Barbeiro</h1>
  <p>Adiciona um barbeiro à equipa</p>
</div>

<?php if ($mensagem): ?><div class="alert alert-success">✓ <?= htmlspecialchars($mensagem) ?></div><?php endif; ?>
<?php if ($erro):     ?><div class="alert alert-error">✕ <?= htmlspecialchars($erro) ?></div><?php endif; ?>

<div class="card">
  <div class="card-title">Perfil do barbeiro</div>
  <form method="POST">
    <div class="form-grid">
      <div class="field full">
        <label>Utilizador do sistema *</label>
        <select name="usuario_id" required>
          <option value="">— Seleccione —</option>
          <?php foreach ($usuarios as $u): ?>
          <option value="<?= $u['id'] ?>" <?= ($_POST['usuario_id']??'')===$u['id']?'selected':'' ?>>
            <?= htmlspecialchars($u['email']) ?>
          </option>
          <?php endforeach; ?>
        </select>
        <?php if (!$usuarios): ?>
        <small style="color:var(--gold);font-size:11px">⚠ Cria primeiro um utilizador com papel "Barbeiro"</small>
        <?php endif; ?>
      </div>
      <div class="field full">
        <label>Nome *</label>
        <input type="text" name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required placeholder="Ex: João Silva">
      </div>
      <div class="field full">
        <label>Especialidades <span style="color:var(--muted);font-size:11px">(separadas por vírgula)</span></label>
        <input type="text" name="especialidades" value="<?= htmlspecialchars($_POST['especialidades'] ?? '') ?>" placeholder="Ex: corte degradê, barba, tratamento capilar">
      </div>
      <div class="field full">
        <label>Bio / Apresentação</label>
        <textarea name="bio" placeholder="Breve descrição do barbeiro..."><?= htmlspecialchars($_POST['bio'] ?? '') ?></textarea>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Guardar</button>
      <a href="index.php" class="btn btn-ghost">← Voltar</a>
    </div>
  </form>
</div>

<?php require_once 'includes/footer.php'; ?>

<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
requireRole('admin');

$mensagem = $erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome      = trim($_POST['nome']      ?? '');
    $telefone  = trim($_POST['telefone']  ?? '') ?: null;
    $email     = trim($_POST['email']     ?? '') ?: null;
    $data_nasc = $_POST['data_nasc']       ?? '' ?: null;
    $notas     = trim($_POST['notas']     ?? '') ?: null;

    if (!$nome) {
        $erro = 'O nome é obrigatório.';
    } else {
        $conn = getDB();
        $stmt = $conn->prepare("INSERT INTO cliente (id, nome, telefone, email, data_nasc, notas) VALUES (UUID(),?,?,?,?,?)");
        $stmt->bind_param('sssss', $nome, $telefone, $email, $data_nasc, $notas);
        if ($stmt->execute()) {
            $mensagem = "Cliente \"$nome\" registado com sucesso!";
            $_POST = [];
        } else {
            $erro = 'Erro ao guardar: ' . $conn->error;
        }
        $stmt->close();
    }
}
?>

<div class="page-header">
  <h1>Novo Cliente</h1>
  <p>Regista um novo cliente na barbearia</p>
</div>

<?php if ($mensagem): ?><div class="alert alert-success">✓ <?= htmlspecialchars($mensagem) ?></div><?php endif; ?>
<?php if ($erro):     ?><div class="alert alert-error">✕ <?= htmlspecialchars($erro) ?></div><?php endif; ?>

<div class="card">
  <div class="card-title">Dados pessoais</div>
  <form method="POST">
    <div class="form-grid">
      <div class="field full">
        <label>Nome completo *</label>
        <input type="text" name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required placeholder="Ex: Manuel Cossa">
      </div>
      <div class="field">
        <label>Telefone</label>
        <input type="tel" name="telefone" value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>" placeholder="+258 84 000 0000">
      </div>
      <div class="field">
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="cliente@email.com">
      </div>
      <div class="field">
        <label>Data de Nascimento</label>
        <input type="date" name="data_nasc" value="<?= htmlspecialchars($_POST['data_nasc'] ?? '') ?>">
      </div>
      <div class="field full">
        <label>Notas internas</label>
        <textarea name="notas" placeholder="Ex: Prefere corte às quartas, alérgico a produto X..."><?= htmlspecialchars($_POST['notas'] ?? '') ?></textarea>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Guardar</button>
      <a href="index.php" class="btn btn-ghost">← Voltar</a>
    </div>
  </form>
</div>

<?php require_once 'includes/footer.php'; ?>

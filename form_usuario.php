<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
requireRole('admin');

ini_set('display_errors', 1);
error_reporting(E_ALL);

$mensagem = $erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email     = trim($_POST['email']     ?? '');
    $senha     = $_POST['senha']          ?? '';
    $confirmar = $_POST['confirmar']      ?? '';
    $papel     = $_POST['papel']          ?? 'cliente';

    if (!$email || !$senha) {
        $erro = 'Email e senha são obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Email inválido.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($senha !== $confirmar) {
        $erro = 'As senhas não coincidem.';
    } else {
        $conn = getDB();
        $hash = password_hash($senha, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO usuario (id, email, senha_hash, papel) VALUES (UUID(),?,?,?)");
        $stmt->bind_param('sss', $email, $hash, $papel);
        if (!$stmt) {
    die("Erro no prepare: " . $conn->error);
}
        if ($stmt->execute()) {
            $mensagem = 'Utilizador criado com sucesso!';
        } else {
            $erro = str_contains($conn->error, 'Duplicate') ? 'Este email já está registado.' : 'Erro: ' . $conn->error;
        }
        $stmt->close();
    }
}
?>

<div class="page-header">
  <h1>Novo Utilizador</h1>
  <p>Cria uma conta de acesso ao sistema</p>
</div>

<?php if ($mensagem): ?><div class="alert alert-success">✓ <?= htmlspecialchars($mensagem) ?></div><?php endif; ?>
<?php if ($erro):     ?><div class="alert alert-error">✕ <?= htmlspecialchars($erro) ?></div><?php endif; ?>

<div class="card">
  <div class="card-title">Dados de acesso</div>
  <form action="form_usuario.php" method="POST">
    <div class="form-grid">
      <div class="field full">
        <label>Email *</label>
        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required placeholder="utilizador@email.com">
      </div>
      <div class="field">
        <label>Senha *</label>
        <input type="password" name="senha" required placeholder="Mínimo 6 caracteres">
      </div>
      <div class="field">
        <label>Confirmar Senha *</label>
        <input type="password" name="confirmar" required placeholder="Repita a senha">
      </div>
      <div class="field full">
        <label>Papel</label>
        <select name="papel">
          <option value="cliente"  <?= ($_POST['papel']??'cliente')==='cliente'  ?'selected':'' ?>>Cliente</option>
          <option value="barbeiro" <?= ($_POST['papel']??'')==='barbeiro' ?'selected':'' ?>>Barbeiro</option>
          <option value="admin"    <?= ($_POST['papel']??'')==='admin'    ?'selected':'' ?>>Administrador</option>
        </select>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Guardar</button>
      <a href="index.php" class="btn btn-ghost">← Voltar</a>
    </div>
  </form>
</div>

<?php require_once 'includes/footer.php'; ?>

<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$mensagem = $erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome        = trim($_POST['nome']      ?? '');
    $descricao   = trim($_POST['descricao'] ?? '') ?: null;
    $preco       = $_POST['preco']          ?? '';
    $duracao_min = $_POST['duracao_min']    ?? '';
    $categoria   = trim($_POST['categoria'] ?? '') ?: null;

    if (!$nome) {
        $erro = 'O nome do serviço é obrigatório.';
    } elseif (!is_numeric($preco) || $preco < 0) {
        $erro = 'Preço inválido.';
    } elseif (!is_numeric($duracao_min) || $duracao_min <= 0) {
        $erro = 'Duração inválida.';
    } else {
        $conn        = getDB();
        $preco       = (float)$preco;
        $duracao_min = (int)$duracao_min;
        $stmt        = $conn->prepare("INSERT INTO servico (id, nome, descricao, preco, duracao_min, categoria) VALUES (UUID(),?,?,?,?,?)");
        $stmt->bind_param('ssdis', $nome, $descricao, $preco, $duracao_min, $categoria);
        if ($stmt->execute()) {
            $mensagem = "Serviço \"$nome\" criado com sucesso!";
            $_POST = [];
        } else {
            $erro = 'Erro ao guardar: ' . $conn->error;
        }
        $stmt->close();
    }
}
?>

<div class="page-header">
  <h1>Novo Serviço</h1>
  <p>Adiciona um serviço ao catálogo</p>
</div>

<?php if ($mensagem): ?><div class="alert alert-success">✓ <?= htmlspecialchars($mensagem) ?></div><?php endif; ?>
<?php if ($erro):     ?><div class="alert alert-error">✕ <?= htmlspecialchars($erro) ?></div><?php endif; ?>

<div class="card">
  <div class="card-title">Detalhes do serviço</div>
  <form method="POST">
    <div class="form-grid">
      <div class="field full">
        <label>Nome do serviço *</label>
        <input type="text" name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required placeholder="Ex: Corte degradê">
      </div>
      <div class="field">
        <label>Preço (MZN) *</label>
        <input type="number" name="preco" value="<?= htmlspecialchars($_POST['preco'] ?? '') ?>" required min="0" step="0.01" placeholder="0.00">
      </div>
      <div class="field">
        <label>Duração (minutos) *</label>
        <input type="number" name="duracao_min" value="<?= htmlspecialchars($_POST['duracao_min'] ?? '') ?>" required min="1" placeholder="Ex: 45">
      </div>
      <div class="field full">
        <label>Categoria</label>
        <select name="categoria">
          <option value="">— Sem categoria —</option>
          <?php foreach (['corte','barba','combo','tratamento','outro'] as $cat): ?>
          <option value="<?= $cat ?>" <?= ($_POST['categoria']??'')===$cat?'selected':'' ?>><?= ucfirst($cat) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field full">
        <label>Descrição</label>
        <textarea name="descricao" placeholder="Descreve o serviço..."><?= htmlspecialchars($_POST['descricao'] ?? '') ?></textarea>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 Guardar</button>
      <a href="index.php" class="btn btn-ghost">← Voltar</a>
    </div>
  </form>
</div>

<?php require_once 'includes/footer.php'; ?>

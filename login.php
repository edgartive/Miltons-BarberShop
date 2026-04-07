<?php
// login.php — Página de autenticação

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se já está autenticado, redireciona conforme o papel
if (!empty($_SESSION['usuario_id'])) {
    $papel = $_SESSION['papel'] ?? '';
    switch ($papel) {
        case 'admin':
            header('Location: index.php');
            break;
        case 'barbeiro':
        case 'cliente':
            header('Location: form_agendamento.php');
            break;
        default:
            header('Location: login.php');
            break;
    }
    exit;
}

require_once 'includes/db.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (!$email || !$senha) {
        $erro = 'Email e senha são obrigatórios.';
    } else {
        $conn = getDB();
        $stmt = $conn->prepare("SELECT id, email, senha_hash, papel FROM usuario WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($senha, $user['senha_hash'])) {
            // Regenera o ID de sessão para prevenir fixação de sessão
            session_regenerate_id(true);

            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['email']      = $user['email'];
            $_SESSION['papel']      = $user['papel'];

            // Redireciona conforme o papel
            switch ($user['papel']) {
                case 'admin':
                    header('Location: index.php');
                    break;
                case 'barbeiro':
                    header('Location: form_agendamento.php');
                    break;
                case 'cliente':
                    header('Location: form_agendamento.php');
                    break;
                default:
                    header('Location: index.php');
                    break;
            }
            exit;
        } else {
            $erro = 'Email ou senha incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Barbearia</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg:        #0e0e0e;
      --surface:   #171717;
      --surface2:  #1f1f1f;
      --border:    #2a2a2a;
      --gold:      #c9a84c;
      --gold-light:#e8c97a;
      --text:      #f0ede6;
      --muted:     #7a7672;
      --danger:    #c94c4c;
      --success:   #4caf7a;
      --radius:    10px;
      --font-head: 'Playfair Display', serif;
      --font-body: 'DM Sans', sans-serif;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: var(--bg);
      color: var(--text);
      font-family: var(--font-body);
      font-size: 15px;
      font-weight: 300;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .login-wrapper {
      width: 100%;
      max-width: 420px;
      padding: 24px;
    }
    .login-logo {
      text-align: center;
      margin-bottom: 40px;
    }
    .login-logo span {
      font-family: var(--font-head);
      font-size: 28px;
      color: var(--gold);
      letter-spacing: .5px;
      display: block;
    }
    .login-logo small {
      color: var(--muted);
      font-size: 11px;
      letter-spacing: 2px;
      text-transform: uppercase;
    }
    .card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 36px 32px;
    }
    .card-title {
      font-size: 12px;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--gold);
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .card-title::after {
      content: '';
      flex: 1;
      height: 1px;
      background: var(--border);
    }
    .field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .field label {
      font-size: 12px;
      letter-spacing: 1px;
      text-transform: uppercase;
      color: var(--muted);
      font-weight: 500;
    }
    .field input {
      background: var(--surface2);
      border: 1px solid var(--border);
      border-radius: 7px;
      color: var(--text);
      font-family: var(--font-body);
      font-size: 14px;
      padding: 11px 14px;
      transition: border-color .15s, box-shadow .15s;
      outline: none;
      width: 100%;
    }
    .field input:focus {
      border-color: var(--gold);
      box-shadow: 0 0 0 3px rgba(201,168,76,.1);
    }
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 12px 24px;
      border-radius: 7px;
      font-family: var(--font-body);
      font-size: 13.5px;
      font-weight: 500;
      cursor: pointer;
      border: none;
      transition: all .15s;
      width: 100%;
      margin-top: 8px;
    }
    .btn-primary {
      background: var(--gold);
      color: #0e0e0e;
    }
    .btn-primary:hover { background: var(--gold-light); }
    .alert {
      padding: 12px 16px;
      border-radius: 7px;
      font-size: 13.5px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .alert-error { background: rgba(201,76,76,.1); border: 1px solid rgba(201,76,76,.3); color: var(--danger); }
    .login-footer {
      text-align: center;
      margin-top: 20px;
      font-size: 12px;
      color: var(--muted);
    }
  </style>
</head>
<body>

<div class="login-wrapper">
  <div class="login-logo">
    <span>Milton's BarberShop</span>
    <small>Sistema de Gestão</small>
  </div>

  <div class="card">
    <div class="card-title">Entrar na conta</div>

    <?php if ($erro): ?>
      <div class="alert alert-error">✕ <?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
      <div class="field">
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required placeholder="utilizador@email.com" autofocus>
      </div>
      <div class="field">
        <label>Senha</label>
        <input type="password" name="senha" required placeholder="A sua senha">
      </div>
      <button type="submit" class="btn btn-primary">🔐 Entrar</button>
    </form>
  </div>

  <div class="login-footer">
    Acesso restrito a utilizadores registados
  </div>
</div>

</body>
</html>

<?php
// includes/header.php
require_once __DIR__ . '/auth.php';
requireLogin();

$pagina_atual = basename($_SERVER['PHP_SELF'], '.php');
$_papel       = getPapel();
$_email       = getEmail();
$titulos = [
    'index'          => 'Início',
    'form_usuario'   => 'Novo Utilizador',
    'form_cliente'   => 'Novo Cliente',
    'form_barbeiro'  => 'Novo Barbeiro',
    'form_servico'   => 'Novo Serviço',
    'form_disponibilidade' => 'Disponibilidade',
    'form_bloqueio'  => 'Bloqueio de Agenda',
    'form_agendamento' => 'Novo Agendamento',
    'form_pagamento' => 'Registar Pagamento',
    'form_avaliacao' => 'Nova Avaliação',
    'form_notificacao' => 'Nova Notificação',
];
$titulo = $titulos[$pagina_atual] ?? 'Barbearia';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($titulo) ?> — Barbearia</title>
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
    }

    /* ── Sidebar ── */
    .sidebar {
      width: 240px;
      min-height: 100vh;
      background: var(--surface);
      border-right: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0; left: 0; bottom: 0;
      padding: 0 0 24px;
      z-index: 100;
    }
    .sidebar-logo {
      padding: 28px 24px 20px;
      border-bottom: 1px solid var(--border);
      margin-bottom: 16px;
    }
    .sidebar-logo span {
      font-family: var(--font-head);
      font-size: 22px;
      color: var(--gold);
      letter-spacing: .5px;
      display: block;
    }
    .sidebar-logo small {
      color: var(--muted);
      font-size: 11px;
      letter-spacing: 2px;
      text-transform: uppercase;
    }
    .nav-group { margin: 0 12px 4px; }
    .nav-label {
      font-size: 10px;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--muted);
      padding: 12px 12px 6px;
      display: block;
    }
    .nav-link {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 9px 12px;
      border-radius: 7px;
      color: var(--text);
      text-decoration: none;
      font-size: 13.5px;
      font-weight: 400;
      transition: background .15s, color .15s;
      opacity: .75;
    }
    .nav-link:hover { background: var(--surface2); opacity: 1; }
    .nav-link.active { background: rgba(201,168,76,.12); color: var(--gold); opacity: 1; }
    .nav-link .icon { font-size: 15px; width: 18px; text-align: center; }

    /* ── Main ── */
    .main {
      margin-left: 240px;
      flex: 1;
      padding: 40px 48px;
      max-width: 760px;
    }
    .page-header {
      margin-bottom: 32px;
      padding-bottom: 20px;
      border-bottom: 1px solid var(--border);
    }
    .page-header h1 {
      font-family: var(--font-head);
      font-size: 28px;
      color: var(--gold);
      margin-bottom: 4px;
    }
    .page-header p { color: var(--muted); font-size: 13.5px; }

    /* ── Card / Form ── */
    .card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 28px 32px;
      margin-bottom: 24px;
    }
    .card-title {
      font-size: 12px;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--gold);
      margin-bottom: 20px;
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

    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-grid .full { grid-column: 1 / -1; }

    .field { display: flex; flex-direction: column; gap: 6px; }
    .field label {
      font-size: 12px;
      letter-spacing: 1px;
      text-transform: uppercase;
      color: var(--muted);
      font-weight: 500;
    }
    .field input,
    .field select,
    .field textarea {
      background: var(--surface2);
      border: 1px solid var(--border);
      border-radius: 7px;
      color: var(--text);
      font-family: var(--font-body);
      font-size: 14px;
      padding: 10px 14px;
      transition: border-color .15s, box-shadow .15s;
      outline: none;
      width: 100%;
    }
    .field input:focus,
    .field select:focus,
    .field textarea:focus {
      border-color: var(--gold);
      box-shadow: 0 0 0 3px rgba(201,168,76,.1);
    }
    .field select option { background: var(--surface2); }
    .field textarea { resize: vertical; min-height: 90px; }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 11px 24px;
      border-radius: 7px;
      font-family: var(--font-body);
      font-size: 13.5px;
      font-weight: 500;
      cursor: pointer;
      border: none;
      transition: all .15s;
      text-decoration: none;
    }
    .btn-primary {
      background: var(--gold);
      color: #0e0e0e;
    }
    .btn-primary:hover { background: var(--gold-light); }
    .btn-ghost {
      background: transparent;
      border: 1px solid var(--border);
      color: var(--text);
    }
    .btn-ghost:hover { border-color: var(--gold); color: var(--gold); }

    .form-actions {
      display: flex;
      gap: 12px;
      margin-top: 24px;
      padding-top: 20px;
      border-top: 1px solid var(--border);
    }

    /* ── Alertas ── */
    .alert {
      padding: 12px 16px;
      border-radius: 7px;
      font-size: 13.5px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .alert-success { background: rgba(76,175,122,.1); border: 1px solid rgba(76,175,122,.3); color: var(--success); }
    .alert-error   { background: rgba(201,76,76,.1);  border: 1px solid rgba(201,76,76,.3);  color: var(--danger); }

    /* ── Home cards ── */
    .home-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 24px; }
    .home-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 20px;
      text-decoration: none;
      color: var(--text);
      transition: border-color .15s, transform .15s;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .home-card:hover { border-color: var(--gold); transform: translateY(-2px); }
    .home-card .hc-icon { font-size: 22px; }
    .home-card .hc-title { font-weight: 500; font-size: 14px; }
    .home-card .hc-desc { font-size: 12px; color: var(--muted); }
  </style>
</head>
<body>

<nav class="sidebar">
  <div class="sidebar-logo">
    <span>Milton's BarberShop</span>
    <small>Sistema de Gestão</small>
  </div>

  <div class="nav-group">
    <span class="nav-label">Principal</span>
    <a href="index.php" class="nav-link <?= $pagina_atual==='index'?'active':'' ?>">
      <span class="icon">🏠</span> Início
    </a>
    <a href="form_agendamento.php" class="nav-link <?= $pagina_atual==='form_agendamento'?'active':'' ?>">
      <span class="icon">📅</span> Agendamentos
    </a>
  </div>

  <?php if (hasRole('admin', 'barbeiro')): ?>
  <div class="nav-group">
    <span class="nav-label">Cadastros</span>
    <?php if (hasRole('admin')): ?>
    <a href="form_cliente.php"  class="nav-link <?= $pagina_atual==='form_cliente'?'active':'' ?>">
      <span class="icon">👤</span> Clientes
    </a>
    <a href="form_barbeiro.php" class="nav-link <?= $pagina_atual==='form_barbeiro'?'active':'' ?>">
      <span class="icon">✂️</span> Barbeiros
    </a>
    <a href="form_servico.php"  class="nav-link <?= $pagina_atual==='form_servico'?'active':'' ?>">
      <span class="icon">💈</span> Serviços
    </a>
    <a href="form_usuario.php"  class="nav-link <?= $pagina_atual==='form_usuario'?'active':'' ?>">
      <span class="icon">🔑</span> Utilizadores
    </a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php if (hasRole('admin', 'barbeiro')): ?>
  <div class="nav-group">
    <span class="nav-label">Agenda</span>
    <a href="form_disponibilidade.php" class="nav-link <?= $pagina_atual==='form_disponibilidade'?'active':'' ?>">
      <span class="icon">🕐</span> Disponibilidade
    </a>
    <a href="form_bloqueio.php" class="nav-link <?= $pagina_atual==='form_bloqueio'?'active':'' ?>">
      <span class="icon">🚫</span> Bloqueios
    </a>
  </div>
  <?php endif; ?>

  <div class="nav-group">
    <span class="nav-label">Operações</span>
    <a href="form_pagamento.php"   class="nav-link <?= $pagina_atual==='form_pagamento'?'active':'' ?>">
      <span class="icon">💰</span> Pagamentos
    </a>
    <a href="form_avaliacao.php"   class="nav-link <?= $pagina_atual==='form_avaliacao'?'active':'' ?>">
      <span class="icon">⭐</span> Avaliações
    </a>
    <?php if (hasRole('admin', 'barbeiro')): ?>
    <a href="form_notificacao.php" class="nav-link <?= $pagina_atual==='form_notificacao'?'active':'' ?>">
      <span class="icon">🔔</span> Notificações
    </a>
    <?php endif; ?>
  </div>

  <div style="margin-top:auto;padding:16px 12px 0;border-top:1px solid var(--border);margin-left:12px;margin-right:12px;">
    <div style="font-size:11px;color:var(--muted);margin-bottom:4px;text-transform:uppercase;letter-spacing:1px;">
      <?= htmlspecialchars($_papel) ?>
    </div>
    <div style="font-size:12px;color:var(--text);margin-bottom:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($_email) ?>">
      <?= htmlspecialchars($_email) ?>
    </div>
    <div style="position:absolute; bottom:20px; left:12px; right:12px;">
    <?php if (!empty($_SESSION['usuario_id'])): ?>
    <a href="logout.php" class="nav-link" style="color:var(--danger);opacity:1;">
      <span class="icon">🚪</span> Sair
    </a>
    <?php endif; ?>
  </div>
  
  
</nav>

<main class="main">

<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

$conn = getDB();

$stats = [
    'clientes'     => $conn->query("SELECT COUNT(*) FROM cliente")->fetch_row()[0],
    'barbeiros'    => $conn->query("SELECT COUNT(*) FROM barbeiro WHERE ativo=1")->fetch_row()[0],
    'agendamentos' => $conn->query("SELECT COUNT(*) FROM agendamento WHERE DATE(data_hora)=CURDATE()")->fetch_row()[0],
    'servicos'     => $conn->query("SELECT COUNT(*) FROM servico WHERE ativo=1")->fetch_row()[0],
]; 
?>

<div class="page-header">
  <h1>Painel</h1>
  <p>Bem-vindo ao sistema de gestão da barbearia</p>
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:32px">
  <?php foreach ([
    ['👤','Clientes',  $stats['clientes'],     'Total registados'],
    ['✂️','Barbeiros', $stats['barbeiros'],    'Activos'],
    ['📅','Hoje',      $stats['agendamentos'], 'Agendamentos hoje'],
    ['💈','Serviços',  $stats['servicos'],     'Disponíveis'],
  ] as [$icon,$label,$val,$sub]): ?>
  <div class="card" style="margin:0">
    <div style="font-size:22px;margin-bottom:8px"><?= $icon ?></div>
    <div style="font-size:28px;font-family:var(--font-head);color:var(--gold)"><?= $val ?></div>
    <div style="font-weight:500;font-size:14px;margin-top:2px"><?= $label ?></div>
    <div style="font-size:12px;color:var(--muted)"><?= $sub ?></div>
  </div>
  <?php endforeach; ?>
</div>

<div class="card">
  <div class="card-title">Acesso rápido</div>
  <div class="home-grid">
    <?php foreach ([
      ['form_agendamento.php','📅','Novo Agendamento',  'Marcar consulta'],
      ['form_cliente.php',    '👤','Novo Cliente',       'Registar cliente'],
      ['form_barbeiro.php',   '✂️','Novo Barbeiro',      'Adicionar barbeiro'],
      ['form_servico.php',    '💈','Novo Serviço',       'Criar serviço'],
      ['form_pagamento.php',  '💰','Registar Pagamento', 'Confirmar pagamento'],
      ['form_avaliacao.php',  '⭐','Nova Avaliação',     'Avaliar atendimento'],
    ] as [$url,$icon,$titulo,$desc]): ?>
    <a href="<?= $url ?>" class="home-card">
      <span class="hc-icon"><?= $icon ?></span>
      <span class="hc-title"><?= $titulo ?></span>
      <span class="hc-desc"><?= $desc ?></span>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>

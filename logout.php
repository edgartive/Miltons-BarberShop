<?php
// logout.php — Termina a sessão e redireciona para o login

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destrói todos os dados da sessão
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}
session_destroy();

header('Location: login.php');
exit;

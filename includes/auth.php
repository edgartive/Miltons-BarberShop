<?php
// includes/auth.php — Autenticação e autorização via sessions

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica se o utilizador está autenticado.
 * Se não estiver, redireciona para login.php
 */
function requireLogin(): void {
    if (empty($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Verifica se o utilizador tem o papel exigido.
 * Se não tiver, redireciona conforme o seu papel real.
 */
function requireRole(string ...$papeis): void {
    requireLogin();
    $papel = $_SESSION['papel'] ?? '';
    if (!in_array($papel, $papeis, true)) {
        redirectByRole();
    }
}

/**
 * Redireciona o utilizador para a página correcta conforme o seu papel.
 */
function redirectByRole(): never {
    $papel = $_SESSION['papel'] ?? '';
    switch ($papel) {
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
            header('Location: login.php');
            break;
    }
    exit;
}

/**
 * Devolve o papel do utilizador autenticado.
 */
function getPapel(): string {
    return $_SESSION['papel'] ?? '';
}

/**
 * Devolve o email do utilizador autenticado.
 */
function getEmail(): string {
    return $_SESSION['email'] ?? '';
}

/**
 * Verifica se o utilizador tem um dos papéis indicados (sem redirecionar).
 */
function hasRole(string ...$papeis): bool {
    return in_array($_SESSION['papel'] ?? '', $papeis, true);
}

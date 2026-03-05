<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Caminho absoluto para o session.php
$sessionPath = realpath(__DIR__ . '/../../../app/layouts/session.php');

if (!$sessionPath || !file_exists($sessionPath)) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Arquivo session.php NÃO encontrado!');
}

// Inclui a sessão (deve conter session_start)
require_once $sessionPath;

// Garante que a sessão está ativa
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Verifica autenticação
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header("Location: /web/login?return=not_authenticated");
    exit();
}

// (Opcional) Bloquear fora da rede interna
$ip = $_SERVER['REMOTE_ADDR'];
if (
    !preg_match('/^192\.168\./', $ip) &&
    !preg_match('/^10\./', $ip) &&
    !preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ip)
) {
    header("HTTP/1.1 403 Forbidden");
    exit("Acesso permitido apenas pela rede interna.");
}

?>
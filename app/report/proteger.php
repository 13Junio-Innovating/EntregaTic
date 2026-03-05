<?php
require_once __DIR__ . '/../layouts/session.php';

// Verifica se o usuário está autenticado
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header("Location: ../web/login?return=not_authenticated");
    exit();
}

// (Opcional) Verifica se o IP está dentro da rede privada
/*$ip = $_SERVER['REMOTE_ADDR'];
$redeInterna = (
    preg_match('/^192\.168\./', $ip) ||
    preg_match('/^10\./', $ip) ||
    preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ip)
);

if (!$redeInterna) {
    header("HTTP/1.1 403 Forbidden");
    echo "Acesso permitido apenas pela rede interna.";
    exit;
}*/


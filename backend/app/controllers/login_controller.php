<?php
session_start();

require __DIR__ . "/../database/db_connection.php";
require __DIR__ . "/../models/login_model.php";
require __DIR__ . "/../services/login_service.php";
require __DIR__ . "/../models/license_model.php";
require __DIR__ . "/../services/license_service.php";
require_once __DIR__ . '/../services/ldap_service.php';

function redirect($msg)
{
    header('Content-Type: application/json');
    echo json_encode($msg);
    exit();
}

// Captura dados do POST
$access_credentials = json_decode(json_encode($_POST));
$csrf_token = $access_credentials->csrfToken ?? null;
$access_user = $access_credentials->accessUser ?? null;
$access_password = $access_credentials->accessPassword ?? null;

// Verificações iniciais
if (!isset($csrf_token) || $csrf_token != $_SESSION['csrf_token']) {
    redirect(["error" => "erro1", "message" => "Token de autenticação inválido."]);
}
if (empty($access_user) || empty($access_password)) {
    redirect(["error" => "erro2", "message" => "Usuário ou senha inválidos."]);
}
if (strlen($access_user) < 3) {
    redirect(["error" => "erro3", "message" => "O usuário deve ter no mínimo 3 caracteres."]);
}
if (strlen($access_password) < 6) {
    redirect(["error" => "erro4", "message" => "A senha deve ter no mínimo 6 caracteres."]);
}

// Tenta autenticar via LDAP
$ldapService = new LdapService();
$ldapAuth = $ldapService->authenticate($access_user, $access_password);

if ($ldapAuth['status'] === true) {
    $tipo_permissao = 'usuario';
    if ($ldapAuth['grupo'] === 'TIC_ADMIN') {
        $tipo_permissao = 'administrador';
    } elseif ($ldapAuth['grupo'] === 'TIC_Manut') {
        $tipo_permissao = 'administrador';
    } elseif ($ldapAuth['grupo'] === 'TIC_USER') {
        $tipo_permissao = 'administrador';
    }

    $_SESSION['csrf_token'] = $csrf_token;
    $_SESSION['id_user'] = null;
    $_SESSION['access_user'] = $access_user;
    $_SESSION['access_password'] = $access_password;
    $_SESSION['name_user'] = $ldapAuth['name'];
    $_SESSION['email_user'] = $ldapAuth['mail'];
    $_SESSION['type_permission'] = $tipo_permissao;
    $_SESSION['cpf_user'] = null;
    $_SESSION['authenticated'] = true;

    redirect(["success" => "Login via LDAP com sucesso!"]);
    exit(); // para garantir que o fallback local não execute
}

// Fallback para login local
$connect = new DbConnection();
$model_user = new LoginModel();
$model_user->__set("usuario_acesso", $access_user);
$service_user = new LoginService($connect, $model_user);
$user_exists = $service_user->verifyUserExists();

if (empty($user_exists)) {
    redirect(["error" => "erro5", "message" => "Usuário inexistente."]);
}

$password_decoder = $service_user->verifyPassword();
if (!password_verify($access_password, $password_decoder->senha_usuario)) {
    redirect(["error" => "erro6", "message" => "Senha inválida."]);
}

// Login local OK
$model_license = new LicenseModel();
$license_service = new LicenseService($connect, $model_license);
$id_user = $user_exists->id;
$license_exists = $license_service->verifyLicenseExists($id_user);
date_default_timezone_set('America/Sao_Paulo');

if (empty($license_exists->id_usuario) && empty($license_exists->data_ativacao_sistema)) {
    $now = date("Y-m-d H:i:s");
    $model_license->__set("id_usuario", $id_user);
    $model_license->__set("data_ativacao_sistema", $now);
    $model_license->__set("data_ultima_renovacao", $now);
    $data_renovacao = new DateTime($now);
    $data_renovacao->add(new DateInterval('P30D'));
    $model_license->__set("data_proxima_renovacao", $data_renovacao->format('Y-m-d H:i:s'));
    $license_service->generateLicense($id_user);
}

if (!empty($license_exists->id)) {
    $model_license->__set('id_usuario', $id_user);
    $last_renewal = $license_service->getLastRenewal();
    $now = new DateTime();
    $next = new DateTime($last_renewal->data_proxima_renovacao);
    if ($now > $next) {
        redirect(["error" => "erro8", "message" => "Licença expirada, favor renovar."]);
    }
}

// Seta sessão local
$_SESSION['csrf_token'] = $csrf_token;
$_SESSION['id_user'] = $user_exists->id;
$_SESSION['access_user'] = $access_user;
$_SESSION['access_password'] = $access_password;
$_SESSION['name_user'] = $user_exists->nome;
$_SESSION['email_user'] = $user_exists->email;
$_SESSION['type_permission'] = $user_exists->tipo_permissao;
$_SESSION['cpf_user'] = $user_exists->cpf;
$_SESSION['authenticated'] = true;

// Atualiza último acesso
$model_user->__set('id', $user_exists->id);
$model_user->__set('data_ultimo_acesso', date("Y-m-d H:i:s"));
$service_user->updateLastAccess();

redirect(["success" => "Login local efetuado com sucesso!"]);
?>

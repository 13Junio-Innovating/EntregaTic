<?php
session_start();
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: ../web/login?return=not_authenticated');
    exit();
}

$user_logged = null;

if (isset($_SESSION['id_user']) && !empty($_SESSION['id_user'])) {
    require_once '../controllers/db_connection.php';
    $connect = new DbConnection();
    $connect = $connect->getConnection();

    $user_id = $_SESSION['id_user'];
    $query = "SELECT * FROM tb_usuarios WHERE id = :id";
    $stmt = $connect->prepare($query);
    $stmt->bindValue(':id', $user_id);
    $stmt->execute();
    $user_logged = $stmt->fetch(PDO::FETCH_OBJ);
} else {
    // se login for via LDAP e não houver id_user
    $user_logged = (object)[
        'nome' => $_SESSION['name_user'],
        'usuario_acesso' => $_SESSION['name_user'],
        'email' => $_SESSION['email_user'],
        'tipo_permissao' => $_SESSION['type_permission'],
        'cpf' => $_SESSION['cpf_user'],
    ];
}

// pegando os dados do usuário logado para trabalhar em outras páginas com por exemplo a página de editar perfil
// se tiver alguma alteração no perfil do usuário, a atualização será refletida sem fazer o logoff.
/*require_once '../controllers/db_connection.php';
$connect = new DbConnection();
$connect = $connect->getConnection();
$user_id = $_SESSION['id_user'];
$query = "SELECT * FROM tb_usuarios WHERE id = :id";
$stmt = $connect->prepare($query);
$stmt->bindValue(':id', $user_id);
$stmt->execute();
$user_logged = $stmt->fetch(PDO::FETCH_OBJ);*/

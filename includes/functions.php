<?php
// Verifica se usuário está logado
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../index.php');
        exit;
    }
}

// Verifica se é Gestor (Segurança para área admin)
function require_gestor() {
    require_login();
    if ($_SESSION['user_perfil'] !== 'GESTOR') {
        // Se motorista tentar acessar admin, manda pro mobile
        header('Location: ../mobile/index.php');
        exit;
    }
}
?>

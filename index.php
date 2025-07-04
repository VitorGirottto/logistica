<?php
require_once 'config/config.php';

// Se o usuário já está logado, redirecionar para o dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit();
} else {
    header('Location: login.php');
    exit();
}
?>

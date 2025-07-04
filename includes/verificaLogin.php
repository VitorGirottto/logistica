<?php
// Incluir configurações
require_once 'config/config.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Buscar dados do usuário logado
$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM usuarios WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $_SESSION['usuario_id']);
$stmt->execute();
$usuario_logado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario_logado) {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>

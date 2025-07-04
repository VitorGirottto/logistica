<?php
// Verificar se a sessão já está ativa antes de iniciar
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configurações gerais
define('BASE_URL', 'http://localhost/desenvolvimento/logistica/');
define('SITE_NAME', 'Sistema de Logística');

// Incluir conexão com banco
require_once __DIR__ . '/database.php';

// Função para verificar se usuário está logado
function verificarLogin() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit();
    }
}

// Função para proteger contra XSS
function limparInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Função para formatar data
function formatarData($data) {
    return date('d/m/Y H:i', strtotime($data));
}

// Função para formatar moeda
function formatarMoeda($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

// Função para debug (remover em produção)
function debug($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}
?>

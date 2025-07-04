<?php
require_once 'config/config.php';

$database = new Database();
$db = $database->getConnection();

echo "<h1>Debug - Contas a Receber</h1>";

try {
    // Verificar se a tabela existe
    $query = "SHOW TABLES LIKE 'contas_receber'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $tabela_existe = $stmt->fetch();
    
    echo "<h2>Tabela existe:</h2>";
    var_dump($tabela_existe);
    
    // Verificar estrutura da tabela
    $query = "DESCRIBE contas_receber";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $estrutura = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Estrutura da tabela:</h2>";
    echo "<pre>";
    print_r($estrutura);
    echo "</pre>";
    
    // Verificar dados na tabela
    $query = "SELECT * FROM contas_receber LIMIT 10";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Dados na tabela (primeiros 10):</h2>";
    echo "<pre>";
    print_r($dados);
    echo "</pre>";
    
    // Testar consulta mensal
    $query_mensal = "
        SELECT 
            DATE_FORMAT(data_vencimento, '%Y-%m') as mes,
            COALESCE(SUM(valor), 0) as total
        FROM contas_receber 
        WHERE data_vencimento >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(data_vencimento, '%Y-%m')
        ORDER BY mes
    ";
    $stmt_mensal = $db->prepare($query_mensal);
    $stmt_mensal->execute();
    $dados_mensais = $stmt_mensal->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Consulta mensal:</h2>";
    echo "<pre>";
    print_r($dados_mensais);
    echo "</pre>";
    
    // Testar consulta de status
    $query_status = "
        SELECT 
            status,
            COUNT(*) as total
        FROM contas_receber 
        GROUP BY status
    ";
    $stmt_status = $db->prepare($query_status);
    $stmt_status->execute();
    $dados_status = $stmt_status->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Status das contas:</h2>";
    echo "<pre>";
    print_r($dados_status);
    echo "</pre>";
    
} catch(PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>

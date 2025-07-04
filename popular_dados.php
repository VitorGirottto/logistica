<?php
require_once 'config/config.php';

echo "<h1>Populando Banco de Dados com Dados de Teste</h1>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Limpar dados existentes (opcional)
    echo "<h2>Limpando dados existentes...</h2>";
    $db->exec("DELETE FROM ocorrencias");
    $db->exec("DELETE FROM entregas");
    $db->exec("DELETE FROM motoristas WHERE id > 2"); // Manter usuários admin
    $db->exec("DELETE FROM veiculos");
    
    // Inserir motoristas
    echo "<h2>Inserindo motoristas...</h2>";
    $motoristas = [
        ['Carlos Santos', 'Manhã', 1],
        ['Maria Oliveira', 'Tarde', 1],
        ['Pedro Costa', 'Noite', 1],
        ['Ana Lima', 'Manhã', 1],
        ['José Ferreira', 'Tarde', 0]
    ];
    
    $query = "INSERT INTO motoristas (nome, turno, ativo) VALUES (?, ?, ?)";
    $stmt = $db->prepare($query);
    foreach ($motoristas as $motorista) {
        $stmt->execute($motorista);
        echo "✅ Motorista: {$motorista[0]}<br>";
    }
    
    // Inserir veículos
    echo "<h2>Inserindo veículos...</h2>";
    $veiculos = [
        ['Mercedes Sprinter', 'ABC-1234', 'Ativo', 0],
        ['Iveco Daily', 'DEF-5678', 'Ativo', 0],
        ['Ford Transit', 'GHI-9012', 'Manutenção', 1],
        ['Volkswagen Delivery', 'JKL-3456', 'Ativo', 0],
        ['Fiat Ducato', 'MNO-7890', 'Inativo', 0]
    ];
    
    $query = "INSERT INTO veiculos (modelo, placa, status, em_manutencao) VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    foreach ($veiculos as $veiculo) {
        $stmt->execute($veiculo);
        echo "✅ Veículo: {$veiculo[0]} - {$veiculo[1]}<br>";
    }
    
    // Inserir entregas
    echo "<h2>Inserindo entregas...</h2>";
    $entregas = [
        ['São Paulo - SP', 'Rio de Janeiro - RJ', 'Entregue', '2024-01-15 08:00:00', '2024-01-15 18:00:00', 450.00, 1, 1, 430.5, 'Sudeste'],
        ['São Paulo - SP', 'Belo Horizonte - MG', 'Em Trânsito', '2024-01-16 09:00:00', null, 320.00, 2, 2, 586.2, 'Sudeste'],
        ['São Paulo - SP', 'Curitiba - PR', 'Atrasada', '2024-01-14 07:00:00', null, 280.00, 3, 4, 408.7, 'Sul'],
        ['São Paulo - SP', 'Salvador - BA', 'Pendente', null, null, 680.00, null, null, 1445.3, 'Nordeste'],
        ['São Paulo - SP', 'Brasília - DF', 'Entregue', '2024-01-13 06:00:00', '2024-01-13 20:00:00', 520.00, 1, 1, 1015.8, 'Centro-Oeste'],
        ['Rio de Janeiro - RJ', 'São Paulo - SP', 'Entregue', '2024-01-12 10:00:00', '2024-01-12 20:00:00', 400.00, 2, 2, 430.5, 'Sudeste'],
        ['Belo Horizonte - MG', 'Vitória - ES', 'Em Trânsito', '2024-01-17 08:00:00', null, 350.00, 1, 4, 524.1, 'Sudeste'],
        ['Porto Alegre - RS', 'Florianópolis - SC', 'Entregue', '2024-01-11 07:00:00', '2024-01-11 15:00:00', 180.00, 3, 1, 476.8, 'Sul']
    ];
    
    $query = "INSERT INTO entregas (origem, destino, status, data_saida, data_entrega, custo, motorista_id, veiculo_id, km, regiao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    foreach ($entregas as $entrega) {
        $stmt->execute($entrega);
        echo "✅ Entrega: {$entrega[0]} → {$entrega[1]}<br>";
    }
    
    // Inserir ocorrências
    echo "<h2>Inserindo ocorrências...</h2>";
    $ocorrencias = [
        ['Atraso', 'Trânsito intenso na rodovia', 3, '2024-01-14 15:30:00'],
        ['Avaria', 'Pequeno dano na carga', 1, '2024-01-15 12:00:00'],
        ['Combustível', 'Parada para abastecimento', 2, '2024-01-16 14:00:00'],
        ['Mecânica', 'Problema no motor', 7, '2024-01-17 10:00:00'],
        ['Documentação', 'Falta de documento fiscal', 4, '2024-01-16 16:00:00']
    ];
    
    $query = "INSERT INTO ocorrencias (tipo, descricao, entrega_id, data) VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    foreach ($ocorrencias as $ocorrencia) {
        $stmt->execute($ocorrencia);
        echo "✅ Ocorrência: {$ocorrencia[0]}<br>";
    }
    
    echo "<h2>✅ Dados inseridos com sucesso!</h2>";
    echo "<p><a href='roteirizacao_novo.php'>Testar Gráficos</a></p>";
    echo "<p><a href='teste_dados.php'>Verificar Dados</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Erro: " . $e->getMessage() . "</h2>";
}
?>

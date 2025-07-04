<?php
require_once 'includes/verificaLogin.php';

$database = new Database();
$db = $database->getConnection();

// Verificar se há dados nas tabelas
$debug_info = [];

try {
    // Contar registros em cada tabela
    $tabelas = ['entregas', 'motoristas', 'veiculos', 'ocorrencias'];
    foreach ($tabelas as $tabela) {
        $query = "SELECT COUNT(*) as total FROM $tabela";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $debug_info[$tabela] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    // Se não há dados, inserir dados de exemplo
    if ($debug_info['entregas'] == 0) {
        
        foreach ($queries as $query) {
            try {
                $stmt = $db->prepare($query);
                $stmt->execute();
            } catch (Exception $e) {
                // Ignorar erros de duplicação
            }
        }
    }
    
    // Buscar dados para os gráficos
    $dados_graficos = [];
    
    // 1. Entregas por região
    $query = "SELECT regiao, COUNT(*) as total FROM entregas WHERE regiao IS NOT NULL GROUP BY regiao";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $dados_graficos['entregas_regiao'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. Motoristas por turno
    $query = "SELECT turno, COUNT(*) as total FROM motoristas WHERE ativo = 1 GROUP BY turno";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $dados_graficos['motoristas_turno'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. Status das entregas
    $query = "SELECT status, COUNT(*) as total FROM entregas GROUP BY status";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $dados_graficos['entregas_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. Custo por região
    $query = "SELECT regiao, AVG(custo) as custo_medio FROM entregas WHERE regiao IS NOT NULL AND custo > 0 GROUP BY regiao";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $dados_graficos['custo_regiao'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Dados para os cards
    $query = "SELECT COUNT(*) as total FROM entregas WHERE status = 'Atrasada'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $entregas_atrasadas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $query = "SELECT COUNT(*) as total FROM veiculos WHERE status = 'Ativo'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $frota_ativa = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
} catch (Exception $e) {
    $erro_sistema = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roteirização - Sistema Logística</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
</head>
<body>
    <div class="main-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Roteirização e Analytics</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($usuario_logado['nome'], 0, 1)); ?>
                    </div>
                    <span><?php echo $usuario_logado['nome']; ?></span>
                    <a href="logout.php" class="btn btn-danger btn-sm">Sair</a>
                </div>
            </div>
            
            <div class="content-area">
                
                <?php if (isset($erro_sistema)): ?>
                    <div class="alert alert-error">
                        <strong>Erro do Sistema:</strong> <?php echo $erro_sistema; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Debug Panel -->
                <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; font-size: 12px;">
                        <?php foreach ($debug_info as $tabela => $total): ?>
                            <div>
                                <strong><?php echo ucfirst($tabela); ?>:</strong> <?php echo $total; ?> registros
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="margin-top: 10px; font-size: 12px;">
                        <span id="chartjs-status"></span>
                    </div>
                </div>
                
                <!-- Cards de métricas -->
                <div class="stats-grid">
                    <div class="stat-card danger">
                        <div class="stat-number"><?php echo $entregas_atrasadas ?? 0; ?></div>
                        <div class="stat-label">Entregas Atrasadas</div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-number"><?php echo $frota_ativa ?? 0; ?></div>
                        <div class="stat-label">Frota Ativa</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($dados_graficos['entregas_regiao'] ?? []); ?></div>
                        <div class="stat-label">Regiões Atendidas</div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-number"><?php echo count($dados_graficos['motoristas_turno'] ?? []); ?></div>
                        <div class="stat-label">Turnos Ativos</div>
                    </div>
                </div>
                
                <!-- Gráficos -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px;">
                    
                    <!-- Gráfico 1: Entregas por Região -->
                    <div class="chart-container">
                        <h3 class="chart-title">Entregas por Região</h3>
                        <div style="position: relative; height: 300px;">
                            <canvas id="grafico1"></canvas>
                        </div>
                        <div style="font-size: 12px; color: #666; margin-top: 10px;">
                            Status: <span id="status-grafico1">Carregando...</span>
                        </div>
                    </div>
                    
                    <!-- Gráfico 2: Motoristas por Turno -->
                    <div class="chart-container">
                        <h3 class="chart-title">Motoristas por Turno</h3>
                        <div style="position: relative; height: 300px;">
                            <canvas id="grafico2"></canvas>
                        </div>
                        <div style="font-size: 12px; color: #666; margin-top: 10px;">
                            Status: <span id="status-grafico2">Carregando...</span>
                        </div>
                    </div>
                    
                    <!-- Gráfico 3: Status das Entregas -->
                    <div class="chart-container">
                        <h3 class="chart-title">Status das Entregas</h3>
                        <div style="position: relative; height: 300px;">
                            <canvas id="grafico3"></canvas>
                        </div>
                        <div style="font-size: 12px; color: #666; margin-top: 10px;">
                            Status: <span id="status-grafico3">Carregando...</span>
                        </div>
                    </div>
                    
                    <!-- Gráfico 4: Custo por Região -->
                    <div class="chart-container">
                        <h3 class="chart-title">Custo Médio por Região</h3>
                        <div style="position: relative; height: 300px;">
                            <canvas id="grafico4"></canvas>
                        </div>
                        <div style="font-size: 12px; color: #666; margin-top: 10px;">
                            Status: <span id="status-grafico4">Carregando...</span>
                        </div>
                    </div>
                    
                </div>
                
            </div>
        </div>
    </div>
    
    <script>
        // Verificar se Chart.js carregou
        document.getElementById('chartjs-status').textContent = typeof Chart !== 'undefined' ? '✅ Carregado' : '❌ Não carregado';
        
        // Aguardar DOM carregar
        document.addEventListener('DOMContentLoaded', function() {
            
            // Dados do PHP
            const dadosGraficos = {
                entregasRegiao: <?php echo json_encode($dados_graficos['entregas_regiao'] ?? []); ?>,
                motoristasTurno: <?php echo json_encode($dados_graficos['motoristas_turno'] ?? []); ?>,
                entregasStatus: <?php echo json_encode($dados_graficos['entregas_status'] ?? []); ?>,
                custoRegiao: <?php echo json_encode($dados_graficos['custo_regiao'] ?? []); ?>
            };
            
            
            // Cores padrão
            const cores = ['#8B5CF6', '#F59E0B', '#10B981', '#EF4444', '#3B82F6', '#F97316'];
            
            // Função para criar gráfico com tratamento de erro
            function criarGrafico(id, config, statusId) {
                try {
                    const canvas = document.getElementById(id);
                    if (!canvas) {
                        throw new Error(`Canvas ${id} não encontrado`);
                    }
                    
                    const ctx = canvas.getContext('2d');
                    const chart = new Chart(ctx, config);
                    
                    document.getElementById(statusId).textContent = '✅ Carregado';
                    
                    return chart;
                } catch (error) {
                    console.error(`❌ Erro no gráfico ${id}:`, error);
                    document.getElementById(statusId).textContent = '❌ Erro: ' + error.message;
                    return null;
                }
            }
            
            // Gráfico 1: Entregas por Região
            if (dadosGraficos.entregasRegiao.length > 0) {
                criarGrafico('grafico1', {
                    type: 'doughnut',
                    data: {
                        labels: dadosGraficos.entregasRegiao.map(item => item.regiao),
                        datasets: [{
                            data: dadosGraficos.entregasRegiao.map(item => parseInt(item.total)),
                            backgroundColor: cores.slice(0, dadosGraficos.entregasRegiao.length)
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                }, 'status-grafico1');
            } else {
                document.getElementById('status-grafico1').textContent = '⚠️ Sem dados';
            }
            
            // Gráfico 2: Motoristas por Turno
            if (dadosGraficos.motoristasTurno.length > 0) {
                criarGrafico('grafico2', {
                    type: 'pie',
                    data: {
                        labels: dadosGraficos.motoristasTurno.map(item => item.turno),
                        datasets: [{
                            data: dadosGraficos.motoristasTurno.map(item => parseInt(item.total)),
                            backgroundColor: ['#F59E0B', '#10B981', '#8B5CF6']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                }, 'status-grafico2');
            } else {
                document.getElementById('status-grafico2').textContent = '⚠️ Sem dados';
            }
            
            // Gráfico 3: Status das Entregas
            if (dadosGraficos.entregasStatus.length > 0) {
                criarGrafico('grafico3', {
                    type: 'bar',
                    data: {
                        labels: dadosGraficos.entregasStatus.map(item => item.status),
                        datasets: [{
                            label: 'Quantidade',
                            data: dadosGraficos.entregasStatus.map(item => parseInt(item.total)),
                            backgroundColor: '#8B5CF6'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                }, 'status-grafico3');
            } else {
                document.getElementById('status-grafico3').textContent = '⚠️ Sem dados';
            }
            
            // Gráfico 4: Custo por Região
            if (dadosGraficos.custoRegiao.length > 0) {
                criarGrafico('grafico4', {
                    type: 'bar',
                    data: {
                        labels: dadosGraficos.custoRegiao.map(item => item.regiao),
                        datasets: [{
                            label: 'Custo Médio (R$)',
                            data: dadosGraficos.custoRegiao.map(item => parseFloat(item.custo_medio)),
                            backgroundColor: '#F59E0B'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                }, 'status-grafico4');
            } else {
                document.getElementById('status-grafico4').textContent = '⚠️ Sem dados';
            }
            
        });
    </script>
</body>
</html>

<?php
require_once 'includes/verificaLogin.php';

// Buscar dados para os cards
$database = new Database();
$db = $database->getConnection();

// Total de entregas
$query = "SELECT COUNT(*) as total FROM entregas";
$stmt = $db->prepare($query);
$stmt->execute();
$total_entregas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Entregas atrasadas
$query = "SELECT COUNT(*) as total FROM entregas WHERE status = 'Atrasada'";
$stmt = $db->prepare($query);
$stmt->execute();
$entregas_atrasadas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Motoristas ativos
$query = "SELECT COUNT(*) as total FROM motoristas WHERE ativo = 1";
$stmt = $db->prepare($query);
$stmt->execute();
$motoristas_ativos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Veículos ativos
$query = "SELECT COUNT(*) as total FROM veiculos WHERE status = 'Ativo'";
$stmt = $db->prepare($query);
$stmt->execute();
$veiculos_ativos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Dados para gráficos
// Entregas por região
$query = "SELECT regiao, COUNT(*) as total FROM entregas GROUP BY regiao";
$stmt = $db->prepare($query);
$stmt->execute();
$entregas_regiao = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Entregas por status
$query = "SELECT status, COUNT(*) as total FROM entregas GROUP BY status";
$stmt = $db->prepare($query);
$stmt->execute();
$entregas_status = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="main-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Dashboard Geral</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($usuario_logado['nome'], 0, 1)); ?>
                    </div>
                    <span><?php echo $usuario_logado['nome']; ?></span>
                    <a href="logout.php" class="btn btn-danger btn-sm">Sair</a>
                </div>
            </div>
            
            <div class="content-area">
                <!-- Cards de estatísticas -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $total_entregas; ?></div>
                        <div class="stat-label">Total de Entregas</div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-number"><?php echo $entregas_atrasadas; ?></div>
                        <div class="stat-label">Entregas Atrasadas</div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-number"><?php echo $motoristas_ativos; ?></div>
                        <div class="stat-label">Motoristas Ativos</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $veiculos_ativos; ?></div>
                        <div class="stat-label">Veículos Ativos</div>
                    </div>
                </div>
                
                <!-- Gráficos -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <div class="chart-container">
                        <h3 class="chart-title">Entregas por Região</h3>
                        <canvas id="chartRegiao"></canvas>
                    </div>
                    
                    <div class="chart-container">
                        <h3 class="chart-title">Status das Entregas</h3>
                        <canvas id="chartStatus"></canvas>
                    </div>
                </div>
                
                <!-- Tabela de entregas recentes -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Entregas Recentes</h3>
                        <a href="entregas.php" class="btn btn-primary btn-sm">Ver Todas</a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Origem</th>
                                <th>Destino</th>
                                <th>Status</th>
                                <th>Data Saída</th>
                                <th>Custo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM entregas ORDER BY criado_em DESC LIMIT 5";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $entregas_recentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($entregas_recentes as $entrega):
                            ?>
                            <tr>
                                <td>#<?php echo $entrega['id']; ?></td>
                                <td><?php echo $entrega['origem']; ?></td>
                                <td><?php echo $entrega['destino']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '', $entrega['status'])); ?>">
                                        <?php echo $entrega['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo $entrega['data_saida'] ? formatarData($entrega['data_saida']) : '-'; ?></td>
                                <td><?php echo formatarMoeda($entrega['custo']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Gráfico de entregas por região
        const ctxRegiao = document.getElementById('chartRegiao').getContext('2d');
        new Chart(ctxRegiao, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($entregas_regiao, 'regiao')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($entregas_regiao, 'total')); ?>,
                    backgroundColor: [
                        '#8B5CF6',
                        '#F59E0B',
                        '#10B981',
                        '#EF4444',
                        '#3B82F6'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Gráfico de status das entregas
        const ctxStatus = document.getElementById('chartStatus').getContext('2d');
        new Chart(ctxStatus, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($entregas_status, 'status')); ?>,
                datasets: [{
                    label: 'Quantidade',
                    data: <?php echo json_encode(array_column($entregas_status, 'total')); ?>,
                    backgroundColor: '#8B5CF6'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>

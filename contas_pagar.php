<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/verificaLogin.php';

$database = new Database();
$db = $database->getConnection();

// Buscar dados para dashboard
$query_total = "SELECT COUNT(*) as total, COALESCE(SUM(valor), 0) as valor_total FROM contas_pagar";
$stmt_total = $db->prepare($query_total);
$stmt_total->execute();
$dados_total = $stmt_total->fetch(PDO::FETCH_ASSOC);

$query_pagas = "SELECT COALESCE(SUM(valor_pago), 0) as total FROM contas_pagar WHERE status = 'Paga'";
$stmt_pagas = $db->prepare($query_pagas);
$stmt_pagas->execute();
$pagas = $stmt_pagas->fetch(PDO::FETCH_ASSOC)['total'];

$query_vencidas = "SELECT COUNT(*) as total FROM contas_pagar WHERE status = 'Vencida'";
$stmt_vencidas = $db->prepare($query_vencidas);
$stmt_vencidas->execute();
$vencidas = $stmt_vencidas->fetch(PDO::FETCH_ASSOC)['total'];

// Dados para gráfico de gastos mensais
$query_gastos = "SELECT 
    DATE_FORMAT(COALESCE(data_pagamento, data_vencimento), '%Y-%m') as mes,
    SUM(CASE WHEN status = 'Paga' THEN valor_pago ELSE 0 END) as pago,
    SUM(CASE WHEN status IN ('Pendente', 'Vencida') THEN valor ELSE 0 END) as a_pagar
    FROM contas_pagar 
    WHERE COALESCE(data_pagamento, data_vencimento) >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(COALESCE(data_pagamento, data_vencimento), '%Y-%m')
    ORDER BY mes";
$stmt_gastos = $db->prepare($query_gastos);
$stmt_gastos->execute();
$dados_gastos = $stmt_gastos->fetchAll(PDO::FETCH_ASSOC);

// Dados para gráfico de vencimentos do mês atual
$query_vencimentos = "SELECT 
    status,
    COUNT(*) as quantidade,
    COALESCE(SUM(valor), 0) as valor_total
    FROM contas_pagar 
    WHERE MONTH(data_vencimento) = MONTH(CURRENT_DATE()) AND YEAR(data_vencimento) = YEAR(CURRENT_DATE())
    GROUP BY status";
$stmt_vencimentos = $db->prepare($query_vencimentos);
$stmt_vencimentos->execute();
$dados_vencimentos = $stmt_vencimentos->fetchAll(PDO::FETCH_ASSOC);

// Buscar contas para listagem
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : '';
$status_filtro = isset($_GET['status']) ? $_GET['status'] : '';

$query = "SELECT * FROM contas_pagar WHERE 1=1";
$params = [];

if (!empty($filtro)) {
    $query .= " AND (descricao LIKE ? OR fornecedor LIKE ?)";
    $params[] = "%$filtro%";
    $params[] = "%$filtro%";
}

if (!empty($status_filtro)) {
    $query .= " AND status = ?";
    $params[] = $status_filtro;
}

$query .= " ORDER BY data_vencimento DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$contas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contas a Pagar - Sistema Logística</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/sidebar-moderno.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="main-container">
        <?php include 'includes/sidebar-moderno.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">
                    <i class="fas fa-credit-card"></i>
                    Contas a Pagar
                </h1>
                <div class="user-info">
                    <span>Bem-vindo, <?php echo $_SESSION['usuario_nome']; ?></span>
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['usuario_nome'], 0, 1)); ?>
                    </div>
                </div>
            </div>

            <div class="content-area">
                <!-- Dashboard Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">R$ <?php echo number_format($pagas, 2, ',', '.'); ?></div>
                        <div class="stat-label">Pagas</div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-number">R$ <?php echo number_format($dados_total['valor_total'] - $pagas, 2, ',', '.'); ?></div>
                        <div class="stat-label">A Pagar</div>
                    </div>
                    <div class="stat-card danger">
                        <div class="stat-number"><?php echo $vencidas; ?></div>
                        <div class="stat-label">Vencidas</div>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="stats-grid">
                    <div class="chart-container">
                        <h3 class="chart-title">Gastos Mensais</h3>
                        <canvas id="chartGastos" width="400" height="200"></canvas>
                    </div>
                    <div class="chart-container">
                        <h3 class="chart-title">Vencimentos do Mês</h3>
                        <canvas id="chartVencimentos" width="400" height="200"></canvas>
                    </div>
                </div>

                <!-- Botão Nova Conta -->
                <div style="margin-bottom: 20px;">
                    <button class="btn btn-primary" onclick="abrirModal()">
                        <i class="fas fa-plus"></i> Nova Conta
                    </button>
                </div>

                <!-- Filtros e Tabela -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Lista de Contas a Pagar</h3>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="filtro" placeholder="Buscar por descrição, fornecedor..." value="<?php echo htmlspecialchars($filtro); ?>">
                            <select id="status">
                                <option value="">Todos os Status</option>
                                <option value="Pendente" <?php echo $status_filtro == 'Pendente' ? 'selected' : ''; ?>>Pendente</option>
                                <option value="Paga" <?php echo $status_filtro == 'Paga' ? 'selected' : ''; ?>>Paga</option>
                                <option value="Vencida" <?php echo $status_filtro == 'Vencida' ? 'selected' : ''; ?>>Vencida</option>
                            </select>
                            <button class="btn btn-primary" onclick="filtrar()">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <button class="btn btn-secondary" onclick="limparFiltros()">
                                <i class="fas fa-times"></i> Limpar
                            </button>
                        </div>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Descrição</th>
                                <th>Fornecedor</th>
                                <th>Valor</th>
                                <th>Vencimento</th>
                                <th>Status</th>
                                <th>Valor Pago</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contas as $conta): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($conta['descricao']); ?></td>
                                <td><?php echo htmlspecialchars($conta['fornecedor']); ?></td>
                                <td>R$ <?php echo number_format($conta['valor'], 2, ',', '.'); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($conta['data_vencimento'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($conta['status']); ?>">
                                        <?php echo $conta['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $conta['valor_pago'] ? 'R$ ' . number_format($conta['valor_pago'], 2, ',', '.') : '-'; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editarConta(<?php echo $conta['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="excluirConta(<?php echo $conta['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Preparar dados para os gráficos
        const dadosGastos = <?php echo json_encode($dados_gastos); ?>;
        const dadosVencimentos = <?php echo json_encode($dados_vencimentos); ?>;

        // Gráfico de Gastos
        const ctxGastos = document.getElementById('chartGastos').getContext('2d');
        new Chart(ctxGastos, {
            type: 'line',
            data: {
                labels: dadosGastos.map(item => {
                    const [ano, mes] = item.mes.split('-');
                    return `${mes}/${ano}`;
                }),
                datasets: [{
                    label: 'Gastos Mensais',
                    data: dadosGastos.map(item => item.pago),
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Gráfico de Vencimentos
        const ctxVencimentos = document.getElementById('chartVencimentos').getContext('2d');
        new Chart(ctxVencimentos, {
            type: 'doughnut',
            data: {
                labels: dadosVencimentos.map(item => item.status),
                datasets: [{
                    data: dadosVencimentos.map(item => item.quantidade),
                    backgroundColor: [
                        '#f59e0b', // Pendente
                        '#10b981', // Paga
                        '#ef4444', // Vencida
                        '#6b7280'  // Cancelada
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            }
        });

        function filtrar() {
            const filtro = document.getElementById('filtro').value;
            const status = document.getElementById('status').value;
            window.location.href = `contas_pagar.php?filtro=${filtro}&status=${status}`;
        }

        function limparFiltros() {
            window.location.href = 'contas_pagar.php';
        }

        function abrirModal() {
            console.log('Abrir modal nova conta');
        }

        function editarConta(id) {
            console.log('Editar conta:', id);
        }

        function excluirConta(id) {
            if (confirm('Tem certeza que deseja excluir esta conta?')) {
                console.log('Excluir conta:', id);
            }
        }
    </script>

    <style>
        .status-pendente {
            background: #fef3c7;
            color: #92400e;
        }

        .status-paga {
            background: #d1fae5;
            color: #065f46;
        }

        .status-vencida {
            background: #fee2e2;
            color: #991b1b;
        }

        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: 300px;
        }

        .chart-title {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 16px;
            font-weight: 600;
        }
    </style>
</body>
</html>

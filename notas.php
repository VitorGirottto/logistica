<?php
// Incluir configurações (que já gerencia a sessão)
require_once 'config/config.php';
require_once 'includes/verificaLogin.php';

$database = new Database();
$db = $database->getConnection();

// Processar ações (criar, editar, excluir)
if ($_POST) {
    if (isset($_POST['acao'])) {
        switch ($_POST['acao']) {
            case 'criar':
                $query = "INSERT INTO notas (numero, modelo, serie, valor, data_emissao, data_vencimento, cliente, cnpj_cpf, descricao, status, observacoes) 
                         VALUES (:numero, :modelo, :serie, :valor, :data_emissao, :data_vencimento, :cliente, :cnpj_cpf, :descricao, :status, :observacoes)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':numero', $_POST['numero']);
                $stmt->bindParam(':modelo', $_POST['modelo']);
                $stmt->bindParam(':serie', $_POST['serie']);
                $stmt->bindParam(':valor', $_POST['valor']);
                $stmt->bindParam(':data_emissao', $_POST['data_emissao']);
                $stmt->bindParam(':data_vencimento', $_POST['data_vencimento']);
                $stmt->bindParam(':cliente', $_POST['cliente']);
                $stmt->bindParam(':cnpj_cpf', $_POST['cnpj_cpf']);
                $stmt->bindParam(':descricao', $_POST['descricao']);
                $stmt->bindParam(':status', $_POST['status']);
                $stmt->bindParam(':observacoes', $_POST['observacoes']);
                
                if ($stmt->execute()) {
                    $mensagem = "Nota criada com sucesso!";
                    $tipo_mensagem = "success";
                } else {
                    $mensagem = "Erro ao criar nota!";
                    $tipo_mensagem = "error";
                }
                break;
                
            case 'editar':
                $query = "UPDATE notas SET numero = :numero, modelo = :modelo, serie = :serie, valor = :valor, 
                         data_emissao = :data_emissao, data_vencimento = :data_vencimento, cliente = :cliente, 
                         cnpj_cpf = :cnpj_cpf, descricao = :descricao, status = :status, observacoes = :observacoes 
                         WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $_POST['id']);
                $stmt->bindParam(':numero', $_POST['numero']);
                $stmt->bindParam(':modelo', $_POST['modelo']);
                $stmt->bindParam(':serie', $_POST['serie']);
                $stmt->bindParam(':valor', $_POST['valor']);
                $stmt->bindParam(':data_emissao', $_POST['data_emissao']);
                $stmt->bindParam(':data_vencimento', $_POST['data_vencimento']);
                $stmt->bindParam(':cliente', $_POST['cliente']);
                $stmt->bindParam(':cnpj_cpf', $_POST['cnpj_cpf']);
                $stmt->bindParam(':descricao', $_POST['descricao']);
                $stmt->bindParam(':status', $_POST['status']);
                $stmt->bindParam(':observacoes', $_POST['observacoes']);
                
                if ($stmt->execute()) {
                    $mensagem = "Nota atualizada com sucesso!";
                    $tipo_mensagem = "success";
                } else {
                    $mensagem = "Erro ao atualizar nota!";
                    $tipo_mensagem = "error";
                }
                break;
                
            case 'excluir':
                $query = "DELETE FROM notas WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $_POST['id']);
                
                if ($stmt->execute()) {
                    $mensagem = "Nota excluída com sucesso!";
                    $tipo_mensagem = "success";
                } else {
                    $mensagem = "Erro ao excluir nota!";
                    $tipo_mensagem = "error";
                }
                break;
        }
    }
}

// Buscar dados para dashboard
$query_total = "SELECT COUNT(*) as total FROM notas";
$stmt_total = $db->prepare($query_total);
$stmt_total->execute();
$total_notas = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];

$query_mes = "SELECT COUNT(*) as total FROM notas WHERE MONTH(data_emissao) = MONTH(CURRENT_DATE()) AND YEAR(data_emissao) = YEAR(CURRENT_DATE())";
$stmt_mes = $db->prepare($query_mes);
$stmt_mes->execute();
$notas_mes = $stmt_mes->fetch(PDO::FETCH_ASSOC)['total'];

// Dados para gráficos
$query_mensal = "SELECT 
    DATE_FORMAT(data_emissao, '%Y-%m') as mes,
    COUNT(*) as quantidade
    FROM notas 
    WHERE data_emissao >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(data_emissao, '%Y-%m')
    ORDER BY mes";
$stmt_mensal = $db->prepare($query_mensal);
$stmt_mensal->execute();
$dados_mensais = $stmt_mensal->fetchAll(PDO::FETCH_ASSOC);

$query_modelo = "SELECT 
    modelo,
    COUNT(*) as quantidade
    FROM notas 
    WHERE MONTH(data_emissao) = MONTH(CURRENT_DATE()) AND YEAR(data_emissao) = YEAR(CURRENT_DATE())
    GROUP BY modelo";
$stmt_modelo = $db->prepare($query_modelo);
$stmt_modelo->execute();
$dados_modelo = $stmt_modelo->fetchAll(PDO::FETCH_ASSOC);

// Buscar notas para listagem
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : '';
$status_filtro = isset($_GET['status']) ? $_GET['status'] : '';

$query = "SELECT * FROM notas WHERE 1=1";
$params = [];

if (!empty($filtro)) {
    $query .= " AND (numero LIKE ? OR cliente LIKE ? OR descricao LIKE ?)";
    $params[] = "%$filtro%";
    $params[] = "%$filtro%";
    $params[] = "%$filtro%";
}

if (!empty($status_filtro)) {
    $query .= " AND status = ?";
    $params[] = $status_filtro;
}

$query .= " ORDER BY data_emissao DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$notas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notas Fiscais - Sistema Logística</title>
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
                    <i class="fas fa-file-invoice"></i>
                    Notas Fiscais
                </h1>
                <div class="user-info">
                    <span>Bem-vindo, <?php echo $_SESSION['usuario_nome']; ?></span>
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['usuario_nome'], 0, 1)); ?>
                    </div>
                </div>
            </div>

            <div class="content-area">
                <?php if (isset($mensagem)): ?>
                    <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                        <?php echo $mensagem; ?>
                    </div>
                <?php endif; ?>

                <!-- Dashboard Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $total_notas; ?></div>
                        <div class="stat-label">Total de Notas</div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-number"><?php echo $notas_mes; ?></div>
                        <div class="stat-label">Notas do Mês</div>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="stats-grid">
                    <div class="chart-container">
                        <h3 class="chart-title">Notas por Emissão Mensal</h3>
                        <canvas id="chartMensal"></canvas>
                    </div>
                    <div class="chart-container">
                        <h3 class="chart-title">Notas por Modelo (Mês Atual)</h3>
                        <canvas id="chartModelo"></canvas>
                    </div>
                </div>

                <!-- Botão Nova Nota -->
                <div style="margin-bottom: 20px;">
                    <button class="btn btn-primary" onclick="abrirModal()">
                        <i class="fas fa-plus"></i> Nova Nota
                    </button>
                </div>

                <!-- Filtros -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Lista de Notas Fiscais</h3>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="filtro" placeholder="Buscar por número, cliente..." value="<?php echo htmlspecialchars($filtro); ?>">
                            <select id="status">
                                <option value="">Todos os Status</option>
                                <option value="Emitida" <?php echo $status_filtro == 'Emitida' ? 'selected' : ''; ?>>Emitida</option>
                                <option value="Cancelada" <?php echo $status_filtro == 'Cancelada' ? 'selected' : ''; ?>>Cancelada</option>
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
                                <th>Número</th>
                                <th>Cliente</th>
                                <th>Modelo</th>
                                <th>Valor</th>
                                <th>Data Emissão</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notas as $nota): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($nota['numero']); ?></td>
                                <td><?php echo htmlspecialchars($nota['cliente']); ?></td>
                                <td><?php echo htmlspecialchars($nota['modelo']); ?></td>
                                <td>R$ <?php echo number_format($nota['valor'], 2, ',', '.'); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($nota['data_emissao'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($nota['status']); ?>">
                                        <?php echo $nota['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editarNota(<?php echo htmlspecialchars(json_encode($nota)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="excluirNota(<?php echo $nota['id']; ?>)">
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

    <!-- Modal Nova/Editar Nota -->
    <div id="modalNota" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Nova Nota Fiscal</h3>
                <span class="close" onclick="fecharModal()">&times;</span>
            </div>
            <form method="POST" id="formNota">
                <input type="hidden" name="acao" id="acao" value="criar">
                <input type="hidden" id="notaId" name="id">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="numero">Número:</label>
                        <input type="text" id="numero" name="numero" required>
                    </div>
                    <div class="form-group">
                        <label for="cliente">Cliente:</label>
                        <input type="text" id="cliente" name="cliente" required>
                    </div>
                    <div class="form-group">
                        <label for="modelo">Modelo:</label>
                        <select id="modelo" name="modelo" required>
                            <option value="">Selecione...</option>
                            <option value="NFe">NFe - Nota Fiscal Eletrônica</option>
                            <option value="NFCe">NFCe - Nota Fiscal Consumidor</option>
                            <option value="NFSe">NFSe - Nota Fiscal Serviço</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="serie">Série:</label>
                        <input type="text" id="serie" name="serie" required>
                    </div>
                    <div class="form-group">
                        <label for="valor">Valor:</label>
                        <input type="number" id="valor" name="valor" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="data_emissao">Data Emissão:</label>
                        <input type="date" id="data_emissao" name="data_emissao" required>
                    </div>
                    <div class="form-group">
                        <label for="data_vencimento">Data Vencimento:</label>
                        <input type="date" id="data_vencimento" name="data_vencimento">
                    </div>
                    <div class="form-group">
                        <label for="cnpj_cpf">CNPJ/CPF:</label>
                        <input type="text" id="cnpj_cpf" name="cnpj_cpf">
                    </div>
                    <div class="form-group">
                        <label for="status_nota">Status:</label>
                        <select id="status_nota" name="status" required>
                            <option value="Emitida">Emitida</option>
                            <option value="Cancelada">Cancelada</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="descricao">Descrição:</label>
                    <textarea id="descricao" name="descricao" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="observacoes">Observações:</label>
                    <textarea id="observacoes" name="observacoes" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Gráfico Mensal
        const ctxMensal = document.getElementById('chartMensal').getContext('2d');
        new Chart(ctxMensal, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($dados_mensais, 'mes')); ?>,
                datasets: [{
                    label: 'Notas Emitidas',
                    data: <?php echo json_encode(array_column($dados_mensais, 'quantidade')); ?>,
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Gráfico por Modelo
        const ctxModelo = document.getElementById('chartModelo').getContext('2d');
        new Chart(ctxModelo, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($dados_modelo, 'modelo')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($dados_modelo, 'quantidade')); ?>,
                    backgroundColor: ['#8b5cf6', '#f59e0b', '#10b981', '#ef4444']
                }]
            },
            options: {
                responsive: true
            }
        });

        // Funções JavaScript
        function abrirModal() {
            document.getElementById('modalNota').style.display = 'block';
            document.getElementById('modalTitle').textContent = 'Nova Nota Fiscal';
            document.getElementById('formNota').reset();
            document.getElementById('notaId').value = '';
            document.getElementById('acao').value = 'criar';
        }

        function fecharModal() {
            document.getElementById('modalNota').style.display = 'none';
        }

        function filtrar() {
            const filtro = document.getElementById('filtro').value;
            const status = document.getElementById('status').value;
            window.location.href = `notas.php?filtro=${filtro}&status=${status}`;
        }

        function limparFiltros() {
            window.location.href = 'notas.php';
        }

        function editarNota(nota) {
            document.getElementById('acao').value = 'editar';
            document.getElementById('notaId').value = nota.id;
            document.getElementById('modalTitle').textContent = 'Editar Nota Fiscal';
            
            document.getElementById('numero').value = nota.numero;
            document.getElementById('cliente').value = nota.cliente;
            document.getElementById('modelo').value = nota.modelo;
            document.getElementById('serie').value = nota.serie || '';
            document.getElementById('valor').value = nota.valor;
            document.getElementById('data_emissao').value = nota.data_emissao;
            document.getElementById('data_vencimento').value = nota.data_vencimento || '';
            document.getElementById('cnpj_cpf').value = nota.cnpj_cpf || '';
            document.getElementById('status_nota').value = nota.status;
            document.getElementById('descricao').value = nota.descricao || '';
            document.getElementById('observacoes').value = nota.observacoes || '';
            
            abrirModal();
        }

        function excluirNota(id) {
            if (confirm('Tem certeza que deseja excluir esta nota?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const modal = document.getElementById('modalNota');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>

    <style>
        /* Modal Styles */
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            color: #333;
        }

        .close {
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #999;
        }

        .close:hover {
            color: #333;
        }

        .modal form {
            padding: 25px;
        }

        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .status-emitida {
            background: #d1fae5;
            color: #065f46;
        }

        .status-cancelada {
            background: #fee2e2;
            color: #991b1b;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</body>
</html>

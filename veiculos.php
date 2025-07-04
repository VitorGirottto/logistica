<?php
require_once 'includes/verificaLogin.php';

$database = new Database();
$db = $database->getConnection();

$mensagem = '';
$tipo_mensagem = '';

// Processar ações
if ($_POST) {
    if (isset($_POST['acao'])) {
        switch ($_POST['acao']) {
            case 'criar':
                $modelo = limparInput($_POST['modelo']);
                $placa = limparInput($_POST['placa']);
                $status = limparInput($_POST['status']);
                $em_manutencao = isset($_POST['em_manutencao']) ? 1 : 0;
                
                $query = "INSERT INTO veiculos (modelo, placa, status, em_manutencao) VALUES (:modelo, :placa, :status, :em_manutencao)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':modelo', $modelo);
                $stmt->bindParam(':placa', $placa);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':em_manutencao', $em_manutencao);
                
                if ($stmt->execute()) {
                    $mensagem = 'Veículo criado com sucesso!';
                    $tipo_mensagem = 'success';
                } else {
                    $mensagem = 'Erro ao criar veículo!';
                    $tipo_mensagem = 'error';
                }
                break;
                
            case 'editar':
                $id = intval($_POST['id']);
                $modelo = limparInput($_POST['modelo']);
                $placa = limparInput($_POST['placa']);
                $status = limparInput($_POST['status']);
                $em_manutencao = isset($_POST['em_manutencao']) ? 1 : 0;
                
                $query = "UPDATE veiculos SET modelo = :modelo, placa = :placa, status = :status, em_manutencao = :em_manutencao WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':modelo', $modelo);
                $stmt->bindParam(':placa', $placa);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':em_manutencao', $em_manutencao);
                
                if ($stmt->execute()) {
                    $mensagem = 'Veículo atualizado com sucesso!';
                    $tipo_mensagem = 'success';
                } else {
                    $mensagem = 'Erro ao atualizar veículo!';
                    $tipo_mensagem = 'error';
                }
                break;
                
            case 'excluir':
                $id = intval($_POST['id']);
                
                // Verificar se o veículo tem entregas associadas
                $query = "SELECT COUNT(*) as total FROM entregas WHERE veiculo_id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $entregas_associadas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                if ($entregas_associadas > 0) {
                    $mensagem = 'Não é possível excluir este veículo pois ele possui entregas associadas!';
                    $tipo_mensagem = 'error';
                } else {
                    $query = "DELETE FROM veiculos WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':id', $id);
                    
                    if ($stmt->execute()) {
                        $mensagem = 'Veículo excluído com sucesso!';
                        $tipo_mensagem = 'success';
                    } else {
                        $mensagem = 'Erro ao excluir veículo!';
                        $tipo_mensagem = 'error';
                    }
                }
                break;
        }
    }
}

// Buscar veículos
$query = "SELECT v.*, COUNT(e.id) as total_entregas 
          FROM veiculos v 
          LEFT JOIN entregas e ON v.id = e.veiculo_id 
          GROUP BY v.id 
          ORDER BY v.modelo";
$stmt = $db->prepare($query);
$stmt->execute();
$veiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veículos - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Gestão de Veículos</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($usuario_logado['nome'], 0, 1)); ?>
                    </div>
                    <span><?php echo $usuario_logado['nome']; ?></span>
                    <a href="logout.php" class="btn btn-danger btn-sm">Sair</a>
                </div>
            </div>
            
            <div class="content-area">
                <?php if ($mensagem): ?>
                    <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                        <?php echo $mensagem; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Formulário para novo veículo -->
                <div class="form-container">
                    <h3>Novo Veículo</h3>
                    <form method="POST">
                        <input type="hidden" name="acao" value="criar">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="modelo">Modelo:</label>
                                <input type="text" id="modelo" name="modelo" required>
                            </div>
                            <div class="form-group">
                                <label for="placa">Placa:</label>
                                <input type="text" id="placa" name="placa" required>
                            </div>
                            <div class="form-group">
                                <label for="status">Status:</label>
                                <select id="status" name="status" required>
                                    <option value="Ativo">Ativo</option>
                                    <option value="Inativo">Inativo</option>
                                    <option value="Manutenção">Manutenção</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="em_manutencao"> Em Manutenção
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Criar Veículo</button>
                    </form>
                </div>
                
                <!-- Lista de veículos -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Lista de Veículos</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Modelo</th>
                                <th>Placa</th>
                                <th>Status</th>
                                <th>Manutenção</th>
                                <th>Total Entregas</th>
                                <th>Cadastrado em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($veiculos as $veiculo): ?>
                            <tr>
                                <td>#<?php echo $veiculo['id']; ?></td>
                                <td><?php echo $veiculo['modelo']; ?></td>
                                <td><?php echo $veiculo['placa']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($veiculo['status']); ?>">
                                        <?php echo $veiculo['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $veiculo['em_manutencao'] ? 'status-atrasada' : 'status-entregue'; ?>">
                                        <?php echo $veiculo['em_manutencao'] ? 'Sim' : 'Não'; ?>
                                    </span>
                                </td>
                                <td><?php echo $veiculo['total_entregas']; ?></td>
                                <td><?php echo formatarData($veiculo['criado_em']); ?></td>
                                <td>
                                    <button onclick="editarVeiculo(<?php echo htmlspecialchars(json_encode($veiculo)); ?>)" 
                                            class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="excluirVeiculo(<?php echo $veiculo['id']; ?>)" 
                                            class="btn btn-danger btn-sm">
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
    
    <!-- Modal para editar veículo -->
    <div id="modalEditar" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 500px;">
            <h3>Editar Veículo</h3>
            <form method="POST" id="formEditar">
                <input type="hidden" name="acao" value="editar">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label for="edit_modelo">Modelo:</label>
                    <input type="text" id="edit_modelo" name="modelo" required>
                </div>
                <div class="form-group">
                    <label for="edit_placa">Placa:</label>
                    <input type="text" id="edit_placa" name="placa" required>
                </div>
                <div class="form-group">
                    <label for="edit_status">Status:</label>
                    <select id="edit_status" name="status" required>
                        <option value="Ativo">Ativo</option>
                        <option value="Inativo">Inativo</option>
                        <option value="Manutenção">Manutenção</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="edit_em_manutencao" name="em_manutencao"> Em Manutenção
                    </label>
                </div>
                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <button type="button" onclick="fecharModal()" class="btn btn-danger">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function editarVeiculo(veiculo) {
            document.getElementById('edit_id').value = veiculo.id;
            document.getElementById('edit_modelo').value = veiculo.modelo;
            document.getElementById('edit_placa').value = veiculo.placa;
            document.getElementById('edit_status').value = veiculo.status;
            document.getElementById('edit_em_manutencao').checked = veiculo.em_manutencao == 1;
            
            document.getElementById('modalEditar').style.display = 'block';
        }
        
        function fecharModal() {
            document.getElementById('modalEditar').style.display = 'none';
        }
        
        function excluirVeiculo(id) {
            if (confirm('Tem certeza que deseja excluir este veículo?')) {
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
    </script>
</body>
</html>

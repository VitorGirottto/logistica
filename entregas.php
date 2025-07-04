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
                $origem = limparInput($_POST['origem']);
                $destino = limparInput($_POST['destino']);
                $status = limparInput($_POST['status']);
                $custo = floatval($_POST['custo']);
                $motorista_id = !empty($_POST['motorista_id']) ? intval($_POST['motorista_id']) : null;
                $veiculo_id = !empty($_POST['veiculo_id']) ? intval($_POST['veiculo_id']) : null;
                $km = floatval($_POST['km']);
                $regiao = limparInput($_POST['regiao']);
                
                $query = "INSERT INTO entregas (origem, destino, status, custo, motorista_id, veiculo_id, km, regiao) 
                         VALUES (:origem, :destino, :status, :custo, :motorista_id, :veiculo_id, :km, :regiao)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':origem', $origem);
                $stmt->bindParam(':destino', $destino);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':custo', $custo);
                $stmt->bindParam(':motorista_id', $motorista_id);
                $stmt->bindParam(':veiculo_id', $veiculo_id);
                $stmt->bindParam(':km', $km);
                $stmt->bindParam(':regiao', $regiao);
                
                if ($stmt->execute()) {
                    $mensagem = 'Entrega criada com sucesso!';
                    $tipo_mensagem = 'success';
                } else {
                    $mensagem = 'Erro ao criar entrega!';
                    $tipo_mensagem = 'error';
                }
                break;
                
            case 'editar':
                $id = intval($_POST['id']);
                $origem = limparInput($_POST['origem']);
                $destino = limparInput($_POST['destino']);
                $status = limparInput($_POST['status']);
                $custo = floatval($_POST['custo']);
                $motorista_id = !empty($_POST['motorista_id']) ? intval($_POST['motorista_id']) : null;
                $veiculo_id = !empty($_POST['veiculo_id']) ? intval($_POST['veiculo_id']) : null;
                $km = floatval($_POST['km']);
                $regiao = limparInput($_POST['regiao']);
                
                $query = "UPDATE entregas SET origem = :origem, destino = :destino, status = :status, 
                         custo = :custo, motorista_id = :motorista_id, veiculo_id = :veiculo_id, 
                         km = :km, regiao = :regiao WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':origem', $origem);
                $stmt->bindParam(':destino', $destino);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':custo', $custo);
                $stmt->bindParam(':motorista_id', $motorista_id);
                $stmt->bindParam(':veiculo_id', $veiculo_id);
                $stmt->bindParam(':km', $km);
                $stmt->bindParam(':regiao', $regiao);
                
                if ($stmt->execute()) {
                    $mensagem = 'Entrega atualizada com sucesso!';
                    $tipo_mensagem = 'success';
                } else {
                    $mensagem = 'Erro ao atualizar entrega!';
                    $tipo_mensagem = 'error';
                }
                break;
                
            case 'excluir':
                $id = intval($_POST['id']);
                
                $query = "DELETE FROM entregas WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    $mensagem = 'Entrega excluída com sucesso!';
                    $tipo_mensagem = 'success';
                } else {
                    $mensagem = 'Erro ao excluir entrega!';
                    $tipo_mensagem = 'error';
                }
                break;
        }
    }
}

// Buscar entregas
$query = "SELECT e.*, m.nome as motorista_nome, v.modelo as veiculo_modelo 
          FROM entregas e 
          LEFT JOIN motoristas m ON e.motorista_id = m.id 
          LEFT JOIN veiculos v ON e.veiculo_id = v.id 
          ORDER BY e.criado_em DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$entregas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar motoristas para select
$query = "SELECT id, nome FROM motoristas WHERE ativo = 1";
$stmt = $db->prepare($query);
$stmt->execute();
$motoristas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar veículos para select
$query = "SELECT id, modelo, placa FROM veiculos WHERE status = 'Ativo'";
$stmt = $db->prepare($query);
$stmt->execute();
$veiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entregas - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Gestão de Entregas</h1>
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
                
                <!-- Formulário para nova entrega -->
                <div class="form-container">
                    <h3>Nova Entrega</h3>
                    <form method="POST">
                        <input type="hidden" name="acao" value="criar">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="origem">Origem:</label>
                                <input type="text" id="origem" name="origem" required>
                            </div>
                            <div class="form-group">
                                <label for="destino">Destino:</label>
                                <input type="text" id="destino" name="destino" required>
                            </div>
                            <div class="form-group">
                                <label for="status">Status:</label>
                                <select id="status" name="status" required>
                                    <option value="Pendente">Pendente</option>
                                    <option value="Em Trânsito">Em Trânsito</option>
                                    <option value="Entregue">Entregue</option>
                                    <option value="Atrasada">Atrasada</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="custo">Custo (R$):</label>
                                <input type="number" id="custo" name="custo" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label for="motorista_id">Motorista:</label>
                                <select id="motorista_id" name="motorista_id">
                                    <option value="">Selecione um motorista</option>
                                    <?php foreach ($motoristas as $motorista): ?>
                                        <option value="<?php echo $motorista['id']; ?>">
                                            <?php echo $motorista['nome']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="veiculo_id">Veículo:</label>
                                <select id="veiculo_id" name="veiculo_id">
                                    <option value="">Selecione um veículo</option>
                                    <?php foreach ($veiculos as $veiculo): ?>
                                        <option value="<?php echo $veiculo['id']; ?>">
                                            <?php echo $veiculo['modelo'] . ' - ' . $veiculo['placa']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="km">Quilometragem:</label>
                                <input type="number" id="km" name="km" step="0.1">
                            </div>
                            <div class="form-group">
                                <label for="regiao">Região:</label>
                                <select id="regiao" name="regiao" required>
                                    <option value="Norte">Norte</option>
                                    <option value="Nordeste">Nordeste</option>
                                    <option value="Centro-Oeste">Centro-Oeste</option>
                                    <option value="Sudeste">Sudeste</option>
                                    <option value="Sul">Sul</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Criar Entrega</button>
                    </form>
                </div>
                
                <!-- Lista de entregas -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Lista de Entregas</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Origem</th>
                                <th>Destino</th>
                                <th>Status</th>
                                <th>Motorista</th>
                                <th>Veículo</th>
                                <th>Custo</th>
                                <th>KM</th>
                                <th>Região</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entregas as $entrega): ?>
                            <tr>
                                <td>#<?php echo $entrega['id']; ?></td>
                                <td><?php echo $entrega['origem']; ?></td>
                                <td><?php echo $entrega['destino']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '', $entrega['status'])); ?>">
                                        <?php echo $entrega['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo $entrega['motorista_nome'] ?: '-'; ?></td>
                                <td><?php echo $entrega['veiculo_modelo'] ?: '-'; ?></td>
                                <td><?php echo formatarMoeda($entrega['custo']); ?></td>
                                <td><?php echo $entrega['km'] ? number_format($entrega['km'], 1) . ' km' : '-'; ?></td>
                                <td><?php echo $entrega['regiao']; ?></td>
                                <td>
                                    <button onclick="editarEntrega(<?php echo htmlspecialchars(json_encode($entrega)); ?>)" 
                                            class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="excluirEntrega(<?php echo $entrega['id']; ?>)" 
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
    
    <!-- Modal para editar entrega -->
    <div id="modalEditar" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 600px;">
            <h3>Editar Entrega</h3>
            <form method="POST" id="formEditar">
                <input type="hidden" name="acao" value="editar">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_origem">Origem:</label>
                        <input type="text" id="edit_origem" name="origem" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_destino">Destino:</label>
                        <input type="text" id="edit_destino" name="destino" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_status">Status:</label>
                        <select id="edit_status" name="status" required>
                            <option value="Pendente">Pendente</option>
                            <option value="Em Trânsito">Em Trânsito</option>
                            <option value="Entregue">Entregue</option>
                            <option value="Atrasada">Atrasada</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_custo">Custo (R$):</label>
                        <input type="number" id="edit_custo" name="custo" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_motorista_id">Motorista:</label>
                        <select id="edit_motorista_id" name="motorista_id">
                            <option value="">Selecione um motorista</option>
                            <?php foreach ($motoristas as $motorista): ?>
                                <option value="<?php echo $motorista['id']; ?>">
                                    <?php echo $motorista['nome']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_veiculo_id">Veículo:</label>
                        <select id="edit_veiculo_id" name="veiculo_id">
                            <option value="">Selecione um veículo</option>
                            <?php foreach ($veiculos as $veiculo): ?>
                                <option value="<?php echo $veiculo['id']; ?>">
                                    <?php echo $veiculo['modelo'] . ' - ' . $veiculo['placa']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_km">Quilometragem:</label>
                        <input type="number" id="edit_km" name="km" step="0.1">
                    </div>
                    <div class="form-group">
                        <label for="edit_regiao">Região:</label>
                        <select id="edit_regiao" name="regiao" required>
                            <option value="Norte">Norte</option>
                            <option value="Nordeste">Nordeste</option>
                            <option value="Centro-Oeste">Centro-Oeste</option>
                            <option value="Sudeste">Sudeste</option>
                            <option value="Sul">Sul</option>
                        </select>
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <button type="button" onclick="fecharModal()" class="btn btn-danger">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function editarEntrega(entrega) {
            document.getElementById('edit_id').value = entrega.id;
            document.getElementById('edit_origem').value = entrega.origem;
            document.getElementById('edit_destino').value = entrega.destino;
            document.getElementById('edit_status').value = entrega.status;
            document.getElementById('edit_custo').value = entrega.custo;
            document.getElementById('edit_motorista_id').value = entrega.motorista_id || '';
            document.getElementById('edit_veiculo_id').value = entrega.veiculo_id || '';
            document.getElementById('edit_km').value = entrega.km || '';
            document.getElementById('edit_regiao').value = entrega.regiao;
            
            document.getElementById('modalEditar').style.display = 'block';
        }
        
        function fecharModal() {
            document.getElementById('modalEditar').style.display = 'none';
        }
        
        function excluirEntrega(id) {
            if (confirm('Tem certeza que deseja excluir esta entrega?')) {
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

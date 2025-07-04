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
                $nome = limparInput($_POST['nome']);
                $turno = limparInput($_POST['turno']);
                $ativo = isset($_POST['ativo']) ? 1 : 0;
                
                $query = "INSERT INTO motoristas (nome, turno, ativo) VALUES (:nome, :turno, :ativo)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':turno', $turno);
                $stmt->bindParam(':ativo', $ativo);
                
                if ($stmt->execute()) {
                    $mensagem = 'Motorista criado com sucesso!';
                    $tipo_mensagem = 'success';
                } else {
                    $mensagem = 'Erro ao criar motorista!';
                    $tipo_mensagem = 'error';
                }
                break;
                
            case 'editar':
                $id = intval($_POST['id']);
                $nome = limparInput($_POST['nome']);
                $turno = limparInput($_POST['turno']);
                $ativo = isset($_POST['ativo']) ? 1 : 0;
                
                $query = "UPDATE motoristas SET nome = :nome, turno = :turno, ativo = :ativo WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':turno', $turno);
                $stmt->bindParam(':ativo', $ativo);
                
                if ($stmt->execute()) {
                    $mensagem = 'Motorista atualizado com sucesso!';
                    $tipo_mensagem = 'success';
                } else {
                    $mensagem = 'Erro ao atualizar motorista!';
                    $tipo_mensagem = 'error';
                }
                break;
                
            case 'excluir':
                $id = intval($_POST['id']);
                
                // Verificar se o motorista tem entregas associadas
                $query = "SELECT COUNT(*) as total FROM entregas WHERE motorista_id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $entregas_associadas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                if ($entregas_associadas > 0) {
                    $mensagem = 'Não é possível excluir este motorista pois ele possui entregas associadas!';
                    $tipo_mensagem = 'error';
                } else {
                    $query = "DELETE FROM motoristas WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':id', $id);
                    
                    if ($stmt->execute()) {
                        $mensagem = 'Motorista excluído com sucesso!';
                        $tipo_mensagem = 'success';
                    } else {
                        $mensagem = 'Erro ao excluir motorista!';
                        $tipo_mensagem = 'error';
                    }
                }
                break;
        }
    }
}

// Buscar motoristas
$query = "SELECT m.*, COUNT(e.id) as total_entregas 
          FROM motoristas m 
          LEFT JOIN entregas e ON m.id = e.motorista_id 
          GROUP BY m.id 
          ORDER BY m.nome";
$stmt = $db->prepare($query);
$stmt->execute();
$motoristas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motoristas - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Gestão de Motoristas</h1>
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
                
                <!-- Formulário para novo motorista -->
                <div class="form-container">
                    <h3>Novo Motorista</h3>
                    <form method="POST">
                        <input type="hidden" name="acao" value="criar">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nome">Nome:</label>
                                <input type="text" id="nome" name="nome" required>
                            </div>
                            <div class="form-group">
                                <label for="turno">Turno:</label>
                                <select id="turno" name="turno" required>
                                    <option value="Manhã">Manhã</option>
                                    <option value="Tarde">Tarde</option>
                                    <option value="Noite">Noite</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="ativo" checked> Ativo
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Criar Motorista</button>
                    </form>
                </div>
                
                <!-- Lista de motoristas -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Lista de Motoristas</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Turno</th>
                                <th>Status</th>
                                <th>Total Entregas</th>
                                <th>Cadastrado em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($motoristas as $motorista): ?>
                            <tr>
                                <td>#<?php echo $motorista['id']; ?></td>
                                <td><?php echo $motorista['nome']; ?></td>
                                <td><?php echo $motorista['turno']; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $motorista['ativo'] ? 'status-entregue' : 'status-atrasada'; ?>">
                                        <?php echo $motorista['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                    </span>
                                </td>
                                <td><?php echo $motorista['total_entregas']; ?></td>
                                <td><?php echo formatarData($motorista['criado_em']); ?></td>
                                <td>
                                    <button onclick="editarMotorista(<?php echo htmlspecialchars(json_encode($motorista)); ?>)" 
                                            class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="excluirMotorista(<?php echo $motorista['id']; ?>)" 
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
    
    <!-- Modal para editar motorista -->
    <div id="modalEditar" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 500px;">
            <h3>Editar Motorista</h3>
            <form method="POST" id="formEditar">
                <input type="hidden" name="acao" value="editar">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label for="edit_nome">Nome:</label>
                    <input type="text" id="edit_nome" name="nome" required>
                </div>
                <div class="form-group">
                    <label for="edit_turno">Turno:</label>
                    <select id="edit_turno" name="turno" required>
                        <option value="Manhã">Manhã</option>
                        <option value="Tarde">Tarde</option>
                        <option value="Noite">Noite</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="edit_ativo" name="ativo"> Ativo
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
        function editarMotorista(motorista) {
            document.getElementById('edit_id').value = motorista.id;
            document.getElementById('edit_nome').value = motorista.nome;
            document.getElementById('edit_turno').value = motorista.turno;
            document.getElementById('edit_ativo').checked = motorista.ativo == 1;
            
            document.getElementById('modalEditar').style.display = 'block';
        }
        
        function fecharModal() {
            document.getElementById('modalEditar').style.display = 'none';
        }
        
        function excluirMotorista(id) {
            if (confirm('Tem certeza que deseja excluir este motorista?')) {
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

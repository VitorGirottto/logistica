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
            case 'atualizar':
                $nome = limparInput($_POST['nome']);
                $email = limparInput($_POST['email']);
                $cargo = limparInput($_POST['cargo']);
                $biografia = limparInput($_POST['biografia']);
                
                // Verificar se o email já existe para outro usuário
                $query = "SELECT id FROM usuarios WHERE email = :email AND id != :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':id', $_SESSION['usuario_id']);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $mensagem = 'Este e-mail já está sendo usado por outro usuário!';
                    $tipo_mensagem = 'error';
                } else {
                    $query = "UPDATE usuarios SET nome = :nome, email = :email, cargo = :cargo, biografia = :biografia WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':nome', $nome);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':cargo', $cargo);
                    $stmt->bindParam(':biografia', $biografia);
                    $stmt->bindParam(':id', $_SESSION['usuario_id']);
                    
                    if ($stmt->execute()) {
                        $_SESSION['usuario_nome'] = $nome;
                        $_SESSION['usuario_email'] = $email;
                        $_SESSION['usuario_cargo'] = $cargo;
                        
                        $mensagem = 'Perfil atualizado com sucesso!';
                        $tipo_mensagem = 'success';
                        
                        // Atualizar dados do usuário logado
                        $usuario_logado['nome'] = $nome;
                        $usuario_logado['email'] = $email;
                        $usuario_logado['cargo'] = $cargo;
                        $usuario_logado['biografia'] = $biografia;
                    } else {
                        $mensagem = 'Erro ao atualizar perfil!';
                        $tipo_mensagem = 'error';
                    }
                }
                break;
                
            case 'alterar_senha':
                $senha_atual = $_POST['senha_atual'];
                $nova_senha = $_POST['nova_senha'];
                $confirmar_senha = $_POST['confirmar_senha'];
                
                if ($nova_senha !== $confirmar_senha) {
                    $mensagem = 'As senhas não coincidem!';
                    $tipo_mensagem = 'error';
                } else {
                    // Verificar senha atual
                    if (password_verify($senha_atual, $usuario_logado['senha'])) {
                        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                        
                        $query = "UPDATE usuarios SET senha = :senha WHERE id = :id";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':senha', $senha_hash);
                        $stmt->bindParam(':id', $_SESSION['usuario_id']);
                        
                        if ($stmt->execute()) {
                            $mensagem = 'Senha alterada com sucesso!';
                            $tipo_mensagem = 'success';
                        } else {
                            $mensagem = 'Erro ao alterar senha!';
                            $tipo_mensagem = 'error';
                        }
                    } else {
                        $mensagem = 'Senha atual incorreta!';
                        $tipo_mensagem = 'error';
                    }
                }
                break;
                
            case 'excluir_conta':
                $senha_confirmacao = $_POST['senha_confirmacao'];
                
                if (password_verify($senha_confirmacao, $usuario_logado['senha'])) {
                    $query = "DELETE FROM usuarios WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':id', $_SESSION['usuario_id']);
                    
                    if ($stmt->execute()) {
                        session_destroy();
                        header('Location: login.php?msg=conta_excluida');
                        exit();
                    } else {
                        $mensagem = 'Erro ao excluir conta!';
                        $tipo_mensagem = 'error';
                    }
                } else {
                    $mensagem = 'Senha incorreta!';
                    $tipo_mensagem = 'error';
                }
                break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Meu Perfil</h1>
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
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    
                    <!-- Dados do perfil -->
                    <div class="form-container">
                        <h3>Dados do Perfil</h3>
                        <form method="POST">
                            <input type="hidden" name="acao" value="atualizar">
                            
                            <div class="form-group">
                                <label for="nome">Nome:</label>
                                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario_logado['nome']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">E-mail:</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario_logado['email']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="cargo">Cargo:</label>
                                <input type="text" id="cargo" name="cargo" value="<?php echo htmlspecialchars($usuario_logado['cargo']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="biografia">Biografia:</label>
                                <textarea id="biografia" name="biografia" rows="4"><?php echo htmlspecialchars($usuario_logado['biografia']); ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Atualizar Perfil</button>
                        </form>
                    </div>
                    
                    <!-- Alterar senha -->
                    <div class="form-container">
                        <h3>Alterar Senha</h3>
                        <form method="POST">
                            <input type="hidden" name="acao" value="alterar_senha">
                            
                            <div class="form-group">
                                <label for="senha_atual">Senha Atual:</label>
                                <input type="password" id="senha_atual" name="senha_atual" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="nova_senha">Nova Senha:</label>
                                <input type="password" id="nova_senha" name="nova_senha" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirmar_senha">Confirmar Nova Senha:</label>
                                <input type="password" id="confirmar_senha" name="confirmar_senha" required>
                            </div>
                            
                            <button type="submit" class="btn btn-warning">Alterar Senha</button>
                        </form>
                    </div>
                    
                </div>
                
                <!-- Informações da conta -->
                <div class="form-container">
                    <h3>Informações da Conta</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div>
                            <strong>Membro desde:</strong><br>
                            <?php echo formatarData($usuario_logado['criado_em']); ?>
                        </div>
                        <div>
                            <strong>Último acesso:</strong><br>
                            <?php echo date('d/m/Y H:i'); ?>
                        </div>
                        <div>
                            <strong>Cargo:</strong><br>
                            <?php echo $usuario_logado['cargo']; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Zona de perigo -->
                <div class="form-container" style="border-left: 4px solid #EF4444;">
                    <h3 style="color: #EF4444;">Zona de Perigo</h3>
                    <p style="color: #666; margin-bottom: 20px;">
                        Atenção: Esta ação é irreversível. Todos os seus dados serão permanentemente excluídos.
                    </p>
                    
                    <button onclick="confirmarExclusao()" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Excluir Minha Conta
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de confirmação de exclusão -->
    <div id="modalExcluir" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 400px;">
            <h3 style="color: #EF4444; margin-bottom: 20px;">Confirmar Exclusão</h3>
            <p style="margin-bottom: 20px;">
                Para confirmar a exclusão da sua conta, digite sua senha:
            </p>
            
            <form method="POST">
                <input type="hidden" name="acao" value="excluir_conta">
                <div class="form-group">
                    <label for="senha_confirmacao">Senha:</label>
                    <input type="password" id="senha_confirmacao" name="senha_confirmacao" required>
                </div>
                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                    <button type="button" onclick="fecharModalExcluir()" class="btn btn-primary">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function confirmarExclusao() {
            document.getElementById('modalExcluir').style.display = 'block';
        }
        
        function fecharModalExcluir() {
            document.getElementById('modalExcluir').style.display = 'none';
        }
    </script>
</body>
</html>

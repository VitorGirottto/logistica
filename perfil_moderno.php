<?php
require_once 'includes/verificaLogin.php';

$database = new Database();
$db = $database->getConnection();

$mensagem = '';
$tipo_mensagem = '';

// Processar ações (mesmo código original)
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
    <link rel="stylesheet" href="css/perfil-moderno.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="perfil-moderno">
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
                
                <div class="form-grid">
                    
                    <!-- Dados do perfil -->
                    <div class="form-container">
                        <h3><i class="fas fa-user-edit"></i> Dados do Perfil</h3>
                        <form method="POST">
                            <input type="hidden" name="acao" value="atualizar">
                            
                            <div class="form-group">
                                <label for="nome"><i class="fas fa-user"></i> Nome:</label>
                                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario_logado['nome']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope"></i> E-mail:</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario_logado['email']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="cargo"><i class="fas fa-briefcase"></i> Cargo:</label>
                                <input type="text" id="cargo" name="cargo" value="<?php echo htmlspecialchars($usuario_logado['cargo']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="biografia"><i class="fas fa-file-text"></i> Biografia:</label>
                                <textarea id="biografia" name="biografia" placeholder="Conte um pouco sobre você..."><?php echo htmlspecialchars($usuario_logado['biografia']); ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Atualizar Perfil
                            </button>
                        </form>
                    </div>
                    
                    <!-- Alterar senha -->
                    <div class="form-container">
                        <h3><i class="fas fa-lock"></i> Alterar Senha</h3>
                        <form method="POST">
                            <input type="hidden" name="acao" value="alterar_senha">
                            
                            <div class="form-group">
                                <label for="senha_atual"><i class="fas fa-key"></i> Senha Atual:</label>
                                <input type="password" id="senha_atual" name="senha_atual" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="nova_senha"><i class="fas fa-lock"></i> Nova Senha:</label>
                                <input type="password" id="nova_senha" name="nova_senha" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirmar_senha"><i class="fas fa-check-circle"></i> Confirmar Nova Senha:</label>
                                <input type="password" id="confirmar_senha" name="confirmar_senha" required>
                            </div>
                            
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-shield-alt"></i> Alterar Senha
                            </button>
                        </form>
                    </div>
                    
                </div>
                
                <!-- Informações da conta -->
                <div class="info-card">
                    <h3><i class="fas fa-info-circle"></i> Informações da Conta</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong><i class="fas fa-calendar-plus"></i> Membro desde:</strong>
                            <div><?php echo formatarData($usuario_logado['criado_em']); ?></div>
                        </div>
                        <div class="info-item">
                            <strong><i class="fas fa-clock"></i> Último acesso:</strong>
                            <div><?php echo date('d/m/Y H:i'); ?></div>
                        </div>
                        <div class="info-item">
                            <strong><i class="fas fa-user-tag"></i> Cargo:</strong>
                            <div><?php echo $usuario_logado['cargo']; ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Zona de perigo -->
                <div class="danger-zone">
                    <h3><i class="fas fa-exclamation-triangle"></i> Zona de Perigo</h3>
                    <p>
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
    <div id="modalExcluir" style="display: none;" class="modal-overlay">
        <div class="modal-content">
            <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Exclusão</h3>
            <p>
                Para confirmar a exclusão da sua conta, digite sua senha:
            </p>
            
            <form method="POST">
                <input type="hidden" name="acao" value="excluir_conta">
                <div class="form-group">
                    <label for="senha_confirmacao"><i class="fas fa-key"></i> Senha:</label>
                    <input type="password" id="senha_confirmacao" name="senha_confirmacao" required>
                </div>
                <div style="margin-top: 20px; display: flex; gap: 15px;">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Confirmar Exclusão
                    </button>
                    <button type="button" onclick="fecharModalExcluir()" class="btn btn-primary">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function confirmarExclusao() {
            document.getElementById('modalExcluir').style.display = 'flex';
        }
        
        function fecharModalExcluir() {
            document.getElementById('modalExcluir').style.display = 'none';
        }
        
        // Efeitos de interação
        document.addEventListener('DOMContentLoaded', function() {
            // Animação de entrada dos cards
            const cards = document.querySelectorAll('.form-container, .info-card, .danger-zone');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 150);
            });
            
            // Efeito de foco nos inputs
            document.querySelectorAll('input, textarea').forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                    this.parentElement.style.transition = 'transform 0.2s ease';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });
            
            // Efeito ripple nos botões
            document.querySelectorAll('.btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.style.position = 'absolute';
                    ripple.style.borderRadius = '50%';
                    ripple.style.background = 'rgba(255, 255, 255, 0.3)';
                    ripple.style.transform = 'scale(0)';
                    ripple.style.animation = 'ripple 0.6s linear';
                    ripple.style.pointerEvents = 'none';
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        });
    </script>
    
    <style>
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    </style>
</body>
</html>

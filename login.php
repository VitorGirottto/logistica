<?php
require_once 'config/config.php';

$erro = '';

if ($_POST) {
    $email = limparInput($_POST['email']);
    $senha = $_POST['senha'];
    
    if (!empty($email) && !empty($senha)) {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT id, nome, email, senha, cargo FROM usuarios WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($senha, $usuario['senha'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_cargo'] = $usuario['cargo'];
                
                header('Location: dashboard.php');
                exit();
            } else {
                $erro = 'Senha incorreta!';
            }
        } else {
            $erro = 'Usuário não encontrado!';
        }
    } else {
        $erro = 'Preencha todos os campos!';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Sistema de Logística</h1>
                <p>Faça login para acessar o sistema</p>
            </div>
            
            <?php if ($erro): ?>
                <div class="alert alert-error">
                    <?php echo $erro; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="email">E-mail:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" required>
                </div>
                
                <button type="submit" class="btn-login">Entrar</button>
            </form>
            
            <div class="login-footer">
                <p>Use: admin@logistica.com / password</p>
            </div>
        </div>
    </div>
</body>
</html>

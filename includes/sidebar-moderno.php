<?php
// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Incluir conexão com banco para badges
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();
?>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>Sistema Logística</h2>
        <p>Gestão Completa</p>
    </div>
    
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard Geral
        </a>
        
        <div class="menu-separator"></div>
        
        <a href="entregas.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'entregas.php' ? 'active' : ''; ?>">
            <i class="fas fa-truck"></i>
            Entregas
            <?php
            // Badge de notificação para entregas atrasadas
            try {
                $query = "SELECT COUNT(*) as total FROM entregas WHERE status = 'Atrasada'";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $atrasadas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                if ($atrasadas > 0): ?>
                    <span class="notification-badge"><?php echo $atrasadas; ?></span>
                <?php endif;
            } catch(Exception $e) {
                // Silenciar erro se tabela não existir
            }
            ?>
        </a>
        
        <a href="motoristas.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'motoristas.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-tie"></i>
            Motoristas
        </a>
        
        <a href="veiculos.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'veiculos.php' ? 'active' : ''; ?>">
            <i class="fas fa-car"></i>
            Veículos
            <?php
            // Badge para veículos em manutenção
            try {
                $query = "SELECT COUNT(*) as total FROM veiculos WHERE em_manutencao = 1";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $manutencao = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                if ($manutencao > 0): ?>
                    <span class="notification-badge"><?php echo $manutencao; ?></span>
                <?php endif;
            } catch(Exception $e) {
                // Silenciar erro se tabela não existir
            }
            ?>
        </a>
        
        <div class="menu-separator"></div>
        
        <a href="roteirizacao.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'roteirizacao.php' ? 'active' : ''; ?>">
            <i class="fas fa-route"></i>
            Roteirização
        </a>
        
        <div class="menu-separator"></div>
        
        <!-- NOVOS MENUS FINANCEIROS -->
        <a href="notas.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'notas.php' ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice"></i>
            Notas Fiscais
            <?php
            // Badge para notas do mês atual
            try {
                $query = "SELECT COUNT(*) as total FROM notas WHERE MONTH(data_emissao) = MONTH(CURRENT_DATE()) AND YEAR(data_emissao) = YEAR(CURRENT_DATE())";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $notas_mes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                if ($notas_mes > 0): ?>
                    <span class="notification-badge"><?php echo $notas_mes; ?></span>
                <?php endif;
            } catch(Exception $e) {
                // Silenciar erro se tabela não existir
            }
            ?>
        </a>
        
        <a href="contas_receber.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'contas_receber.php' ? 'active' : ''; ?>">
            <i class="fas fa-hand-holding-usd"></i>
            Contas a Receber
            <?php
            // Badge para contas vencidas
            try {
                $query = "SELECT COUNT(*) as total FROM contas_receber WHERE status = 'Vencida'";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $vencidas_receber = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                if ($vencidas_receber > 0): ?>
                    <span class="notification-badge notification-danger"><?php echo $vencidas_receber; ?></span>
                <?php endif;
            } catch(Exception $e) {
                // Silenciar erro se tabela não existir
            }
            ?>
        </a>
        
        <a href="contas_pagar.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'contas_pagar.php' ? 'active' : ''; ?>">
            <i class="fas fa-credit-card"></i>
            Contas a Pagar
            <?php
            // Badge para contas vencidas
            try {
                $query = "SELECT COUNT(*) as total FROM contas_pagar WHERE status = 'Vencida'";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $vencidas_pagar = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                if ($vencidas_pagar > 0): ?>
                    <span class="notification-badge notification-danger"><?php echo $vencidas_pagar; ?></span>
                <?php endif;
            } catch(Exception $e) {
                // Silenciar erro se tabela não existir
            }
            ?>
        </a>
        
        <div class="menu-separator"></div>
        
        <a href="perfil.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'perfil.php' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i>
            Meu Perfil
        </a>
        
        <div class="menu-separator"></div>
        
        <a href="logout.php" class="menu-item">
            <i class="fas fa-sign-out-alt"></i>
            Sair
        </a>
    </nav>
    
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['usuario_nome'], 0, 1)); ?>
            </div>
            <div>
                <div style="font-weight: 500; font-size: 14px;"><?php echo explode(' ', $_SESSION['usuario_nome'])[0]; ?></div>
                <div style="opacity: 0.7; font-size: 12px;"><?php echo $_SESSION['usuario_cargo'] ?? 'Usuário'; ?></div>
            </div>
        </div>
    </div>
</div>

<script>
// Adicionar efeitos de interação
document.addEventListener('DOMContentLoaded', function() {
    // Efeito ripple nos itens do menu
    document.querySelectorAll('.menu-item').forEach(item => {
        item.addEventListener('click', function(e) {
            // Criar efeito ripple
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
    
    // Animação suave ao carregar
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.style.opacity = '0';
        sidebar.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
            sidebar.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            sidebar.style.opacity = '1';
            sidebar.style.transform = 'translateX(0)';
        }, 100);
    }
});
</script>

<style>
/* Efeito ripple */
.ripple {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: scale(0);
    animation: ripple-animation 0.6s linear;
    pointer-events: none;
}

@keyframes ripple-animation {
    to {
        transform: scale(4);
        opacity: 0;
    }
}

/* Badge de perigo para contas vencidas */
.notification-danger {
    background: linear-gradient(135deg, #ff6b6b, #ee5a52) !important;
    animation: pulse-danger 2s infinite;
}

@keyframes pulse-danger {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}
</style>

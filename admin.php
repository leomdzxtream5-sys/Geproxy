<?php
require 'config.php';
redirectIfNotLogged();

if (!isAdmin()) {
    header('Location: dashboard.php');
    exit;
}

// Processa ações do admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_saldo'])) {
        $user_id = $_POST['user_id'];
        $valor = floatval($_POST['valor']);
        $stmt = $pdo->prepare("UPDATE usuarios SET saldo = saldo + ? WHERE id = ?");
        $stmt->execute([$valor, $user_id]);
    }
    
    if (isset($_POST['remover_usuario'])) {
        $user_id = $_POST['user_id'];
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ? AND is_admin = FALSE");
        $stmt->execute([$user_id]);
    }
}

// Estatísticas do sistema
$stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
$total_usuarios = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM proxies");
$total_proxies = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT SUM(quantidade) as total FROM historico_proxies");
$total_gerados = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->query("SELECT SUM(custo) as total FROM historico_proxies");
$total_faturado = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->query("SELECT * FROM usuarios ORDER BY criado_em DESC");
$usuarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> | Admin</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<div class="app-wrapper">
    <aside class="sidebar">
        <div class="brand">DELTA<span>.</span></div>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-item">🏠 Dashboard</a>
            <a href="admin.php" class="nav-item active">⚙️ Admin</a>
            <a href="logout.php" class="nav-item" style="color: #ff4444; margin-top: auto;">🚪 Sair</a>
        </nav>
    </aside>
    
    <main class="main-content">
        <div class="page-header">
            <h1>⚙️ Painel Administrativo</h1>
        </div>
        
        <div class="stats-grid">
            <div class="glass-panel">
                <div class="stat-value"><?= $total_usuarios ?></div>
                <div class="stat-label">👥 Usuários</div>
            </div>
            <div class="glass-panel">
                <div class="stat-value"><?= $total_proxies ?></div>
                <div class="stat-label">📦 Proxies Ativos</div>
            </div>
            <div class="glass-panel">
                <div class="stat-value"><?= $total_gerados ?></div>
                <div class="stat-label">🔄 Proxies Gerados</div>
            </div>
            <div class="glass-panel">
                <div class="stat-value">R$ <?= number_format($total_faturado, 2) ?></div>
                <div class="stat-label">💰 Faturado</div>
            </div>
        </div>
        
        <div class="glass-panel">
            <h3>👥 Gerenciar Usuários</h3>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>Discord</th>
                            <th>Saldo</th>
                            <th>Admin</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td>#<?= $u['id'] ?></td>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= $u['discord_id'] ? '✅' : '❌' ?></td>
                            <td>R$ <?= number_format($u['saldo'], 2) ?></td>
                            <td><?= $u['is_admin'] ? '✅' : '❌' ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <input type="number" name="valor" placeholder="R$" style="width: 80px; padding: 5px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 4px; color: white;">
                                    <button type="submit" name="add_saldo" class="btn-primary" style="padding: 5px 10px; font-size: 0.8rem;">+</button>
                                </form>
                                <?php if (!$u['is_admin']): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Remover usuário?')">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button type="submit" name="remover_usuario" style="padding: 5px 10px; background: rgba(255,0,0,0.2); border: 1px solid rgba(255,0,0,0.3); color: #ff4444; border-radius: 4px; cursor: pointer;">🗑️</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>

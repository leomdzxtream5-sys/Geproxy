<?php
require 'config.php';

// Se já estiver logado, redireciona
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$erro = '';

// Processa login via formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    if (!empty($username) && !empty($senha)) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['username'] = $usuario['username'];
            $_SESSION['is_admin'] = $usuario['is_admin'];
            $_SESSION['saldo'] = $usuario['saldo'];
            header('Location: dashboard.php');
            exit;
        } else {
            $erro = 'Usuário ou senha inválidos!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> | Login</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-box">
        <div class="brand">DELTA<span>.</span></div>
        <p style="color: var(--text-gray); margin-bottom: 2rem;">Acesse seu painel administrativo</p>
        
        <?php if ($erro): ?>
            <div style="background: rgba(255,0,0,0.1); border: 1px solid rgba(255,0,0,0.3); padding: 10px; border-radius: 8px; margin-bottom: 1rem; color: #ff4444;">
                <?= $erro ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="input-group">
                <label>👤 Usuário</label>
                <input type="text" name="usuario" placeholder="Digite seu usuário" required>
            </div>
            <div class="input-group">
                <label>🔒 Senha</label>
                <input type="password" name="senha" placeholder="Digite sua senha" required>
            </div>
            <label class="checkbox-label">
                <input type="checkbox" name="lembrar"> Lembrar de mim
            </label>
            <button type="submit" class="btn-primary" style="width:100%; margin-top: 1rem;">ENTRAR</button>
        </form>
        
        <div class="divider">OU</div>
        
        <a href="https://discord.com/api/oauth2/authorize?client_id=<?= DISCORD_CLIENT_ID ?>&redirect_uri=<?= urlencode(DISCORD_REDIRECT_URI) ?>&response_type=code&scope=identify%20guilds%20guilds.members.read" class="btn-discord">
            LOGAR COM DISCORD
        </a>
        
        <p style="color: var(--text-gray); font-size: 0.8rem; margin-top: 2rem;">
            Ainda não tem uma conta? 
            <a href="#" style="color: var(--primary);">Entre com o Discord para se cadastrar.</a>
        </p>
    </div>
</body>
</html>

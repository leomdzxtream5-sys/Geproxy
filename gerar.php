<?php
require '../config.php';
redirectIfNotLogged();

$usuario_id = $_SESSION['usuario_id'];
$mensagem = '';
$proxies_gerados = [];

// Pega dados do usuário
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$user = $stmt->fetch();

$preco_por_proxy = 0.50; // R$ 0,50 cada proxy

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantidade = intval($_POST['quantidade'] ?? 1);
    $tipo = $_POST['tipo'] ?? 'http';
    $pais = $_POST['pais'] ?? 'br';
    $is_trial = isset($_GET['trial']);
    
    if ($quantidade < 1) $quantidade = 1;
    if ($quantidade > 100) $quantidade = 100;
    
    $custo = $quantidade * $preco_por_proxy;
    
    // Verifica trial
    if ($is_trial && !$user['trial_used']) {
        if ($quantidade > 10) $quantidade = 10;
        $custo = 0;
    } elseif ($user['saldo'] < $custo) {
        $mensagem = 'Saldo insuficiente!';
    } else {
        // Gera os proxies
        for ($i = 0; $i < $quantidade; $i++) {
            $host = mt_rand(1, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(1, 255);
            $port = mt_rand(1000, 9999);
            $proxy_user = 'proxy_' . substr(md5(uniqid()), 0, 8);
            $proxy_pass = substr(md5(uniqid()), 0, 12);
            
            $stmt = $pdo->prepare("INSERT INTO proxies (usuario_id, proxy_host, proxy_port, proxy_user, proxy_pass, tipo, pais) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$usuario_id, $host, $port, $proxy_user, $proxy_pass, $tipo, $pais]);
            
            $proxies_gerados[] = [
                'host' => $host,
                'port' => $port,
                'user' => $proxy_user,
                'pass' => $proxy_pass,
                'tipo' => $tipo
            ];
        }
        
        // Debita saldo (se não for trial)
        if (!$is_trial) {
            $stmt = $pdo->prepare("UPDATE usuarios SET saldo = saldo - ? WHERE id = ?");
            $stmt->execute([$custo, $usuario_id]);
            $_SESSION['saldo'] = $user['saldo'] - $custo;
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET trial_used = TRUE WHERE id = ?");
            $stmt->execute([$usuario_id]);
        }
        
        // Registra histórico
        $stmt = $pdo->prepare("INSERT INTO historico_proxies (usuario_id, quantidade, tipo, custo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$usuario_id, $quantidade, $tipo, $custo]);
        
        $mensagem = $is_trial ? '🎯 Trial ativado! ' : '';
        $mensagem .= "$quantidade proxies gerados com sucesso!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> | Gerar Proxies</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body style="background: #000; color: white; font-family: 'Outfit', sans-serif; padding: 2rem;">
    <div style="max-width: 800px; margin: 0 auto;">
        <a href="../dashboard.php" style="color: var(--primary); text-decoration: none; margin-bottom: 1rem; display: block;">← Voltar ao Dashboard</a>
        
        <h1>🔄 Gerar Proxies</h1>
        <p style="color: var(--text-gray);">Gere proxies HTTP/HTTPS/SOCKS5 para Android</p>
        
        <?php if ($mensagem): ?>
            <div style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); padding: 15px; border-radius: 8px; margin: 1rem 0;">
                <?= $mensagem ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($proxies_gerados)): ?>
            <div class="glass-panel" style="margin: 1rem 0;">
                <h3>✅ Proxies Gerados</h3>
                <div style="background: #0a0a0a; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 0.85rem; max-height: 300px; overflow-y: auto;">
                    <?php foreach ($proxies_gerados as $p): ?>
                        <?= $p['tipo'] ?>://<?= $p['user'] ?>:<?= $p['pass'] ?>@<?= $p['host'] ?>:<?= $p['port'] ?><br>
                    <?php endforeach; ?>
                </div>
                <button onclick="copyProxies()" style="margin-top: 10px;" class="btn-primary">📋 Copiar Todos</button>
            </div>
        <?php endif; ?>
        
        <div class="glass-panel">
            <form method="POST">
                <div class="input-group">
                    <label>Quantidade de Proxies</label>
                    <input type="number" name="quantidade" value="1" min="1" max="100">
                </div>
                
                <div class="input-group">
                    <label>Tipo de Proxy</label>
                    <select name="tipo">
                        <option value="http">HTTP</option>
                        <option value="https">HTTPS</option>
                        <option value="socks5">SOCKS5</option>
                    </select>
                </div>
                
                <div class="input-group">
                    <label>País</label>
                    <select name="pais">
                        <option value="br">🇧🇷 Brasil</option>
                        <option value="us">🇺🇸 Estados Unidos</option>
                        <option value="uk">🇬🇧 Reino Unido</option>
                        <option value="ca">🇨🇦 Canadá</option>
                        <option value="de">🇩🇪 Alemanha</option>
                        <option value="fr">🇫🇷 França</option>
                        <option value="jp">🇯🇵 Japão</option>
                    </select>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                    <span>Custo: <strong>R$ <?= number_format($preco_por_proxy, 2) ?></strong> por proxy</span>
                    <span>Saldo: <strong>R$ <?= number_format($user['saldo'], 2) ?></strong></span>
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 1rem;">🚀 GERAR PROXIES</button>
            </form>
        </div>
    </div>
    
    <script>
    function copyProxies() {
        const text = document.querySelector('div[style*="font-family: monospace"]').innerText;
        navigator.clipboard.writeText(text);
        alert('Proxies copiados!');
    }
    </script>
</body>
</html>

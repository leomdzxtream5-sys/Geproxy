<?php
require 'config.php';

if (!isset($_GET['code'])) {
    die('Código de autorização não encontrado.');
}

$code = $_GET['code'];

// Troca o code por token
$ch = curl_init('https://discord.com/api/oauth2/token');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'client_id' => DISCORD_CLIENT_ID,
    'client_secret' => DISCORD_CLIENT_SECRET,
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => DISCORD_REDIRECT_URI,
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode !== 200) {
    die('Erro ao obter token do Discord.');
}

$token_data = json_decode($response, true);
$access_token = $token_data['access_token'];

// Pega dados do usuário no Discord
$ch = curl_init('https://discord.com/api/users/@me');
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $access_token"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$user_response = curl_exec($ch);
curl_close($ch);

$user = json_decode($user_response, true);

if (!$user || isset($user['message'])) {
    die('Erro ao obter dados do usuário.');
}

// Verifica se já existe
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE discord_id = ?");
$stmt->execute([$user['id']]);
$existing = $stmt->fetch();

if ($existing) {
    // Login
    $_SESSION['usuario_id'] = $existing['id'];
    $_SESSION['username'] = $existing['username'];
    $_SESSION['is_admin'] = $existing['is_admin'];
    $_SESSION['saldo'] = $existing['saldo'];
} else {
    // Cadastro automático
    $codigo_afiliado = substr(md5(uniqid()), 0, 10);
    $stmt = $pdo->prepare("INSERT INTO usuarios (discord_id, username, email, avatar, afiliado_codigo) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user['id'], $user['username'], $user['email'] ?? '', "https://cdn.discordapp.com/avatars/{$user['id']}/{$user['avatar']}.png", $codigo_afiliado]);
    
    $_SESSION['usuario_id'] = $pdo->lastInsertId();
    $_SESSION['username'] = $user['username'];
    $_SESSION['is_admin'] = false;
    $_SESSION['saldo'] = 0;
}

header('Location: dashboard.php');
exit;
?>

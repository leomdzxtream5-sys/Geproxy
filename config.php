<?php
session_start();
error_reporting(0);

// Config do banco
define('DB_HOST', 'localhost');
define('DB_NAME', 'delta_gerador');
define('DB_USER', 'root');
define('DB_PASS', '');

// Discord OAuth
define('DISCORD_CLIENT_ID', 'SEU_CLIENT_ID_AQUI');
define('DISCORD_CLIENT_SECRET', 'SEU_CLIENT_SECRET_AQUI');
define('DISCORD_REDIRECT_URI', 'https://seudominio.com/discord_callback.php');

// Config do sistema
define('SITE_NAME', 'Delta Gerador');
define('SITE_URL', 'https://seudominio.com');

try {
    $pdo = new PDO("pgsql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Se for MySQL, tenta conectar
    try {
        $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        die("Erro de conexão: " . $e->getMessage());
    }
}

// Verifica se está logado
function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function redirectIfNotLogged() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}
?>

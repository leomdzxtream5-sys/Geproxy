<?php
require 'config.php';

header('Content-Type: application/json');

// Verifica autenticação
if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Não autorizado. Por favor, faça login novamente.']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'download':
        $format = $_GET['format'] ?? 'txt';
        
        $stmt = $pdo->prepare("SELECT * FROM proxies WHERE usuario_id = ? ORDER BY criado_em DESC");
        $stmt->execute([$usuario_id]);
        $proxies = $stmt->fetchAll();
        
        switch ($format) {
            case 'txt':
                header('Content-Type: text/plain');
                header('Content-Disposition: attachment; filename="proxies.txt"');
                foreach ($proxies as $p) {
                    echo "{$p['tipo']}://{$p['proxy_user']}:{$p['proxy_pass']}@{$p['proxy_host']}:{$p['proxy_port']}\n";
                }
                break;
                
            case 'json':
                echo json_encode(['proxies' => $proxies], JSON_PRETTY_PRINT);
                break;
                
            case 'csv':
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="proxies.csv"');
                echo "tipo,host,porta,usuario,senha,pais\n";
                foreach ($proxies as $p) {
                    echo "{$p['tipo']},{$p['proxy_host']},{$p['proxy_port']},{$p['proxy_user']},{$p['proxy_pass']},{$p['pais']}\n";
                }
                break;
        }
        break;
        
    case 'stats':
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM proxies WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $proxies_count = $stmt->fetch()['total'];
        
        echo json_encode([
            'status' => 'success',
            'proxies_ativos' => $proxies_count,
            'saldo' => $_SESSION['saldo']
        ]);
        break;
        
    default:
        echo json_encode(['status' => 'error', 'message' => 'Ação inválida']);
        break;
}
?>

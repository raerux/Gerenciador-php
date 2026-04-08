<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Login
if ($action === 'login' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['email']) || !isset($data['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Email ou senha inválidos']);
        exit;
    }

    try {
        authenticate($data['email'], $data['password']);
        echo json_encode(['success' => true, 'message' => 'Login realizado com sucesso']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Registro
if ($action === 'register' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
        exit;
    }

    try {
        register($data['name'], $data['email'], $data['password']);
        echo json_encode(['success' => true, 'message' => 'Usuário registrado com sucesso']);
    }
    catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email já existe']);
    }
    exit;
}

// Logout
if ($action === 'logout') {
    logout();
    echo json_encode(['success' => true, 'message' => 'Logout realizado']);
    exit;
}

// Verificar autenticação para as demais requisições
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

// Obter usuário atual
if ($action === 'current' && $method === 'GET') {
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name']
        ]
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Ação inválida']);

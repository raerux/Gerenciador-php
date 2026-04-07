<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

// Listar tarefas do usuário
if ($action === 'list' && $method === 'GET') {
    $stmt = $db->prepare('SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$userId]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'tasks' => $tasks]);
    exit;
}

// Criar tarefa
if ($action === 'create' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['title']) || empty($data['title'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Título é obrigatório']);
        exit;
    }

    $stmt = $db->prepare('INSERT INTO tasks (user_id, title, description) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $data['title'], $data['description'] ?? '']);

    $taskId = $db->lastInsertId();
    echo json_encode(['success' => true, 'message' => 'Tarefa criada', 'id' => $taskId]);
    exit;
}

// Atualizar tarefa
if ($action === 'update' && $method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $taskId = $data['id'] ?? null;

    // Verificar se tarefa pertence ao usuário
    $stmt = $db->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
    $stmt->execute([$taskId, $userId]);

    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acesso negado']);
        exit;
    }

    $stmt = $db->prepare('UPDATE tasks SET title = ?, description = ?, status = ? WHERE id = ?');
    $stmt->execute([$data['title'] ?? '', $data['description'] ?? '', $data['status'] ?? 'pendente', $taskId]);

    echo json_encode(['success' => true, 'message' => 'Tarefa atualizada']);
    exit;
}

// Deletar tarefa
if ($action === 'delete' && $method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $taskId = $data['id'] ?? null;

    // Verificar se tarefa pertence ao usuário
    $stmt = $db->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
    $stmt->execute([$taskId, $userId]);

    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acesso negado']);
        exit;
    }

    $stmt = $db->prepare('DELETE FROM tasks WHERE id = ?');
    $stmt->execute([$taskId]);

    echo json_encode(['success' => true, 'message' => 'Tarefa deletada']);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Ação inválida']);

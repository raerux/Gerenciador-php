<?php
require 'config.php';
header('Content-Type: application/json; charset=utf-8');

function out($arr, $code = 200) {
    http_response_code($code);
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) out(['ok' => false, 'erro' => 'JSON inválido.'], 400);

$acao = $data['acao'] ?? '';
$statusValidos = ['pendente', 'em_andamento', 'concluida'];

try {
    if ($acao === 'listar') {
        $stmt = $pdo->query("SELECT * FROM tarefas ORDER BY id DESC");
        out(['ok' => true, 'tarefas' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    if ($acao === 'criar') {
        $titulo = trim($data['titulo'] ?? '');
        $descricao = trim($data['descricao'] ?? '');
        $status = $data['status'] ?? 'pendente';

        if ($titulo === '') out(['ok' => false, 'erro' => 'O título é obrigatório.'], 422);
        if (!in_array($status, $statusValidos, true)) out(['ok' => false, 'erro' => 'Status inválido.'], 422);

        $stmt = $pdo->prepare("INSERT INTO tarefas (titulo, descricao, status) VALUES (:titulo, :descricao, :status)");
        $stmt->execute([
            ':titulo' => $titulo,
            ':descricao' => ($descricao !== '' ? $descricao : null),
            ':status' => $status
        ]);
        out(['ok' => true]);
    }

    if ($acao === 'editar') {
        $id = (int)($data['id'] ?? 0);
        $titulo = trim($data['titulo'] ?? '');
        $descricao = trim($data['descricao'] ?? '');
        $status = $data['status'] ?? 'pendente';

        if ($id <= 0) out(['ok' => false, 'erro' => 'ID inválido.'], 422);
        if ($titulo === '') out(['ok' => false, 'erro' => 'O título é obrigatório.'], 422);
        if (!in_array($status, $statusValidos, true)) out(['ok' => false, 'erro' => 'Status inválido.'], 422);

        $stmt = $pdo->prepare("UPDATE tarefas SET titulo=:titulo, descricao=:descricao, status=:status WHERE id=:id");
        $stmt->execute([
            ':titulo' => $titulo,
            ':descricao' => ($descricao !== '' ? $descricao : null),
            ':status' => $status,
            ':id' => $id
        ]);
        out(['ok' => true]);
    }

    if ($acao === 'excluir') {
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) out(['ok' => false, 'erro' => 'ID inválido.'], 422);

        $stmt = $pdo->prepare("DELETE FROM tarefas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        out(['ok' => true]);
    }

    out(['ok' => false, 'erro' => 'Ação inválida.'], 400);

} catch (Throwable $e) {
    out(['ok' => false, 'erro' => 'Erro interno.'], 500);
}
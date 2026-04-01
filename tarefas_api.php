<?php
require 'config.php';
header('Content-Type: application/json; charset=utf-8');

// Resposta padrão JSON
function jsonOut(array $data, int $code=200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) jsonOut(['ok'=>false,'erro'=>'JSON inválido.'],400);

$acao = $input['acao'] ?? '';
$statusValidos = ['pendente','em_andamento','concluida'];

try {
    if ($acao === 'listar') {
        $tarefas = $pdo->query("SELECT * FROM tarefas ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        jsonOut(['ok'=>true,'tarefas'=>$tarefas]);
    }

    if ($acao === 'criar') {
        $titulo = trim($input['titulo'] ?? '');
        $descricao = trim($input['descricao'] ?? '');
        $status = $input['status'] ?? 'pendente';

        if ($titulo === '') jsonOut(['ok'=>false,'erro'=>'O título é obrigatório.'],422);
        if (!in_array($status,$statusValidos,true)) jsonOut(['ok'=>false,'erro'=>'Status inválido.'],422);

        $stmt = $pdo->prepare("INSERT INTO tarefas (titulo,descricao,status) VALUES (:titulo,:descricao,:status)");
        $stmt->execute([
            ':titulo'=>$titulo,
            ':descricao'=>$descricao !== '' ? $descricao : null,
            ':status'=>$status
        ]);
        jsonOut(['ok'=>true]);
    }

    if ($acao === 'editar') {
        $id = (int)($input['id'] ?? 0);
        $titulo = trim($input['titulo'] ?? '');
        $descricao = trim($input['descricao'] ?? '');
        $status = $input['status'] ?? 'pendente';

        if ($id <= 0) jsonOut(['ok'=>false,'erro'=>'ID inválido.'],422);
        if ($titulo === '') jsonOut(['ok'=>false,'erro'=>'O título é obrigatório.'],422);
        if (!in_array($status,$statusValidos,true)) jsonOut(['ok'=>false,'erro'=>'Status inválido.'],422);

        $stmt = $pdo->prepare("UPDATE tarefas SET titulo=:titulo, descricao=:descricao, status=:status WHERE id=:id");
        $stmt->execute([
            ':titulo'=>$titulo,
            ':descricao'=>$descricao !== '' ? $descricao : null,
            ':status'=>$status,
            ':id'=>$id
        ]);
        jsonOut(['ok'=>true]);
    }

    if ($acao === 'excluir') {
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) jsonOut(['ok'=>false,'erro'=>'ID inválido.'],422);

        $stmt = $pdo->prepare("DELETE FROM tarefas WHERE id=:id");
        $stmt->execute([':id'=>$id]);
        jsonOut(['ok'=>true]);
    }

    jsonOut(['ok'=>false,'erro'=>'Ação inválida.'],400);

} catch (Throwable $e) {
    jsonOut(['ok'=>false,'erro'=>'Erro interno.'],500);
}
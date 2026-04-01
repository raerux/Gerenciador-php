<?php
require 'config.php'; // Importa conexão PDO
header('Content-Type: application/json; charset=utf-8'); // Define resposta como JSON

function jsonOut(array $data, int $code=200): void { // Função de saída padrão
    http_response_code($code); // Define status HTTP
    echo json_encode($data, JSON_UNESCAPED_UNICODE); // Retorna JSON com acentos
    exit; // Encerra execução
}

$input = json_decode(file_get_contents('php://input'), true); // Lê JSON do body
if (!is_array($input)) jsonOut(['ok'=>false,'erro'=>'JSON inválido.'],400); // Valida JSON

$acao = $input['acao'] ?? ''; // Ação solicitada
$statusValidos = ['pendente','em_andamento','concluida']; // Status permitidos

try {
    if ($acao === 'listar') { // Listar tarefas
        $tarefas = $pdo->query("SELECT * FROM tarefas ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC); // Busca no banco
        jsonOut(['ok'=>true,'tarefas'=>$tarefas]); // Retorna lista
    }

    if ($acao === 'criar') { // Criar tarefa
        $titulo = trim($input['titulo'] ?? ''); // Lê título
        $descricao = trim($input['descricao'] ?? ''); // Lê descrição
        $status = $input['status'] ?? 'pendente'; // Lê status

        if ($titulo === '') jsonOut(['ok'=>false,'erro'=>'O título é obrigatório.'],422); // Valida título
        if (!in_array($status,$statusValidos,true)) jsonOut(['ok'=>false,'erro'=>'Status inválido.'],422); // Valida status

        $stmt = $pdo->prepare("INSERT INTO tarefas (titulo,descricao,status) VALUES (:titulo,:descricao,:status)"); // SQL insert
        $stmt->execute([
            ':titulo'=>$titulo, // título
            ':descricao'=>$descricao !== '' ? $descricao : null, // descrição (ou null)
            ':status'=>$status // status
        ]);
        jsonOut(['ok'=>true]); // Sucesso
    }

    if ($acao === 'editar') { // Editar tarefa
        $id = (int)($input['id'] ?? 0); // Lê ID
        $titulo = trim($input['titulo'] ?? ''); // Lê título
        $descricao = trim($input['descricao'] ?? ''); // Lê descrição
        $status = $input['status'] ?? 'pendente'; // Lê status

        if ($id <= 0) jsonOut(['ok'=>false,'erro'=>'ID inválido.'],422); // Valida ID
        if ($titulo === '') jsonOut(['ok'=>false,'erro'=>'O título é obrigatório.'],422); // Valida título
        if (!in_array($status,$statusValidos,true)) jsonOut(['ok'=>false,'erro'=>'Status inválido.'],422); // Valida status

        $stmt = $pdo->prepare("UPDATE tarefas SET titulo=:titulo, descricao=:descricao, status=:status WHERE id=:id"); // SQL update
        $stmt->execute([
            ':titulo'=>$titulo, // novo título
            ':descricao'=>$descricao !== '' ? $descricao : null, // nova descrição
            ':status'=>$status, // novo status
            ':id'=>$id // ID da tarefa
        ]);
        jsonOut(['ok'=>true]); // Sucesso
    }

    if ($acao === 'excluir') { // Excluir tarefa
        $id = (int)($input['id'] ?? 0); // Lê ID
        if ($id <= 0) jsonOut(['ok'=>false,'erro'=>'ID inválido.'],422); // Valida ID

        $stmt = $pdo->prepare("DELETE FROM tarefas WHERE id=:id"); // SQL delete
        $stmt->execute([':id'=>$id]); // Executa exclusão
        jsonOut(['ok'=>true]); // Sucesso
    }

    jsonOut(['ok'=>false,'erro'=>'Ação inválida.'],400); // Ação não reconhecida
} catch (Throwable $e) { // Captura erro inesperado
    jsonOut(['ok'=>false,'erro'=>'Erro interno.'],500); // Retorna erro genérico
}
<?php
require 'config.php'; // Carrega conexão com banco (PDO)
header('Content-Type: application/json; charset=utf-8'); // Define resposta como JSON UTF-8

function out($arr, $code = 200) { // Função padrão para responder e encerrar
    http_response_code($code); // Define código HTTP (200, 400, 422, 500...)
    echo json_encode($arr, JSON_UNESCAPED_UNICODE); // Converte array para JSON sem escapar acentos
    exit; // Encerra script
}

$raw = file_get_contents('php://input'); // Lê corpo bruto da requisição
$data = json_decode($raw, true); // Converte JSON recebido para array associativo

if (!is_array($data)) out(['ok' => false, 'erro' => 'JSON inválido.'], 400); // Valida se veio JSON correto

$acao = $data['acao'] ?? ''; // Pega ação enviada pelo front (listar/criar/editar/excluir)
$statusValidos = ['pendente', 'em_andamento', 'concluida']; // Lista branca de status permitidos

try { // Inicia bloco de tratamento seguro de erros
    if ($acao === 'listar') { // Ação: listar tarefas
        $stmt = $pdo->query("SELECT * FROM tarefas ORDER BY id DESC"); // Busca tarefas mais novas primeiro
        out(['ok' => true, 'tarefas' => $stmt->fetchAll(PDO::FETCH_ASSOC)]); // Retorna array de tarefas
    }

    if ($acao === 'criar') { // Ação: criar tarefa
        $titulo = trim($data['titulo'] ?? ''); // Lê e limpa título
        $descricao = trim($data['descricao'] ?? ''); // Lê e limpa descrição
        $status = $data['status'] ?? 'pendente'; // Lê status ou define padrão

        if ($titulo === '') out(['ok' => false, 'erro' => 'O título é obrigatório.'], 422); // Valida título obrigatório
        if (!in_array($status, $statusValidos, true)) out(['ok' => false, 'erro' => 'Status inválido.'], 422); // Valida status permitido

        $stmt = $pdo->prepare("INSERT INTO tarefas (titulo, descricao, status) VALUES (:titulo, :descricao, :status)"); // SQL de inserção
        $stmt->execute([
            ':titulo' => $titulo, // Valor do título
            ':descricao' => ($descricao !== '' ? $descricao : null), // Descrição vazia vira null
            ':status' => $status // Valor do status
        ]);
        out(['ok' => true]); // Responde sucesso
    }

    if ($acao === 'editar') { // Ação: editar tarefa
        $id = (int)($data['id'] ?? 0); // Lê id e converte para inteiro
        $titulo = trim($data['titulo'] ?? ''); // Lê título
        $descricao = trim($data['descricao'] ?? ''); // Lê descrição
        $status = $data['status'] ?? 'pendente'; // Lê status

        if ($id <= 0) out(['ok' => false, 'erro' => 'ID inválido.'], 422); // Valida id
        if ($titulo === '') out(['ok' => false, 'erro' => 'O título é obrigatório.'], 422); // Valida título
        if (!in_array($status, $statusValidos, true)) out(['ok' => false, 'erro' => 'Status inválido.'], 422); // Valida status

        $stmt = $pdo->prepare("UPDATE tarefas SET titulo=:titulo, descricao=:descricao, status=:status WHERE id=:id"); // SQL update
        $stmt->execute([
            ':titulo' => $titulo, // Novo título
            ':descricao' => ($descricao !== '' ? $descricao : null), // Nova descrição
            ':status' => $status, // Novo status
            ':id' => $id // ID da tarefa alterada
        ]);
        out(['ok' => true]); // Retorna sucesso
    }

    if ($acao === 'excluir') { // Ação: excluir tarefa
        $id = (int)($data['id'] ?? 0); // Lê id
        if ($id <= 0) out(['ok' => false, 'erro' => 'ID inválido.'], 422); // Valida id

        $stmt = $pdo->prepare("DELETE FROM tarefas WHERE id = :id"); // SQL delete
        $stmt->execute([':id' => $id]); // Executa exclusão
        out(['ok' => true]); // Retorna sucesso
    }

    out(['ok' => false, 'erro' => 'Ação inválida.'], 400); // Caso ação não exista

} catch (Throwable $e) { // Captura erros inesperados
    out(['ok' => false, 'erro' => 'Erro interno.'], 500); // Retorna erro genérico
}
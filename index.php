<?php require 'config.php'; ?> <!-- Carrega conexão/configuração do banco -->

<!doctype html> <!-- Define documento HTML5 -->
<html lang="pt-BR"> <!-- Define idioma da página -->
<head> <!-- Início do cabeçalho -->
    <meta charset="utf-8"> <!-- Define codificação UTF-8 -->
    <meta name="viewport" content="width=device-width,initial-scale=1"> <!-- Responsividade em mobile -->
    <title>Gerenciador de Tarefas</title> <!-- Título da aba do navegador -->

    <style> /* Início do CSS */
        body{font-family:Arial;max-width:950px;margin:30px auto} /* Fonte, largura máxima e centralização */
        .btn{padding:8px 12px;background:#2563eb;color:#fff;border:0;border-radius:6px;cursor:pointer} /* Estilo do botão principal */
        table{width:100%;border-collapse:collapse;margin-top:16px} /* Tabela ocupa tudo, bordas unificadas */
        th,td{border:1px solid #ddd;padding:10px;vertical-align:top} /* Borda e espaçamento de células */
        th{background:#f3f4f6} /* Fundo do cabeçalho da tabela */
        dialog{border:1px solid #ddd;border-radius:10px;padding:16px;width:420px} /* Estilo do modal */
        input,textarea,select{width:100%;padding:8px;margin-top:4px} /* Campos ocupam largura total */
        .erro{color:#dc2626} /* Cor da mensagem de erro */
    </style> <!-- Fim do CSS -->
</head> <!-- Fim do cabeçalho -->

<body> <!-- Início do corpo da página -->
<h1>Gerenciador de Tarefas</h1> <!-- Título principal -->
<button class="btn" onclick="novo()">+ Nova Tarefa</button> <!-- Botão que abre modal para criar -->

<table> <!-- Início da tabela -->
    <thead> <!-- Cabeçalho da tabela -->
    <tr><th>ID</th><th>Título</th><th>Descrição</th><th>Status</th><th>Criada</th><th>Atualizada</th><th>Ações</th></tr> <!-- Colunas -->
    </thead> <!-- Fim do cabeçalho -->
    <tbody id="tb"><tr><td colspan="7">Carregando...</td></tr></tbody> <!-- Corpo da tabela; JS vai preencher -->
</table> <!-- Fim da tabela -->

<dialog id="m"> <!-- MODAL: janela nativa HTML para criar/editar -->
    <form id="f"> <!-- Formulário do modal -->
        <h3 id="mt">Nova Tarefa</h3><p class="erro" id="e"></p> <!-- Título dinâmico + área de erro -->
        <input type="hidden" id="id"> <!-- ID oculto; vazio = criar, preenchido = editar -->
        <p><label>Título</label><input id="titulo" maxlength="150" required></p> <!-- Campo título -->
        <p><label>Descrição</label><textarea id="descricao" rows="4"></textarea></p> <!-- Campo descrição -->
        <p><label>Status</label> <!-- Label do status -->
            <select id="status"><option value="pendente">Pendente</option><option value="em_andamento">Em andamento</option><option value="concluida">Concluída</option></select> <!-- Opções de status -->
        </p>
        <button type="button" onclick="m.close()">Cancelar</button> <!-- Fecha modal sem salvar -->
        <button class="btn">Salvar</button> <!-- Envia formulário -->
    </form> <!-- Fim do formulário -->
</dialog> <!-- Fim do modal -->

<script> // Início do JavaScript
    const api='tarefas_api.php',tb=q('#tb'),m=q('#m'),f=q('#f'),e=q('#e'); // Referências rápidas: URL da API e elementos principais
    function q(s){return document.querySelector(s)} // Atalho para selecionar elemento no DOM

    const esc=s=>(s??'').toString().replace(/[&<>"']/g,m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m])); // Escapa texto para evitar XSS ao renderizar HTML

    const req=(acao,d={})=>fetch(api,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({acao,...d})}).then(r=>r.json()); // Função genérica: envia ação + dados em JSON para API e retorna JSON

    async function listar(){ // Função para carregar tarefas e montar tabela
        const j=await req('listar'); // Chama API com ação listar
        if(!j.ok) return tb.innerHTML=`<tr><td colspan="7">${esc(j.erro||'Erro')}</td></tr>`; // Se erro, mostra mensagem
        if(!j.tarefas.length) return tb.innerHTML='<tr><td colspan="7">Nenhuma tarefa cadastrada.</td></tr>'; // Se vazio, mostra aviso

        tb.innerHTML=j.tarefas.map(t=>`<tr> <!-- Para cada tarefa, cria uma linha -->
    <td>${t.id}</td><td>${esc(t.titulo)}</td><td>${esc(t.descricao||'').replace(/\n/g,'<br>')}</td><td>${esc(t.status)}</td> <!-- Dados básicos -->
    <td>${t.data_criacao||''}</td><td>${t.data_atualizacao||''}</td> <!-- Datas -->
    <td><button onclick='edit(${JSON.stringify(t).replace(/'/g,"&apos;")})'>Editar</button> <button onclick="del(${t.id})">Excluir</button></td> <!-- Ações -->
  </tr>`).join(''); // Junta tudo em uma string única
    }

    function novo(){e.textContent='';f.reset();id.value='';mt.textContent='Nova Tarefa';m.showModal()} // Limpa formulário, seta modo "novo" e abre modal
    function edit(t){e.textContent='';id.value=t.id;titulo.value=t.titulo||'';descricao.value=t.descricao||'';status.value=t.status||'pendente';mt.textContent='Editar #'+t.id;m.showModal()} // Preenche dados da tarefa e abre modal em modo edição

    async function del(id){if(!confirm('Deseja realmente excluir?'))return;const j=await req('excluir',{id});if(!j.ok)return alert(j.erro||'Erro');listar()} // Confirma exclusão, chama API e atualiza lista

    f.onsubmit=async ev=>{ // Evento ao enviar formulário
        ev.preventDefault(); // Evita recarregar página
        const d={id:id.value?+id.value:null,titulo:titulo.value.trim(),descricao:descricao.value.trim(),status:status.value}; // Monta payload
        const j=await req(d.id?'editar':'criar',d); // Se tem id -> editar; senão -> criar
        if(!j.ok)return e.textContent=j.erro||'Erro'; // Exibe erro no modal
        m.close(); // Fecha modal após sucesso
        listar(); // Recarrega tabela sem F5
    }

    listar(); // Carrega tabela assim que página abre
</script> <!-- Fim do JavaScript -->
</body>
</html>
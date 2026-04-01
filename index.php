<?php require 'config.php'; ?> <!-- Importa config/ conexão com banco -->

<!doctype html> <!-- Define HTML5 -->
<html lang="pt-BR"> <!-- Define idioma -->
<head> <!-- Início head -->
    <meta charset="utf-8"> <!-- Define UTF-8 -->
    <meta name="viewport" content="width=device-width,initial-scale=1"> <!-- Responsividade -->
    <title>Gerenciador de Tarefas</title> <!-- Título da aba -->
    <style> /* Estilos da página */
        body{font-family:Arial;max-width:950px;margin:30px auto} /* Layout base */
        .btn{padding:8px 12px;background:#2563eb;color:#fff;border:0;border-radius:6px;cursor:pointer} /* Botão azul */
        table{width:100%;border-collapse:collapse;margin-top:16px} /* Tabela com largura total */
        th,td{border:1px solid #ddd;padding:10px;vertical-align:top} /* Bordas e espaçamento */
        th{background:#f3f4f6} /* Fundo do cabeçalho */
        dialog{border:1px solid #ddd;border-radius:10px;padding:16px;width:420px} /* Estilo do modal */
        input,textarea,select{width:100%;padding:8px;margin-top:4px} /* Campos */
        .erro{color:#dc2626} /* Texto de erro vermelho */
    </style> <!-- Fim estilos -->
</head> <!-- Fim head -->
<body> <!-- Início body -->

<h1>Gerenciador de Tarefas</h1> <!-- Título -->
<button class="btn" onclick="abrirNova()">+ Nova Tarefa</button> <!-- Abre modal novo -->

<table> <!-- Tabela de tarefas -->
    <thead> <!-- Cabeçalho -->
    <tr> <!-- Linha do cabeçalho -->
        <th>ID</th> <!-- Coluna ID -->
        <th>Título</th> <!-- Coluna título -->
        <th>Descrição</th> <!-- Coluna descrição -->
        <th>Status</th> <!-- Coluna status -->
        <th>Criada</th> <!-- Coluna data criação -->
        <th>Atualizada</th> <!-- Coluna data atualização -->
        <th>Ações</th> <!-- Coluna ações -->
    </tr>
    </thead>
    <tbody id="listaTarefas"><tr><td colspan="7">Carregando...</td></tr></tbody> <!-- Conteúdo dinâmico -->
</table>

<dialog id="modalTarefa"> <!-- Modal para criar/editar -->
    <form id="formTarefa"> <!-- Formulário do modal -->
        <h3 id="tituloModal">Nova Tarefa</h3> <!-- Título dinâmico -->
        <p class="erro" id="mensagemErro"></p> <!-- Mensagem de erro -->
        <input type="hidden" id="idTarefa"> <!-- ID oculto para edição -->
        <p><label>Título</label><input id="tituloTarefa" maxlength="150" required></p> <!-- Campo título -->
        <p><label>Descrição</label><textarea id="descricaoTarefa" rows="4"></textarea></p> <!-- Campo descrição -->
        <p><label>Status</label> <!-- Label status -->
            <select id="statusTarefa"> <!-- Campo status -->
                <option value="pendente">Pendente</option> <!-- Status pendente -->
                <option value="em_andamento">Em andamento</option> <!-- Status em andamento -->
                <option value="concluida">Concluída</option> <!-- Status concluída -->
            </select>
        </p>
        <button type="button" onclick="modalTarefa.close()">Cancelar</button> <!-- Fecha sem salvar -->
        <button class="btn">Salvar</button> <!-- Envia formulário -->
    </form>
</dialog>

<script>
    const api='tarefas_api.php'; // URL da API
    const $=s=>document.querySelector(s); // Atalho para querySelector

    const lista=$('#listaTarefas'); // tbody da tabela
    const modal=$('#modalTarefa'); // modal
    const form=$('#formTarefa'); // formulário
    const erro=$('#mensagemErro'); // área de erro

    const id=$('#idTarefa'); // input hidden ID
    const titulo=$('#tituloTarefa'); // input título
    const descricao=$('#descricaoTarefa'); // textarea descrição
    const status=$('#statusTarefa'); // select status
    const tituloModal=$('#tituloModal'); // título do modal

    const esc=s=>(s??'').toString().replace(/[&<>"']/g,c=>({ // Escapa texto para segurança
        '&':'&amp;', // & para entidade HTML
        '<':'&lt;', // < para entidade HTML
        '>':'&gt;', // > para entidade HTML
        '"':'&quot;', // aspas duplas para entidade HTML
        "'":'&#039;' // aspas simples para entidade HTML
    }[c]));

    const apiPost=(acao,dados={})=>fetch(api,{ // Função padrão de POST JSON
        method:'POST', // Método HTTP POST
        headers:{'Content-Type':'application/json'}, // Cabeçalho JSON
        body:JSON.stringify({acao,...dados}) // Corpo com ação + dados
    }).then(r=>r.json()); // Converte resposta para JSON

    async function carregarTarefas(){ // Lista tarefas da API
        const r=await apiPost('listar'); // Requisição de listagem
        if(!r.ok) return lista.innerHTML=`<tr><td colspan="7">${esc(r.erro||'Erro')}</td></tr>`; // Mostra erro se falhar
        if(!r.tarefas.length) return lista.innerHTML='<tr><td colspan="7">Nenhuma tarefa cadastrada.</td></tr>'; // Mostra vazio

        lista.innerHTML=r.tarefas.map(t=>`
    <tr>
      <td>${t.id}</td> <!-- ID -->
      <td>${esc(t.titulo)}</td> <!-- Título seguro -->
      <td>${esc(t.descricao||'').replace(/\n/g,'<br>')}</td> <!-- Descrição com quebra -->
      <td>${esc(t.status)}</td> <!-- Status seguro -->
      <td>${t.data_criacao||''}</td> <!-- Data criação -->
      <td>${t.data_atualizacao||''}</td> <!-- Data atualização -->
      <td>
        <button onclick='abrirEditar(${JSON.stringify(t).replace(/'/g,"&apos;")})'>Editar</button> <!-- Editar -->
        <button onclick='excluirTarefa(${t.id})'>Excluir</button> <!-- Excluir -->
      </td>
    </tr>
  `).join(''); // Junta tudo numa string
    }

    function abrirNova(){ // Abre modal para criação
        erro.textContent=''; // Limpa erro
        form.reset(); // Limpa formulário
        id.value=''; // Remove ID (modo criar)
        tituloModal.textContent='Nova Tarefa'; // Título do modal
        modal.showModal(); // Abre modal
    }

    function abrirEditar(t){ // Abre modal para edição
        erro.textContent=''; // Limpa erro
        id.value=t.id; // Define ID
        titulo.value=t.titulo||''; // Preenche título
        descricao.value=t.descricao||''; // Preenche descrição
        status.value=t.status||'pendente'; // Preenche status
        tituloModal.textContent=`Editar #${t.id}`; // Título do modal
        modal.showModal(); // Abre modal
    }

    async function excluirTarefa(idTarefa){ // Exclui tarefa
        if(!confirm('Deseja realmente excluir?')) return; // Confirma antes de excluir
        const r=await apiPost('excluir',{id:idTarefa}); // Envia ID para exclusão
        if(!r.ok) return alert(r.erro||'Erro ao excluir'); // Mostra erro
        carregarTarefas(); // Atualiza tabela sem F5
    }

    form.onsubmit=async e=>{ // Evento submit do formulário
        e.preventDefault(); // Evita reload da página
        const dados={ // Monta dados do formulário
            id:id.value?Number(id.value):null, // ID numérico se existir
            titulo:titulo.value.trim(), // Título sem espaços extras
            descricao:descricao.value.trim(), // Descrição sem espaços extras
            status:status.value // Status selecionado
        };
        const r=await apiPost(dados.id?'editar':'criar',dados); // Decide criar ou editar
        if(!r.ok) return erro.textContent=r.erro||'Erro ao salvar'; // Mostra erro no modal
        modal.close(); // Fecha modal em sucesso
        carregarTarefas(); // Recarrega lista
    };

    carregarTarefas(); // Primeira carga da tabela
</script>
</body>
</html>
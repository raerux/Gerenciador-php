<?php require 'config.php'; ?> <!-- Conexão PDO -->

<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Gerenciador de Tarefas</title>
    <style>
        body{font-family:Arial;max-width:950px;margin:30px auto}
        .btn{padding:8px 12px;background:#2563eb;color:#fff;border:0;border-radius:6px;cursor:pointer}
        table{width:100%;border-collapse:collapse;margin-top:16px}
        th,td{border:1px solid #ddd;padding:10px;vertical-align:top} th{background:#f3f4f6}
        dialog{border:1px solid #ddd;border-radius:10px;padding:16px;width:420px}
        input,textarea,select{width:100%;padding:8px;margin-top:4px}
        .erro{color:#dc2626}
    </style>
</head>
<body>
<h1>Gerenciador de Tarefas</h1>
<button class="btn" onclick="abrirNova()">+ Nova Tarefa</button>

<table>
    <thead>
    <tr><th>ID</th><th>Título</th><th>Descrição</th><th>Status</th><th>Criada</th><th>Atualizada</th><th>Ações</th></tr>
    </thead>
    <tbody id="listaTarefas"><tr><td colspan="7">Carregando...</td></tr></tbody>
</table>

<!-- Modal para criar/editar -->
<dialog id="modalTarefa">
    <form id="formTarefa">
        <h3 id="tituloModal">Nova Tarefa</h3>
        <p class="erro" id="mensagemErro"></p>
        <input type="hidden" id="idTarefa">
        <p><label>Título</label><input id="tituloTarefa" maxlength="150" required></p>
        <p><label>Descrição</label><textarea id="descricaoTarefa" rows="4"></textarea></p>
        <p><label>Status</label>
            <select id="statusTarefa">
                <option value="pendente">Pendente</option>
                <option value="em_andamento">Em andamento</option>
                <option value="concluida">Concluída</option>
            </select>
        </p>
        <button type="button" onclick="modalTarefa.close()">Cancelar</button>
        <button class="btn">Salvar</button>
    </form>
</dialog>

<script>
    const api='tarefas_api.php';
    const $=s=>document.querySelector(s);
    const lista=$('#listaTarefas'), modal=$('#modalTarefa'), form=$('#formTarefa'), erro=$('#mensagemErro');
    const id=$('#idTarefa'), titulo=$('#tituloTarefa'), descricao=$('#descricaoTarefa'), status=$('#statusTarefa'), tituloModal=$('#tituloModal');

    // Evita XSS na renderização
    const esc=s=>(s??'').toString().replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));

    // Chamada única da API (JSON)
    const apiPost=(acao,dados={})=>fetch(api,{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({acao,...dados})
    }).then(r=>r.json());

    async function carregarTarefas(){
        const r=await apiPost('listar');
        if(!r.ok) return lista.innerHTML=`<tr><td colspan="7">${esc(r.erro||'Erro')}</td></tr>`;
        if(!r.tarefas.length) return lista.innerHTML='<tr><td colspan="7">Nenhuma tarefa cadastrada.</td></tr>';

        lista.innerHTML=r.tarefas.map(t=>`
    <tr>
      <td>${t.id}</td>
      <td>${esc(t.titulo)}</td>
      <td>${esc(t.descricao||'').replace(/\n/g,'<br>')}</td>
      <td>${esc(t.status)}</td>
      <td>${t.data_criacao||''}</td>
      <td>${t.data_atualizacao||''}</td>
      <td>
        <button onclick='abrirEditar(${JSON.stringify(t).replace(/'/g,"&apos;")})'>Editar</button>
        <button onclick='excluirTarefa(${t.id})'>Excluir</button>
      </td>
    </tr>
  `).join('');
    }

    function abrirNova(){
        erro.textContent=''; form.reset(); id.value='';
        tituloModal.textContent='Nova Tarefa';
        modal.showModal();
    }

    function abrirEditar(t){
        erro.textContent='';
        id.value=t.id; titulo.value=t.titulo||''; descricao.value=t.descricao||''; status.value=t.status||'pendente';
        tituloModal.textContent=`Editar #${t.id}`;
        modal.showModal();
    }

    async function excluirTarefa(idTarefa){
        if(!confirm('Deseja realmente excluir?')) return;
        const r=await apiPost('excluir',{id:idTarefa});
        if(!r.ok) return alert(r.erro||'Erro ao excluir');
        carregarTarefas(); // Atualiza sem F5
    }

    // Salvar (criar/editar) sem reload
    form.onsubmit=async e=>{
        e.preventDefault();
        const dados={
            id:id.value?Number(id.value):null,
            titulo:titulo.value.trim(),
            descricao:descricao.value.trim(),
            status:status.value
        };
        const r=await apiPost(dados.id?'editar':'criar',dados);
        if(!r.ok) return erro.textContent=r.erro||'Erro ao salvar';
        modal.close();
        carregarTarefas();
    };

    carregarTarefas(); // Lista inicial
</script>
</body>
</html>
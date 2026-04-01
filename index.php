<?php require 'config.php';

?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Gerenciador de Tarefas</title>
    <style>
        body{font-family:Arial;max-width:950px;margin:30px auto}.btn{padding:8px 12px;background:#2563eb;color:#fff;border:0;border-radius:6px;cursor:pointer}
        table{width:100%;border-collapse:collapse;margin-top:16px}th,td{border:1px solid #ddd;padding:10px;vertical-align:top}th{background:#f3f4f6}
        dialog{border:1px solid #ddd;border-radius:10px;padding:16px;width:420px}input,textarea,select{width:100%;padding:8px;margin-top:4px}.erro{color:#dc2626}
    </style>
</head>
<body>
<h1>Gerenciador de Tarefas</h1>
<button class="btn" onclick="novo()">+ Nova Tarefa</button>

<table>
    <thead><tr><th>ID</th><th>Título</th><th>Descrição</th><th>Status</th><th>Criada</th><th>Atualizada</th><th>Ações</th></tr></thead>
    <tbody id="tb"><tr><td colspan="7">Carregando...</td></tr></tbody>
</table>

<dialog id="m">
    <form id="f">
        <h3 id="mt">Nova Tarefa</h3><p class="erro" id="e"></p>
        <input type="hidden" id="id">
        <p><label>Título</label><input id="titulo" maxlength="150" required></p>
        <p><label>Descrição</label><textarea id="descricao" rows="4"></textarea></p>
        <p><label>Status</label>
            <select id="status"><option value="pendente">Pendente</option><option value="em_andamento">Em andamento</option><option value="concluida">Concluída</option></select>
        </p>
        <button type="button" onclick="m.close()">Cancelar</button>
        <button class="btn">Salvar</button>
    </form>
</dialog>

<script>
    const api='tarefas_api.php',tb=q('#tb'),m=q('#m'),f=q('#f'),e=q('#e');
    function q(s){return document.querySelector(s)}
    const esc=s=>(s??'').toString().replace(/[&<>"']/g,m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m]));
    const req=(acao,d={})=>fetch(api,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({acao,...d})}).then(r=>r.json());

    async function listar(){
        const j=await req('listar'); if(!j.ok) return tb.innerHTML=`<tr><td colspan="7">${esc(j.erro||'Erro')}</td></tr>`;
        if(!j.tarefas.length) return tb.innerHTML='<tr><td colspan="7">Nenhuma tarefa cadastrada.</td></tr>';
        tb.innerHTML=j.tarefas.map(t=>`<tr>
    <td>${t.id}</td><td>${esc(t.titulo)}</td><td>${esc(t.descricao||'').replace(/\n/g,'<br>')}</td><td>${esc(t.status)}</td>
    <td>${t.data_criacao||''}</td><td>${t.data_atualizacao||''}</td>
    <td><button onclick='edit(${JSON.stringify(t).replace(/'/g,"&apos;")})'>Editar</button> <button onclick="del(${t.id})">Excluir</button></td>
  </tr>`).join('');
    }
    function novo(){e.textContent='';f.reset();id.value='';mt.textContent='Nova Tarefa';m.showModal()}
    function edit(t){e.textContent='';id.value=t.id;titulo.value=t.titulo||'';descricao.value=t.descricao||'';status.value=t.status||'pendente';mt.textContent='Editar #'+t.id;m.showModal()}
    async function del(id){if(!confirm('Deseja realmente excluir?'))return;const j=await req('excluir',{id});if(!j.ok)return alert(j.erro||'Erro');listar()}
    f.onsubmit=async ev=>{ev.preventDefault();const d={id:id.value?+id.value:null,titulo:titulo.value.trim(),descricao:descricao.value.trim(),status:status.value};const j=await req(d.id?'editar':'criar',d);if(!j.ok)return e.textContent=j.erro||'Erro';m.close();listar()}
    listar();
</script>
</body>
</html>
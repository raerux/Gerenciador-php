    async function login() {
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    if (!email || !password) {
        alert('Preencha todos os campos');
        return;
    }
    
    try {
        const res = await fetch('api/users.php?action=login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });

        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Resposta inválida do servidor:', text);
            return;
        }
        
        if (data.success) {
            await loadDashboard();
        }
    } catch (error) {
        alert('Erro ao fazer login: ' + error.message);
    }
}

async function register() {
    const name = document.getElementById('registerName').value;
    const email = document.getElementById('registerEmail').value;
    const password = document.getElementById('registerPassword').value;
    
    if (!name || !email || !password) {
        alert('Preencha todos os campos');
        return;
    }
    
    try {
        const res = await fetch('api/users.php?action=register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, email, password })
        });
        
        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Resposta inválida do servidor:', text);
            alert('Erro: Resposta inválida do servidor');
            return;
        }

        if (data.success) {
            alert('Registrado com sucesso! Faça login');
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao registrar: ' + error.message);
    }
}

async function logout() {
    try {
        await fetch('api/users.php?action=logout');
        showAuthForm();
        document.getElementById('currentUser').textContent = '';
    } catch (error) {
        console.error('Erro:', error);
    }
}

async function loadDashboard() {
    try {
        const res = await fetch('api/users.php?action=current');
        const data = await res.json();

        if (data.success) {
            document.getElementById('currentUser').textContent = data.user.name;
            showDashboard();
            await loadTasks();
        } else {
            alert("Erro ao fazer login!")
        }
    } catch (error) {
        console.error('Erro:',  error);
    }
}

// ============ TAREFAS ============
let currentTasks = [];

async function loadTasks() {
    try {
        const res = await fetch('api/tasks.php?action=list');
        const data = await res.json();
        
        if (data.success) {
            currentTasks = data.tasks;
            displayTasks(data.tasks);
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
    }
}

function displayTasks(tasks) {
    const tasksList = document.getElementById('tasksList');
    tasksList.innerHTML = '';
    
    if (tasks.length === 0) {
        tasksList.innerHTML = '<p class="text-muted">Nenhuma tarefa ainda. Crie uma nova!</p>';
        return;
    }
    
    tasks.forEach(task => {
        const statusColors = {
            'pendente': 'warning',
            'em_andamento': 'info',
            'concluido': 'success'
        };
        const statusLabel = {
            'pendente': 'Pendente',
            'em_andamento': 'Em Andamento',
            'concluido': 'Concluído'
        };
        const badgeColor = statusColors[task.status] || 'secondary';
        const label = statusLabel[task.status] || task.status;

        const html = `
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="mb-1">${task.title}</h6>
                        <small class="text-muted">${task.description || 'Sem descrição'}</small>
                    </div>
                    <button class="btn btn-sm btn-danger" onclick="deleteTask(${task.id})">
                        Deletar
                    </button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <select class="form-select form-select-sm" style="width: auto;" onchange="updateTaskStatus(${task.id}, this.value)">
                        <option value="pendente" ${task.status === 'pendente' ? 'selected' : ''}>Pendente</option>
                        <option value="em_andamento" ${task.status === 'em_andamento' ? 'selected' : ''}>Em Andamento</option>
                        <option value="concluido" ${task.status === 'concluido' ? 'selected' : ''}>Concluído</option>
                    </select>
                    <span class="badge bg-${badgeColor}">${label}</span>
                </div>
            </div>
        `;
        tasksList.innerHTML += html;
    });
}

async function addTask() {
    const title = document.getElementById('taskTitle').value;
    const description = document.getElementById('taskDescription').value;
    
    if (!title) {
        alert('Título é obrigatório');
        return;
    }
    
    try {
        const res = await fetch('api/tasks.php?action=create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title, description })
        });
        
        const data = await res.json();
        if (data.success) {
            document.getElementById('taskTitle').value = '';
            document.getElementById('taskDescription').value = '';
            await loadTasks();
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
    }
}

async function updateTaskStatus(id, status) {
    const task = currentTasks.find(t => t.id === id);
    if (!task) {
        alert('Tarefa não encontrada');
        return false;
    }

    try {
        const updateRes = await fetch('api/tasks.php?action=update', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id,
                title: task.title,
                description: task.description,
                status: status
            })
        });

        if (updateRes.ok) {
            await loadTasks();
            alert("O status da tarefa foi alterado!");
        }

    } catch (error) {
        console.error('Erro:', error);
    }
}

async function deleteTask(id) {
    if (!confirm('Tem certeza?')) return;
    
    try {
        const res = await fetch('api/tasks.php?action=delete', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        
        const data = await res.json();
        if (data.success) {
            await loadTasks();
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
    }
}

// ============ INTERFACE ============
function showAuthForm() {
    document.getElementById('authSection').style.display = 'block';
    document.getElementById('dashboardSection').style.display = 'none';
}

function showDashboard() {
    document.getElementById('authSection').style.display = 'none';
    document.getElementById('dashboardSection').style.display = 'block';
}

function showLoginForm() {
    document.getElementById('loginForm').style.display = 'block';
    document.getElementById('registerForm').style.display = 'none';
}

function showRegisterForm() {
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('registerForm').style.display = 'block';
}
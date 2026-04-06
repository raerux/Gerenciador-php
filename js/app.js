// ============ AUTENTICAÇÃO ============
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
        
        const data = await res.json();
        if (data.success) {
            loadDashboard();
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
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
        
        const data = await res.json();
        if (data.success) {
            alert('Registrado com sucesso! Faça login');
            document.getElementById('registerForm').reset();
            showLoginForm();
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
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
            loadTasks();
        } else {
            showAuthForm();
        }
    } catch (error) {
        console.error('Erro:', error);
        showAuthForm();
    }
}

// ============ TAREFAS ============
async function loadTasks() {
    try {
        const res = await fetch('api/tasks.php?action=list');
        const data = await res.json();
        
        if (data.success) {
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
            loadTasks();
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
    }
}

async function updateTaskStatus(id, status) {
    try {
        const res = await fetch('api/tasks.php?action=list');
        const data = await res.json();
        const task = data.tasks.find(t => t.id === id);
        
        if (task) {
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
                loadTasks();
            }
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
            loadTasks();
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

// Verificar autenticação ao carregar
document.addEventListener('DOMContentLoaded', loadDashboard);


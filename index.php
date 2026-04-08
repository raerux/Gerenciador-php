<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Tarefas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            max-width: 600px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .list-group-item {
            border-bottom: 1px solid #eee;
            padding: 15px;
        }
        .list-group-item:last-child {
            border-bottom: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        #dashboardSection {
            animation: slideIn 0.3s ease-in;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- SEÇÃO DE AUTENTICAÇÃO -->
        <div id="authSection">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="mb-0">Gerenciador de Tarefas</h4>
                </div>
                <div class="card-body">
                    <!-- FORMULÁRIO LOGIN -->
                    <div id="loginForm">
                        <h5 class="mb-4">Fazer Login</h5>
                        <div class="mb-3">
                            <input type="email" class="form-control" id="loginEmail" placeholder="Email">
                        </div>
                        <div class="mb-3">
                            <input type="password" class="form-control" id="loginPassword" placeholder="Senha">
                        </div>
                        <button class="btn btn-primary w-100 mb-3" onclick="login()">Entrar</button>
                        <p class="text-center">Novo por aqui? <a href="#" onclick="showRegisterForm(); return false;">Criar conta</a></p>
                    </div>

                    <!-- FORMULÁRIO REGISTRO -->
                    <div id="registerForm" style="display: none;">
                        <h5 class="mb-4">Criar Conta</h5>
                        <div class="mb-3">
                            <input type="text" class="form-control" id="registerName" placeholder="Nome completo">
                        </div>
                        <div class="mb-3">
                            <input type="email" class="form-control" id="registerEmail" placeholder="Email">
                        </div>
                        <div class="mb-3">
                            <input type="password" class="form-control" id="registerPassword" placeholder="Senha">
                        </div>
                        <button class="btn btn-primary w-100 mb-3" onclick="register() && showLoginForm()">Registrar</button>
                        <p class="text-center">Já tem conta? <a href="#" onclick="showLoginForm(); return false;">Fazer Login</a></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEÇÃO DASHBOARD -->
        <div id="dashboardSection" style="display: none;">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Minhas Tarefas</h4>
                    <div>
                        <small id="currentUser" class="me-3"></small>
                        <button class="btn btn-light btn-sm" onclick="logout()">Sair</button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- ADICIONAR TAREFA -->
                    <div class="mb-4">
                        <h6>Adicionar Tarefa</h6>
                        <input type="text" class="form-control mb-2" id="taskTitle" placeholder="Título da tarefa">
                        <textarea class="form-control mb-2" id="taskDescription" placeholder="Descrição (opcional)" rows="2"></textarea>
                        <button class="btn btn-primary w-100" onclick="addTask()" >Adicionar Tarefa</button>
                    </div>

                    <hr>

                    <!-- LISTA DE TAREFAS -->
                    <h6>Tarefas</h6>
                    <div id="tasksList" class="list-group">
                        <p class="text-muted">Carregando...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/app.js"></script>
</body>
</html>

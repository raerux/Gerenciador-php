<?php
// Iniciar sessão primeiro (antes de qualquer output)
session_start();

// Configuração do banco de dados MySQL
$host = 'localhost';
$user = 'root';
$password = 'password';
$database = 'gerenciador';
$port = 3306;
$db = null;

try {
    $db = new PDO("mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4", $user, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    $errorMsg = 'Erro ao conectar ao banco de dados: ' . $e->getMessage();
    error_log($errorMsg);
    die(json_encode(['success' => false, 'message' => 'Erro ao conectar ao banco de dados. Verifique a configuração do servidor.']));
}

// Função para autenticar usuário
function authenticate($email, $password) {
    global $db;
    $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        return true;
    }
    return false;
}

// Função para registrar usuário
function register($name, $email, $password) {
    global $db;
    try {
        $stmt = $db->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_BCRYPT)]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Função para verificar se usuário está autenticado
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

// Função para fazer logout
function logout() {
    session_destroy();
}

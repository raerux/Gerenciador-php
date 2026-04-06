<?php
header('Content-Type: application/json; charset=utf-8');

// Configuração do banco de dados MySQL
$host = 'localhost';
$user = 'root';
$password = 'password';
$database = 'gerenciador';
$port = 3306;

try {
    $db = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Criar banco de dados se não existir
    $db->exec("CREATE DATABASE IF NOT EXISTS $database");

    // Selecionar banco de dados
    $db->exec("USE $database");

    // Criar tabelas se não existirem
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS tasks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            status ENUM('pendente', 'em_andamento', 'concluido') DEFAULT 'pendente',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
} catch (PDOException $e) {
    die('Erro ao conectar ao MySQL: ' . $e->getMessage());
}

// Iniciar sessão
session_start();

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
?>


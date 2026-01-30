<?php
// Previne erro de "headers already sent"
ob_start();

// Configurações do Banco de Dados (InfinityFree)
$host = 'sql100.infinityfree.com';
$dbname = 'if0_41007169_sigyo_bdt';
$user = 'if0_41007169'; 
$pass = 'k8YuCvnUKWSNX5i';

// Define o fuso horário do PHP para Rondônia/Manaus (-04:00)
date_default_timezone_set('America/Manaus');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    
    // Tenta forçar o horário do MySQL para -04:00 na conexão atual
    // (Algumas hospedagens bloqueiam isso, mas se funcionar ajuda nas queries diretas)
    $pdo->exec("SET time_zone = '-04:00'");
    
} catch(PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// Inicia sessão SEGURO
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

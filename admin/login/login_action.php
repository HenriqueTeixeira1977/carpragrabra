<?php
session_start();
require_once '../config.php';

$usuario = $_POST['usuario'] ?? '';
$senha   = $_POST['senha'] ?? '';

$sql = "SELECT * FROM usuarios WHERE usuario = :usuario";
$stmt = $pdo->prepare($sql);
$stmt->execute([':usuario' => $usuario]);
$user = $stmt->fetch();

if ($user && password_verify($senha, $user['senha'])) {
    $_SESSION['admin_user'] = $user['usuario'];
    $_SESSION['admin_nome'] = $user['nome'];
    header('Location: ../dashboard/dashboard.php');
    exit;
} else {
    header('Location: login.php?erro=1');
    exit;
}

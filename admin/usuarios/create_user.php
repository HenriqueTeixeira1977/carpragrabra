<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome    = $_POST['nome'] ?? '';
    $usuario = $_POST['usuario'] ?? '';
    $senha   = $_POST['senha'] ?? '';
    $funcao  = $_POST['funcao'] ?? 'Vendedor';

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // Verifica se já existe usuário com esse e-mail
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);

    if ($stmt->fetch()) {
        echo "Usuário com e-mail '$usuario' já existe.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, usuario, senha, funcao) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nome, $usuario, $senhaHash, $funcao])) {
            echo "Usuário criado com sucesso!";
        } else {
            echo "Erro ao criar o usuário.";
        }
    }
} else {
    echo "Acesso inválido.";
}

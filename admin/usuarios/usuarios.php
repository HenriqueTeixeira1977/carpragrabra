<?php
require_once '../config.php';
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login/login.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM usuarios ORDER BY nome ASC");
$usuarios = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Usuários - Painel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h3 class="mb-4">Gerenciamento de Usuários</h3>
    <a href="novo_usuario.php" class="btn btn-success mb-3">+ Novo Usuário</a>
    <table class="table table-bordered table-hover bg-white">
        <thead class="table-dark">
            <tr>
                <th>Nome</th>
                <th>Usuário (e-mail)</th>
                <th>Função</th>
                <th style="width: 160px;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= htmlspecialchars($usuario['nome']) ?></td>
                    <td><?= htmlspecialchars($usuario['usuario']) ?></td>
                    <td><?= htmlspecialchars($usuario['funcao']) ?></td>
                    <td>
                        <a href="usuario_editar.php?id=<?= $usuario['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                        <a href="usuario_excluir.php?id=<?= $usuario['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este usuário?')">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="../dashboard.php" class="btn btn-secondary">Voltar ao Painel</a>
</div>

</body>
</html>

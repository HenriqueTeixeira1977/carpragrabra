<?php
require_once '../config.php';
session_start();

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: usuarios.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome    = $_POST['nome'];
    $usuario = $_POST['usuario'];
    $funcao  = $_POST['funcao'];
    $novaSenha = $_POST['senha'];

    if (!empty($novaSenha)) {
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, usuario = ?, funcao = ?, senha = ? WHERE id = ?");
        $success = $stmt->execute([$nome, $usuario, $funcao, $senhaHash, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, usuario = ?, funcao = ? WHERE id = ?");
        $success = $stmt->execute([$nome, $usuario, $funcao, $id]);
    }

    if ($success) {
        header("Location: usuarios.php");
        exit;
    } else {
        echo "Erro ao atualizar.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h3 class="mb-4">Editar Usuário</h3>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Usuário (e-mail)</label>
            <input type="email" name="usuario" class="form-control" value="<?= htmlspecialchars($usuario['usuario']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Função</label>
            <select name="funcao" class="form-select">
                <option value="Administrador" <?= $usuario['funcao'] == 'Administrador' ? 'selected' : '' ?>>Administrador</option>
                <option value="Gerente" <?= $usuario['funcao'] == 'Gerente' ? 'selected' : '' ?>>Gerente</option>
                <option value="Vendedor" <?= $usuario['funcao'] == 'Vendedor' ? 'selected' : '' ?>>Vendedor</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Nova Senha (opcional)</label>
            <input type="password" name="senha" class="form-control" placeholder="Deixe em branco para manter a senha atual">
        </div>

        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

</body>
</html>

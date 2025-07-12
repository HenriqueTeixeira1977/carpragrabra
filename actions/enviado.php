<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');
$nome = $_SESSION['nome'] ?? 'Cliente';
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obrigado!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .card {
            max-width: 600px;
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background-color: #28a745;
            border: none;
        }
        .btn-primary:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="card p-5 text-center">
        <h1 class="mb-4">Obrigado, <?= htmlspecialchars($nome) ?>!</h1>
        <p class="mb-4">Recebemos suas informações! Em breve entraremos em contato para te atender com a máxima atenção.</p>
        <a href="../index.php" class="btn btn-primary">Voltar para o Início</a>
        <div>
            <p class="text-muted mb-4">
                <?= date("d/m/Y H:i") ?>
            </p>
        </div>
    </div>
</body>
</html>

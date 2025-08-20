<?php
session_start();
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: ../dashboard/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login Administrativo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
        }
        h1, p{
            color: black;
        }
        .login-container {
            height: 100vh;
        }
        .bg-image {
            background: url('../assets/img/img-login.png') no-repeat left center;
            background-size: cover;
        }
        .box{background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            width: 95%;
            max-width: 700px;
            height: 70%;
            padding: 15px;
            box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.5);
            border-radius: 15px;
        }
        .box .title{
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container-fluid login-container">
    <div class="row h-100">
        <!-- Lado esquerdo com imagem e chamada -->
        <div class="col-md-6 d-none d-md-flex align-items-center justify-content-center bg-image text-white text-center p-5"></div>

        <!-- Lado direito com formulário -->
        <div class="col-md-6 d-flex align-items-center justify-content-center flex-direction-column bg-light">
            
            <div class="box" id="box" d-flex align-items-center justify-content-center flex-direction-column>
                <div class="title" id="title">
                    <h1 class="display-5 fw-bold">Bem-vindo ao Painel Administrativo</h1>
                    <p class="lead">Gerencie seus leads com facilidade e eficiência.</p>
                </div>

                <!-- FORMULARIO DE LOGIN -->
                <div id="form" class="w-100" style="max-width: 400px;">
                    <h2 class="mb-4 text-center">Login Admin</h2>
    
                    <?php if (isset($_GET['erro'])): ?>
                        <div class="alert alert-danger">Usuário ou senha inválidos!</div>
                    <?php endif; ?>
    
                    <form method="POST" action="login_action.php">
                        <div class="mb-3">
                            <label for="usuario" class="form-label">Usuário</label>
                            <input type="text" class="form-control" id="usuario" name="usuario" required>
                        </div>
    
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="senha" name="senha" required>
                        </div>
    
                        <button type="submit" class="btn btn-success w-100">Entrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>

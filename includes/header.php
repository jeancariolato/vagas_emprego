<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Job Board</a>
            <div class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['is_admin']): ?>
                        <a class="nav-link" href="admin/jobs.php">Gerenciar Vagas</a>
                        <a class="nav-link" href="admin/categories.php">Gerenciar Categorias</a>
                    <?php endif; ?>
                    <a class="nav-link" href="logout.php">Sair</a>
                <?php else: ?>
                    <a class="nav-link" href="login.php">Login</a>
                    <a class="nav-link" href="register.php">Cadastro</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
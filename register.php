<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $linkedin = $_POST['linkedin'];

    $photo = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "uploads/";
        $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo = $target_dir . uniqid('user_', true) . '.' . $file_extension;
        
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
    }

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, photo, linkedin, is_admin) VALUES (?, ?, ?, ?, ?, 0)");
    $stmt->execute([$name, $email, $password, $photo, $linkedin]);

    header("Location: login.php?registered=success");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crie sua Conta - JobLine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-panel-left">
        <div class="brand-content">
            <h1>Junte-se à Comunidade</h1>
            <p>Encontre as melhores vagas e impulsione sua carreira.</p>
        </div>
    </div>

    <div class="auth-panel-right">
        <div class="form-container">
            <h2>Crie sua Conta</h2>
            <p class="subtitle">Vamos começar sua jornada.</p>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-1">
                    <label for="name" class="form-label">Nome Completo</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-1">
                    <label for="email" class="form-label">Seu Melhor E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-1">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-1">
                    <label for="photo" class="form-label">Foto de Perfil (Opcional)</label>
                    <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                </div>
                <div class="mb-1">
                    <label for="linkedin" class="form-label">URL do seu LinkedIn (Opcional)</label>
                    <input type="url" class="form-control" id="linkedin" name="linkedin" placeholder="https://linkedin.com/in/seu-usuario">
                </div>
                <button type="submit" class="btn btn-primary">Criar Conta</button>
            </form>
            
            <div class="form-footer-link">
                <p>Já tem uma conta? <a href="login.php">Faça Login</a></p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
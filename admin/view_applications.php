<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit;
}

$job_id = $_GET['job_id'];
$job = $pdo->prepare("SELECT title FROM jobs WHERE id = ?")->execute([$job_id]);
$job = $pdo->fetch();
$applications = $pdo->prepare("SELECT u.* FROM applications a JOIN users u ON a.user_id = u.id WHERE a.job_id = ?");
$applications->execute([$job_id]);
$applications = $applications->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Inscritos na Vaga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Inscritos na Vaga: <?php echo $job['title']; ?></h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Foto</th>
                    <th>LinkedIn</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?php echo $app['name']; ?></td>
                        <td><?php echo $app['email']; ?></td>
                        <td><img src="<?php echo $app['photo'] ?: 'default.jpg'; ?>" width="50"></td>
                        <td><a href="<?php echo $app['linkedin']; ?>" target="_blank">Perfil</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="jobs.php" class="btn btn-primary">Voltar</a>
    </div>
</body>
</html>
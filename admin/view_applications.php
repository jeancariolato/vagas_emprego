<?php
session_start();
require_once '../includes/db.php';

// Verifica se o usuário está logado e é admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit;
}

// Garante que um ID de vaga foi passado pela URL
if (!isset($_GET['job_id'])) {
    header("Location: jobs.php");
    exit;
}
$job_id = $_GET['job_id'];

// --- CÓDIGO CORRIGIDO PARA BUSCAR O TÍTULO DA VAGA ---
// 1. Prepara a consulta
$stmt_job = $pdo->prepare("SELECT title FROM jobs WHERE id = ?");
// 2. Executa a consulta
$stmt_job->execute([$job_id]);
// 3. Busca o resultado a partir do statement
$job = $stmt_job->fetch();
// --------------------------------------------------------

// Se a vaga não for encontrada, redireciona de volta
if (!$job) {
    header("Location: jobs.php");
    exit;
}

// Busca os usuários inscritos na vaga (esta parte já estava correta)
$stmt_apps = $pdo->prepare("SELECT u.name, u.email, u.photo, u.linkedin FROM applications a JOIN users u ON a.user_id = u.id WHERE a.job_id = ?");
$stmt_apps->execute([$job_id]);
$applications = $stmt_apps->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Inscritos na Vaga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<nav class="navbar navbar-expand-lg admin-navbar">
    <div class="container">
        <a class="navbar-brand" href="../index.php">Job<span>Line</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" href="jobs.php">Gerenciar Vagas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="categories.php">Categorias</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                 <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Admin
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="../login.php">Sair</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="main-content container mt-4">
    <h2>Inscritos na Vaga: "<?php echo htmlspecialchars($job['title']); ?>"</h2>

    <div class="admin-panel">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Foto</th>
                        <th>LinkedIn</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applications)): ?>
                        <tr>
                            <td colspan="4" class="text-center">Nenhum candidato inscrito nesta vaga ainda.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($app['name']); ?></td>
                                <td><?php echo htmlspecialchars($app['email']); ?></td>
                                <td>
                                    <img src="../<?php echo htmlspecialchars($app['photo'] ?: 'uploads/default_user.jpg'); ?>" width="50" height="50" style="object-fit: cover; border-radius: 50%;">
                                </td>
                                <td>
                                    <?php if (!empty($app['linkedin'])): ?>
                                        <a href="<?php echo htmlspecialchars($app['linkedin']); ?>" target="_blank" class="btn btn-sm btn-info">Ver Perfil</a>
                                    <?php else: ?>
                                        <span class="text-secondary">Não informado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            <a href="jobs.php" class="btn btn-secondary">Voltar para Vagas</a>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $contact = $_POST['contact'];
        $category_id = $_POST['category_id'];
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../uploads/";
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image = 'uploads/' . uniqid('job_', true) . '.' . $file_extension;
            move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image);
        }
        $stmt = $pdo->prepare("INSERT INTO jobs (title, description, contact, image, category_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $contact, $image, $category_id]);

    } elseif (isset($_POST['toggle'])) {
        $id = $_POST['id'];
        $is_active = $_POST['is_active'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE jobs SET is_active = ? WHERE id = ?");
        $stmt->execute([$is_active, $id]);
    }
    header("Location: jobs.php");
    exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$jobs = $pdo->query("SELECT j.*, c.name as category_name FROM jobs j JOIN categories c ON j.category_id = c.id ORDER BY j.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gerenciar Vagas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
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
    <h2>Gerenciar Vagas</h2>

    <div class="admin-panel">
        <h4 class="mb-4">Adicionar Nova Vaga</h4>
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="title" class="form-label">Título da Vaga</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="category_id" class="form-label">Categoria</label>
                    <select class="form-select" id="category_id" name="category_id" required>
                        <option value="" disabled selected>Selecione uma categoria</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Descrição Completa</label>
                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="contact" class="form-label">Contato (E-mail, Telefone, etc.)</label>
                    <input type="text" class="form-control" id="contact" name="contact" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="image" class="form-label">Imagem da Vaga (Opcional)</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                </div>
            </div>
            <button type="submit" name="add" class="btn btn-primary px-4">Criar Vaga</button>
        </form>
    </div>

    <div class="admin-panel">
        <h4 class="mb-4">Vagas Cadastradas</h4>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Categoria</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $job): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($job['title']); ?></td>
                            <td><?php echo htmlspecialchars($job['category_name']); ?></td>
                            <td>
                                <span class="badge <?php echo $job['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo $job['is_active'] ? 'Ativa' : 'Inativa'; ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="id" value="<?php echo $job['id']; ?>">
                                    <input type="hidden" name="is_active" value="<?php echo $job['is_active']; ?>">
                                    <button type="submit" name="toggle" class="btn btn-sm <?php echo $job['is_active'] ? 'btn-danger' : 'btn-success'; ?>">
                                        <?php echo $job['is_active'] ? 'Desativar' : 'Ativar'; ?>
                                    </button>
                                </form>
                                <a href="view_applications.php?job_id=<?php echo $job['id']; ?>" class="btn btn-sm btn-info">Ver Inscritos</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
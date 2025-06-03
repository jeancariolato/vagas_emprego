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
        if ($_FILES['image']['name']) {
            $target_dir = "../uploads/";
            $image = $target_dir . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $image);
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

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
$jobs = $pdo->query("SELECT j.*, c.name as category_name FROM jobs j JOIN categories c ON j.category_id = c.id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Vagas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Gerenciar Vagas</h2>
        <form method="POST" enctype="multipart/form-data" class="mb-4">
            <div class="mb-3">
                <label for="title" class="form-label">Título</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Descrição</label>
                <textarea class="form-control" id="description" name="description" required></textarea>
            </div>
            <div class="mb-3">
                <label for="contact" class="form-label">Contato</label>
                <input type="text" class="form-control" id="contact" name="contact" required>
            </div>
            <div class="mb-3">
                <label for="category_id" class="form-label">Categoria</label>
                <select class="form-control" id="category_id" name="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Imagem</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
            </div>
            <button type="submit" name="add" class="btn btn-primary">Criar Vaga</button>
        </form>
        <table class="table">
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
                        <td><?php echo $job['title']; ?></td>
                        <td><?php echo $job['category_name']; ?></td>
                        <td><?php echo $job['is_active'] ? 'Ativa' : 'Inativa'; ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
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
</body>
</html>
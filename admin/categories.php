<?php
session_start();
require_once '../includes/db.php';

// Verifica se o usuário está logado e é admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php");
    exit;
}

// Lógica de manipulação do formulário (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $name = $_POST['name'];
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
    } elseif (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        // Adicionar verificação se a categoria está em uso antes de deletar (Opcional mas recomendado)
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
    }
    header("Location: categories.php");
    exit;
}

// Busca os dados para exibir na página
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gerenciar Categorias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css"> </head>
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
                    <a class="nav-link" href="jobs.php">Gerenciar Vagas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="categories.php">Categorias</a>
                </li>
               
            </ul>
            <ul class="navbar-nav">
                 <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Admin
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="../logout.php">Sair</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="main-content container mt-4">
    <h2>Gerenciar Categorias</h2>

    <div class="admin-panel">
        <h4 class="mb-4">Adicionar Nova Categoria</h4>
        <form method="POST" class="row g-3 align-items-end">
            <div class="col">
                <label for="name" class="form-label">Nome da Categoria</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="col-auto">
                <button type="submit" name="add" class="btn btn-primary">Adicionar</button>
            </div>
        </form>
    </div>

    <div class="admin-panel">
         <h4 class="mb-4">Categorias Cadastradas</h4>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td class="text-end">
                                <form method="POST" class="d-inline-block me-2" id="edit-form-<?php echo $category['id']; ?>">
                                    <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-sm" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                                        <button type="submit" name="edit" class="btn btn-sm btn-warning">Editar</button>
                                    </div>
                                </form>
                                <form method="POST" class="d-inline-block" onsubmit="return confirm('Tem certeza que deseja excluir esta categoria?');">
                                    <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger">Excluir</button>
                                </form>
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
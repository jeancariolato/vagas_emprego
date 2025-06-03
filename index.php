<?php
session_start();
require_once 'includes/db.php';

$category_id = $_GET['category_id'] ?? '';
$where = $category_id ? "WHERE j.category_id = :category_id AND j.is_active = 1" : "WHERE j.is_active = 1";
$stmt = $pdo->prepare("SELECT j.*, c.name as category_name FROM jobs j JOIN categories c ON j.category_id = c.id $where");
if ($category_id) $stmt->bindParam(':category_id', $category_id);
$stmt->execute();
$jobs = $stmt->fetchAll();
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

if (isset($_POST['apply']) && isset($_SESSION['user_id'])) {
    $job_id = $_POST['job_id'];
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("INSERT INTO applications (user_id, job_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $job_id]);
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Vagas de Emprego</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Vagas de Emprego</h2>
        <form method="GET" class="mb-4">
            <label for="category_id" class="form-label">Filtrar por Categoria</label>
            <select class="form-control" id="category_id" name="category_id" onchange="this.form.submit()">
                <option value="">Todas</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php if ($category_id == $category['id']) echo 'selected'; ?>>
                        <?php echo $category['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <div class="row">
            <?php foreach ($jobs as $job): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="<?php echo $job['image'] ?: 'default.jpg'; ?>" class="card-img-top" alt="Vaga">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $job['title']; ?></h5>
                            <p class="card-text"><?php echo substr($job['description'], 0, 100); ?>...</p>
                            <p><strong>Categoria:</strong> <?php echo $job['category_name']; ?></p>
                            <p><strong>Contato:</strong> <?php echo $job['contact']; ?></p>
                            <?php if (isset($_SESSION['user_id']) && !$_SESSION['is_admin']): ?>
                                <form method="POST">
                                    <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                    <button type="submit" name="apply" class="btn btn-primary">Candidatar-se</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="login.php" class="btn btn-primary">Faça login para se candidatar</a>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
session_start();
require_once 'includes/db.php';

// A LÓGICA PHP PERMANECE A MESMA
$category_id = $_GET['category_id'] ?? '';
$where = $category_id ? "WHERE j.category_id = :category_id AND j.is_active = 1" : "WHERE j.is_active = 1";
$stmt = $pdo->prepare("SELECT j.*, c.name as category_name FROM jobs j JOIN categories c ON j.category_id = c.id $where ORDER BY j.created_at DESC");
if ($category_id) {
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
}
$stmt->execute();
$jobs = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// 1. Buscar inscrições do usuário logado
$user_applications = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT job_id FROM applications WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_applications = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply']) && isset($_SESSION['user_id'])) {
    $job_id = $_POST['job_id'];
    $user_id = $_SESSION['user_id'];
    
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ? AND job_id = ?");
    $checkStmt->execute([$user_id, $job_id]);
    if ($checkStmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO applications (user_id, job_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $job_id]);
    }
    
    header("Location: index.php?applied=true");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JobLine - Encontre seu Emprego</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">Job<span>Line</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
               <ul class="navbar-nav ms-auto align-items-center">
    <?php if (isset($_SESSION['user_id'])): ?>
        <li class="nav-item">
            <a class="nav-link" href="#">Olá, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuário'); ?></a>
        </li>
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
             <li class="nav-item">
                <a class="nav-link" href="admin/jobs.php">Painel Admin</a>
            </li>
        <?php endif; ?>
        <li class="nav-item">
            <a class="nav-link" href="login.php">Sair</a>
        </li>
    <?php else: ?>
        <li class="nav-item">
            <a class="nav-link" href="login.php">Login</a>
        </li>
        <li class="nav-item ms-2">
            <a class="btn btn-primary" href="register.php">Cadastre-se</a>
        </li>
    <?php endif; ?>
</ul>
            </div>
        </div>
    </nav>

    <header class="hero-section">
        <div class="container">
            <h1>A sua próxima <span>oportunidade</span> <br>está aqui!</h1>
            <p class="lead">Navegue por centenas de vagas de emprego nas melhores empresas. Filtre por categoria para encontrar a oportunidade perfeita para você.</p>
            
            <form method="GET" class="filter-form">
                <select class="form-select" id="category_id" name="category_id" onchange="this.form.submit()">
                    <option value="">Filtrar por Todas as Categorias</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php if ($category_id == $category['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </header>

    <main class="container mt-5">
        <div class="row gy-4">
            <?php if (empty($jobs)): ?>
                <div class="col-12 text-center">
                    <p class="lead">Nenhuma vaga encontrada. Tente outra categoria ou verifique mais tarde.</p>
                </div>
            <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card">
                            <?php if (!empty($job['image'])): ?>
                                <img src="<?php echo htmlspecialchars($job['image']); ?>" alt="Imagem da vaga" class="card-img-top" style="width:48px; height:48px; object-fit:cover; margin: 24px auto 0; border-radius: 50%; display:block;">
                            <?php else: ?>
                                <i class="bi bi-briefcase-fill card-icon" style="font-size:48px; display:block; margin:24px auto 0;"></i>
                            <?php endif; ?>
                            <h5 class="card-title"><?php echo htmlspecialchars($job['title']); ?></h5>
                            <span class="badge rounded-pill"><?php echo htmlspecialchars($job['category_name']); ?></span>
                            <p class="card-text" style="font-size: 0.92em;"><?php echo htmlspecialchars($job['description']); ?></p>
                            <div class="text-muted mb-2" style="font-size:0.95em;">
                                <strong>Contato:</strong> <?php echo htmlspecialchars($job['contact']); ?>
                            </div>
                            <?php if (isset($_SESSION['user_id']) && (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin'])): ?>
                                <?php if (in_array($job['id'], $user_applications)): ?>
                                    <button class="btn btn-secondary w-100" disabled>Inscrito!</button>
                                <?php else: ?>
                                    <form method="POST" class="mt-auto">
                                        <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                        <button type="submit" name="apply" class="btn btn-primary w-100">Candidatar-se</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> JobLine. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
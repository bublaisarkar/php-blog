<?php
session_start();
require "db_conn.php";

$logged = false;
$user_id = null;

if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    $logged = true;
    $user_id = $_SESSION['user_id'];
}

/* Fetch latest posts */
$sql = "SELECT post.post_id, post.post_title, post.cover_url, 
               post.crated_at, category.category 
        FROM post 
        JOIN category ON post.category = category.id
        WHERE post.publish = 1
        ORDER BY post.crated_at DESC
        LIMIT 6";

$stmt = $conn->query($sql);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Blog</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>

<body>

<?php include 'inc/NavBar.php'; ?>

<!-- HERO SECTION -->
<div class="bg-dark text-white text-center p-5 mb-4">
    <h1>Welcome to My Tech Blog</h1>
    <p>Programming • AI • Technology • Tutorials</p>
</div>

<!-- POSTS GRID -->
<div class="container">
    <div class="row">

        <?php if ($posts): ?>
            <?php foreach ($posts as $post): ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <img src="upload/blog/<?= $post['cover_url'] ?>" class="card-img-top" style="height:200px;object-fit:cover;">
                        <div class="card-body">
                            <span class="badge bg-primary mb-2">
                                <?= htmlspecialchars($post['category']) ?>
                            </span>

                            <h5 class="card-title">
                                <?= htmlspecialchars($post['post_title']) ?>
                            </h5>

                            <a href="blog-view.php?post_id=<?= $post['post_id'] ?>" class="btn btn-sm btn-dark">
                                Read More
                            </a>
                        </div>
                        <div class="card-footer text-muted small">
                            <?= date("d M Y", strtotime($post['crated_at'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No posts found.</p>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'inc/Footer.php'; ?>
</body>
</html>
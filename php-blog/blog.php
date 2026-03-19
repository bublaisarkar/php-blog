<?php 
session_start();

$logged = false;
if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    $logged = true;
    $user_id = $_SESSION['user_id'];
}

include_once("admin/data/Post.php");
include_once("admin/data/Comment.php");
include_once("db_conn.php");

$notFound = 0;

/* pagination setup */
$limit = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

if(isset($_GET['search'])){

    $key = $_GET['search'];

    /* total search posts */
    $stmt = $conn->prepare("SELECT COUNT(*) FROM post 
                            WHERE publish = 1 
                            AND post_title LIKE :key");
    $stmt->execute(['key'=>"%$key%"]);
    $total_posts = $stmt->fetchColumn();

    $total_pages = ceil($total_posts / $limit);

    /* fetch search posts */
    $stmt = $conn->prepare("SELECT * FROM post
                            WHERE publish = 1
                            AND post_title LIKE :key
                            ORDER BY crated_at DESC
                            LIMIT $start, $limit");
    $stmt->execute(['key'=>"%$key%"]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(!$posts){
        $notFound = 1;
    }

}else{

    /* total posts */
    $total_posts = $conn->query(
        "SELECT COUNT(*) FROM post WHERE publish = 1"
    )->fetchColumn();

    $total_pages = ceil($total_posts / $limit);

    /* fetch posts */
    $posts = $conn->query(
        "SELECT * FROM post
         WHERE publish = 1
         ORDER BY crated_at DESC
         LIMIT $start, $limit"
    )->fetchAll(PDO::FETCH_ASSOC);
}

$categories = get5Categoies($conn); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
<?php 
if(isset($_GET['search'])){
    echo "Search '".htmlspecialchars($_GET['search'])."'";
}else{
    echo "Blog Page";
}
?>
</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/style.css">
</head>

<body>

<?php include 'inc/NavBar.php'; ?>

<div class="container mt-5">
<section class="d-flex">

<?php if ($posts) { ?>
<main class="main-blog">

<h1 class="display-4 mb-4 fs-3">
<?php 
if(isset($_GET['search'])){
    echo "Search <b>'".htmlspecialchars($_GET['search'])."'</b>";
}
?>
</h1>

<?php foreach ($posts as $post) { ?>
<div class="card main-blog-card mb-5">

<img src="upload/blog/<?= htmlspecialchars($post['cover_url']) ?>" 
     class="card-img-top">

<div class="card-body">

<h5 class="card-title"><?= htmlspecialchars($post['post_title']) ?></h5>

<?php 
$p = strip_tags($post['post_text']); 
$p = substr($p, 0, 200);
?>

<p class="card-text"><?= $p ?>...</p>

<a href="blog-view.php?post_id=<?= $post['post_id'] ?>" 
   class="btn btn-primary">Read more</a>

<hr>

<div class="d-flex justify-content-between">
<div class="react-btns">

<?php 
$post_id = $post['post_id'];

if ($logged) {
    $liked = isLikedByUserID($conn, $post_id, $user_id);

    if($liked){
?>
<i class="fa fa-thumbs-up liked like-btn"
   post-id="<?= $post_id ?>"
   liked="1"></i>
<?php }else{ ?>
<i class="fa fa-thumbs-up like like-btn"
   post-id="<?= $post_id ?>"
   liked="0"></i>
<?php } } else{ ?>
<i class="fa fa-thumbs-up"></i>
<?php } ?>

Likes (
<span><?= likeCountByPostID($conn, $post_id) ?></span>
)

<a href="blog-view.php?post_id=<?= $post_id ?>#comments">
<i class="fa fa-comment"></i> Comments (
<?= CountByPostID($conn, $post_id) ?>
)
</a>

</div>

<small class="text-body-secondary">
<?= date("d M Y", strtotime($post['crated_at'])) ?>
</small>

</div>
</div>
</div>
<?php } ?>

<!-- pagination -->
<?php if(!isset($_GET['search']) && $total_pages > 1){ ?>
<div class="d-flex justify-content-center mt-4">
<nav>
<ul class="pagination">

<?php if($page > 1){ ?>
<li class="page-item">
<a class="page-link" href="?page=<?= $page-1 ?>">Previous</a>
</li>
<?php } ?>

<?php for($i=1; $i <= $total_pages; $i++){ ?>
<li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
<a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
</li>
<?php } ?>

<?php if($page < $total_pages){ ?>
<li class="page-item">
<a class="page-link" href="?page=<?= $page+1 ?>">Next</a>
</li>
<?php } ?>

</ul>
</nav>
</div>
<?php } ?>

</main>

<?php } else { ?>

<main class="main-blog p-2">
<?php if($notFound){ ?>
<div class="alert alert-warning">
No search results found -
<b>'<?= htmlspecialchars($_GET['search']) ?>'</b>
</div>
<?php } else { ?>
<div class="alert alert-warning">
No posts yet.
</div>
<?php } ?>
</main>

<?php } ?>

<aside class="aside-main">
<div class="list-group category-aside">

<a class="list-group-item list-group-item-action active">
Category
</a>

<?php foreach ($categories as $category ) { ?>
<a href="category.php?category_id=<?= $category['id'] ?>" 
   class="list-group-item list-group-item-action">
<?= $category['category']; ?>
</a>
<?php } ?>

</div>
</aside>

</section>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<script>
$(document).ready(function(){
    $(".like-btn").click(function(){

        var post_id = $(this).attr('post-id');
        var liked = $(this).attr('liked');

        if (liked == 1) {
            $(this).attr('liked', '0');
            $(this).removeClass('liked');
        } else {
            $(this).attr('liked', '1');
            $(this).addClass('liked');
        }

        $(this).next().load("ajax/like-unlike.php",
        {
            post_id: post_id
        });

    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'inc/Footer.php'; ?>
</body>
</html>
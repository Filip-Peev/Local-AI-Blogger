<?php
include 'db.php';

$slug = $_GET['slug'] ?? '';

if (!$slug) {
    die("Invalid post");
}

/* -------------------------
   GET POST
--------------------------*/
$stmt = $conn->prepare("
    SELECT * FROM posts
    WHERE slug = ?
    LIMIT 1
");
$stmt->execute([$slug]);

$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    die("Post not found");
}

/* -------------------------
   INCREMENT VIEWS
--------------------------*/
$conn->prepare("
    UPDATE posts SET views = views + 1 WHERE id = ?
")->execute([$post['id']]);

/* -------------------------
   SAFE VALUES
--------------------------*/
$title = $post['seo_title'] ?: $post['title'];
$description = $post['seo_description'] ?? '';
$tags = $post['tags'] ?? '';
?>

<!DOCTYPE html>
<html>

<head>
    <title><?php echo htmlspecialchars($title); ?></title>

    <meta name="description" content="<?php echo htmlspecialchars($description); ?>">

    <style>
        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial;
            background: #0b1220;
            color: #e5e7eb;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: auto;
            padding: 40px 20px;
        }

        a {
            color: #60a5fa;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .meta {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 20px;
        }

        .tags {
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 20px;
        }

        .content {
            line-height: 1.7;
            font-size: 16px;
        }

        .content h2,
        .content h3 {
            margin-top: 25px;
        }

        .content code {
            background: #111a2e;
            padding: 3px 6px;
            border-radius: 5px;
        }

        .content pre {
            background: #111a2e;
            padding: 12px;
            border-radius: 8px;
            overflow-x: auto;
        }
    </style>
</head>

<body>

    <div class="container">

        <a href="index.php">← Back to blog</a>

        <h1><?php echo htmlspecialchars($title); ?></h1>

        <div class="meta">
            👁 <?php echo $post['views']; ?> views •
            📅 <?php echo $post['created_at']; ?>
        </div>

        <div class="tags">
            <?php echo htmlspecialchars($tags); ?>
        </div>

        <div class="content">
            <?php echo $post['content']; ?>
        </div>

    </div>

</body>

</html>
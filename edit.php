<?php
include 'auth.php';
include 'db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("Invalid ID");
}

/* -------------------------
   FETCH POST
--------------------------*/
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    die("Post not found");
}

/* -------------------------
   SAVE CHANGES
--------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = $_POST['title'];
    $content = $_POST['content'];
    $tags = $_POST['tags'];
    $seo_title = $_POST['seo_title'];
    $seo_desc = $_POST['seo_description'];

    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $title));

    $stmt = $conn->prepare("
        UPDATE posts SET
            title = ?,
            slug = ?,
            content = ?,
            tags = ?,
            seo_title = ?,
            seo_description = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $title,
        $slug,
        $content,
        $tags,
        $seo_title,
        $seo_desc,
        $id
    ]);

    header("Location: admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Post</title>

    <style>
        body {
            font-family: system-ui;
            background: #0b1220;
            color: white;
            padding: 40px;
        }

        input,
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            background: #111a2e;
            border: 1px solid #1f2a44;
            color: white;
            border-radius: 6px;
        }

        textarea {
            height: 300px;
            font-family: monospace;
        }

        button {
            padding: 10px 15px;
            background: #60a5fa;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>

</head>

<body>

    <h1>✏️ Edit Post</h1>

    <form method="POST">

        <label>Title</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>">

        <label>SEO Title</label>
        <input type="text" name="seo_title" value="<?php echo htmlspecialchars($post['seo_title']); ?>">

        <label>SEO Description</label>
        <input type="text" name="seo_description" value="<?php echo htmlspecialchars($post['seo_description']); ?>">

        <label>Tags</label>
        <input type="text" name="tags" value="<?php echo htmlspecialchars($post['tags']); ?>">

        <label>Content (HTML)</label>
        <textarea name="content"><?php echo htmlspecialchars($post['content']); ?></textarea>

        <button type="submit">💾 Save Changes</button>

    </form>

    <br>
    <a href="admin.php">← Back to admin</a>

</body>

</html>
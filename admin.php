<?php
include 'auth.php';
include 'db.php';

/* -------------------------
   ACTIONS
--------------------------*/
if (isset($_GET['approve'])) {
    $conn->prepare("UPDATE posts SET status='published' WHERE id=?")
        ->execute([$_GET['approve']]);
    header("Location: admin.php");
    exit;
}

if (isset($_GET['unpublish'])) {
    $conn->prepare("UPDATE posts SET status='draft' WHERE id=?")
        ->execute([$_GET['unpublish']]);
    header("Location: admin.php");
    exit;
}

if (isset($_GET['delete'])) {
    $conn->prepare("DELETE FROM posts WHERE id=?")
        ->execute([$_GET['delete']]);
    header("Location: admin.php");
    exit;
}

/* -------------------------
   PAGINATION
--------------------------*/
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 4;
$offset = ($page - 1) * $limit;

/* -------------------------
   FETCH POSTS
   unpublished FIRST
--------------------------*/
$sql = "
    SELECT *
    FROM posts
    ORDER BY
        CASE WHEN status = 'published' THEN 1 ELSE 0 END ASC,
        created_at DESC
    LIMIT $limit OFFSET $offset
";

$posts = $conn->query($sql)->fetchAll();

/* -------------------------
   COUNT TOTAL
--------------------------*/
$total = $conn->query("SELECT COUNT(*) as c FROM posts")->fetch();
$totalPages = ceil(($total['c'] ?? 0) / $limit);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin</title>

    <style>
        body {
            font-family: system-ui;
            background: #0b1220;
            color: white;
            padding: 40px;
        }

        h1 {
            margin-bottom: 20px;
        }

        .post {
            background: #111a2e;
            padding: 16px;
            margin-bottom: 12px;
            border-radius: 12px;
            border: 1px solid #1f2a44;
        }

        .meta {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 6px;
        }

        .status {
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 999px;
            background: #1f2a44;
            display: inline-block;
            margin-left: 6px;
            color: #cbd5e1;
        }

        /* -------------------------
           BUTTONS
        --------------------------*/
        .actions {
            margin-top: 12px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid transparent;
            transition: 0.2s;
        }

        .btn:hover {
            transform: translateY(-1px);
            opacity: 0.9;
        }

        .btn-preview {
            border-color: #334155;
            color: #60a5fa;
        }

        .btn-edit {
            background: #2563eb;
            color: white;
        }

        .btn-publish {
            background: #16a34a;
            color: white;
        }

        .btn-delete {
            background: #dc2626;
            color: white;
        }

        /* -------------------------
           PAGINATION UI
        --------------------------*/
        .pagination {
            margin-top: 25px;
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border-radius: 8px;
            background: #111a2e;
            border: 1px solid #1f2a44;
            color: #60a5fa;
            text-decoration: none;
            font-size: 14px;
        }

        .pagination a:hover {
            background: #1a2542;
        }

        .pagination .active {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
        }

        .btn-unpublish {
            background: #64748b;
            color: white;
        }
    </style>
</head>

<body>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>Admin Dashboard</h1>
        <a href="logout.php" class="btn" style="background: #334155; color: #f1f5f9; border: 1px solid #475569;">
            Log Out
        </a>
    </div>

    <?php foreach ($posts as $p): ?>
        <div class="post">

            <strong><?php echo htmlspecialchars($p['title']); ?></strong>

            <div class="meta">
                Status:
                <span class="status"><?php echo $p['status']; ?></span>
                • Views: <?php echo $p['views']; ?>
                • Created: <?php echo $p['created_at']; ?>
            </div>

            <div class="actions">

                <a class="btn btn-preview" href="post.php?slug=<?php echo $p['slug']; ?>" target="_blank">
                    Preview
                </a>

                <a class="btn btn-edit" href="edit.php?id=<?php echo $p['id']; ?>">
                    Edit
                </a>

                <?php if ($p['status'] !== 'published'): ?>
                    <a class="btn btn-publish" href="?approve=<?php echo $p['id']; ?>">
                        Publish
                    </a>
                <?php else: ?>
                    <a class="btn btn-unpublish" href="?unpublish=<?php echo $p['id']; ?>"
                        style="background: #64748b; color: white;">
                        Unpublish
                    </a>
                <?php endif; ?>

                <a class="btn btn-delete" href="?delete=<?php echo $p['id']; ?>"
                    onclick="return confirm('Delete this post?')">
                    Delete
                </a>

            </div>

        </div>
    <?php endforeach; ?>

    <!-- PAGINATION -->
    <div class="pagination">

        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>">← Prev</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i == $page): ?>
                <span class="active"><?php echo $i; ?></span>
            <?php else: ?>
                <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>">Next →</a>
        <?php endif; ?>

    </div>

</body>

</html>
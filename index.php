<?php
include 'db.php';

$search = $_GET['search'] ?? '';
$page = max(1, (int) ($_GET['page'] ?? 1));

$limit = 4;
$offset = ($page - 1) * $limit;

/* -------------------------
   BASE QUERY PARTS
--------------------------*/
$where = "WHERE status = 'published'";
$params = [];

if ($search) {
    $where .= " AND (
        title LIKE :s1 
        OR seo_title LIKE :s2 
        OR tags LIKE :s3
    )";

    $searchTerm = "%$search%";
    $params[':s1'] = $searchTerm;
    $params[':s2'] = $searchTerm;
    $params[':s3'] = $searchTerm;
}

/* -------------------------
   FETCH POSTS
--------------------------*/
$sql = "
    SELECT * FROM posts 
    $where 
    ORDER BY created_at DESC 
    LIMIT $limit OFFSET $offset
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

/* -------------------------
   COUNT
--------------------------*/
$countSql = "SELECT COUNT(*) as total FROM posts $where";
$countStmt = $conn->prepare($countSql);
$countStmt->execute($params);
$total = $countStmt->fetch();

$totalCount = $total['total'] ?? 0;
$totalPages = ceil($totalCount / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Neural Crypt</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial;
            background: #0b1220;
            color: #e5e7eb;
            margin: 0;
            line-height: 1.5;
        }

        .container {
            max-width: 900px;
            margin: auto;
            padding: 24px 16px;
        }

        h1 {
            font-size: 32px;
            margin-bottom: 20px;
        }

        h1 a {
            color: inherit;
            text-decoration: none;
        }

        /* SEARCH */
        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-box input {
            flex: 1;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #1f2a44;
            background: #111a2e;
            color: white;
            font-size: 15px;
            outline: none;
        }

        .search-box button {
            padding: 12px 16px;
            border-radius: 10px;
            border: none;
            background: #60a5fa;
            color: #0b1220;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }

        /* POST CARD */
        .post {
            background: #111a2e;
            border: 1px solid #1f2a44;
            padding: 18px;
            margin-bottom: 14px;
            border-radius: 14px;
            transition: 0.2s;
        }

        .post:hover {
            transform: translateY(-2px);
            border-color: #2b3a5c;
        }

        .post a {
            text-decoration: none;
            color: #60a5fa;
            font-size: 20px;
            font-weight: 600;
            display: block;
        }

        .post a:hover {
            color: #93c5fd;
        }

        .meta {
            margin-top: 6px;
            font-size: 13px;
            color: #64748b;
        }

        .tags {
            margin-top: 8px;
            font-size: 13px;
            color: #94a3b8;
        }

        /* PAGINATION */
        .pagination {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .pagination a,
        .pagination span {
            color: #60a5fa;
            text-decoration: none;
            font-size: 14px;
        }

        .pagination a:hover {
            text-decoration: underline;
        }

        /* -------------------------
           📱 MOBILE RESPONSIVE
        --------------------------*/
        @media (max-width: 600px) {

            h1 {
                font-size: 24px;
            }

            .search-box {
                flex-direction: column;
            }

            .search-box button {
                width: 100%;
            }

            .post {
                padding: 14px;
            }

            .post a {
                font-size: 18px;
            }

            .meta,
            .tags {
                font-size: 12px;
            }

            .pagination {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <div class="container">

        <h1>
            <a href="index.php">The Neural Crypt</a>
        </h1>

        <form method="GET" class="search-box">
            <input type="text" name="search" placeholder="Search GPU, Unreal, Physics..."
                value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>

        <?php if ($search): ?>
            <div style="margin-bottom:12px; color:#94a3b8;">
                Results for: <strong><?php echo htmlspecialchars($search); ?></strong>
                (<a href="index.php" style="color:#60a5fa;">Clear</a>)
            </div>
        <?php endif; ?>

        <?php if (empty($posts)): ?>
            <p style="color:#94a3b8;">No posts found.</p>
        <?php endif; ?>

        <?php foreach ($posts as $p): ?>
            <div class="post">
                <a href="post.php?slug=<?php echo $p['slug']; ?>">
                    <?php echo htmlspecialchars($p['seo_title'] ?: $p['title']); ?>
                </a>

                <div class="meta">
                    👁 <?php echo $p['views']; ?> views •
                    📅 <?php echo $p['created_at']; ?>
                </div>

                <div class="tags">
                    <?php echo htmlspecialchars($p['tags']); ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>">
                    ← Previous
                </a>
            <?php else: ?>
                <span></span>
            <?php endif; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>">
                    Next →
                </a>
            <?php endif; ?>
        </div>

    </div>

</body>

</html>
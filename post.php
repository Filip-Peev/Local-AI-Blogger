<?php
include 'db.php';
$slug = $_GET['slug'] ?? '';
if (!$slug) die("Invalid post");

$stmt = $conn->prepare("SELECT * FROM posts WHERE slug = ? LIMIT 1");
$stmt->execute([$slug]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) die("Post not found");

$conn->prepare("UPDATE posts SET views = views + 1 WHERE id = ?")->execute([$post['id']]);

$title = $post['seo_title'] ?: $post['title'];
$description = $post['seo_description'] ?? '';
$tags = $post['tags'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@400;600&family=EB+Garamond:wght@400;600&family=Lora:wght@400;600&family=Merriweather:wght@400;700&family=Bitter:wght@400;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #0b1220;
            --card: #111a2e;
            --border: #1f2a44;
            --text-main: #e2e8f0;
            --text-muted: #94a3b8;
            --accent: #60a5fa;
        }

        body {
            /* Default font */
            font-family: 'Crimson Pro', serif;
            background: var(--bg);
            color: var(--text-main);
            line-height: 1.8;
            margin: 0;
            padding: 0;
            transition: font-family 0.2s ease;
        }

        .container {
            max-width: 720px;
            margin: auto;
            padding: 40px 20px;
        }

        /* FONT SWITCHER STICKY BAR */
        .reader-controls {
            position: sticky;
            top: 0;
            background: rgba(11, 18, 32, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            padding: 10px 0;
            z-index: 100;
            margin-bottom: 20px;
        }

        .controls-flex {
            max-width: 720px;
            margin: auto;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 0 20px;
        }

        .font-dropdown {
            background: var(--card);
            color: var(--text-main);
            border: 1px solid var(--border);
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
        }

        /* TYPOGRAPHY */
        .content {
            font-size: 21px;
        }

        .content p {
            text-indent: 2em;
            margin-bottom: 1.5em;
            text-align: justify;
        }

        .content p:first-of-type {
            text-indent: 0;
        }

        /* Headers & Meta */
        h1 {
            font-family: system-ui, sans-serif;
            font-size: 42px;
            color: #fff;
            margin-bottom: 10px;
        }

        .meta {
            font-family: system-ui;
            color: var(--text-muted);
            margin-bottom: 40px;
            font-size: 14px;
        }

        .back-link {
            color: var(--accent);
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="reader-controls">
        <div class="controls-flex">
            <span style="font-size: 12px; color: var(--text-muted); margin-right: 10px; font-family: system-ui;">READER FONT:</span>
            <select id="fontSwitcher" class="font-dropdown" onchange="changeFont(this.value)">
                <option value="'Crimson Pro', serif">Crimson Pro (Classic Book)</option>
                <option value="'EB Garamond', serif">EB Garamond (Elegant/Gothic)</option>
                <option value="'Lora', serif">Lora (Modern Serif)</option>
                <option value="'Merriweather', serif">Merriweather (High Legibility)</option>
                <option value="'Bitter', serif">Bitter (Bold & Sturdy)</option>
            </select>
        </div>
    </div>

    <div class="container">
        <a href="index.php" class="back-link">← LIBRARY</a>

        <article>
            <h1><?php echo htmlspecialchars($title); ?></h1>
            <div class="meta">
                📅 <?php echo date("F j, Y", strtotime($post['created_at'])); ?> • 👁 <?php echo $post['views']; ?> views
            </div>

            <div class="content" id="storyContent">
                <?php echo $post['content']; ?>
            </div>
        </article>
    </div>

    <script>
        function changeFont(fontValue) {
            // Apply font to body
            document.body.style.fontFamily = fontValue;
            // Save to browser memory
            localStorage.setItem('userFont', fontValue);
        }

        // On Load: Check if user has a saved preference
        window.onload = function() {
            const savedFont = localStorage.getItem('userFont');
            if (savedFont) {
                document.body.style.fontFamily = savedFont;
                document.getElementById('fontSwitcher').value = savedFont;
            }
        };
    </script>
</body>

</html>
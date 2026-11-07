<?php
session_start();

if (!defined('BASE_URL')) {
    define('BASE_URL', '/newsdesk1.0');
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/admin.php';
require_once __DIR__ . '/../includes/upload.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit();
}

header('Cache-Control: no-store');

if (empty($_SESSION['articles_csrf'])) {
    $_SESSION['articles_csrf'] = bin2hex(random_bytes(32));
}

function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = (string)($_POST['csrf'] ?? '');
        $articleId = (int)($_POST['article_id'] ?? 0);
        $action = (string)($_POST['action'] ?? '');

        if (!hash_equals($_SESSION['articles_csrf'], $token)) {
            $_SESSION['articles_message'] = ['error', 'The form expired. Refresh the page and try again.'];
        } elseif ($articleId < 1 || !in_array($action, ['approve', 'reject', 'delete'], true)) {
            $_SESSION['articles_message'] = ['error', 'That action could not be completed.'];
        } elseif ($action === 'delete') {
            try {
                $stmt = $conn->prepare('SELECT image FROM articles WHERE id = ?');
                $stmt->bind_param('i', $articleId);
                $stmt->execute();
                $imageName = $stmt->get_result()->fetch_assoc()['image'] ?? null;

                $conn->begin_transaction();

                $stmt = $conn->prepare('DELETE FROM comments WHERE article_id = ?');
                $stmt->bind_param('i', $articleId);
                $stmt->execute();

                $stmt = $conn->prepare('DELETE FROM bookmarks WHERE article_id = ?');
                $stmt->bind_param('i', $articleId);
                $stmt->execute();

                $stmt = $conn->prepare('DELETE FROM articles WHERE id = ?');
                $stmt->bind_param('i', $articleId);
                $stmt->execute();

                if ($stmt->affected_rows === 0) {
                    throw new RuntimeException('Article not found.');
                }

                $conn->commit();
                removeImage($imageName);
                $_SESSION['articles_message'] = ['success', 'Article deleted.'];
            } catch (Throwable $error) {
                $conn->rollback();
                $_SESSION['articles_message'] = ['error', 'The article could not be deleted.'];
            }
        } else {
            $newStatus = $action === 'approve' ? 'approved' : 'rejected';

            try {
                $stmt = $conn->prepare(
                    "UPDATE articles
                 SET status = ?,
                     published_at = CASE
                         WHEN ? = 'approved' THEN COALESCE(published_at, CURRENT_TIMESTAMP)
                         ELSE published_at
                     END
                 WHERE id = ? AND status = 'pending'"
                );
                $stmt->bind_param('ssi', $newStatus, $newStatus, $articleId);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $_SESSION['articles_message'] = [
                        'success',
                        $newStatus === 'approved' ? 'Article approved and published.' : 'Article rejected.'
                    ];
                } else {
                    $_SESSION['articles_message'] = ['error', 'That article is no longer pending.'];
                }
            } catch (mysqli_sql_exception $error) {
                $_SESSION['articles_message'] = ['error', 'The status could not be saved.'];
            }
        }

        header('Location: ' . BASE_URL . '/admin/admin_articles.php');
        exit();
    }
}

$message = $_SESSION['articles_message'] ?? null;
unset($_SESSION['articles_message']);

$articles = [];
$loadError = '';
try {
    $result = $conn->query(
        "SELECT a.id, a.title, a.status, a.created_at, a.published_at,
                c.name AS category, u.name AS author
         FROM articles a
         LEFT JOIN categories c ON c.id = a.category_id
         LEFT JOIN users u ON u.id = a.author_id
         ORDER BY
             CASE a.status
                 WHEN 'pending' THEN 0
                 WHEN 'draft' THEN 1
                 WHEN 'approved' THEN 2
                 WHEN 'rejected' THEN 3
                 ELSE 4
             END,
             a.created_at DESC"
    );
    $articles = $result->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $error) {
    $loadError = 'Articles could not be loaded. Check the database connection and article table.';
}

adminHeader('Articles', 'articles');
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/admin/admin.css">
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage articles - Green Paper</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #202820;
            --muted: #687168;
            --line: #d8ded7;
            --green: #1f6b3e;
            --paper: #f5f6f2;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--paper);
            color: var(--ink);
            font: 15px/1.5 Arial, sans-serif;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 16px max(20px, calc((100% - 1100px)/2));
            background: #184c31;
            color: #fff;
        }

        header a {
            color: #fff;
            text-decoration: none;
        }

        header strong {
            font: 600 22px Georgia, serif;
        }

        main {
            max-width: 1100px;
            margin: 36px auto;
            padding: 0 20px;
        }

        h1 {
            margin: 0 0 6px;
            font: 400 34px Georgia, serif;
        }

        .intro {
            margin: 0 0 22px;
            color: var(--muted);
        }

        .notice {
            margin: 0 0 18px;
            padding: 12px 15px;
            border: 1px solid var(--line);
            background: #fff;
        }

        .notice.success {
            border-left: 4px solid var(--green);
        }

        .notice.error {
            border-left: 4px solid #b3261e;
        }

        .table-wrap {
            overflow-x: auto;
            background: #fff;
            border: 1px solid var(--line);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }

        th,
        td {
            padding: 13px 15px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: middle;
        }

        th {
            background: #eef2ed;
            font-size: 12px;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        tr:last-child td {
            border-bottom: 0;
        }

        .title {
            font-weight: 700;
        }

        .sub {
            color: var(--muted);
            font-size: 13px;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 10px;
            border-radius: 20px;
            background: #ecefea;
            color: #4a4a44;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: currentColor;
        }

        .status.draft {
            background: #e8e8e3;
            color: #4a4a44;
        }

        .status.pending {
            background: #fff1cb;
            color: #765300;
        }

        .status.approved {
            background: #dff1e3;
            color: #155c2e;
        }

        .status.rejected {
            background: #f8e2df;
            color: #8a241c;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        button {
            padding: 8px 11px;
            border: 1px solid var(--green);
            background: #fff;
            color: var(--green);
            font: inherit;
            font-size: 13px;
            cursor: pointer;
        }

        button:hover {
            background: var(--green);
            color: #fff;
        }

        button.reject {
            border-color: #a32a2a;
            color: #a32a2a;
        }

        button.reject:hover {
            background: #a32a2a;
            color: #fff;
        }

        .empty {
            padding: 24px;
            color: var(--muted);
        }

        @media (max-width:700px) {
            main {
                margin: 24px auto;
            }

            h1 {
                font-size: 28px;
            }

            header {
                padding: 14px 20px;
            }
        }
    </style>
</head>

<body>
    <main>
        <h1>Manage articles</h1>
        <p class="intro">Review pending submissions and see the status of every article.</p>

        <?php if ($message): ?>
            <div class="notice <?= h($message[0]) ?>"><?= h($message[1]) ?></div>
        <?php endif; ?>
        <?php if ($loadError): ?>
            <div class="notice error"><?= h($loadError) ?></div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $article): ?>
                            <tr>
                                <td class="title"><a href="<?= h(BASE_URL) ?>/article.php?id=<?= (int)$article['id'] ?>"><?= h($article['title']) ?></a></td>
                                <td><?= h($article['author'] ?? 'Unknown') ?></td>
                                <td><?= h($article['category'] ?? 'Uncategorized') ?></td>
                                <td><span class="status <?= h($article['status']) ?>"><?= h($article['status'] === 'approved' ? 'published' : $article['status']) ?></span></td>
                                <td class="sub"><?= h($article['created_at'] ?? '') ?></td>
                                <td>
                                    <div class="actions">
                                        <?php if ($article['status'] === 'pending'): ?>
                                            <form method="post">
                                                <input type="hidden" name="csrf"
                                                    value="<?= h($_SESSION['articles_csrf']) ?>">
                                                <input type="hidden" name="article_id"
                                                    value="<?= (int)$article['id'] ?>">
                                                <button name="action" value="approve" type="submit">
                                                    Approve
                                                </button>
                                            </form>

                                            <form method="post">
                                                <input type="hidden" name="csrf"
                                                    value="<?= h($_SESSION['articles_csrf']) ?>">
                                                <input type="hidden" name="article_id"
                                                    value="<?= (int)$article['id'] ?>">
                                                <button class="reject" name="action" value="reject" type="submit">
                                                    Reject
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <form method="post"
                                            onsubmit="return confirm('Permanently delete this article and its comments?')">
                                            <input type="hidden" name="csrf"
                                                value="<?= h($_SESSION['articles_csrf']) ?>">
                                            <input type="hidden" name="article_id"
                                                value="<?= (int)$article['id'] ?>">
                                            <button class="reject" name="action" value="delete" type="submit">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$articles): ?>
                            <tr>
                                <td class="empty" colspan="6">No articles have been submitted yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</body>

</html>
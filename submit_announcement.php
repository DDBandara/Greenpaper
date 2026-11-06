<?php
session_start();

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/admin.php';
adminHeader('Submit an Announcement', 'announcements');
require_once __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit();
}

if (empty($_SESSION['announcement_csrf'])) {
    $_SESSION['announcement_csrf'] = bin2hex(random_bytes(32));
}

$notice = $_SESSION['announcement_notice'] ?? '';
unset($_SESSION['announcement_notice']);

$announcements = [];

$stmt = mysqli_prepare(
    $conn,
    'SELECT id, title, body, is_active, created_at
     FROM announcements
     ORDER BY created_at DESC'
);

if (!$stmt) {
    die('Could not prepare the announcements query.');
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $announcements[] = $row;
}

mysqli_stmt_close($stmt);
?>


    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Submit an Announcement</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= safe(BASE_URL) ?>/admin/admin.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            green: '#1b6b3e',
                            greenHover: '#155431',
                            border: '#e2dfd5',
                            label: '#5a5850'
                        }
                    },
                    fontFamily: {
                        serif: ['"Playfair Display"', 'Georgia', 'serif'],
                        sans: ['Inter', 'sans-serif']
                    }
                }
            }
        };
    </script>

    <style>
        body {
            background: #f6f5f0;
            color: #222220;
            font-family: Inter, sans-serif;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: #1b6b3e;
            box-shadow: 0 0 0 1px #1b6b3e;
        }
    </style>

    <link rel="stylesheet" href="<?= safe(BASE_URL) ?>/admin/admin.css">
    <div class="max-w-6xl mx-auto">
        <div class="mb-8">
            <a href="<?= safe(BASE_URL) ?>/index.php"
               class="font-serif text-2xl text-gray-900">
                Green Paper
            </a>
        </div>

        <?php if ($notice !== ''): ?>
            <div class="mb-6 border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                <?= safe($notice) ?>
            </div>
        <?php endif; ?>

        <header class="mb-8 md:mb-10">
            <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-brand-green">
                Write for NSBM NewsDesk
            </p>
            <h1 class="mb-3 font-serif text-4xl font-normal tracking-tight text-gray-900 md:text-5xl lg:text-6xl">
                Submit an announcement
            </h1>
            <p class="text-base text-gray-600">
                An admin reviews every submission before it is published.
            </p>
        </header>

        <form method="post"
              action="<?= safe(BASE_URL) ?>/announcement_submit.php"
              class="grid grid-cols-1 gap-8 lg:grid-cols-12 lg:gap-12">

            <input type="hidden"
                   name="csrf"
                   value="<?= safe($_SESSION['announcement_csrf']) ?>">

            <div class="flex flex-col gap-6 lg:col-span-8">
                <div>
                    <label for="title"
                           class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-brand-label">
                        Title
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        maxlength="200"
                        required
                        placeholder="Announcement title"
                        class="w-full border border-brand-border bg-[#fcfcf9] px-4 py-3 text-base text-gray-800 placeholder-gray-400">
                </div>

                <div>
                    <label for="body"
                           class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-brand-label">
                        Description
                    </label>
                    <textarea
                        id="body"
                        name="body"
                        rows="14"
                        maxlength="10000"
                        required
                        placeholder="Write your announcement details here..."
                        class="min-h-[320px] w-full resize-y border border-brand-border bg-[#fcfcf9] px-4 py-3 text-base text-gray-800 placeholder-gray-400"></textarea>
                </div>
            </div>

            <div class="flex flex-col gap-6 lg:col-span-4">
                <button
                    type="submit"
                    class="w-full bg-brand-green px-4 py-3.5 text-center text-sm font-bold text-white transition-colors hover:bg-brand-greenHover">
                    Submit announcement
                </button>

                <div class="border border-brand-border bg-[#fbfbfa]">
                    <div class="border-b border-brand-border p-5">
                        <h2 class="font-serif text-xl font-normal text-gray-900">
                            How it works
                        </h2>
                    </div>
                    <ol class="divide-y divide-brand-border">
                        <li class="flex items-start gap-4 p-4">
                            <span class="pt-0.5 font-serif text-2xl leading-none text-brand-green">1</span>
                            <p class="pt-1 text-xs leading-relaxed text-gray-700">You submit the announcement.</p>
                        </li>
                        <li class="flex items-start gap-4 p-4">
                            <span class="pt-0.5 font-serif text-2xl leading-none text-brand-green">2</span>
                            <p class="pt-1 text-xs leading-relaxed text-gray-700">An admin reviews it.</p>
                        </li>
                        <li class="flex items-start gap-4 p-4">
                            <span class="pt-0.5 font-serif text-2xl leading-none text-brand-green">3</span>
                            <p class="pt-1 text-xs leading-relaxed text-gray-700">Approved announcements appear on the site.</p>
                        </li>
                    </ol>
                </div>
            </div>
        </form>
    </div>

    <section class="mt-12">
    <h2 class="mb-5 font-serif text-2xl text-gray-900">
        Announcements
    </h2>

    <?php if (empty($announcements)): ?>
        <p class="text-gray-600">There are no announcements yet.</p>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($announcements as $announcement): ?>
                <article class="border border-brand-border bg-[#fbfbfa] p-5">
                    <h3 class="mb-2 font-serif text-xl text-gray-900">
                        <?= safe($announcement['title']) ?>
                    </h3>

                    <p class="whitespace-pre-line text-gray-700">
                        <?= safe($announcement['body']) ?>
                    </p>

                    <time class="mt-3 block text-sm text-gray-500">
                        <?= safe(date('d M Y', strtotime($announcement['created_at']))) ?>
                    </time>

                    <p class="mt-2 text-sm">
                        Status:
                        <strong>
                            <?= (int)$announcement['is_active'] === 1 ? 'Published' : 'Pending' ?>
                        </strong>
                    </p>

                    <div class="actions mt-4">
                        <?php if ((int)$announcement['is_active'] === 0): ?>
                            <form method="post"
                                  action="<?= safe(BASE_URL) ?>/admin/delete_announcement.php">
                                <input type="hidden" name="csrf"
                                       value="<?= safe($_SESSION['csrf']) ?>">
                                <input type="hidden" name="id"
                                       value="<?= (int)$announcement['id'] ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit">Approve</button>
                            </form>
                        <?php endif; ?>

                        <form method="post"
                              action="<?= safe(BASE_URL) ?>/admin/delete_announcement.php"
                              onsubmit="return confirm('Delete this announcement?');">
                            <input type="hidden" name="csrf"
                                   value="<?= safe($_SESSION['csrf']) ?>">
                            <input type="hidden" name="id"
                                   value="<?= (int)$announcement['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button class="reject" type="submit">Delete</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php
require_once __DIR__ . '/../includes/admin.php';

$me = (int)$_SESSION['user_id'];
$q = trim($_GET['q'] ?? '');
$roleFilter = $_GET['role'] ?? '';
if (!in_array($roleFilter, ['admin', 'user'], true)) {
    $roleFilter = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $q = trim($_POST['q'] ?? '');
    $roleFilter = in_array($_POST['filter'] ?? '', ['admin', 'user'], true) ? $_POST['filter'] : '';

    if (!csrfOk()) {
        flash('The form expired. Please try again.');
    } elseif ($target === $me) {
        flash('You cannot change your own role.');
    } elseif (in_array($action, ['promote', 'demote'], true)) {
        $newRole = $action === 'promote' ? 'admin' : 'user';
        $stmt = $conn->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stmt->bind_param('si', $newRole, $target);
        $stmt->execute();
        flash($action === 'promote' ? 'The user is now an admin.' : 'The admin role was removed.');
    }
    header('Location: users.php?q=' . urlencode($q) . '&role=' . urlencode($roleFilter));
    exit();
}

$sql = 'SELECT id, name, email, role, created_at FROM users WHERE 1 = 1';
$types = '';
$params = [];
if ($q !== '') {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $sql .= ' AND (name LIKE ? OR email LIKE ?)';
    $types .= 'ss';
    $params[] = $like;
    $params[] = $like;
}
if ($roleFilter !== '') {
    $sql .= ' AND role = ?';
    $types .= 's';
    $params[] = $roleFilter;
}
$sql .= " ORDER BY (role = 'admin') DESC, created_at ASC LIMIT 200";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$counts = getRows($conn, "SELECT COUNT(*) AS total, SUM(role = 'admin') AS admins FROM users")[0] ?? ['total' => 0, 'admins' => 0];
$total = (int)$counts['total'];
$admins = (int)$counts['admins'];

adminHeader('Users', 'users');
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/admin/admin.css">
<div class="page-top">
    <span class="page-label">Users &bull; <?= $total ?></span>
    <span class="page-note"><?= plural($admins, 'admin') ?> &bull; <?= plural($total - $admins, 'user') ?></span>
</div>

<form class="filters" method="get" action="users.php">
    <input type="text" name="q" value="<?= safe($q) ?>" placeholder="Search by name or email">
    <select name="role" onchange="this.form.submit()">
        <option value="">All roles</option>
        <option value="admin"<?= $roleFilter === 'admin' ? ' selected' : '' ?>>Admin</option>
        <option value="user"<?= $roleFilter === 'user' ? ' selected' : '' ?>>User</option>
    </select>
</form>

<div class="table-box">
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Joined</th>
                <th class="right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td class="user-name"><?= safe($user['name']) ?></td>
                    <td class="grey"><?= safe($user['email']) ?></td>
                    <td class="<?= $user['role'] === 'admin' ? 'role-admin' : 'role-user' ?>"><?= strtoupper(safe($user['role'])) ?></td>
                    <td class="grey"><?= showDate($user['created_at']) ?></td>
                    <td class="right">
                        <?php if ((int)$user['id'] === $me): ?>
                            <span class="grey">you</span>
                        <?php else: ?>
                            <form method="post" action="users.php">
                                <input type="hidden" name="csrf" value="<?= safe($_SESSION['csrf']) ?>">
                                <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                                <input type="hidden" name="q" value="<?= safe($q) ?>">
                                <input type="hidden" name="filter" value="<?= safe($roleFilter) ?>">
                                <?php if ($user['role'] === 'admin'): ?>
                                    <button type="submit" name="action" value="demote" class="link-btn">Remove admin</button>
                                <?php else: ?>
                                    <button type="submit" name="action" value="promote" class="link-btn">Make admin</button>
                                <?php endif; ?>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$users): ?>
                <tr><td colspan="5" class="grey">No users match your search.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="table-foot">Showing <?= count($users) ?> of <?= $total ?> users</div>

<?php adminFooter(); ?>
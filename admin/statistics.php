<?php
require_once 'auth.php';
require_once '../config/database.php';

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = ($_GET['saved'] ?? '') === '1';

$sectionStmt = $pdo->query('SELECT * FROM statistics_section LIMIT 1');
$section = $sectionStmt->fetch(PDO::FETCH_ASSOC);
if (!$section) {
    exit('Statistics section was not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Invalid form request.');
    }

    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'update_section') {
            $smallHeading = trim($_POST['small_heading'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $buttonText = trim($_POST['button_text'] ?? '');

            if ($smallHeading === '' || $title === '' || $description === '' || $buttonText === '') {
                throw new RuntimeException('Complete all section fields.');
            }
            if (strlen($smallHeading) > 255 || strlen($title) > 255 || strlen($buttonText) > 100) {
                throw new RuntimeException('One or more fields are too long.');
            }

            $stmt = $pdo->prepare('UPDATE statistics_section SET small_heading = ?, title = ?, description = ?, button_text = ? WHERE id = ?');
            $stmt->execute([$smallHeading, $title, $description, $buttonText, $section['id']]);
        } elseif ($action === 'add_counter' || $action === 'edit_counter') {
            $icon = trim($_POST['icon'] ?? '');
            $number = trim($_POST['counter_number'] ?? '');
            $suffix = trim($_POST['counter_suffix'] ?? '');
            $label = trim($_POST['counter_label'] ?? '');
            $order = filter_var($_POST['display_order'] ?? null, FILTER_VALIDATE_INT);

            if ($icon === '' || $number === '' || $label === '' || $order === false || $order < 1) {
                throw new RuntimeException('Complete all required counter fields.');
            }
            if (!ctype_digit($number) || strlen($number) > 15 || strlen($suffix) > 10 ||
                strlen($icon) > 50 || strlen($label) > 100) {
                throw new RuntimeException('Invalid counter value. Use a whole number and a separate suffix.');
            }
            if (!preg_match('/^[a-z0-9-]+$/', $icon)) {
                throw new RuntimeException('Use a valid Lucide icon name, such as building-2.');
            }

            if ($action === 'add_counter') {
                $stmt = $pdo->prepare('INSERT INTO statistics (icon, counter_number, counter_suffix, counter_label, display_order) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$icon, $number, $suffix, $label, $order]);
            } else {
                $id = filter_var($_POST['counter_id'] ?? null, FILTER_VALIDATE_INT);
                if (!$id || $id < 1) {
                    throw new RuntimeException('Invalid counter selected.');
                }
                $stmt = $pdo->prepare('UPDATE statistics SET icon = ?, counter_number = ?, counter_suffix = ?, counter_label = ?, display_order = ? WHERE id = ?');
                $stmt->execute([$icon, $number, $suffix, $label, $order, $id]);
            }
        } elseif ($action === 'delete_counter') {
            $id = filter_var($_POST['counter_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id || $id < 1) {
                throw new RuntimeException('Invalid counter selected.');
            }
            $stmt = $pdo->prepare('DELETE FROM statistics WHERE id = ?');
            $stmt->execute([$id]);
        } else {
            throw new RuntimeException('Unknown form action.');
        }

        header('Location: statistics.php?saved=1');
        exit;
    } catch (RuntimeException $ex) {
        $error = $ex->getMessage();
    } catch (PDOException $ex) {
        error_log($ex->getMessage());
        $error = 'Could not save changes. Please check your database and try again.';
    }
}

$sectionStmt = $pdo->query('SELECT * FROM statistics_section LIMIT 1');
$section = $sectionStmt->fetch(PDO::FETCH_ASSOC);
$counterStmt = $pdo->query('SELECT * FROM statistics ORDER BY display_order, id');
$counters = $counterStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics Management - Constructify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <h2>Constructify</h2>
            <span>Admin Panel</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="sidebar-link">Dashboard</a>
            <a href="settings.php" class="sidebar-link">Website Settings</a>
            <a href="home.php" class="sidebar-link">Home</a>
            <a href="about.php" class="sidebar-link">About</a>
            <a href="services.php" class="sidebar-link">Services</a>
            <a href="statistics.php" class="sidebar-link active">Statistics</a>
            <a href="projects.php" class="sidebar-link">Projects</a>
        </nav>
        <div class="sidebar-bottom">
            <a href="../index.php" target="_blank" rel="noopener" class="sidebar-link">View Website</a>
            <a href="logout.php" class="sidebar-link logout-link">Logout</a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-header">
            <div class="header-left">
                <button type="button" class="menu-toggle" id="menuToggle" aria-label="Toggle sidebar" aria-controls="adminSidebar" aria-expanded="false">&#9776;</button>
                <h1>Statistics Management</h1>
            </div>
            <div class="admin-user"><?= e($_SESSION['admin_username']) ?></div>
        </header>
        <main class="admin-content">
            <div class="settings-container">
                <div class="welcome-section">
                    <h2>Edit Statistics Section</h2>
                    <p>Manage the section heading, description, button text and statistic cards.</p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success">Changes saved successfully.</div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" class="settings-form mb-4">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action" value="update_section">
                    <h3 class="mb-4">Statistics Content</h3>
                    <div class="mb-3">
                        <label for="small_heading" class="form-label">Small Heading</label>
                        <input class="form-control" type="text" id="small_heading" name="small_heading" maxlength="255" value="<?= e($section['small_heading']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label">Main Title</label>
                        <input class="form-control" type="text" id="title" name="title" maxlength="255" value="<?= e($section['title']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required><?= e($section['description']) ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="button_text" class="form-label">Button Text</label>
                        <input class="form-control" type="text" id="button_text" name="button_text" maxlength="100" value="<?= e($section['button_text']) ?>" required>
                    </div>
                    <button type="submit" class="save-settings-btn">Save Statistics Content</button>
                </form>

                <div class="settings-form mb-4">
                    <h3 class="mb-4">Statistics Cards</h3>
                    <?php if (!$counters): ?>
                        <p class="text-muted">No statistic cards yet. Add one below.</p>
                    <?php endif; ?>
                    <?php foreach ($counters as $index => $counter): ?>
                        <div class="counter-edit-card mb-4">
                            <h4>Counter <?= $index + 1 ?></h4>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="action" value="edit_counter">
                                <input type="hidden" name="counter_id" value="<?= (int) $counter['id'] ?>">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="icon_<?= (int) $counter['id'] ?>">Lucide Icon</label>
                                        <input type="text" class="form-control" id="icon_<?= (int) $counter['id'] ?>" name="icon" maxlength="50" value="<?= e($counter['icon']) ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="number_<?= (int) $counter['id'] ?>">Number</label>
                                        <input type="number" class="form-control" id="number_<?= (int) $counter['id'] ?>" name="counter_number" min="0" step="1" value="<?= e($counter['counter_number']) ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="suffix_<?= (int) $counter['id'] ?>">Suffix</label>
                                        <input type="text" class="form-control" id="suffix_<?= (int) $counter['id'] ?>" name="counter_suffix" maxlength="10" value="<?= e($counter['counter_suffix']) ?>">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label" for="label_<?= (int) $counter['id'] ?>">Label</label>
                                        <input type="text" class="form-control" id="label_<?= (int) $counter['id'] ?>" name="counter_label" maxlength="100" value="<?= e($counter['counter_label']) ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="order_<?= (int) $counter['id'] ?>">Display Order</label>
                                        <input type="number" class="form-control" id="order_<?= (int) $counter['id'] ?>" name="display_order" min="1" value="<?= (int) $counter['display_order'] ?>" required>
                                    </div>
                                </div>
                                <button type="submit" class="save-settings-btn mt-3">Save Counter</button>
                            </form>
                            <form method="POST" class="mt-2" onsubmit="return confirm('Delete this statistic card?');">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="action" value="delete_counter">
                                <input type="hidden" name="counter_id" value="<?= (int) $counter['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger">Delete Counter</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form method="POST" class="settings-form">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action" value="add_counter">
                    <h3 class="mb-4">Add Statistic Card</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="new_icon">Lucide Icon</label>
                            <input type="text" class="form-control" id="new_icon" name="icon" maxlength="50" placeholder="Example: hard-hat" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="new_number">Number</label>
                            <input type="number" class="form-control" id="new_number" name="counter_number" min="0" step="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="new_suffix">Suffix</label>
                            <input type="text" class="form-control" id="new_suffix" name="counter_suffix" maxlength="10" placeholder="+">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="new_label">Label</label>
                            <input type="text" class="form-control" id="new_label" name="counter_label" maxlength="100" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="new_order">Display Order</label>
                            <input type="number" class="form-control" id="new_order" name="display_order" min="1" value="<?= count($counters) + 1 ?>" required>
                        </div>
                    </div>
                    <button type="submit" class="save-settings-btn mt-4">Add Statistic Card</button>
                </form>
            </div>
        </main>
    </div>
</div>
<script>
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('adminSidebar');
    menuToggle.addEventListener('click', function () {
        sidebar.classList.toggle('open');
        menuToggle.setAttribute('aria-expanded', sidebar.classList.contains('open'));
    });
</script>
</body>
</html>

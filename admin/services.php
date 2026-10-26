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

$stmt = $pdo->query('SELECT * FROM services_section LIMIT 1');
$section = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$section) {
    exit('Services section was not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        exit('Invalid form request.');
    }

    $action = $_POST['action'] ?? '';
    $uploadedPath = null;

    try {
        if ($action === 'update_section') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $imageTitle = trim($_POST['image_title'] ?? '');
            $imageButtonText = trim($_POST['image_button_text'] ?? '');

            if ($title === '' || $description === '' || $imageTitle === '' || $imageButtonText === '') {
                throw new Exception('Please complete all text fields.');
            }
            if (strlen($title) > 255 || strlen($imageTitle) > 255 || strlen($imageButtonText) > 100) {
                throw new Exception('One of the text fields is too long.');
            }

            $imagePath = $section['main_image'];
            if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $file = $_FILES['main_image'];
                if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024) {
                    throw new Exception('Image upload failed or exceeded 5 MB.');
                }
                $info = @getimagesize($file['tmp_name']);
                $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                $mime = $info['mime'] ?? '';
                if (!isset($types[$mime]) || !in_array($info[2] ?? 0, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
                    throw new Exception('Only JPG, PNG and WebP images are allowed.');
                }
                $folder = __DIR__ . '/../uploads/services/';
                if (!is_dir($folder) && !mkdir($folder, 0755, true)) {
                    throw new Exception('Could not create the image folder.');
                }
                $filename = bin2hex(random_bytes(16)) . '.' . $types[$mime];
                $destination = $folder . $filename;
                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    throw new Exception('Could not save the uploaded image.');
                }
                $uploadedPath = $destination;
                $imagePath = 'uploads/services/' . $filename;
            }

            $stmt = $pdo->prepare(
                'UPDATE services_section SET title = ?, description = ?, main_image = ?, image_title = ?, image_button_text = ? WHERE id = ?'
            );
            $stmt->execute([$title, $description, $imagePath, $imageTitle, $imageButtonText, $section['id']]);
        } elseif ($action === 'add_service' || $action === 'edit_service') {
            $icon = trim($_POST['icon'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $order = filter_var($_POST['display_order'] ?? null, FILTER_VALIDATE_INT);

            if ($icon === '' || $title === '' || $description === '' || $order === false || $order < 1) {
                throw new Exception('Please enter valid service details.');
            }
            if (strlen($icon) > 50 || strlen($title) > 150) {
                throw new Exception('Service icon or title is too long.');
            }

            if ($action === 'add_service') {
                $stmt = $pdo->prepare(
                    'INSERT INTO services (icon, title, description, display_order) VALUES (?, ?, ?, ?)'
                );
                $stmt->execute([$icon, $title, $description, $order]);
            } else {
                $id = filter_var($_POST['service_id'] ?? null, FILTER_VALIDATE_INT);
                if (!$id || $id < 1) {
                    throw new Exception('Invalid service ID.');
                }
                $stmt = $pdo->prepare(
                    'UPDATE services SET icon = ?, title = ?, description = ?, display_order = ? WHERE id = ?'
                );
                $stmt->execute([$icon, $title, $description, $order, $id]);
            }
        } elseif ($action === 'delete_service') {
            $id = filter_var($_POST['service_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id || $id < 1) {
                throw new Exception('Invalid service ID.');
            }
            $stmt = $pdo->prepare('DELETE FROM services WHERE id = ?');
            $stmt->execute([$id]);
        } else {
            throw new Exception('Unknown action.');
        }

        header('Location: services.php?saved=1');
        exit;
    } catch (Exception $ex) {
        if ($uploadedPath !== null && is_file($uploadedPath)) {
            unlink($uploadedPath);
        }
        if ($ex instanceof PDOException) {
            error_log($ex->getMessage());
            $error = 'Could not save your changes.';
        } else {
            $error = $ex->getMessage();
        }
    }
}

$stmt = $pdo->query('SELECT * FROM services_section LIMIT 1');
$section = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt = $pdo->query('SELECT * FROM services ORDER BY display_order, id');
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services Management - Constructify</title>
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
            <a href="services.php" class="sidebar-link active">Services</a>
            <a href="statistics.php" class="sidebar-link">Statistics</a>
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
                <button type="button" class="menu-toggle" id="menuToggle"
                        aria-label="Toggle sidebar" aria-controls="adminSidebar"
                        aria-expanded="false">&#9776;</button>
                <h1>Services Management</h1>
            </div>
            <div class="admin-user"><?= e($_SESSION['admin_username']) ?></div>
        </header>

        <main class="admin-content">
            <div class="settings-container">
                <div class="welcome-section">
                    <h2>Edit Services Section</h2>
                    <p>Manage the section heading, photograph and service cards.</p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success">Changes saved successfully.</div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <!-- Services section content -->
                <form method="POST" enctype="multipart/form-data" class="settings-form mb-4">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action" value="update_section">
                    <h3 class="mb-4">Services Content</h3>

                    <div class="mb-3">
                        <label for="section_title" class="form-label">Section Title</label>
                        <input type="text" id="section_title" name="title" class="form-control"
                               maxlength="255" value="<?= e($section['title']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="section_description" class="form-label">Section Description</label>
                        <textarea id="section_description" name="description" class="form-control"
                                  rows="4" required><?= e($section['description']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image_title" class="form-label">Text on Photograph</label>
                        <input type="text" id="image_title" name="image_title" class="form-control"
                               maxlength="255" value="<?= e($section['image_title']) ?>" required>
                    </div>
                    <div class="mb-4">
                        <label for="image_button_text" class="form-label">Photograph Button Text</label>
                        <input type="text" id="image_button_text" name="image_button_text"
                               class="form-control" maxlength="100"
                               value="<?= e($section['image_button_text']) ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Current Photograph</label>
                        <div class="current-logo">
                            <?php if (!empty($section['main_image'])): ?>
                                <img src="../<?= e($section['main_image']) ?>" alt="Current services photograph">
                            <?php else: ?>
                                <span class="text-muted">No image uploaded</span>
                            <?php endif; ?>
                        </div>
                        <label for="main_image" class="form-label mt-3">Replace Photograph</label>
                        <input type="file" id="main_image" name="main_image" class="form-control"
                               accept=".jpg,.jpeg,.png,.webp">
                        <small class="text-muted">JPG, PNG or WebP; maximum 5 MB. Leave empty to keep the current image.</small>
                    </div>
                    <button type="submit" class="save-settings-btn">Save Services Content</button>
                </form>

                <!-- Edit existing services -->
                <div class="settings-form mb-4">
                    <h3 class="mb-3">Existing Services</h3>
                    <?php if (empty($services)): ?>
                        <p class="text-muted">No services yet. Add one below.</p>
                    <?php endif; ?>

                    <?php foreach ($services as $service): ?>
                        <div class="counter-edit-card mb-4">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="action" value="edit_service">
                                <input type="hidden" name="service_id" value="<?= (int) $service['id'] ?>">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Lucide Icon</label>
                                        <input type="text" name="icon" class="form-control" maxlength="50"
                                               value="<?= e($service['icon']) ?>" required>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Title</label>
                                        <input type="text" name="title" class="form-control" maxlength="150"
                                               value="<?= e($service['title']) ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="3"
                                                  required><?= e($service['description']) ?></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Display Order</label>
                                        <input type="number" name="display_order" class="form-control" min="1"
                                               value="<?= (int) $service['display_order'] ?>" required>
                                    </div>
                                </div>
                                <button type="submit" class="save-settings-btn mt-3">Save Service</button>
                            </form>
                            <form method="POST" class="mt-2"
                                  onsubmit="return confirm('Delete this service?');">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="action" value="delete_service">
                                <input type="hidden" name="service_id" value="<?= (int) $service['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger">Delete Service</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Add a service -->
                <form method="POST" class="settings-form">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action" value="add_service">
                    <h3 class="mb-4">Add New Service</h3>
                    <div class="mb-3">
                        <label for="new_icon" class="form-label">Lucide Icon</label>
                        <input type="text" id="new_icon" name="icon" class="form-control"
                               maxlength="50" placeholder="Example: hard-hat" required>
                        <small class="text-muted">Use an icon name such as building-2, wrench or hard-hat.</small>
                    </div>
                    <div class="mb-3">
                        <label for="new_title" class="form-label">Title</label>
                        <input type="text" id="new_title" name="title" class="form-control"
                               maxlength="150" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_description" class="form-label">Description</label>
                        <textarea id="new_description" name="description" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="new_order" class="form-label">Display Order</label>
                        <input type="number" id="new_order" name="display_order" class="form-control"
                               min="1" value="<?= count($services) + 1 ?>" required>
                    </div>
                    <button type="submit" class="save-settings-btn">Add Service</button>
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

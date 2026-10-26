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

// Get About content
$stmt = $pdo->query("SELECT * FROM about_section LIMIT 1");
$about = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$about) {
    exit('About section not found.');
}

// Upload an image and return its database path
function uploadAboutImage($fieldName, $currentPath, &$newFiles) {

    if (
        !isset($_FILES[$fieldName]) ||
        $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE
    ) {
        return $currentPath;
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Image upload failed.');
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('Images must be smaller than 5 MB.');
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    $info = @getimagesize($file['tmp_name']);
    $mime = $info['mime'] ?? '';

    if (
        !isset($allowed[$mime]) ||
        !in_array($info[2] ?? 0, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)
    ) {
        throw new Exception('Only JPG, PNG and WebP images are allowed.');
    }

    $folder = __DIR__ . '/../uploads/about/';

    if (!is_dir($folder) && !mkdir($folder, 0755, true)) {
        throw new Exception('Could not create the upload folder.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $destination = $folder . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Could not save the image.');
    }

    $newFiles[] = $destination;

    return 'uploads/about/' . $filename;
}

// Handle forms
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        exit('Invalid form request.');
    }

    $action = $_POST['action'] ?? '';
    $newFiles = [];

    try {

        // Update main About section
        if ($action === 'update_about') {

            $smallHeading = trim($_POST['small_heading'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $experienceNumber = trim($_POST['experience_number'] ?? '');
            $experienceText = trim($_POST['experience_text'] ?? '');
            $firstButton = trim($_POST['first_button_text'] ?? '');
            $secondButton = trim($_POST['second_button_text'] ?? '');

            if (
                $smallHeading === '' ||
                $title === '' ||
                $description === '' ||
                $experienceNumber === '' ||
                $experienceText === '' ||
                $firstButton === '' ||
                $secondButton === ''
            ) {
                throw new Exception('Please complete all text fields.');
            }

            if (
                strlen($smallHeading) > 255 ||
                strlen($title) > 255 ||
                strlen($experienceNumber) > 20 ||
                strlen($experienceText) > 100 ||
                strlen($firstButton) > 100 ||
                strlen($secondButton) > 100
            ) {
                throw new Exception('One or more text fields are too long.');
            }

            $mainImage = uploadAboutImage(
                'main_image',
                $about['main_image'],
                $newFiles
            );

            $secondImage = uploadAboutImage(
                'second_image',
                $about['second_image'],
                $newFiles
            );

            $stmt = $pdo->prepare(
                "UPDATE about_section
                 SET small_heading = ?,
                     title = ?,
                     description = ?,
                     main_image = ?,
                     second_image = ?,
                     experience_number = ?,
                     experience_text = ?,
                     first_button_text = ?,
                     second_button_text = ?
                 WHERE id = ?"
            );

            $stmt->execute([
                $smallHeading,
                $title,
                $description,
                $mainImage,
                $secondImage,
                $experienceNumber,
                $experienceText,
                $firstButton,
                $secondButton,
                $about['id']
            ]);
        }

        // Add a feature
        elseif ($action === 'add_feature') {

            $icon = trim($_POST['icon'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $order = filter_var(
                $_POST['display_order'] ?? null,
                FILTER_VALIDATE_INT
            );

            if ($icon === '' || $title === '' || $description === '' ||
                $order === false || $order < 1 ||
                strlen($icon) > 50 || strlen($title) > 150) {
                throw new Exception('Please enter valid feature details.');
            }

            $stmt = $pdo->prepare(
                "INSERT INTO about_features
                 (icon, title, description, display_order)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->execute([$icon, $title, $description, $order]);
        }

        // Edit a feature
        elseif ($action === 'edit_feature') {

            $id = filter_var($_POST['feature_id'] ?? null, FILTER_VALIDATE_INT);
            $icon = trim($_POST['icon'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $order = filter_var(
                $_POST['display_order'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$id || $icon === '' || $title === '' ||
                $description === '' || $order === false ||
                $order < 1 || strlen($icon) > 50 ||
                strlen($title) > 150) {
                throw new Exception('Please enter valid feature details.');
            }

            $stmt = $pdo->prepare(
                "UPDATE about_features
                 SET icon = ?, title = ?, description = ?, display_order = ?
                 WHERE id = ?"
            );

            $stmt->execute([$icon, $title, $description, $order, $id]);
        }

        // Delete a feature
        elseif ($action === 'delete_feature') {

            $id = filter_var($_POST['feature_id'] ?? null, FILTER_VALIDATE_INT);

            if (!$id) {
                throw new Exception('Invalid feature.');
            }

            $stmt = $pdo->prepare("DELETE FROM about_features WHERE id = ?");
            $stmt->execute([$id]);
        }

        else {
            throw new Exception('Unknown action.');
        }

        header('Location: about.php?saved=1');
        exit;

    } catch (Exception $e) {
        // Remove new uploads if either image upload or database save fails.
        foreach ($newFiles as $filePath) {
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }

        if ($e instanceof PDOException) {
            error_log($e->getMessage());
            $error = 'Could not save the About changes.';
        } else {
            $error = $e->getMessage();
        }
    }
}

// Refresh About content and feature records
$stmt = $pdo->query("SELECT * FROM about_section LIMIT 1");
$about = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->query(
    "SELECT * FROM about_features ORDER BY display_order, id"
);
$features = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Management - Constructify</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/admin.css">
</head>

<body>

<div class="admin-layout">

    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">

        <div class="sidebar-brand">
            <h2>Constructify</h2>
            <span>Admin Panel</span>
        </div>

        <nav class="sidebar-nav">
            <a href="dashboard.php" class="sidebar-link">Dashboard</a>
            <a href="settings.php" class="sidebar-link">Website Settings</a>
            <a href="home.php" class="sidebar-link">Home</a>
            <a href="about.php" class="sidebar-link active">About</a>
            <a href="services.php" class="sidebar-link">Services</a>
            <a href="statistics.php" class="sidebar-link">Statistics</a>
            <a href="projects.php" class="sidebar-link">Projects</a>
        </nav>

        <div class="sidebar-bottom">
            <a href="../index.php" target="_blank" rel="noopener"
               class="sidebar-link">View Website</a>

            <a href="logout.php" class="sidebar-link logout-link">Logout</a>
        </div>

    </aside>

    <div class="admin-main">

        <header class="admin-header">
            <div class="header-left">

                <button
                    type="button"
                    class="menu-toggle"
                    id="menuToggle"
                    aria-controls="adminSidebar"
                    aria-expanded="false"
                    aria-label="Toggle sidebar"
                >&#9776;</button>

                <h1>About Management</h1>
            </div>

            <div class="admin-user">
                <?= e($_SESSION['admin_username']) ?>
            </div>
        </header>

        <main class="admin-content">
            <div class="settings-container">

                <div class="welcome-section">
                    <h2>Edit About Section</h2>
                    <p>Manage your company information, photographs and features.</p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        Changes saved successfully.
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <!-- Main About Content -->
                <form method="POST" enctype="multipart/form-data"
                      class="settings-form mb-4">

                    <input type="hidden" name="csrf_token"
                           value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action" value="update_about">

                    <h3 class="mb-4">About Content</h3>

                    <div class="mb-3">
                        <label for="small_heading" class="form-label">Small Heading</label>
                        <input type="text" class="form-control" id="small_heading"
                               name="small_heading"
                               value="<?= e($about['small_heading']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Main Title</label>
                        <input type="text" class="form-control" id="title"
                               name="title"
                               value="<?= e($about['title']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description"
                                  name="description" rows="4"
                                  required><?= e($about['description']) ?></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="experience_number" class="form-label">
                                Experience Number
                            </label>
                            <input type="text" class="form-control"
                                   id="experience_number"
                                   name="experience_number"
                                   value="<?= e($about['experience_number']) ?>"
                                   required>
                        </div>

                        <div class="col-md-8">
                            <label for="experience_text" class="form-label">
                                Experience Text
                            </label>
                            <input type="text" class="form-control"
                                   id="experience_text"
                                   name="experience_text"
                                   value="<?= e($about['experience_text']) ?>"
                                   required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="first_button_text" class="form-label">
                            First Button Text
                        </label>
                        <input type="text" class="form-control"
                               id="first_button_text"
                               name="first_button_text"
                               value="<?= e($about['first_button_text']) ?>"
                               required>
                    </div>

                    <div class="mb-4">
                        <label for="second_button_text" class="form-label">
                            Second Button Text
                        </label>
                        <input type="text" class="form-control"
                               id="second_button_text"
                               name="second_button_text"
                               value="<?= e($about['second_button_text']) ?>"
                               required>
                    </div>

                    <h4 class="mb-3">About Images</h4>

                    <div class="mb-4">
                        <label class="form-label">Current Main Image</label>
                        <div class="current-logo">
                            <?php if (!empty($about['main_image'])): ?>
                                <img src="../<?= e($about['main_image']) ?>"
                                     alt="Main About image">
                            <?php else: ?>
                                <span>No image uploaded</span>
                            <?php endif; ?>
                        </div>

                        <label for="main_image" class="form-label mt-3">
                            Replace Main Image
                        </label>
                        <input type="file" class="form-control"
                               id="main_image" name="main_image"
                               accept=".jpg,.jpeg,.png,.webp">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Current Second Image</label>
                        <div class="current-logo">
                            <?php if (!empty($about['second_image'])): ?>
                                <img src="../<?= e($about['second_image']) ?>"
                                     alt="Second About image">
                            <?php else: ?>
                                <span>No image uploaded</span>
                            <?php endif; ?>
                        </div>

                        <label for="second_image" class="form-label mt-3">
                            Replace Second Image
                        </label>
                        <input type="file" class="form-control"
                               id="second_image" name="second_image"
                               accept=".jpg,.jpeg,.png,.webp">
                    </div>

                    <button type="submit" class="save-settings-btn">
                        Save About Content
                    </button>
                </form>

                <!-- Existing Features -->
                <div class="settings-form mb-4">
                    <h3 class="mb-3">About Features</h3>

                    <?php foreach ($features as $feature): ?>

                        <form method="POST" class="counter-edit-card mb-3">

                            <input type="hidden" name="csrf_token"
                                   value="<?= e($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="action" value="edit_feature">
                            <input type="hidden" name="feature_id"
                                   value="<?= (int) $feature['id'] ?>">

                            <div class="row g-3">

                                <div class="col-md-4">
                                    <label class="form-label">Icon</label>
                                    <input type="text" class="form-control"
                                           name="icon" maxlength="50"
                                           value="<?= e($feature['icon']) ?>"
                                           required>
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control"
                                           name="title" maxlength="150"
                                           value="<?= e($feature['title']) ?>"
                                           required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control"
                                              rows="2" required><?= e($feature['description']) ?></textarea>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Display Order</label>
                                    <input type="number" name="display_order"
                                           class="form-control" min="1"
                                           value="<?= (int) $feature['display_order'] ?>"
                                           required>
                                </div>

                            </div>

                            <div class="d-flex gap-2 mt-3">
                                <button type="submit" class="save-settings-btn">
                                    Save Feature
                                </button>
                            </div>

                        </form>

                        <form method="POST" class="mb-4"
                              onsubmit="return confirm('Delete this feature?');">

                            <input type="hidden" name="csrf_token"
                                   value="<?= e($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="action" value="delete_feature">
                            <input type="hidden" name="feature_id"
                                   value="<?= (int) $feature['id'] ?>">

                            <button type="submit" class="btn btn-outline-danger">
                                Delete Feature
                            </button>
                        </form>

                    <?php endforeach; ?>

                </div>

                <!-- Add Feature -->
                <form method="POST" class="settings-form">

                    <input type="hidden" name="csrf_token"
                           value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action" value="add_feature">

                    <h3 class="mb-4">Add New Feature</h3>

                    <div class="mb-3">
                        <label for="new_icon" class="form-label">Icon</label>
                        <input type="text" id="new_icon" name="icon"
                               class="form-control" maxlength="50"
                               placeholder="Example: shield-check" required>
                        <small class="text-muted">
                            Use a Lucide icon name, such as shield-check, clock or trophy.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="new_title" class="form-label">Title</label>
                        <input type="text" id="new_title" name="title"
                               class="form-control" maxlength="150" required>
                    </div>

                    <div class="mb-3">
                        <label for="new_description" class="form-label">Description</label>
                        <textarea id="new_description" name="description"
                                  class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="new_order" class="form-label">Display Order</label>
                        <input type="number" id="new_order" name="display_order"
                               class="form-control" min="1"
                               value="<?= count($features) + 1 ?>" required>
                    </div>

                    <button type="submit" class="save-settings-btn">
                        Add Feature
                    </button>

                </form>

            </div>
        </main>
    </div>
</div>

<script>
    const menuToggle = document.getElementById("menuToggle");
    const sidebar = document.getElementById("adminSidebar");

    menuToggle.addEventListener("click", function () {
        sidebar.classList.toggle("open");

        menuToggle.setAttribute(
            "aria-expanded",
            sidebar.classList.contains("open")
        );
    });
</script>

</body>
</html>

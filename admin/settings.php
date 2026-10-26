<?php

require_once 'auth.php';
require_once '../config/database.php';

$stmt = $pdo->query("SELECT * FROM site_settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$settings) {
    exit('Website settings were not found.');
}

// Create a security token for the form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = ($_GET['saved'] ?? '') === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        exit('Invalid form request.');
    }

    $companyName = trim($_POST['company_name'] ?? '');
    $phoneNumber = trim($_POST['phone_number'] ?? '');
    $buttonText = trim($_POST['estimate_button_text'] ?? '');

    $logoPath = $settings['logo'];
    $newLogoFile = null;

    if ($companyName === '' || $phoneNumber === '' || $buttonText === '') {
        $error = 'Please complete all text fields.';
    } elseif (
        strlen($companyName) > 150 ||
        strlen($phoneNumber) > 50 ||
        strlen($buttonText) > 100
    ) {
        $error = 'One or more values are too long.';
    }

    // Handle the logo upload
    if (
        $error === '' &&
        isset($_FILES['logo']) &&
        $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        $file = $_FILES['logo'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'There was a problem uploading the logo.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $error = 'The logo must be smaller than 2 MB.';
        } else {

            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];

            $imageInfo = @getimagesize($file['tmp_name']);
            $mimeType = $imageInfo['mime'] ?? '';

            if (
                !isset($allowedTypes[$mimeType]) ||
                !in_array($imageInfo[2] ?? 0, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)
            ) {
                $error = 'Only JPG, PNG and WebP images are allowed.';
            } else {

                $uploadDir = __DIR__ . '/../uploads/logos/';

                if (
                    !is_dir($uploadDir) &&
                    !mkdir($uploadDir, 0755, true)
                ) {
                    $error = 'Could not create the logo folder.';
                } else {

                    $extension = $allowedTypes[$mimeType];

                    $fileName = bin2hex(random_bytes(16)) . '.' . $extension;

                    $destination = $uploadDir . $fileName;

                    if (
                        move_uploaded_file(
                            $file['tmp_name'],
                            $destination
                        )
                    ) {
                        $logoPath = 'uploads/logos/' . $fileName;
                        $newLogoFile = $destination;
                    } else {
                        $error = 'Could not save the uploaded logo.';
                    }
                }
            }
        }
    }

    // Save the changes to MySQL
    if ($error === '') {

        try {

            $stmt = $pdo->prepare(
                "UPDATE site_settings
                 SET company_name = ?,
                     phone_number = ?,
                     estimate_button_text = ?,
                     logo = ?
                 WHERE id = ?"
            );

            $stmt->execute([
                $companyName,
                $phoneNumber,
                $buttonText,
                $logoPath,
                $settings['id']
            ]);

            header('Location: settings.php?saved=1');
            exit;

        } catch (PDOException $e) {
            // Do not keep an uploaded logo if the database update failed.
            if ($newLogoFile !== null && is_file($newLogoFile)) {
                unlink($newLogoFile);
            }
            error_log($e->getMessage());
            $error = 'Could not save the website settings.';
        }
    }

    // Keep submitted values if something goes wrong
    $settings['company_name'] = $companyName;
    $settings['phone_number'] = $phoneNumber;
    $settings['estimate_button_text'] = $buttonText;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Website Settings - Constructify</title>

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

            <a href="settings.php" class="sidebar-link active">
                Website Settings
            </a>

            <a href="home.php" class="sidebar-link">Home</a>
            <a href="about.php" class="sidebar-link">About</a>
            <a href="services.php" class="sidebar-link">Services</a>
            <a href="statistics.php" class="sidebar-link">Statistics</a>
            <a href="projects.php" class="sidebar-link">Projects</a>

        </nav>

        <div class="sidebar-bottom">

            <a href="../index.php" target="_blank"
               rel="noopener" class="sidebar-link">
                View Website
            </a>

            <a href="logout.php" class="sidebar-link logout-link">
                Logout
            </a>

        </div>

    </aside>

    <!-- Main Content -->
    <div class="admin-main">

        <header class="admin-header">

            <div class="header-left">
                <button
                    type="button"
                    class="menu-toggle"
                    id="menuToggle"
                    aria-label="Toggle sidebar"
                    aria-expanded="false"
                    aria-controls="adminSidebar"
                >&#9776;</button>
                <h1>Website Settings</h1>
            </div>

            <div class="admin-user">
                <?= htmlspecialchars($_SESSION['admin_username']) ?>
            </div>

        </header>

        <main class="admin-content">

            <div class="settings-container">

                <div class="welcome-section">

                    <h2>Company Information</h2>

                    <p>
                        Update the information displayed
                        on your portfolio website.
                    </p>

                </div>

                <?php if ($success): ?>

                    <div class="alert alert-success">
                        Website settings updated successfully.
                    </div>

                <?php endif; ?>

                <?php if ($error): ?>

                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>

                <?php endif; ?>

                <form
                    method="POST"
                    enctype="multipart/form-data"
                    class="settings-form"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
                    >

                    <!-- Company Name -->
                    <div class="mb-4">

                        <label for="company_name" class="form-label">
                            Company Name
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="company_name"
                            name="company_name"
                            maxlength="150"
                            value="<?= htmlspecialchars($settings['company_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >

                    </div>

                    <!-- Phone Number -->
                    <div class="mb-4">

                        <label for="phone_number" class="form-label">
                            Phone Number
                        </label>

                        <input
                            type="tel"
                            class="form-control"
                            id="phone_number"
                            name="phone_number"
                            maxlength="50"
                            value="<?= htmlspecialchars($settings['phone_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >

                    </div>

                    <!-- Estimate Button -->
                    <div class="mb-4">

                        <label for="estimate_button_text" class="form-label">
                            Get Estimate Button Text
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="estimate_button_text"
                            name="estimate_button_text"
                            maxlength="100"
                            value="<?= htmlspecialchars($settings['estimate_button_text'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >

                    </div>

                    <!-- Company Logo -->
                    <div class="mb-4">

                        <label class="form-label">
                            Current Logo
                        </label>

                        <div class="current-logo">

                            <?php if (!empty($settings['logo'])): ?>

                                <img
                                    src="../<?= htmlspecialchars($settings['logo'], ENT_QUOTES, 'UTF-8') ?>"
                                    alt="Company Logo"
                                >

                            <?php else: ?>

                                <p class="text-muted mb-0">
                                    No logo uploaded yet.
                                </p>

                            <?php endif; ?>

                        </div>

                    </div>

                    <div class="mb-4">

                        <label for="logo" class="form-label">
                            Upload New Logo
                        </label>

                        <input
                            type="file"
                            class="form-control"
                            id="logo"
                            name="logo"
                            accept=".jpg,.jpeg,.png,.webp"
                        >

                        <small class="text-muted">
                            JPG, PNG or WebP. Maximum 2 MB.
                            Leave empty to keep the current logo.
                        </small>

                    </div>

                    <button type="submit" class="save-settings-btn">
                        Save Changes
                    </button>

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
        menuToggle.setAttribute(
            'aria-expanded',
            sidebar.classList.contains('open')
        );
    });
</script>

</body>
</html>

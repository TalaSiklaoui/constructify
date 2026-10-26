<?php

require_once 'auth.php';
require_once '../config/database.php';

// Escape text when displaying it in HTML
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = ($_GET['saved'] ?? '') === '1';

// Retrieve Home data
$stmt = $pdo->query("SELECT * FROM home_section LIMIT 1");
$home = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->query(
    "SELECT * FROM home_counters ORDER BY display_order, id"
);
$counters = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$home) {
    exit('Home section data was not found.');
}

// Handle submitted forms
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        exit('Invalid form request.');
    }

    $formType = $_POST['form_type'] ?? '';

    // Save Home content
    if ($formType === 'home') {

        $smallHeading = trim($_POST['small_heading'] ?? '');
        $titleLine1 = trim($_POST['title_line1'] ?? '');
        $titleLine2 = trim($_POST['title_line2'] ?? '');
        $highlightedText = trim($_POST['highlighted_text'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $firstButton = trim($_POST['first_button_text'] ?? '');
        $secondButton = trim($_POST['second_button_text'] ?? '');

        $backgroundImage = $home['background_image'];
        $newUploadedFile = null;

        if (
            $smallHeading === '' ||
            $titleLine1 === '' ||
            $titleLine2 === '' ||
            $highlightedText === '' ||
            $description === '' ||
            $firstButton === '' ||
            $secondButton === ''
        ) {
            $error = 'Please complete all text fields.';
        } elseif (
            strlen($smallHeading) > 255 ||
            strlen($titleLine1) > 255 ||
            strlen($titleLine2) > 255 ||
            strlen($highlightedText) > 255 ||
            strlen($firstButton) > 100 ||
            strlen($secondButton) > 100
        ) {
            $error = 'One or more text fields are too long.';
        }

        // Upload a new background only if one was selected
        if (
            $error === '' &&
            isset($_FILES['background_image']) &&
            $_FILES['background_image']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

            $file = $_FILES['background_image'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'The image could not be uploaded.';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $error = 'The image must be smaller than 5 MB.';
            } else {

                $allowedTypes = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp'
                ];

                $imageInfo = @getimagesize($file['tmp_name']);
                $mimeType = $imageInfo['mime'] ?? '';

                $validImages = [
                    IMAGETYPE_JPEG,
                    IMAGETYPE_PNG,
                    IMAGETYPE_WEBP
                ];

                if (
                    !isset($allowedTypes[$mimeType]) ||
                    !in_array($imageInfo[2] ?? 0, $validImages, true)
                ) {
                    $error = 'Only JPG, PNG and WebP images are allowed.';
                } else {

                    $uploadDir = __DIR__ . '/../uploads/home/';

                    if (
                        !is_dir($uploadDir) &&
                        !mkdir($uploadDir, 0755, true)
                    ) {
                        $error = 'Could not create the upload folder.';
                    } else {

                        $extension = $allowedTypes[$mimeType];

                        $fileName = bin2hex(random_bytes(16))
                            . '.' . $extension;

                        $destination = $uploadDir . $fileName;

                        if (move_uploaded_file(
                            $file['tmp_name'],
                            $destination
                        )) {
                            $backgroundImage =
                                'uploads/home/' . $fileName;

                            $newUploadedFile = $destination;
                        } else {
                            $error = 'Could not save the new image.';
                        }
                    }
                }
            }
        }

        if ($error === '') {

            try {

                $stmt = $pdo->prepare(
                    "UPDATE home_section
                     SET small_heading = ?,
                         title_line1 = ?,
                         title_line2 = ?,
                         highlighted_text = ?,
                         description = ?,
                         first_button_text = ?,
                         second_button_text = ?,
                         background_image = ?
                     WHERE id = ?"
                );

                $stmt->execute([
                    $smallHeading,
                    $titleLine1,
                    $titleLine2,
                    $highlightedText,
                    $description,
                    $firstButton,
                    $secondButton,
                    $backgroundImage,
                    $home['id']
                ]);

                header('Location: home.php?saved=1');
                exit;

            } catch (PDOException $e) {

                if ($newUploadedFile && is_file($newUploadedFile)) {
                    unlink($newUploadedFile);
                }

                error_log($e->getMessage());
                $error = 'Could not save the Home content.';
            }
        }

        // Preserve submitted text if validation fails
        $home['small_heading'] = $smallHeading;
        $home['title_line1'] = $titleLine1;
        $home['title_line2'] = $titleLine2;
        $home['highlighted_text'] = $highlightedText;
        $home['description'] = $description;
        $home['first_button_text'] = $firstButton;
        $home['second_button_text'] = $secondButton;
    }

    // Save existing Home counters
    elseif ($formType === 'counters') {

        $submittedCounters = $_POST['counters'] ?? [];

        if (
            !is_array($submittedCounters) ||
            count($submittedCounters) !== count($counters)
        ) {
            $error = 'Invalid counter data.';
        } else {

            $validatedCounters = [];

            foreach ($counters as $counter) {

                $id = (int) $counter['id'];
                $data = $submittedCounters[$id] ?? null;

                if (!is_array($data)) {
                    $error = 'A counter is missing.';
                    break;
                }

                $number = trim($data['number'] ?? '');
                $suffix = trim($data['suffix'] ?? '');
                $label = trim($data['label'] ?? '');

                if (
                    $number === '' ||
                    !ctype_digit($number) ||
                    $label === '' ||
                    strlen($number) > 15 ||
                    strlen($suffix) > 10 ||
                    strlen($label) > 100
                ) {
                    $error = 'Please enter valid counter values.';
                    break;
                }

                $validatedCounters[] = [
                    'id' => $id,
                    'number' => $number,
                    'suffix' => $suffix,
                    'label' => $label
                ];
            }

            if ($error === '') {

                try {

                    $pdo->beginTransaction();

                    $stmt = $pdo->prepare(
                        "UPDATE home_counters
                         SET counter_number = ?,
                             counter_suffix = ?,
                             counter_label = ?
                         WHERE id = ?"
                    );

                    foreach ($validatedCounters as $counter) {

                        $stmt->execute([
                            $counter['number'],
                            $counter['suffix'],
                            $counter['label'],
                            $counter['id']
                        ]);
                    }

                    $pdo->commit();

                    header('Location: home.php?saved=1');
                    exit;

                } catch (PDOException $e) {

                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }

                    error_log($e->getMessage());
                    $error = 'Could not update the counters.';
                }
            }
        }

        // Preserve submitted counter values if saving fails
        foreach ($counters as &$counter) {

            $id = (int) $counter['id'];
            $data = $submittedCounters[$id] ?? null;

            if (is_array($data)) {
                $counter['counter_number'] =
                    $data['number'] ?? '';

                $counter['counter_suffix'] =
                    $data['suffix'] ?? '';

                $counter['counter_label'] =
                    $data['label'] ?? '';
            }
        }

        unset($counter);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Home Management - Constructify</title>

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

            <a href="dashboard.php" class="sidebar-link">
                Dashboard
            </a>

            <a href="settings.php" class="sidebar-link">
                Website Settings
            </a>

            <a href="home.php" class="sidebar-link active">
                Home
            </a>

            <a href="about.php" class="sidebar-link">About</a>
            <a href="services.php" class="sidebar-link">Services</a>
            <a href="statistics.php" class="sidebar-link">Statistics</a>
            <a href="projects.php" class="sidebar-link">Projects</a>

        </nav>

        <div class="sidebar-bottom">

            <a
                href="../index.php"
                target="_blank"
                rel="noopener"
                class="sidebar-link"
            >
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
                    aria-controls="adminSidebar"
                    aria-expanded="false"
                >
                    &#9776;
                </button>

                <h1>Home Management</h1>

            </div>

            <div class="admin-user">
                <?= e($_SESSION['admin_username']) ?>
            </div>

        </header>

        <main class="admin-content">

            <div class="settings-container">

                <div class="welcome-section">
                    <h2>Edit Home Section</h2>
                    <p>
                        Update the text, background image and
                        counters displayed on the homepage.
                    </p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        Home content updated successfully.
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Home Content Form -->
                <form
                    method="POST"
                    enctype="multipart/form-data"
                    class="settings-form mb-4"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e($_SESSION['csrf_token']) ?>"
                    >

                    <input
                        type="hidden"
                        name="form_type"
                        value="home"
                    >

                    <h3 class="mb-4">Home Content</h3>

                    <div class="mb-3">
                        <label class="form-label" for="small_heading">
                            Small Heading
                        </label>

                        <input
                            id="small_heading"
                            type="text"
                            name="small_heading"
                            class="form-control"
                            value="<?= e($home['small_heading']) ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="title_line1">
                            Main Heading - First Line
                        </label>

                        <input
                            id="title_line1"
                            type="text"
                            name="title_line1"
                            class="form-control"
                            value="<?= e($home['title_line1']) ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="title_line2">
                            Main Heading - Second Line
                        </label>

                        <input
                            id="title_line2"
                            type="text"
                            name="title_line2"
                            class="form-control"
                            value="<?= e($home['title_line2']) ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="highlighted_text">
                            Orange Highlighted Text
                        </label>

                        <input
                            id="highlighted_text"
                            type="text"
                            name="highlighted_text"
                            class="form-control"
                            value="<?= e($home['highlighted_text']) ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="description">
                            Description
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            class="form-control"
                            rows="4"
                            required
                        ><?= e($home['description']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="first_button_text">
                            First Button Text
                        </label>

                        <input
                            id="first_button_text"
                            type="text"
                            name="first_button_text"
                            class="form-control"
                            maxlength="100"
                            value="<?= e($home['first_button_text']) ?>"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="second_button_text">
                            Second Button Text
                        </label>

                        <input
                            id="second_button_text"
                            type="text"
                            name="second_button_text"
                            class="form-control"
                            maxlength="100"
                            value="<?= e($home['second_button_text']) ?>"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label class="form-label">
                            Current Background Image
                        </label>

                        <div class="current-logo">

                            <?php if (!empty($home['background_image'])): ?>

                                <img
                                    src="../<?= e($home['background_image']) ?>"
                                    alt="Current Home background"
                                >

                            <?php else: ?>

                                <p class="text-muted mb-0">
                                    No background image uploaded.
                                </p>

                            <?php endif; ?>

                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="background_image">
                            Upload New Background Image
                        </label>

                        <input
                            id="background_image"
                            type="file"
                            name="background_image"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.webp"
                        >

                        <small class="text-muted">
                            JPG, PNG or WebP. Maximum 5 MB.
                            Leave empty to keep the current image.
                        </small>
                    </div>

                    <button type="submit" class="save-settings-btn">
                        Save Home Content
                    </button>

                </form>

                <!-- Home Counters Form -->
                <form method="POST" class="settings-form">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e($_SESSION['csrf_token']) ?>"
                    >

                    <input
                        type="hidden"
                        name="form_type"
                        value="counters"
                    >

                    <h3 class="mb-4">Home Counters</h3>

                    <?php foreach ($counters as $index => $counter): ?>

                        <?php $id = (int) $counter['id']; ?>

                        <div class="counter-edit-card mb-4">

                            <h4>Counter <?= $index + 1 ?></h4>

                            <div class="row g-3">

                                <div class="col-md-4">

                                    <label class="form-label">
                                        Number
                                    </label>

                                    <input
                                        type="number"
                                        min="0"
                                        step="1"
                                        class="form-control"
                                        name="counters[<?= $id ?>][number]"
                                        value="<?= e($counter['counter_number']) ?>"
                                        required
                                    >

                                </div>

                                <div class="col-md-3">

                                    <label class="form-label">
                                        Suffix
                                    </label>

                                    <input
                                        type="text"
                                        maxlength="10"
                                        class="form-control"
                                        name="counters[<?= $id ?>][suffix]"
                                        value="<?= e($counter['counter_suffix']) ?>"
                                    >

                                </div>

                                <div class="col-md-5">

                                    <label class="form-label">
                                        Label
                                    </label>

                                    <input
                                        type="text"
                                        maxlength="100"
                                        class="form-control"
                                        name="counters[<?= $id ?>][label]"
                                        value="<?= e($counter['counter_label']) ?>"
                                        required
                                    >

                                </div>

                            </div>
                        </div>

                    <?php endforeach; ?>

                    <button type="submit" class="save-settings-btn">
                        Save Counters
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

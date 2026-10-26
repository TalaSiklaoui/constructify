<?php
require_once 'auth.php';
require_once '../config/database.php';

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function positiveId($value) {
    $id = filter_var($value, FILTER_VALIDATE_INT);
    if ($id === false || $id < 1) {
        throw new RuntimeException('Invalid project or gallery image selected.');
    }
    return $id;
}

// Save images in the existing project uploads folder.
function uploadProjectImage($field, &$uploadedFiles) {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed.');
    }
    if ($file['size'] <= 0 || $file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Images must be no larger than 5 MB.');
    }
    $image = @getimagesize($file['tmp_name']);
    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!$image || !isset($types[$image[2]])) {
        throw new RuntimeException('Only JPG, PNG and WebP images are allowed.');
    }
    $extension = $types[$image[2]];
    $folder = __DIR__ . '/../uploads/projects/';
    if (!is_dir($folder) && !mkdir($folder, 0755, true)) {
        throw new RuntimeException('Could not create the project upload folder.');
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    $destination = $folder . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Could not save uploaded image.');
    }
    $uploadedFiles[] = $destination;
    return 'uploads/projects/' . $filename;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$section = $pdo->query('SELECT * FROM projects_section LIMIT 1')->fetch(PDO::FETCH_ASSOC);
if (!$section) {
    exit('Projects section was not found.');
}

$error = '';
$success = ($_GET['saved'] ?? '') === '1';
$categories = ['Residential', 'Commercial', 'Infrastructure'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Invalid form request.');
    }

    $uploadedFiles = [];
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'update_section') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            if ($title === '' || $description === '' || strlen($title) > 255) {
                throw new RuntimeException('Enter a valid section title and description.');
            }
            $stmt = $pdo->prepare('UPDATE projects_section SET title = ?, description = ? WHERE id = ?');
            $stmt->execute([$title, $description, $section['id']]);

        } elseif ($action === 'add_project' || $action === 'edit_project') {
            $title = trim($_POST['title'] ?? '');
            $category = $_POST['category'] ?? '';
            $description = trim($_POST['description'] ?? '');
            $order = filter_var($_POST['display_order'] ?? null, FILTER_VALIDATE_INT);

            if ($title === '' || $description === '' || strlen($title) > 255 ||
                !in_array($category, $categories, true) || $order === false || $order < 1) {
                throw new RuntimeException('Enter a title, description, valid category and display order.');
            }

            if ($action === 'add_project') {
                $cover = uploadProjectImage('cover_image', $uploadedFiles);
                if ($cover === null) {
                    throw new RuntimeException('Choose a cover image for the new project.');
                }
                $stmt = $pdo->prepare('INSERT INTO projects (title, category, description, cover_image, display_order) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$title, $category, $description, $cover, $order]);
            } else {
                $id = positiveId($_POST['project_id'] ?? null);
                $stmt = $pdo->prepare('SELECT cover_image FROM projects WHERE id = ?');
                $stmt->execute([$id]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$existing) {
                    throw new RuntimeException('Project not found.');
                }
                $cover = uploadProjectImage('cover_image', $uploadedFiles) ?? $existing['cover_image'];
                $stmt = $pdo->prepare('UPDATE projects SET title = ?, category = ?, description = ?, cover_image = ?, display_order = ? WHERE id = ?');
                $stmt->execute([$title, $category, $description, $cover, $order, $id]);
            }

        } elseif ($action === 'delete_project') {
            $id = positiveId($_POST['project_id'] ?? null);
            // Existing foreign key automatically deletes this project's gallery records.
            $stmt = $pdo->prepare('DELETE FROM projects WHERE id = ?');
            $stmt->execute([$id]);

        } elseif ($action === 'add_gallery_image') {
            $id = positiveId($_POST['project_id'] ?? null);
            $order = filter_var($_POST['display_order'] ?? null, FILTER_VALIDATE_INT);
            if ($order === false || $order < 1) {
                throw new RuntimeException('Enter a valid gallery display order.');
            }
            $stmt = $pdo->prepare('SELECT id FROM projects WHERE id = ?');
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                throw new RuntimeException('Project not found.');
            }
            $path = uploadProjectImage('gallery_image', $uploadedFiles);
            if ($path === null) {
                throw new RuntimeException('Choose a gallery image.');
            }
            $stmt = $pdo->prepare('INSERT INTO project_images (project_id, image_path, display_order) VALUES (?, ?, ?)');
            $stmt->execute([$id, $path, $order]);

        } elseif ($action === 'update_gallery_order') {
            $imageId = positiveId($_POST['image_id'] ?? null);
            $projectId = positiveId($_POST['project_id'] ?? null);
            $order = filter_var($_POST['display_order'] ?? null, FILTER_VALIDATE_INT);
            if ($order === false || $order < 1) {
                throw new RuntimeException('Enter a valid image display order.');
            }
            $stmt = $pdo->prepare('UPDATE project_images SET display_order = ? WHERE id = ? AND project_id = ?');
            $stmt->execute([$order, $imageId, $projectId]);

        } elseif ($action === 'delete_gallery_image') {
            $imageId = positiveId($_POST['image_id'] ?? null);
            $projectId = positiveId($_POST['project_id'] ?? null);
            $stmt = $pdo->prepare('DELETE FROM project_images WHERE id = ? AND project_id = ?');
            $stmt->execute([$imageId, $projectId]);

        } else {
            throw new RuntimeException('Unknown form action.');
        }

        header('Location: projects.php?saved=1');
        exit;

    } catch (RuntimeException $ex) {
        $error = $ex->getMessage();
    } catch (PDOException $ex) {
        error_log($ex->getMessage());
        $error = 'Could not save the changes. Please check your database.';
    }

    // Remove files uploaded during a failed operation.
    foreach ($uploadedFiles as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
}

$section = $pdo->query('SELECT * FROM projects_section LIMIT 1')->fetch(PDO::FETCH_ASSOC);
$projects = $pdo->query('SELECT * FROM projects ORDER BY display_order, id')->fetchAll(PDO::FETCH_ASSOC);
$galleryRows = $pdo->query('SELECT * FROM project_images ORDER BY display_order, id')->fetchAll(PDO::FETCH_ASSOC);
$galleries = [];
foreach ($galleryRows as $image) {
    $galleries[$image['project_id']][] = $image;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects Management - Constructify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/admin.css">
    <style>
        /* Keep project cover previews within their editing cards on all screens. */
        .project-cover-preview {
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }

        .project-cover-preview img {
            display: block;
            width: 100%;
            max-width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
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
            <a href="statistics.php" class="sidebar-link">Statistics</a>
            <a href="projects.php" class="sidebar-link active">Projects</a>
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
                <h1>Projects Management</h1>
            </div>
            <div class="admin-user"><?= e($_SESSION['admin_username']) ?></div>
        </header>

        <main class="admin-content">
            <div class="settings-container">
                <div class="welcome-section">
                    <h2>Edit Projects</h2>
                    <p>Manage the Projects heading, project cards, cover images and photo galleries.</p>
                </div>
                <?php if ($success): ?>
                    <div class="alert alert-success">Changes saved successfully.</div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <!-- Projects section heading -->
                <form method="POST" class="settings-form mb-4">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action" value="update_section">
                    <h3 class="mb-4">Projects Section Content</h3>
                    <div class="mb-3">
                        <label for="section_title" class="form-label">Section Title</label>
                        <input type="text" id="section_title" name="title" class="form-control" maxlength="255" value="<?= e($section['title']) ?>" required>
                    </div>
                    <div class="mb-4">
                        <label for="section_description" class="form-label">Description</label>
                        <textarea id="section_description" name="description" class="form-control" rows="3" required><?= e($section['description']) ?></textarea>
                    </div>
                    <button type="submit" class="save-settings-btn">Save Section Content</button>
                </form>

                <!-- Existing projects -->
                <div class="settings-form mb-4">
                    <h3 class="mb-3">Existing Projects</h3>
                    <?php if (!$projects): ?>
                        <p class="text-muted">No projects yet. Add a project below.</p>
                    <?php endif; ?>

                    <?php foreach ($projects as $project): ?>
                        <?php $projectId = (int) $project['id']; ?>
                        <div class="counter-edit-card mb-4">
                            <h4><?= e($project['title']) ?></h4>

                            <?php if (!empty($project['cover_image'])): ?>
                                <div class="project-cover-preview mb-3">
                                    <img
                                        src="../<?= e($project['cover_image']) ?>"
                                        alt="<?= e($project['title']) ?> cover"
                                    >
                                </div>
                            <?php endif; ?>

                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="action" value="edit_project">
                                <input type="hidden" name="project_id" value="<?= $projectId ?>">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label" for="project_title_<?= $projectId ?>">Project Title</label>
                                        <input class="form-control" id="project_title_<?= $projectId ?>" name="title" maxlength="255" value="<?= e($project['title']) ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="project_category_<?= $projectId ?>">Category</label>
                                        <select class="form-select" id="project_category_<?= $projectId ?>" name="category" required>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= e($category) ?>" <?= $project['category'] === $category ? 'selected' : '' ?>><?= e($category) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="project_description_<?= $projectId ?>">Description</label>
                                        <textarea class="form-control" id="project_description_<?= $projectId ?>" name="description" rows="2" required><?= e($project['description']) ?></textarea>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="project_order_<?= $projectId ?>">Display Order</label>
                                        <input type="number" class="form-control" id="project_order_<?= $projectId ?>" name="display_order" min="1" value="<?= (int) $project['display_order'] ?>" required>
                                    </div>
                                    <div class="col-md-9">
                                        <label class="form-label" for="cover_<?= $projectId ?>">Replace Cover Image</label>
                                        <input type="file" class="form-control" id="cover_<?= $projectId ?>" name="cover_image" accept=".jpg,.jpeg,.png,.webp">
                                        <small class="text-muted">Optional. JPG, PNG or WebP, maximum 5 MB.</small>
                                    </div>
                                </div>
                                <button type="submit" class="save-settings-btn mt-3">Save Project</button>
                            </form>

                            <form method="POST" class="mt-2" onsubmit="return confirm('Delete this project and its gallery images?');">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="action" value="delete_project">
                                <input type="hidden" name="project_id" value="<?= $projectId ?>">
                                <button type="submit" class="btn btn-outline-danger">Delete Project</button>
                            </form>

                            <hr class="my-4">
                            <h5 class="mb-3">Gallery Images</h5>
                            <?php foreach ($galleries[$projectId] ?? [] as $image): ?>
                                <?php $imageId = (int) $image['id']; ?>
                                <div class="border rounded bg-white p-3 mb-3">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-md-4">
                                            <img src="../<?= e($image['image_path']) ?>" alt="Project gallery photo" class="img-fluid rounded" style="max-height:110px;object-fit:cover;">
                                        </div>
                                        <div class="col-md-8">
                                            <form method="POST" class="d-flex flex-wrap align-items-end gap-2 mb-2">
                                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                                <input type="hidden" name="action" value="update_gallery_order">
                                                <input type="hidden" name="project_id" value="<?= $projectId ?>">
                                                <input type="hidden" name="image_id" value="<?= $imageId ?>">
                                                <div>
                                                    <label class="form-label" for="gallery_order_<?= $imageId ?>">Image Order</label>
                                                    <input type="number" id="gallery_order_<?= $imageId ?>" name="display_order" class="form-control" style="width:100px;" min="1" value="<?= (int) $image['display_order'] ?>" required>
                                                </div>
                                                <button type="submit" class="btn btn-outline-secondary">Save Order</button>
                                            </form>
                                            <form method="POST" onsubmit="return confirm('Delete this gallery image?');">
                                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                                <input type="hidden" name="action" value="delete_gallery_image">
                                                <input type="hidden" name="project_id" value="<?= $projectId ?>">
                                                <input type="hidden" name="image_id" value="<?= $imageId ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete Image</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($galleries[$projectId])): ?>
                                <p class="text-muted">No gallery images for this project.</p>
                            <?php endif; ?>

                            <form method="POST" enctype="multipart/form-data" class="border-top pt-3">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="action" value="add_gallery_image">
                                <input type="hidden" name="project_id" value="<?= $projectId ?>">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-7">
                                        <label class="form-label" for="new_gallery_<?= $projectId ?>">Add Gallery Image</label>
                                        <input type="file" id="new_gallery_<?= $projectId ?>" name="gallery_image" class="form-control" accept=".jpg,.jpeg,.png,.webp" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" for="new_gallery_order_<?= $projectId ?>">Order</label>
                                        <input type="number" id="new_gallery_order_<?= $projectId ?>" name="display_order" class="form-control" min="1" value="<?= count($galleries[$projectId] ?? []) + 1 ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-outline-secondary w-100">Add Image</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- New project -->
                <form method="POST" enctype="multipart/form-data" class="settings-form mb-4">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="action" value="add_project">
                    <h3 class="mb-4">Add New Project</h3>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="new_title">Project Title</label>
                            <input type="text" class="form-control" id="new_title" name="title" maxlength="255" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="new_category">Category</label>
                            <select class="form-select" id="new_category" name="category" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= e($category) ?>"><?= e($category) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="new_description">Description</label>
                            <textarea class="form-control" id="new_description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="new_order">Display Order</label>
                            <input type="number" class="form-control" id="new_order" name="display_order" min="1" value="<?= count($projects) + 1 ?>" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="new_cover">Cover Image</label>
                            <input type="file" class="form-control" id="new_cover" name="cover_image" accept=".jpg,.jpeg,.png,.webp" required>
                            <small class="text-muted">JPG, PNG or WebP, maximum 5 MB.</small>
                        </div>
                    </div>
                    <button type="submit" class="save-settings-btn mt-4">Add Project</button>
                    <p class="text-muted mt-2 mb-0">After creating the project, add its gallery photos.</p>
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

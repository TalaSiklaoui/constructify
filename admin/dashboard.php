
<?php

require_once 'auth.php';

// Dashboard management sections
$sections = [
    ['Website Settings', 'Company details and contact information', 'settings.php'],
    ['Home', 'Main heading, background and counters', 'home.php'],
    ['About', 'Company information and features', 'about.php'],
    ['Services', 'Construction services and descriptions', 'services.php'],
    ['Statistics', 'Statistics and counter values', 'statistics.php'],
    ['Projects', 'Portfolio projects and gallery images', 'projects.php']
];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - Constructify</title>

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

                <a href="dashboard.php" class="sidebar-link active">
                    Dashboard
                </a>

                <a href="settings.php" class="sidebar-link">
                    Website Settings
                </a>

                <a href="home.php" class="sidebar-link">
                    Home
                </a>

                <a href="about.php" class="sidebar-link">
                    About
                </a>

                <a href="services.php" class="sidebar-link">
                    Services
                </a>

                <a href="statistics.php" class="sidebar-link">
                    Statistics
                </a>

                <a href="projects.php" class="sidebar-link">
                    Projects
                </a>

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

        <!-- Dashboard Content -->
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
                    >
                        &#9776;
                    </button>

                    <h1>Dashboard</h1>

                </div>

                <div class="admin-user">
                    <?= htmlspecialchars(
                        $_SESSION['admin_username'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </div>

            </header>

            <!-- Dashboard Content -->
            <main class="admin-content">

                <div class="welcome-section">

                    <h2>Welcome to Constructify</h2>

                    <p>
                        Manage the content displayed on your
                        construction portfolio website.
                    </p>

                </div>

                <h3 class="content-heading">Website Management</h3>

                <div class="row g-4">

                    <?php foreach ($sections as $section): ?>

                        <div class="col-md-6 col-xl-4">

                            <div class="management-card">

                                <h4>
                                    <?= htmlspecialchars($section[0]) ?>
                                </h4>

                                <p>
                                    <?= htmlspecialchars($section[1]) ?>
                                </p>

                                <a
                                    href="<?= htmlspecialchars($section[2]) ?>"
                                    class="manage-label"
                                >
                                    Manage Content →
                                </a>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </main>

        </div>

    </div>

    <!-- Mobile Sidebar toggle-->
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

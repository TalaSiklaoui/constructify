<?php

require_once 'config/database.php';

// Website settings
$stmt = $pdo->query("SELECT * FROM site_settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Home section content
$stmt = $pdo->query("SELECT * FROM home_section LIMIT 1");
$home = $stmt->fetch(PDO::FETCH_ASSOC);

// Home counters
$stmt = $pdo->query("SELECT * FROM home_counters ORDER BY display_order, id");
$homeCounters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// About section content
$stmt = $pdo->query("SELECT * FROM about_section LIMIT 1");
$about = $stmt->fetch(PDO::FETCH_ASSOC);

// About features
$stmt = $pdo->query("SELECT * FROM about_features ORDER BY display_order, id");
$aboutFeatures = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Services section content
$stmt = $pdo->query("SELECT * FROM services_section LIMIT 1");
$servicesSection = $stmt->fetch(PDO::FETCH_ASSOC);

// Services cards
$stmt = $pdo->query("SELECT * FROM services ORDER BY display_order, id");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistics section content
$stmt = $pdo->query("SELECT * FROM statistics_section LIMIT 1");
$statisticsSection = $stmt->fetch(PDO::FETCH_ASSOC);

// Statistics counters
$stmt = $pdo->query("SELECT * FROM statistics ORDER BY display_order, id");
$statistics = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Projects section content
$stmt = $pdo->query("SELECT * FROM projects_section LIMIT 1");
$projectsSection = $stmt->fetch(PDO::FETCH_ASSOC);

// Project cards
$stmt = $pdo->query("SELECT * FROM projects ORDER BY display_order, id");
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Project gallery images
$stmt = $pdo->query("SELECT * FROM project_images ORDER BY display_order, id");
$projectImages = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($settings['company_name'], ENT_QUOTES, 'UTF-8') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Lucide icons -->
    <script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js"></script>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-xl bg-white fixed-top">
        <div class="container-fluid px-4">

            <div class="navbar-brand d-flex align-items-center gap-2">
                <div class="logo-icon <?= !empty($settings['logo']) ? 'has-logo' : '' ?>">
                    <?php if (!empty($settings['logo'])): ?>
                        <img
                            src="<?= htmlspecialchars($settings['logo'], ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($settings['company_name'], ENT_QUOTES, 'UTF-8') ?> logo"
                            class="navbar-logo"
                        >
                    <?php else: ?>
                        <i data-lucide="building-2"></i>
                    <?php endif; ?>
                </div>

                <span>
                    <?= htmlspecialchars($settings['company_name'], ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarMenu"
                    aria-controls="navbarMenu"
                    aria-expanded="false"
                    aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMenu">

                <ul class="navbar-nav mx-auto gap-xl-4">

                    <li class="nav-item">
                        <a class="nav-link active" href="#home">Home</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#services">Services</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#projects">Projects</a>
                    </li>

                    <li class="nav-item">
                        <span class="nav-link">Team</span>
                    </li>

                    <li class="nav-item">
                        <span class="nav-link">
                            Pages <i data-lucide="chevron-down" class="small-icon"></i>
                        </span>
                    </li>

                    <li class="nav-item">
                        <span class="nav-link">Contact</span>
                    </li>

                </ul>

                <div class="d-flex align-items-center gap-3 navbar-contact">

                    <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $settings['phone_number']), ENT_QUOTES, 'UTF-8') ?>"
                       class="phone-link">

                        <i data-lucide="phone"></i>

                        <?= htmlspecialchars($settings['phone_number'], ENT_QUOTES, 'UTF-8') ?>
                    </a>

                    <button class="estimate-btn" type="button">
                        <?= htmlspecialchars($settings['estimate_button_text'], ENT_QUOTES, 'UTF-8') ?>
                    </button>

                </div>

            </div>
        </div>
    </nav>

    <main>

    <!-- Home Section -->
    <section
        id="home"
         class="home-section"
         <?php if (!empty($home['background_image'])): ?>
             style="background-image:
             linear-gradient(rgba(19, 42, 64, 0.85), rgba(19, 42, 64, 0.85)),
             url('<?= htmlspecialchars($home['background_image'], ENT_QUOTES) ?>');"
         <?php endif; ?>
    >

        <div class="container home-content">

            <div class="home-badge">
                <i data-lucide="building-2"></i>
                <?= htmlspecialchars($home['small_heading']) ?>
            </div>

            <h1 class="home-title">
                <?= htmlspecialchars($home['title_line1']) ?>

                <span class="d-block">
                    <?= htmlspecialchars($home['title_line2']) ?>
                    <span class="highlight">
                    <?= htmlspecialchars($home['highlighted_text']) ?>
                    </span>
                </span>
            </h1>

             <p class="home-description">
                <?= htmlspecialchars($home['description']) ?>
            </p>

            <div class="home-buttons">

                <a href="#projects" class="home-btn-primary">
                    <?= htmlspecialchars($home['first_button_text']) ?>
                </a>

                <a href="#about" class="home-btn-secondary">
                    <?= htmlspecialchars($home['second_button_text']) ?>
                    <i data-lucide="arrow-right"></i>
                </a>

            </div>

        </div>

    <!-- Home Counters -->
    <div class="container home-counters-container">

        <div class="home-counters">

            <?php foreach ($homeCounters as $counter): ?>

                <div class="home-counter">
                    <h2><?= htmlspecialchars($counter['counter_number']) ?><?= htmlspecialchars($counter['counter_suffix']) ?></h2>

                    <p>
                        <?= htmlspecialchars($counter['counter_label']) ?>
                    </p>
                </div>

            <?php endforeach; ?>

        </div>

    </div>

    </section>

    <!-- About Section -->
    <section id="about" class="about-section">

        <div class="container">
            <div class="row align-items-center g-5">

                <!-- Big Image -->
                <div class="col-lg-6">

                    <div class="about-images">

                        <div class="about-main-image">
                            <?php if (!empty($about['main_image'])): ?>
                                <img
                                    src="<?= htmlspecialchars($about['main_image']) ?>"
                                    alt="Construction work"
                                >
                            <?php endif; ?>
                        </div>

                        <div class="about-second-image">
                            <?php if (!empty($about['second_image'])): ?>
                                <img
                                    src="<?= htmlspecialchars($about['second_image']) ?>"
                                    alt="Construction project"
                                >
                            <?php endif; ?>
                        </div>

                        <div class="experience-badge">
                            <h3><?= htmlspecialchars($about['experience_number']) ?></h3>
                            <span><?= htmlspecialchars($about['experience_text']) ?></span>
                        </div>

                    </div>

                </div>

                <!-- Small Image -->
                <div class="col-lg-6">

                    <div class="about-content">

                        <p class="section-small-heading">
                            <i data-lucide="building-2"></i>
                            <?= htmlspecialchars($about['small_heading']) ?>
                        </p>

                        <h2 class="about-title">
                            <?= htmlspecialchars($about['title']) ?>
                        </h2>

                        <p class="about-description">
                            <?= htmlspecialchars($about['description']) ?>
                        </p>

                        <!-- About Features -->
                        <div class="row g-4 about-features">

                            <?php foreach ($aboutFeatures as $feature): ?>

                                <div class="col-md-6">

                                    <div class="about-feature">

                                        <div class="feature-icon">
                                            <i data-lucide="<?= htmlspecialchars($feature['icon']) ?>"></i>
                                        </div>

                                        <h3>
                                            <?= htmlspecialchars($feature['title']) ?>
                                        </h3>

                                        <p>
                                            <?= htmlspecialchars($feature['description']) ?>
                                        </p>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                        <!-- Buttons -->
                        <div class="about-buttons">

                            <button type="button" class="about-btn-primary">
                                <?= htmlspecialchars($about['first_button_text']) ?>
                                <i data-lucide="arrow-right"></i>
                            </button>

                            <a href="#projects" class="about-btn-secondary">
                                <?= htmlspecialchars($about['second_button_text']) ?>
                            </a>

                        </div>

                    </div>

                </div>

            </div>
        </div>

    </section>

    <!-- Services Section -->
    <section id="services" class="services-section">

        <div class="container">

            <!-- Heading -->
            <div class="services-heading">
                <h2>
                    <?= htmlspecialchars($servicesSection['title']) ?>
                </h2>

                <div class="heading-line"></div>

                <p>
                    <?= htmlspecialchars($servicesSection['description']) ?>
                </p>
            </div>

            <div class="row g-4">

                <!-- Image -->
                <div class="col-lg-4">

                    <div class="services-image-card">

                        <?php if (!empty($servicesSection['main_image'])): ?>
                            <img
                                src="<?= htmlspecialchars($servicesSection['main_image']) ?>"
                                alt="Construction services"
                            >
                        <?php endif; ?>

                        <div class="services-image-content">

                            <h3>
                                <?= htmlspecialchars($servicesSection['image_title']) ?>
                            </h3>

                            <span class="services-image-link">
                                <?= htmlspecialchars($servicesSection['image_button_text']) ?>
                                <i data-lucide="arrow-right"></i>
                            </span>

                        </div>

                    </div>

                </div>

                <!-- Service Cards -->
                <div class="col-lg-8">

                    <div class="row g-4">

                        <?php foreach ($services as $service): ?>

                            <div class="col-md-6">

                                <div class="service-card">

                                    <div class="service-icon">
                                        <i data-lucide="<?= htmlspecialchars($service['icon']) ?>"></i>
                                    </div>

                                    <div class="service-info">

                                        <h3>
                                            <?= htmlspecialchars($service['title']) ?>
                                        </h3>

                                        <p>
                                            <?= htmlspecialchars($service['description']) ?>
                                        </p>

                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                </div>

            </div>

        </div>

    </section>

    <!-- Statistics Section -->
    <section id="statistics" class="statistics-section">

        <div class="container">
            <div class="row align-items-center g-5">

                <div class="col-lg-5">

                    <div class="statistics-content">

                        <p class="statistics-small-heading">
                            <?= htmlspecialchars($statisticsSection['small_heading']) ?>
                        </p>

                        <h2>
                            <?= htmlspecialchars($statisticsSection['title']) ?>
                        </h2>

                        <p class="statistics-description">
                            <?= htmlspecialchars($statisticsSection['description']) ?>
                        </p>

                        <a href="#projects" class="statistics-link">
                            <?= htmlspecialchars($statisticsSection['button_text']) ?>
                            <i data-lucide="arrow-right"></i>
                        </a>

                    </div>

                </div>

                <!-- Counter Cards -->
                <div class="col-lg-7">

                    <div class="row g-4">

                        <?php foreach ($statistics as $counter): ?>

                            <div class="col-sm-6">

                                <div class="statistics-card">

                                    <div class="statistics-icon">
                                        <i data-lucide="<?= htmlspecialchars($counter['icon']) ?>"></i>
                                    </div>

                                    <div class="statistics-info">

                                        <h3><?= htmlspecialchars($counter['counter_number']) ?><?= htmlspecialchars($counter['counter_suffix']) ?></h3>

                                        <p>
                                            <?= htmlspecialchars($counter['counter_label']) ?>
                                        </p>

                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                </div>

            </div>
        </div>

    </section>

    <!-- Projects Section -->
    <section id="projects" class="projects-section">

        <div class="container">

            <div class="projects-heading">
                <h2>
                    <?= htmlspecialchars($projectsSection['title']) ?>
                </h2>

                <div class="heading-line"></div>

                <p>
                    <?= htmlspecialchars($projectsSection['description']) ?>
                </p>
            </div>

            <!-- Category Filters -->
            <div class="project-filters">

                <button type="button" class="filter-btn active" data-filter="all">
                    All Projects
                </button>

                <button type="button" class="filter-btn" data-filter="Residential">
                    Residential
                </button>

                <button type="button" class="filter-btn" data-filter="Commercial">
                    Commercial
                </button>

                <button type="button" class="filter-btn" data-filter="Infrastructure">
                    Infrastructure
                </button>

            </div>

            <!-- Cards -->
            <div class="row g-4 projects-grid">

                <?php foreach ($projects as $project): ?>

                    <div class="col-lg-4 col-md-6 project-item"
                        data-category="<?= htmlspecialchars($project['category']) ?>">

                        <div class="project-card">

                            <?php if (!empty($project['cover_image'])): ?>

                                <img
                                    src="<?= htmlspecialchars($project['cover_image']) ?>"
                                    alt="<?= htmlspecialchars($project['title']) ?>"
                                    class="project-cover"
                                >

                            <?php endif; ?>

                            <div class="project-overlay">

                                <div class="project-details">

                                    <span class="project-category">
                                        <?= htmlspecialchars($project['category']) ?>
                                    </span>

                                    <h3>
                                        <?= htmlspecialchars($project['title']) ?>
                                    </h3>

                                    <p>
                                        <?= htmlspecialchars($project['description']) ?>
                                    </p>

                                </div>

                                <div class="project-actions">

                                    <!-- Open Project Gallery -->
                                    <button
                                        type="button"
                                        class="project-action-btn open-gallery"
                                        data-project="<?= (int) $project['id'] ?>"
                                        aria-label="View project gallery"
                                    >
                                        <i data-lucide="expand"></i>
                                    </button>

                                    <!-- Project Details Visual Only -->
                                    <span class="project-action-btn project-details-icon">
                                        <i data-lucide="arrow-right"></i>
                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>

    </section>

    <!-- Project Gallery Lightbox -->
    <div id="projectLightbox" class="project-lightbox" role="dialog"
        aria-modal="true" aria-label="Project image gallery" aria-hidden="true">

        <button type="button" class="lightbox-close" aria-label="Close gallery">
            <i data-lucide="x"></i>
        </button>

        <button type="button" class="lightbox-prev" aria-label="Previous image">
            <i data-lucide="chevron-left"></i>
        </button>

        <div class="lightbox-content">

            <img id="lightboxImage" src="" alt="Project gallery image">

            <div class="lightbox-footer">
                <button type="button" id="fullscreenBtn">
                    Fullscreen
                    <i data-lucide="maximize"></i>
                </button>
            </div>

        </div>

        <button type="button" id="exitFullscreenBtn" aria-label="Exit fullscreen">
            <i data-lucide="x"></i>
        </button>

        <button type="button" class="lightbox-next" aria-label="Next image">
            <i data-lucide="chevron-right"></i>
        </button>

    </div>

    <script id="projectGalleryData" type="application/json">
    <?= json_encode(
        $projectImages,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE
    ) ?>
    </script>

    <!-- Back to Top -->
    <button type="button" id="backToTop" aria-label="Back to top">
        <i data-lucide="arrow-up"></i>
    </button>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="assets/js/main.js"></script>

</body>
</html>
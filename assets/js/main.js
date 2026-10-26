document.addEventListener("DOMContentLoaded", function () {
  // Initialize Lucide icons
  lucide.createIcons();

  // Project Category Filtering
  const filterButtons = document.querySelectorAll(".filter-btn");
  const projectItems = document.querySelectorAll(".project-item");

  filterButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      const selectedCategory = button.dataset.filter;

      // Update the active filter button
      filterButtons.forEach(function (btn) {
        btn.classList.remove("active");
      });

      button.classList.add("active");

      // Show or hide matching projects
      projectItems.forEach(function (project) {
        const matches =
          selectedCategory === "all" ||
          selectedCategory === project.dataset.category;

        project.classList.toggle("hidden", !matches);
      });
    });
  });

  // Project Image Gallery
  const galleryData = JSON.parse(
    document.getElementById("projectGalleryData").textContent,
  );

  const lightbox = document.getElementById("projectLightbox");
  const lightboxImage = document.getElementById("lightboxImage");

  const closeButton = document.querySelector(".lightbox-close");
  const previousButton = document.querySelector(".lightbox-prev");
  const nextButton = document.querySelector(".lightbox-next");

  const fullscreenBtn = document.getElementById("fullscreenBtn");
  const exitFullscreenBtn = document.getElementById("exitFullscreenBtn");

  let currentImages = [];
  let currentIndex = 0;

  // Display the selected gallery image
  function showImage() {
    lightboxImage.src = currentImages[currentIndex].image_path;
  }

  // Close the image gallery
  function closeGallery() {
    if (document.fullscreenElement) {
      document.exitFullscreen();
    }

    lightbox.classList.remove("open", "fullscreen-mode");
    lightbox.setAttribute("aria-hidden", "true");

    lightboxImage.removeAttribute("src");

    document.body.style.overflow = "";
  }

  // Open a project's gallery
  document.querySelectorAll(".open-gallery").forEach(function (button) {
    button.addEventListener("click", function () {
      const projectId = Number(button.dataset.project);

      // Find images belonging to the selected project
      currentImages = galleryData.filter(function (image) {
        return Number(image.project_id) === projectId;
      });

      if (currentImages.length === 0) {
        return;
      }

      currentIndex = 0;

      showImage();

      lightbox.classList.add("open");
      lightbox.setAttribute("aria-hidden", "false");

      document.body.style.overflow = "hidden";

      // Hide navigation arrows if there is only one image
      const hasMultipleImages = currentImages.length > 1;

      previousButton.style.display = hasMultipleImages ? "flex" : "none";
      nextButton.style.display = hasMultipleImages ? "flex" : "none";

      closeButton.focus();
    });
  });

  // Next gallery image
  nextButton.addEventListener("click", function () {
    currentIndex = (currentIndex + 1) % currentImages.length;

    showImage();
  });

  // Previous gallery image
  previousButton.addEventListener("click", function () {
    currentIndex =
      (currentIndex - 1 + currentImages.length) % currentImages.length;

    showImage();
  });

  // Close gallery using the close button
  closeButton.addEventListener("click", closeGallery);

  // Close gallery by clicking outside the image
  lightbox.addEventListener("click", function (event) {
    if (event.target === lightbox) {
      closeGallery();
    }
  });

  // Gallery keyboard controls
  document.addEventListener("keydown", function (event) {
    if (!lightbox.classList.contains("open")) {
      return;
    }

    if (event.key === "Escape" && !document.fullscreenElement) {
      closeGallery();
    }

    if (event.key === "ArrowRight" && currentImages.length > 1) {
      nextButton.click();
    }

    if (event.key === "ArrowLeft" && currentImages.length > 1) {
      previousButton.click();
    }
  });

  // Fullscreen Gallery
  fullscreenBtn.addEventListener("click", async function () {
    try {
      await lightbox.requestFullscreen();

      lightbox.classList.add("fullscreen-mode");
    } catch (error) {
      console.error("Fullscreen could not be opened:", error);
    }
  });

  // Exit fullscreen mode
  exitFullscreenBtn.addEventListener("click", function () {
    if (document.fullscreenElement) {
      document.exitFullscreen();
    }
  });

  // Update gallery styling when fullscreen changes
  document.addEventListener("fullscreenchange", function () {
    if (!document.fullscreenElement) {
      lightbox.classList.remove("fullscreen-mode");
    }
  });

  // Back to Top Button
  const backToTop = document.getElementById("backToTop");

  window.addEventListener("scroll", function () {
    backToTop.classList.toggle("show", window.scrollY > 300);
  });

  backToTop.addEventListener("click", function () {
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  });

  // Active Navbar Link
  const navLinks = document.querySelectorAll('.navbar .nav-link[href^="#"]');

  const navSections = document.querySelectorAll(
    "#home, #about, #services, #statistics, #projects",
  );

  function updateActiveLink() {
    let currentSection = "";

    const scrollPosition = window.scrollY + 150;

    // Determine the section currently being viewed
    navSections.forEach(function (section) {
      if (scrollPosition >= section.offsetTop) {
        currentSection = section.id;
      }
    });

    // Highlight the corresponding navbar link
    navLinks.forEach(function (link) {
      const isActive = link.getAttribute("href") === "#" + currentSection;

      link.classList.toggle("active", isActive);
    });
  }

  window.addEventListener("scroll", updateActiveLink);

  // Set the correct active link when the page first loads
  updateActiveLink();
});

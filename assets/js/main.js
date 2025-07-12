window.addEventListener("load", () => {
  const pageLoader = document.getElementById("pageLoader");
  if (pageLoader) {
    pageLoader.style.transition = "opacity 0.5s ease";
    pageLoader.style.opacity = "0";
    setTimeout(() => {
      pageLoader.remove();
    }, 500);
  }
});

(() => {
  const storedTheme = localStorage.getItem("theme");
  const getPreferredTheme = () => {
    if (storedTheme) {
      return storedTheme;
    }
    return window.matchMedia("(prefers-color-scheme: dark)").matches
      ? "dark"
      : "light";
  };

  const setTheme = function (theme) {
    if (theme === "auto") {
      document.documentElement.removeAttribute("data-bs-theme");
    } else {
      document.documentElement.setAttribute("data-bs-theme", theme);
    }
  };

  // Initialize
  setTheme(getPreferredTheme());

  document.querySelectorAll("[data-theme-value]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const theme = btn.getAttribute("data-theme-value");
      localStorage.setItem("theme", theme);
      setTheme(theme);
    });
  });

  // Auto-update if OS theme changes and 'auto' is selected
  window
    .matchMedia("(prefers-color-scheme: dark)")
    .addEventListener("change", () => {
      if (localStorage.getItem("theme") === "auto") {
        setTheme(getPreferredTheme());
      }
    });
})();
// Scroll to top button functionality
const btn = document.getElementById("scrollToTopBtn");
window.addEventListener("scroll", () => {
  const nearBottom =
    window.innerHeight + window.scrollY >= document.body.offsetHeight - 500;
  btn.classList.toggle("d-none", !nearBottom);
});

document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll("img").forEach(function (img) {
    img.addEventListener("error", function () {
      img.src = "/eduvault/uploads/avatars/default.png";
    });
  });
  const sortSelect = document.querySelector('select[name="sort"]');
  if (sortSelect) {
    sortSelect.addEventListener("change", function () {
      this.form.submit();
    });
  }
  const toastElements = document.querySelectorAll(".toast");
  toastElements.forEach(function (toastEl) {
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
  });
  const toggleViewBtn = document.getElementById("toggleViewBtn");
  if (toggleViewBtn) {
    toggleViewBtn.addEventListener("click", function () {
      var grid = document.getElementById("bookmarksGrid");
      var table = document.getElementById("bookmarksTable");
      if (grid.classList.contains("d-none")) {
        grid.classList.remove("d-none");
        table.classList.add("d-none");
        this.innerHTML = '<i class="fas fa-th"></i> Toggle View';
      } else {
        grid.classList.add("d-none");
        table.classList.remove("d-none");
        this.innerHTML = '<i class="fas fa-list"></i> Toggle View';
      }
    });
  }
  // Mobile filter toggle
  const filterToggle = document.querySelector(".filter-toggle");
  const filtersCollapse = document.querySelector(".filters-collapse");

  if (filterToggle && filtersCollapse) {
    filterToggle.addEventListener("click", function () {
      filtersCollapse.classList.toggle("show");
    });
  }

  // File Preview Modal Logic
  document.querySelectorAll(".btn-preview-file").forEach(function (btn) {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      var fileSlug = btn.getAttribute("data-file-slug");
      var fileType = btn.getAttribute("data-file-type");
      var fileTitle = btn.getAttribute("data-file-title");
      var filePath = btn.getAttribute("data-file-path");
      var modalLabel = document.getElementById("filePreviewModalLabel");
      var modalBody = document.getElementById("filePreviewBody");
      if (modalLabel) modalLabel.textContent = fileTitle || "File Preview";
      if (modalBody) {
        modalBody.innerHTML =
          '<div class="text-center text-muted py-5">Loading preview...</div>';
        // PDF Preview (using PDF.js)
        if (fileType === "pdf") {
          modalBody.innerHTML =
            '<div id="pdfViewer" style="height:70vh;"></div>';
          // Load PDF.js if not loaded
          if (!window.pdfjsLib) {
            var script = document.createElement("script");
            script.src =
              "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.2.67/pdf.min.js";
            script.onload = function () {
              renderPDF(filePath, "pdfViewer");
            };
            document.body.appendChild(script);
          } else {
            renderPDF(filePath, "pdfViewer");
          }
        } else if (
          ["jpg", "jpeg", "png", "gif", "bmp", "webp"].includes(fileType)
        ) {
          // Images: Use slug if available
          if (fileSlug) {
            modalBody.innerHTML =
              '<img src="/eduvault/files/image_proxy.php?slug=' +
              encodeURIComponent(fileSlug) +
              '" class="img-fluid rounded mx-auto d-block" style="max-height:70vh;" alt="Image Preview">';
          } else if (filePath) {
            modalBody.innerHTML =
              '<img src="' +
              filePath +
              '" class="img-fluid rounded mx-auto d-block" style="max-height:70vh;" alt="Image Preview">';
          } else {
            modalBody.innerHTML =
              '<div class="text-center text-danger py-5">Image preview not available.</div>';
          }
        } else if (["txt", "csv", "md"].includes(fileType)) {
          // Text: Use iframe for raw file
          modalBody.innerHTML =
            '<iframe src="' +
            filePath +
            '" style="width:100%;height:70vh;border:none;"></iframe>';
        } else if (
          ["doc", "docx", "ppt", "pptx", "xls", "xlsx"].includes(fileType)
        ) {
          // MS Office: Use iframe with Google Docs Viewer
          modalBody.innerHTML =
            '<iframe src="https://docs.google.com/gview?url=' +
            encodeURIComponent(
              window.location.origin +
                "/" +
                filePath.replace(/^\.\./, "eduvault")
            ) +
            '&embedded=true" style="width:100%;height:70vh;border:none;"></iframe>';
        } else {
          modalBody.innerHTML =
            '<div class="text-center text-muted py-5">Preview not available for this file type.</div>';
        }
      }
      var modal = new bootstrap.Modal(
        document.getElementById("filePreviewModal")
      );
      modal.show();
      // Accessibility: Move focus back to the triggering button when modal closes
      var filePreviewModal = document.getElementById("filePreviewModal");
      if (filePreviewModal) {
        filePreviewModal.addEventListener(
          "hidden.bs.modal",
          function handler() {
            btn.focus();
            filePreviewModal.removeEventListener("hidden.bs.modal", handler);
          }
        );
      }
    });
  });

  // Bookmark button logic
  document.querySelectorAll(".btn-bookmark-file").forEach(function (btn) {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      var fileId = btn.getAttribute("data-file-id");
      fetch("/eduvault/files/bookmark.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "file_id=" + encodeURIComponent(fileId),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            var icon = btn.querySelector("i");
            if (data.bookmarked) {
              icon.classList.remove("far");
              icon.classList.add("fas");
              btn.title = "Remove Bookmark";
            } else {
              icon.classList.remove("fas");
              icon.classList.add("far");
              btn.title = "Add Bookmark";
            }
          } else if (data.error) {
            alert(data.error);
          }
        })
        .catch(() => alert("Failed to update bookmark."));
    });
  });

  // Tab switching functionality
  const tabLinks = document.querySelectorAll(".tab-link");
  const tabContents = document.querySelectorAll(".tab-content");
  if (tabLinks.length === 0 || tabContents.length === 0) return;
  tabLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();

      // Remove active class from all tabs
      tabLinks.forEach((l) => l.classList.remove("active"));
      tabContents.forEach((c) => (c.style.display = "none"));

      // Add active class to clicked tab
      this.classList.add("active");

      // Show corresponding content
      const targetId = this.getAttribute("data-tab");
      document.getElementById(targetId).style.display = "block";
    });
  });
});

// PDF.js render function
function renderPDF(url, containerId) {
  if (!window.pdfjsLib) return;
  pdfjsLib.GlobalWorkerOptions.workerSrc =
    "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.2.67/pdf.worker.min.js";
  var container = document.getElementById(containerId);
  if (!container) return;
  pdfjsLib
    .getDocument(url)
    .promise.then(function (pdf) {
      container.innerHTML = "";
      for (let pageNum = 1; pageNum <= Math.min(pdf.numPages, 5); pageNum++) {
        // Limit to 5 pages for preview
        pdf.getPage(pageNum).then(function (page) {
          var viewport = page.getViewport({ scale: 1.2 });
          var canvas = document.createElement("canvas");
          canvas.className = "mb-3 shadow-sm";
          canvas.height = viewport.height;
          canvas.width = viewport.width;
          container.appendChild(canvas);
          var renderContext = {
            canvasContext: canvas.getContext("2d"),
            viewport: viewport,
          };
          page.render(renderContext);
        });
      }
      if (pdf.numPages > 5) {
        var more = document.createElement("div");
        more.className = "text-center text-muted";
        more.innerHTML = "Preview limited to first 5 pages.";
        container.appendChild(more);
      }
    })
    .catch(function () {
      container.innerHTML =
        '<div class="text-danger text-center">Failed to load PDF preview.</div>';
    });
}

let googleClient;

window.onload = function () {
  if (typeof google === "undefined" || !google.accounts) {
    return;
  }
  googleClient = google.accounts.id.initialize({
    client_id:
      "982609216899-e94n99lb6b4mi9n1gdbs395at8lrt6hc.apps.googleusercontent.com",
    callback: handleGoogleResponse,
    ux_mode: "popup",
  });

  document
    .getElementById("google-login-btn")
    .addEventListener("click", function () {
      google.accounts.id.prompt((notification) => {
        if (notification.isNotDisplayed() || notification.isSkippedMoment()) {
          console.warn(
            "Popup Sign-In failed or was skipped, falling back to redirect mode"
          );

          google.accounts.id.initialize({
            client_id:
              "982609216899-e94n99lb6b4mi9n1gdbs395at8lrt6hc.apps.googleusercontent.com",
            callback: handleGoogleResponse,
            ux_mode: "redirect",
            login_uri: "http://localhost/eduvault/auth/google-callback.php",
          });

          google.accounts.id.prompt();
        }
      });
    });
};

function handleGoogleResponse(response) {
  const token = response.credential;

  const form = document.createElement("form");
  form.method = "POST";
  form.action = "google-callback.php";

  const input = document.createElement("input");
  input.type = "hidden";
  input.name = "credential";
  input.value = token;

  form.appendChild(input);
  document.body.appendChild(form);
  form.submit();
}

const sidebar = document.getElementById("dashboardSidebar");
const toggleBtn = document.getElementById("sidebarToggle");
const backdrop = document.getElementById("sidebarBackdrop");
const passwordinp = document.getElementById("passwordInput");
if (toggleBtn && sidebar && backdrop) {
  toggleBtn.addEventListener("click", function () {
    sidebar.classList.toggle("show");
    backdrop.style.display = sidebar.classList.contains("show")
      ? "block"
      : "none";
  });
  backdrop.addEventListener("click", function () {
    sidebar.classList.remove("show");
    backdrop.style.display = "none";
  });
}

if (passwordinp) {
  const togglePasswordBtn = document.getElementById("togglePassword");
  if (togglePasswordBtn) {
    togglePasswordBtn.addEventListener("click", togglePasswordVisibility);
  }
}

function togglePasswordVisibility() {
  const type =
    passwordinp.getAttribute("type") === "password" ? "text" : "password";
  passwordinp.setAttribute("type", type);

  // Toggle eye icon
  this.querySelector("i").classList.toggle("fa-eye");
  this.querySelector("i").classList.toggle("fa-eye-slash");
}

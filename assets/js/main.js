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

// Utility: Initialize all tooltips
function initializeTooltips() {
  document
    .querySelectorAll('[data-bs-toggle="tooltip"]')
    .forEach(function (el) {
      new bootstrap.Tooltip(el);
    });
}

// Utility: Generalized toggle view (table/grid)
function initializeToggleView(toggleBtnId, tableId, gridId) {
  var toggleBtn = document.getElementById(toggleBtnId);
  var table = document.getElementById(tableId);
  var grid = document.getElementById(gridId);
  if (!toggleBtn || !table || !grid) {
    return;
  }

  function setViewByScreen() {
    if (window.innerWidth < 768) {
      table.classList.add("d-none");
      grid.classList.remove("d-none");
    } else {
      table.classList.remove("d-none");
      grid.classList.add("d-none");
    }
  }

  setViewByScreen();
  toggleBtn.addEventListener("click", function () {
    table.classList.toggle("d-none");
    grid.classList.toggle("d-none");
  });
  window.addEventListener("resize", setViewByScreen);
}

// Utility: Generalized tab switching
function initializeTabs(tabLinkSelector, tabContentSelector) {
  const tabLinks = document.querySelectorAll(tabLinkSelector);
  const tabContents = document.querySelectorAll(tabContentSelector);
  if (tabLinks.length === 0 || tabContents.length === 0) return;
  tabLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      tabLinks.forEach((l) => l.classList.remove("active"));
      tabContents.forEach((c) => (c.style.display = "none"));
      this.classList.add("active");
      const targetId = this.getAttribute("data-tab");
      document.getElementById(targetId).style.display = "block";
    });
  });
}

// Utility: Generalized image fallback
function initializeImageFallback() {
  document.querySelectorAll("img").forEach(function (img) {
    img.addEventListener("error", function () {
      img.src = "/eduvault/uploads/avatars/default.png";
    });
  });
}

// Utility: Generalized sort select
function initializeSortSelect() {
  const sortSelect = document.querySelector('select[name="sort"]');
  if (sortSelect) {
    sortSelect.addEventListener("change", function () {
      this.form.submit();
    });
  }
}

// Utility: Generalized scroll to top
function initializeScrollToTop(btnId) {
  const btn = document.getElementById(btnId);
  if (!btn) return;
  window.addEventListener("scroll", () => {
    const nearBottom =
      window.innerHeight + window.scrollY >= document.body.offsetHeight - 500;
    btn.classList.toggle("d-none", !nearBottom);
  });
}

// Utility: Generalized filter toggle
function initializeFilterToggle(toggleSelector, collapseSelector) {
  const filterToggle = document.querySelector(toggleSelector);
  const filtersCollapse = document.querySelector(collapseSelector);
  if (filterToggle && filtersCollapse) {
    filterToggle.addEventListener("click", function () {
      filtersCollapse.classList.toggle("show");
    });
  }
}

// Utility: Generalized bookmark button logic
function initializeBookmarkButtons() {
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
}

// Utility: Generalized password toggle
function initializePasswordToggle(inputId, toggleBtnId) {
  const passwordinp = document.getElementById(inputId);
  const togglePasswordBtn = document.getElementById(toggleBtnId);
  if (passwordinp && togglePasswordBtn) {
    togglePasswordBtn.addEventListener("click", function () {
      const type =
        passwordinp.getAttribute("type") === "password" ? "text" : "password";
      passwordinp.setAttribute("type", type);
      this.querySelector("i").classList.toggle("fa-eye");
      this.querySelector("i").classList.toggle("fa-eye-slash");
    });
  }
}

// Utility: Generalized sidebar toggle
function initializeSidebarToggle(sidebarId, toggleBtnId, backdropId) {
  const sidebar = document.getElementById(sidebarId);
  const toggleBtn = document.getElementById(toggleBtnId);
  const backdrop = document.getElementById(backdropId);
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
}

// DOMContentLoaded: Initialize all features

document.addEventListener("DOMContentLoaded", function () {
  // Show all toasts rendered in the DOM (e.g., from PHP flash messages)
  document.querySelectorAll(".toast").forEach(function (toastEl) {
    var toast = new bootstrap.Toast(toastEl);
    toast.show();
  });

  initializeTooltips();
  initializeImageFallback();
  initializeSortSelect();
  initializeScrollToTop("scrollToTopBtn");
  initializeFilterToggle(".filter-toggle", ".filters-collapse");
  initializeBookmarkButtons();
  initializeTabs(".tab-link", ".tab-content");
  initializeToggleView("toggleViewBtn", "bookmarksTable", "bookmarksGrid");
  initializeToggleView("toggleUploadsViewBtn", "uploadsTable", "uploadsGrid");
  initializePasswordToggle("passwordInput", "togglePassword");
  initializeSidebarToggle(
    "dashboardSidebar",
    "sidebarToggle",
    "sidebarBackdrop"
  );

  // File Preview Modal Logic (keep as is, modular)
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
        if (fileType === "pdf") {
          modalBody.innerHTML =
            '<div id="pdfViewer" style="height:70vh;"></div>';
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
          modalBody.innerHTML =
            '<iframe src="' +
            filePath +
            '" style="width:100%;height:70vh;border:none;"></iframe>';
        } else if (
          ["doc", "docx", "ppt", "pptx", "xls", "xlsx"].includes(fileType)
        ) {
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

  // Notification system
  if (
    document.querySelector(".mark-read-btn") ||
    document.querySelector(".mark-all-read-btn")
  ) {
    initializeNotifications();
  }

  // Confirmation for deleting old notifications
  var deleteOldForm = document.getElementById("deleteOldNotificationsForm");
  if (deleteOldForm) {
    deleteOldForm.addEventListener("submit", function (e) {
      if (
        !confirm(
          "Are you sure you want to delete all read notifications older than 30 days? This action cannot be undone."
        )
      ) {
        e.preventDefault();
      }
    });
  }
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

// Notification system functionality
function initializeNotifications() {
  // Mark individual notification as read
  document.querySelectorAll(".mark-read-btn").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      const notificationId = this.dataset.notificationId;
      const notificationItem = this.closest(".list-group-item");

      fetch("/eduvault/handlers/notification_handler.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `action=mark_read&notification_id=${notificationId}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            notificationItem.classList.remove("bg-light");
            notificationItem
              .querySelector(".notification-title")
              .classList.remove("fw-bold");
            notificationItem
              .querySelector(".notification-title")
              .classList.add("text-muted");
            this.remove();

            updateUnreadCount();
          } else {
            showToast("Error: " + data.message, danger);
          }
        })
        .catch((error) => {
          showToast(
            "An error occurred while marking notification as read.",
            danger
          );
        });
    });
  });

  // Mark all as read
  const markAllBtn = document.querySelector(".mark-all-read-btn");
  if (markAllBtn) {
    markAllBtn.addEventListener("click", function (e) {
      e.preventDefault();

      fetch("/eduvault/handlers/notification_handler.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "action=mark_all_read",
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            document.querySelectorAll(".list-group-item").forEach((item) => {
              item.classList.remove("bg-light");
              const title = item.querySelector(".notification-title");
              if (title) {
                title.classList.remove("fw-bold");
                title.classList.add("text-muted");
              }
            });

            document
              .querySelectorAll(".mark-read-btn")
              .forEach((btn) => btn.remove());

            this.style.display = "none";

            updateUnreadCount();

            showToast("All notifications marked as read", "success");
          } else {
            showToast("Error: " + data.message, "danger");
          }
        })
        .catch((error) => {
          showToast(
            "An error occurred while marking all notifications as read.",
            "danger"
          );
        });
    });
  }
}

function updateUnreadCount() {
  fetch("/eduvault/handlers/notification_handler.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "action=get_unread_count",
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const headerBadge = document.querySelector(".navbar .badge");
        if (headerBadge) {
          if (data.count > 0) {
            headerBadge.textContent = data.count;
            headerBadge.style.display = "block";
          } else {
            headerBadge.style.display = "none";
          }
        }

        const pageBadge = document.querySelector(".page-title .badge");
        if (pageBadge) {
          if (data.count > 0) {
            pageBadge.textContent = data.count;
            pageBadge.style.display = "inline-block";
          } else {
            pageBadge.style.display = "none";
          }
        }
      }
    })
    .catch((error) => console.error("Error updating unread count:", error));
}

function showToast(message, type = "info") {
  const toast = document.createElement("div");
  toast.className = `toast align-items-center text-white bg-${type} border-0 position-fixed`;
  toast.style.cssText = "top: 20px; right: 20px; z-index: 9999;";
  toast.setAttribute("role", "alert");
  toast.setAttribute("aria-live", "assertive");
  toast.setAttribute("aria-atomic", "true");

  toast.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">${message}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  `;

  document.body.appendChild(toast);

  const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
  bsToast.show();

  toast.addEventListener("hidden.bs.toast", () => {
    document.body.removeChild(toast);
  });
}

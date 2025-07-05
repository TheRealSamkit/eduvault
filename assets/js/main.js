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
document.addEventListener("DOMContentLoaded", function () {
	const toastElements = document.querySelectorAll(".toast");
	toastElements.forEach(function (toastEl) {
		const toast = new bootstrap.Toast(toastEl);
		toast.show();
	});
});
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
			img.src = "../uploads/avatars/default.png";
		});
	});
});

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

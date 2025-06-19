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

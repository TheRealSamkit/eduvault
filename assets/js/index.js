// Enhanced Index Page Scripts with Dark Theme Utility

// Dark Theme Utility Functions
const ThemeManager = {
    // Get current theme
    getCurrentTheme: function() {
        return localStorage.getItem('theme') || 'light';
    },

    // Set theme
    setTheme: function(theme) {
        localStorage.setItem('theme', theme);
        document.documentElement.setAttribute('data-theme', theme);
        this.updateThemeElements(theme);
    },

    // Toggle between light and dark theme
    toggleTheme: function() {
        const currentTheme = this.getCurrentTheme();
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        this.setTheme(newTheme);
        return newTheme;
    },

    // Update theme-specific elements
    updateThemeElements: function(theme) {
        const body = document.body;
        const isDark = theme === 'dark';
        
        // Update body classes
        body.classList.toggle('dark-theme', isDark);
        
        // Update navbar
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            navbar.classList.toggle('navbar-dark', isDark);
            navbar.classList.toggle('navbar-light', !isDark);
        }

        // Update cards and other elements
        const cards = document.querySelectorAll('.card, .stat-card, .testimonial-card');
        cards.forEach(card => {
            card.classList.toggle('bg-dark', isDark);
            card.classList.toggle('text-light', isDark);
        });

        // Update feature cards
        const featureCards = document.querySelectorAll('.feature-card');
        featureCards.forEach(card => {
            card.classList.toggle('bg-dark', isDark);
            card.classList.toggle('text-light', isDark);
        });

        // Update buttons
        const buttons = document.querySelectorAll('.btn-outline-primary');
        buttons.forEach(btn => {
            if (isDark) {
                btn.classList.add('btn-outline-light');
                btn.classList.remove('btn-outline-primary');
            } else {
                btn.classList.add('btn-outline-primary');
                btn.classList.remove('btn-outline-light');
            }
        });

        // Dispatch theme change event
        window.dispatchEvent(new CustomEvent('themeChanged', { 
            detail: { theme: theme } 
        }));
    },

    // Initialize theme on page load
    init: function() {
        const savedTheme = this.getCurrentTheme();
        this.setTheme(savedTheme);
        
        // Listen for system theme changes
        if (window.matchMedia) {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            mediaQuery.addListener((e) => {
                if (!localStorage.getItem('theme')) {
                    this.setTheme(e.matches ? 'dark' : 'light');
                }
            });
        }
    }
};



// Animated Counter Function
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');
    
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.getAttribute('data-target'));
                const duration = 2000; // 2 seconds
                const increment = target / (duration / 16); // 60fps
                let current = 0;

                const updateCounter = () => {
                    current += increment;
                    if (current >= target) {
                        counter.textContent = target.toLocaleString();
                    } else {
                        counter.textContent = Math.floor(current).toLocaleString();
                        requestAnimationFrame(updateCounter);
                    }
                };

                updateCounter();
                observer.unobserve(counter);
            }
        });
    }, observerOptions);

    counters.forEach(counter => {
        observer.observe(counter);
    });
}

// Smooth Scroll Function
function initializeSmoothScroll() {
    const scrollBtn = document.querySelector('.btn-scroll');
    if (!scrollBtn) return;
    scrollBtn.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
}

// Parallax Effect for Hero Section
function initializeParallax() {
    const heroSection = document.querySelector('.hero-section');
    if (!heroSection) return;

    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const rate = scrolled * -0.5;
        
        if (scrolled <= heroSection.offsetHeight) {
            heroSection.style.transform = `translateY(${rate}px)`;
        }
    });
}

// Card Hover Effects
function initializeCardEffects() {
    const cards = document.querySelectorAll('.feature-card, .resource-card, .testimonial-card');
    
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
}

// Navbar Scroll Effect
function initializeNavbarScroll() {
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 100) {
            navbar.classList.add('navbar-scrolled');
            navbar.style.backgroundColor = 'rgba(255, 255, 255, 0.95)';
            navbar.style.backdropFilter = 'blur(10px)';
        } else {
            navbar.classList.remove('navbar-scrolled');
            navbar.style.backgroundColor = 'white';
            navbar.style.backdropFilter = 'none';
        }
    });
}

// Search Functionality Enhancement
function initializeSearch() {
    const searchInput = document.querySelector('#quickSearch');
    if (!searchInput) return;
    
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length > 2) {
            searchTimeout = setTimeout(() => {
                performQuickSearch(query);
            }, 300);
        }
    });
}


// Main initializer after DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    // Initialize dark theme based on saved or system preference
    // ThemeManager.init();


    // Initialize utility features
    animateCounters();
    initializeSmoothScroll();
    initializeParallax();
    initializeCardEffects();
    initializeNavbarScroll();
    initializeSearch();

    // Optional: If you have a toggle button for theme
    // const themeToggleBtn = document.querySelector('#themeToggleBtn');
    // if (themeToggleBtn) {
    //     themeToggleBtn.addEventListener('click', () => {
    //         ThemeManager.toggleTheme();
    //     });
    // }
const stats = document.querySelectorAll(".stat-number");
   stats.forEach(stat => {
       const target = +stat.textContent;
       stat.textContent = "0";
       let count = 0;
       const increment = Math.ceil(target / (100 * 1000)); // Adjust speed here
       const update = () => {
           count += increment;
           if (count >= target) {
               stat.textContent = target;
           } else {
               stat.textContent = count;
               requestAnimationFrame(update);
           }
       };
       update();
   });
});

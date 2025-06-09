window.addEventListener('load', () => {
    const pageLoader = document.getElementById('pageLoader');
    if (pageLoader) {
        pageLoader.style.transition = 'opacity 0.5s ease';
        pageLoader.style.opacity = '0';
        setTimeout(() => {
            pageLoader.remove();
        }, 500);
    }
});
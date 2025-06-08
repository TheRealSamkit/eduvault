<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var toastElList = [].slice.call(document.querySelectorAll('.toast'));
        toastElList.map(function (toastEl) {
            var toast = new bootstrap.Toast(toastEl);
            toast.show();
        });
    });
</script>
</body>

</html>
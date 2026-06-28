document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('liveSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const term = this.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(function (row) {
                row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        });
    }

    document.querySelectorAll('.alert').forEach(function (el) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
            bsAlert.close();
        }, 3000);
    });
});

document.querySelectorAll('.approve, .deny').forEach(button => {
    button.addEventListener('click', function() {
        const row = this.closest('tr');
        row.querySelector('.approve').style.display = 'none';
        row.querySelector('.deny').style.display = 'none';
        row.querySelector('.delete').style.display = 'inline-block';
    });
});

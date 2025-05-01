document.addEventListener('DOMContentLoaded', () => {
    const editButtons = document.querySelectorAll('.edit-action');
    const editModal = document.getElementById('editAccountModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');

    // Open modal and populate data
    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            const row = this.closest('tr');
            const userId = row.querySelector('.delete-action').getAttribute('data-user-id');
            const fullName = row.querySelector('td:nth-child(2)').innerText;
            const gender = row.querySelector('td:nth-child(3)').innerText;
            const contactNo = row.querySelector('td:nth-child(4)').innerText;
            const role = row.querySelector('td:nth-child(5)').innerText;

            // Populate modal fields
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_full_name').value = fullName;
            document.getElementById('edit_gender').value = gender;
            document.getElementById('edit_contact_no').value = contactNo;
            document.getElementById('edit_role').value = role;

            // Open modal
            editModal.style.display = 'block';
        });
    });

    // Close modal
    const closeModal = () => {
        editModal.style.display = 'none';
    };

    closeModalBtn.addEventListener('click', closeModal);
    cancelEditBtn.addEventListener('click', closeModal);

    // Close modal on clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === editModal) {
            closeModal();
        }
    });
});
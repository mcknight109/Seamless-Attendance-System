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


// Add event listener to all delete buttons
document.querySelectorAll('.delete-action').forEach(button => {
    button.addEventListener('click', function() {
        // Get the user_id from the data attribute
        const userId = this.closest('tr').querySelector('.delete-action').getAttribute('data-user-id');
        
        // Confirm deletion
        if (confirm("Are you sure you want to delete this user?")) {
            // Make AJAX request to delete the user
            fetch('delete_user_handler.php', {
                method: 'POST',
                body: new URLSearchParams({
                    'user_id': userId
                }),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User deleted successfully.');
                    // Optionally, reload the page or remove the row from the table
                    location.reload();
                } else {
                    alert('Failed to delete user. Please try again.');
                }
            });
        }
    });
});


document.addEventListener('DOMContentLoaded', function () {
    const addAccountForm = document.getElementById('addAccountForm');

    // Full name validation (no special characters except spaces)
    const fullNameInput = document.getElementById('full_name');
    fullNameInput.addEventListener('input', function () {
        this.value = this.value.replace(/[^a-zA-Z\s]/g, '');  // Allow only alphabets and spaces
    });

    // Email validation (ensure valid email format)
    const emailInput = document.getElementById('email');
    emailInput.addEventListener('input', function () {
        this.value = this.value.replace(/[^a-zA-Z0-9@._-]/g, '');  // Allow only valid email characters
    });

    // Password validation (only 8 characters)
    const passwordInput = document.getElementById('password');
    passwordInput.addEventListener('input', function () {
        if (this.value.length > 8) {
            this.value = this.value.slice(0, 8);  // Limit password to 8 characters
        }
    });

    // Contact Number validation (only numbers and max 11 digits)
    const contactNoInput = document.getElementById('contact_no');
    contactNoInput.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');  // Allow only numbers
        if (this.value.length > 11) {
            this.value = this.value.slice(0, 11);  // Limit to 11 digits
        }
    });

    // Submit form with additional validations
    addAccountForm.addEventListener('submit', function (event) {
        let valid = true;

        // Validate Full Name (ensure no special characters except spaces)
        if (!/^[a-zA-Z\s]+$/.test(fullNameInput.value)) {
            alert("Full Name should only contain letters and spaces.");
            valid = false;
        }

        // Validate Email (basic email validation using regex)
        if (!/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(emailInput.value)) {
            alert("Please enter a valid email address.");
            valid = false;
        }

        // Validate Password (only 8 characters)
        if (passwordInput.value.length !== 8) {
            alert("Password must be exactly 8 characters long.");
            valid = false;
        }

        // Validate Contact No (only numbers, max 11 digits)
        if (contactNoInput.value.length !== 11 || !/^\d+$/.test(contactNoInput.value)) {
            alert("Contact No should be exactly 11 digits.");
            valid = false;
        }

        if (!valid) {
            event.preventDefault();  // Prevent form submission if validation fails
        }
    });
});


const inputField = document.getElementById('contact_no');

inputField.addEventListener('input', (event) => {
    // Remove any non-numeric characters
    inputField.value = inputField.value.replace(/[^0-9]/g, '');
});
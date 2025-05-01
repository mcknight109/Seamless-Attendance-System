// Get modals
const editAccountModal = document.getElementById('editAccountModal');
const successModal = document.getElementById('successModal');

// Get buttons
const openModalBtn = document.querySelector('.edit-btn'); // Edit button
const closeModalBtn = document.getElementById('closeModalBtn'); // Close edit modal button
const cancelEditBtn = document.getElementById('cancelEditBtn'); // Cancel edit modal button
const closeSuccessModalBtn = document.getElementById('closeSuccessModal'); // Close success modal button

// Show edit account modal
openModalBtn.addEventListener('click', () => {
    editAccountModal.style.display = 'flex';
});

// Close edit account modal
closeModalBtn.addEventListener('click', () => {
    editAccountModal.style.display = 'none';
});

// Cancel edit account modal
cancelEditBtn.addEventListener('click', () => {
    editAccountModal.style.display = 'none';
});

// Close success modal and hide all modals
closeSuccessModalBtn.addEventListener('click', () => {
    successModal.style.display = 'none';
    editAccountModal.style.display = 'none'; // Close edit modal when success modal is closed
});

    // // Handle form submission with AJAX
    // document.getElementById('editAccountForm').addEventListener('submit', (e) => {
    //     e.preventDefault(); // Prevent page reload on form submission

    //     // Get form data
    //     const formData = new FormData(e.target);

    //     // Send data via AJAX to the server
    //     fetch('edit_user.php', {
    //         method: 'POST',
    //         body: formData
    //     })
    //     .then(response => response.json())
    //     .then(data => {
    //         if (data.status === 'success') {
    //             successModal.style.display = 'flex'; // Show success modal on success
    //             setTimeout(() => {
    //                 successModal.style.display = 'none'; // Hide success modal after 1 second
    //                 editAccountModal.style.display = 'none'; // Close the edit modal
    //             }, 1000); // Adjusted timeout to 1000 ms
    //         } else {
    //             alert(data.message || 'Error updating account.');
    //         }
    //     })
    //     .catch(error => {
    //         console.error('Error:', error);
    //         alert('An error occurred while updating the account.');
    //     });
    // });

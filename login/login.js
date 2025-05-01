document.addEventListener('DOMContentLoaded', function() {
    // Select the login button, modal, container, and close button
    const loginBtn = document.querySelector('.btn-login');
    const modal = document.querySelector('.login_container');
    const container = document.querySelector('.container');
    const closeModalBtn = document.getElementById('closeModalBtn');

    // Show the modal and blur the container when the login button is clicked
    loginBtn.addEventListener('click', function() {
        modal.style.display = 'flex';  // Display the modal
        container.style.filter = 'blur(5px)';  // Apply blur effect to the container
    });

    // Hide the modal and remove the blur effect when the close button is clicked
    closeModalBtn.addEventListener('click', function() {
        modal.style.display = 'none';  // Hide the modal
        container.style.filter = 'none';  // Remove blur effect
    });
});
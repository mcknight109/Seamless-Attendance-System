// Prevent special characters except "@" in email
const emailInput = document.getElementById('email');
emailInput.addEventListener('input', function () {
    this.value = this.value.replace(/[^a-zA-Z0-9@._-]/g, ''); // Allows letters, numbers, @, ., _, and -
});

// Restrict password input to letters, numbers, and "@" with max length of 8
const passwordInput = document.getElementById('password');
passwordInput.addEventListener('input', function () {
    this.value = this.value.replace(/[^a-zA-Z0-9@]/g, ''); // Allows letters, numbers, and @
    if (this.value.length > 8) {
        this.value = this.value.slice(0, 8); // Restricts to 8 characters
    }
});

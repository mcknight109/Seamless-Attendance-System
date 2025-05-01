document.addEventListener("DOMContentLoaded", () => {
    const loginButton = document.querySelector(".container .login-btn");
    const loginContainer = document.querySelector(".login_container");
    const closeModalBtn = document.getElementById("closeModalBtn");

    // Hide the modal by default
    loginContainer.style.display = "none";

    // Show the modal when the login button is clicked
    loginButton.addEventListener("click", () => {
        loginContainer.style.display = "flex";
        document.querySelector(".container").classList.add("blur");
    });

    // Hide the modal when clicking outside the login form
    loginContainer.addEventListener("click", (e) => {
        if (e.target === loginContainer) {
            loginContainer.style.display = "none";
            document.querySelector(".container").classList.remove("blur");
        }
    });

    // Close the modal when the close button is clicked
    closeModalBtn.addEventListener("click", () => {
        loginContainer.style.display = "none";
        document.querySelector(".container").classList.remove("blur");
    });
});


// // Select elements
// const loginButton = document.querySelector('.login-btn'); // Button in the container
// const loginContainer = document.querySelector('.login_container');
// const container = document.querySelector('.container');

// // Add event listener to the login button
// loginButton.addEventListener('click', () => {
//     // Display the login modal
//     loginContainer.style.display = 'flex';

//     // Add blur class to the container
//     container.classList.add('blur');
// });


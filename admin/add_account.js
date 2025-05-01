document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("addAccountModal");
    const openModalButton = document.getElementById("openModalButton");
    const closeModalButton = document.getElementById("closeModalBtn");
    const cancelEditButton = document.getElementById("cancelEditBtn");
    const paginationContainer = document.querySelector(".pagination-container");
    const otherElements = document.querySelectorAll(".table-top, .manage-container");

    // Function to toggle visibility of certain elements
    const toggleVisibility = (hide) => {
        const displayValue = hide ? "none" : "block";
        paginationContainer.style.display = displayValue;
        otherElements.forEach(element => {
            element.style.display = displayValue;
        });
    };

    // Function to open the modal
    const openModal = () => {
        modal.classList.remove("hidden");
        modal.style.display = "flex";
        toggleVisibility(true);
    };

    // Function to close the modal
    const closeModal = () => {
        modal.classList.add("hidden");
        modal.style.display = "none";
        toggleVisibility(false);
    };

    // Add event listeners
    openModalButton.addEventListener("click", openModal);
    closeModalButton.addEventListener("click", closeModal);
    cancelEditButton.addEventListener("click", closeModal);

    // Close modal on clicking outside the content
    modal.addEventListener("click", (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });
});

// 
// 
// 
// 
// 



// 
// 
// 
// 
// 

function fetchShiftTimes(shiftType, targetSelectId) {
    if (shiftType === "") {
        document.getElementById(targetSelectId).innerHTML = "<option value=''>Select Shift Time</option>";
        return;
    }

    fetch(`../admin/fetch_shift_times.php?shift_type=${shiftType}`)
        .then(response => response.json())
        .then(data => {
            const targetSelect = document.getElementById(targetSelectId);
            targetSelect.innerHTML = "<option value=''>Select Shift Time</option>"; // Reset options
            data.forEach(shift => {
                const option = document.createElement("option");
                option.value = shift.shift_id;
                option.textContent = `${shift.start_time} - ${shift.end_time}`;
                targetSelect.appendChild(option);
            });
        })
        .catch(error => console.error("Error fetching shift times:", error));
}


// 
// 
// 
// 
// 

function fetchUserShiftDetails(userId) {
    fetch(`../admin/fetch_user_shift.php?user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            // Populate shift type
            const shiftTypeSelect = document.getElementById('edit_shift_type');
            shiftTypeSelect.value = data.shift_type;

            // Populate shift times
            fetchShiftTimes(data.shift_type, 'edit_shift_time')
                .then(() => {
                    const shiftTimeSelect = document.getElementById('edit_shift_time');
                    shiftTimeSelect.value = data.shift_time;
                });
        })
        .catch(error => console.error("Error fetching user shift details:", error));
}

function fetchShiftTimes(shiftType, targetSelectId) {
    if (shiftType === "") {
        document.getElementById(targetSelectId).innerHTML = "<option value=''>Select Shift Time</option>";
        return;
    }

    return fetch(`../admin/fetch_shift_times.php?shift_type=${shiftType}`)
        .then(response => response.json())
        .then(data => {
            const targetSelect = document.getElementById(targetSelectId);
            targetSelect.innerHTML = "<option value=''>Select Shift Time</option>"; // Reset options
            data.forEach(shift => {
                const option = document.createElement("option");
                option.value = shift.shift_id;
                option.textContent = `${shift.start_time} - ${shift.end_time}`;
                targetSelect.appendChild(option);
            });
        })
        .catch(error => console.error("Error fetching shift times:", error));
}

// 
// 
// 
// 
// 


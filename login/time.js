// JavaScript to display the current time
function updateTime() {
    const timeElement = document.getElementById('time');
    const now = new Date();
    const hours = now.getHours() % 12 || 12; // Convert to 12-hour format
    const minutes = now.getMinutes().toString().padStart(2, '0'); // Add leading zero
    const ampm = now.getHours() >= 12 ? 'PM' : 'AM';
    timeElement.textContent = `${hours}:${minutes} ${ampm}`; // Display time without seconds
}

// Update the time every second
setInterval(updateTime, 1000);
// Initialize the time display
updateTime();

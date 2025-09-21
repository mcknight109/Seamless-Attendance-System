// Update date and time dynamically
function updateDateTime() {
    const dateEl = document.getElementById('current-date');
    const timeEl = document.getElementById('current-time');
    const now = new Date();
    // Options for date like "Mon, January 20, 2026"
    const dateOptions = { weekday: 'short', year: 'numeric', month: 'long', day: 'numeric' };
    dateEl.textContent = now.toLocaleDateString('en-US', dateOptions);
    // Format time as hh:mm AM/PM
    let hours = now.getHours();
    const minutes = now.getMinutes().toString().padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    timeEl.textContent = 'Time: ' + hours + ':' + minutes + ' ' + ampm;
}
updateDateTime();
setInterval(updateDateTime, 1000 * 30); // update every 30 seconds
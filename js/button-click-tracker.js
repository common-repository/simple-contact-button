function trackButtonClick(event, buttonElement, buttonType) {
    // Prevent default behavior (navigation)
    event.preventDefault();

    // Check if the gtag function is available
    if (typeof gtag === "function") {
        // Send the GA4 event with button type differentiation
        gtag("event", "contact_button_click", {
            "event_category": "Contact",
            "event_label": buttonType + " Button",
            "value": 1
        });

        // Use setTimeout to ensure the event is sent before navigation
        setTimeout(function() {
            // Navigate to the clicked button's URL
            window.location.href = buttonElement.href; // Use the passed element's href
        }, 500); // 500ms delay to allow event to send
    } else {
        // If gtag is not available, just navigate directly
        window.location.href = buttonElement.href; // Use the passed element's href
    }
}

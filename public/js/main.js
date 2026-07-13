// public/js/main.js
document.addEventListener("DOMContentLoaded", function() {
    const form = document.querySelector("form");
    
    if(form) {
        form.addEventListener("submit", function(e) {
            const passwordInput = document.querySelector("input[type='password']");
            
            // Mfano wa ku-validate urefu wa password
            if(passwordInput && passwordInput.value.length < 6) {
                e.preventDefault();
                alert("Password lazima iwe na urefu wa herufi kuanzia 6!");
            }
        });
    }
});
// Contact Form Handling
document.addEventListener('DOMContentLoaded', () => {
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            // JS送信を止め、HTMLフォームの標準POSTに任せる
            

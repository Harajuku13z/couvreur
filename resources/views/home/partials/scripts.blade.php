    <!-- JavaScript -->
    <script>
        // trackPhoneCall est géré automatiquement par phone-tracking.js dans layouts/app.blade.php
        // Plus besoin de fonction locale, le script global s'occupe de tout

        function trackFormClick(page) {
            fetch('/api/track-form-click', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    page: page
                })
            }).catch(error => console.log('Tracking error:', error));
        }

        function trackServiceClick(service, page) {
            fetch('/api/track-service-click', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    service: service,
                    page: page
                })
            }).catch(error => console.log('Tracking error:', error));
        }

        // Force image display on mobile
        document.addEventListener('DOMContentLoaded', function() {
            // Check if mobile
            if (window.innerWidth <= 768) {
                // Force hero background image
                const heroSection = document.querySelector('.hero-mobile');
                if (heroSection && heroSection.style.backgroundImage) {
                    heroSection.style.backgroundSize = 'cover';
                    heroSection.style.backgroundPosition = 'center center';
                    heroSection.style.backgroundRepeat = 'no-repeat';
                    heroSection.style.backgroundAttachment = 'scroll';
                }
                
                // Force all images to display
                const images = document.querySelectorAll('img');
                images.forEach(img => {
                    img.style.maxWidth = '100%';
                    img.style.height = 'auto';
                    img.style.display = 'block';
                    img.style.width = '100%';
                });
                
                // Force service images to show on mobile
                const serviceImages = document.querySelectorAll('.service-image-mobile img');
                serviceImages.forEach(img => {
                    img.style.display = 'block';
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'cover';
                });
                
                // Force background images
                const bgElements = document.querySelectorAll('[style*="background-image"]');
                bgElements.forEach(el => {
                    el.style.backgroundSize = 'cover';
                    el.style.backgroundPosition = 'center center';
                    el.style.backgroundRepeat = 'no-repeat';
                });
            }
        });
    </script>

document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    const pageLinks = document.querySelectorAll('[data-page]');
    const pages = document.querySelectorAll('.page');
    const hasDropdowns = document.querySelectorAll('.has-dropdown');
    
    // Function to show page based on hash
    function showPageFromHash() {
        const hash = window.location.hash.substring(1) || 'home';
        
        pages.forEach(page => {
            page.classList.remove('active');
        });
        
        const targetPageElement = document.getElementById(hash);
        if (targetPageElement) {
            targetPageElement.classList.add('active');
        } else {
            // Fallback to home if page doesn't exist
            document.getElementById('home').classList.add('active');
        }
        
        document.querySelectorAll('.has-dropdown').forEach(item => {
            item.classList.remove('active');
        });
        
        // Scroll to top when changing pages
        window.scrollTo(0, 0);
    }
    
    // Check hash on page load
    showPageFromHash();
    
    // Listen for hash changes
    window.addEventListener('hashchange', showPageFromHash);
    
    // Logo click handler
    const logoLink = document.querySelector('.logo-link');
    if (logoLink) {
        logoLink.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.hash = 'home';
        });
    }
    
    // Parallax scrolling effect
    const parallaxElements = document.querySelectorAll('.home-about, .home-gallery, .home-cta');
    
    function updateParallax() {
        const scrolled = window.pageYOffset;
        
        parallaxElements.forEach(element => {
            const rect = element.getBoundingClientRect();
            const elementTop = rect.top + scrolled;
            const elementHeight = rect.height;
            
            // Check if element is in viewport
            if (scrolled + window.innerHeight > elementTop && scrolled < elementTop + elementHeight) {
                const speed = 0.5; // Adjust parallax speed
                const yPos = -(scrolled - elementTop) * speed;
                const beforeElement = element.querySelector('::before');
                
                // Apply transform to the ::before pseudo-element via CSS variable
                element.style.setProperty('--parallax-y', `${yPos}px`);
            }
        });
    }
    
    // Update parallax on scroll
    window.addEventListener('scroll', updateParallax);
    window.addEventListener('resize', updateParallax);
    
    // Initial parallax update
    updateParallax();
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const parentLi = this.parentElement;
            const isActive = parentLi.classList.contains('active');
            
            document.querySelectorAll('.has-dropdown').forEach(item => {
                if (item !== parentLi) {
                    item.classList.remove('active');
                }
            });
            
            if (!isActive) {
                parentLi.classList.add('active');
            } else {
                parentLi.classList.remove('active');
            }
        });
    });
    
    hasDropdowns.forEach(dropdown => {
        dropdown.addEventListener('mouseenter', function() {
            this.classList.add('active');
        });
        
        dropdown.addEventListener('mouseleave', function() {
            this.classList.remove('active');
        });
    });
    
    pageLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetPage = this.getAttribute('data-page');
            
            // Update hash to trigger page change
            window.location.hash = targetPage;
        });
    });
    
    const galleryItems = document.querySelectorAll('.gallery-item, .home-gallery-item');
    galleryItems.forEach(item => {
        item.addEventListener('click', function() {
            const img = this.querySelector('img');
            if (!img) return;
            
            const lightbox = document.createElement('div');
            lightbox.className = 'lightbox';
            lightbox.innerHTML = `
                <div class="lightbox-content">
                    <span class="lightbox-close">&times;</span>
                    <img src="${img.src}" alt="${img.alt}">
                </div>
            `;
            
            document.body.appendChild(lightbox);
            
            setTimeout(() => {
                lightbox.style.opacity = '1';
            }, 10);
            
            const closeLightbox = () => {
                lightbox.style.opacity = '0';
                setTimeout(() => {
                    document.body.removeChild(lightbox);
                }, 300);
            };
            
            lightbox.querySelector('.lightbox-close').addEventListener('click', closeLightbox);
            lightbox.addEventListener('click', function(e) {
                if (e.target === lightbox) {
                    closeLightbox();
                }
            });
        });
    });
});

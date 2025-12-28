document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    const pageLinks = document.querySelectorAll('[data-page]');
    const pages = document.querySelectorAll('.page');
    const hasDropdowns = document.querySelectorAll('.has-dropdown');
    
    // Sticky sidebar hide/show on scroll
    const sidebar = document.querySelector('.sidebar');
    let lastScrollY = window.scrollY;
    let scrollTimer = null;
    
    function handleScroll() {
        const currentScrollY = window.scrollY;
        const scrollDelta = currentScrollY - lastScrollY;
        const scrollThreshold = 5;
        
        if (Math.abs(scrollDelta) > scrollThreshold) {
            if (scrollDelta > 0 && currentScrollY > 100) {
                // Scrolling down, hide sidebar
                sidebar.classList.add('hidden');
            } else {
                // Scrolling up, show sidebar
                sidebar.classList.remove('hidden');
            }
            lastScrollY = currentScrollY;
        }
        
        // Clear timer on scroll
        clearTimeout(scrollTimer);
    }
    
    // Debounced scroll handler
    function debouncedScroll() {
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(handleScroll, 16);
    }
    
    window.addEventListener('scroll', debouncedScroll, { passive: true });
    
    // Function to show page based on hash
    function showPageFromHash() {
        const hash = window.location.hash.substring(1) || 'home';
        
        pages.forEach(page => {
            page.classList.remove('active');
        });

    // Inline galleries (team-profile) paginated thumbnails (3x3 per view)
    const profileGalleries = document.querySelectorAll('.team-profile-gallery');
    profileGalleries.forEach(gallery => {
        const thumbs = Array.from(gallery.querySelectorAll('.card-gallery img'));
        const prevBtn = gallery.querySelector('.gallery-nav.prev');
        const nextBtn = gallery.querySelector('.gallery-nav.next');
        if (thumbs.length === 0) return;

        const pageSize = 9;
        let page = 0;
        const totalPages = Math.max(1, Math.ceil(thumbs.length / pageSize));

        const renderPage = () => {
            thumbs.forEach((thumb, idx) => {
                const start = page * pageSize;
                const end = start + pageSize;
                if (idx >= start && idx < end) {
                    thumb.classList.remove('hidden');
                } else {
                    thumb.classList.add('hidden');
                }
            });
        };

        prevBtn && prevBtn.addEventListener('click', () => {
            page = (page - 1 + totalPages) % totalPages;
            renderPage();
        });

        nextBtn && nextBtn.addEventListener('click', () => {
            page = (page + 1) % totalPages;
            renderPage();
        });

        renderPage();
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
    
    // Parallax scrolling effect (exclude home-gallery background)
    const parallaxElements = document.querySelectorAll('.home-about, .home-cta');
    const heroVideos = document.querySelectorAll('.hero-video');
    
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

        // Hero video subtle parallax on scroll
        heroVideos.forEach(video => {
            const rect = video.getBoundingClientRect();
            const offsetCenter = (rect.top + rect.height * 0.5) - (window.innerHeight * 0.5);
            const translateY = Math.max(Math.min(offsetCenter * 0.18, 100), -100); // stronger, clamped
            video.style.setProperty('--hero-parallax', `${translateY}px`);
        });
    }
    
    // Update parallax on scroll
    window.addEventListener('scroll', updateParallax);
    window.addEventListener('resize', updateParallax);
    
    // Initial parallax update
    updateParallax();
    
    // Flip card functionality
    const flipCards = document.querySelectorAll('.flip-card');
    
    flipCards.forEach(card => {
        card.addEventListener('click', function() {
            this.classList.toggle('flipped');
        });
    });
    
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
    
    const galleryItems = document.querySelectorAll('.gallery-item, .home-gallery-item, .card-gallery img, .gallery-main img');
    galleryItems.forEach(item => {
        item.addEventListener('click', function() {
            const img = this.tagName === 'IMG' ? this : this.querySelector('img');
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
    
    // Scroll-triggered animations for process cards, signature, and piercings page
    const observerOptions = {
        threshold: 0.2, // Trigger when 20% of element is visible
        rootMargin: '0px 0px -50px 0px' // Start animation 50px before element comes into view
    };
    
    const animationObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Handle process cards animation
                if (entry.target.classList.contains('process-steps')) {
                    const steps = entry.target.querySelectorAll('.step');
                    steps.forEach((step, index) => {
                        setTimeout(() => {
                            step.classList.add('animate-in');
                        }, index * 200); // 200ms delay between each step
                    });
                }
                
                // Handle signature logo animation
                if (entry.target.classList.contains('signature-divider')) {
                    const logoSvg = entry.target.querySelector('.signature-logo-svg');
                    if (logoSvg) {
                        const text = logoSvg.querySelector('.logo-text');
                        if (text) {
                            text.style.animation = 'none';
                            text.offsetHeight;
                            setTimeout(() => {
                                text.style.animation = 'smoothTextReveal 1.8s ease-out forwards';
                            }, 100);
                        }
                    }
                }

                if (entry.target.classList.contains('piercings-hero')) {
                    entry.target.classList.add('is-visible');
                }

                if (entry.target.classList.contains('piercing-card')) {
                    entry.target.classList.add('is-visible');
                }

                if (entry.target.classList.contains('metric-card')) {
                    entry.target.classList.add('is-visible');
                }
                
                animationObserver.unobserve(entry.target); // Stop observing after animation
            }
        });
    }, observerOptions);
    
    // Observe all process sections (tatuajes, piercings, etc.)
    const processSections = document.querySelectorAll('.process-steps');
    processSections.forEach(section => animationObserver.observe(section));
    
    // Observe the signature section
    const signatureDivider = document.querySelector('.signature-divider');
    if (signatureDivider) {
        animationObserver.observe(signatureDivider);
    }

    // Piercings page animations
    const piercingsHero = document.querySelector('.piercings-hero');
    if (piercingsHero) {
        animationObserver.observe(piercingsHero);
    }

    const piercingCards = document.querySelectorAll('.piercing-card');
    piercingCards.forEach((card, index) => {
        card.style.setProperty('--card-delay', index);
        animationObserver.observe(card);
    });

    const metricCards = document.querySelectorAll('.metric-card');
    metricCards.forEach(card => animationObserver.observe(card));
});

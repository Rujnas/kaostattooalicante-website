document.addEventListener('DOMContentLoaded', function() {
    const hasDropdowns = document.querySelectorAll('.has-dropdown');
    const pages = document.querySelectorAll('.page');
    const SCROLL_KEY_PREFIX = 'kaos-scroll-';
    const loadingOverlay = document.querySelector('.loading-overlay');

    const normalizeHash = (hashValue = window.location.hash) => {
        const raw = hashValue || '';
        return raw.replace('#', '');
    };

    const getPageIdFromHash = (hashValue = window.location.hash) => {
        const normalized = normalizeHash(hashValue);
        return normalized === '' ? 'home' : normalized;
    };

    const getScrollKey = (pageId) => `${SCROLL_KEY_PREFIX}${pageId}`;

    const restoreScrollPosition = (pageId) => {
        const stored = sessionStorage.getItem(getScrollKey(pageId));
        if (stored !== null && !Number.isNaN(parseFloat(stored))) {
            requestAnimationFrame(() => {
                window.scrollTo(0, parseFloat(stored));
            });
        }
    };

    const saveScrollPosition = (pageId) => {
        sessionStorage.setItem(getScrollKey(pageId), window.scrollY.toString());
    };

    let currentPageId = getPageIdFromHash();
    let scrollSaveTimer = null;

    window.addEventListener('load', () => {
        setTimeout(() => {
            document.body.classList.remove('is-loading');
            if (loadingOverlay) {
                loadingOverlay.classList.add('is-hidden');
            }
            restoreScrollPosition(currentPageId);
        }, 300);
    });
    window.addEventListener('beforeunload', () => saveScrollPosition(currentPageId));
    window.addEventListener('scroll', () => {
        clearTimeout(scrollSaveTimer);
        scrollSaveTimer = setTimeout(() => saveScrollPosition(currentPageId), 150);
    }, { passive: true });
    
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
    
    // Logo click handler
    const logoLink = document.querySelector('.logo-link');
    if (logoLink) {
        logoLink.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.hash = 'home';
        });
    }
    
    // Function to show page based on hash with optional scroll control
    function showPageFromHash({ shouldScrollTop = true } = {}) {
        const hash = window.location.hash.substring(1) || 'home';
        
        pages.forEach(page => {
            page.classList.remove('active');
        });

        const targetPageElement = document.getElementById(hash);
        if (targetPageElement) {
            targetPageElement.classList.add('active');
        } else {
            document.getElementById('home').classList.add('active');
        }
        
        document.querySelectorAll('.has-dropdown').forEach(item => {
            item.classList.remove('active');
        });
        
        if (shouldScrollTop) {
            window.scrollTo(0, 0);
        }
    }

    // Initial page load should preserve scroll position
    showPageFromHash({ shouldScrollTop: false });
    
    // Listen for hash changes (navigate to sections)
    window.addEventListener('hashchange', () => showPageFromHash({ shouldScrollTop: true }));

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
    
    // Page navigation
    const navLinks = document.querySelectorAll('.nav-link[data-page], .dropdown-menu a[data-page], .btn-primary[data-page]');
    const categoryLinks = document.querySelectorAll('.category-link[data-page]');
    
    // Sidebar navigation
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetPage = this.getAttribute('data-page');
            console.log('Nav link clicked:', targetPage);
            
            // Update hash to trigger page change
            window.location.hash = targetPage;
        });
    });
    
    // Category navigation
    categoryLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const targetPage = this.getAttribute('data-page');
            console.log('Category link clicked:', targetPage);
            
            // Update hash to trigger page change
            window.location.hash = targetPage;
        });
    });
    
    // Function to show page based on hash
    function showPageFromHash() {
        const hash = window.location.hash.substring(1) || 'home';
        console.log('showPageFromHash called with hash:', hash);
        
        pages.forEach(page => {
            page.classList.remove('active');
        });

        const targetPageElement = document.getElementById(hash);
        console.log('Looking for element with ID:', hash, 'Found:', targetPageElement);
        
        if (targetPageElement) {
            targetPageElement.classList.add('active');
            console.log('Successfully switched to page:', hash);
            // Scroll to top when navigating to a new page
            window.scrollTo(0, 0);
        } else {
            // Fallback to home if page doesn't exist
            document.getElementById('home').classList.add('active');
            console.log('Page not found, fallback to home');
            window.scrollTo(0, 0);
        }
    }

    // Handle initial page load
    showPageFromHash();
    
    // Handle hash changes
    window.addEventListener('hashchange', showPageFromHash);
    
    // Dropdown menu functionality
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
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
    
    const galleryItems = document.querySelectorAll('.gallery-item, .card-gallery img, .gallery-main img');
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
    
    // Inline galleries (team-profile) 3-image carousel with center highlight
    const profileGalleries = document.querySelectorAll('.team-profile-gallery');
    profileGalleries.forEach(gallery => {
        const thumbs = Array.from(gallery.querySelectorAll('.card-gallery img'));
        const prevBtn = gallery.querySelector('.gallery-nav.prev');
        const nextBtn = gallery.querySelector('.gallery-nav.next');
        if (thumbs.length === 0) return;

        let currentIndex = 1; // Start with center image
        const visibleCount = 3;

        const renderGallery = () => {
            thumbs.forEach((thumb, idx) => {
                thumb.classList.remove('hidden', 'center');
                const startIdx = Math.max(0, currentIndex - 1);
                const endIdx = Math.min(thumbs.length, startIdx + visibleCount);
                if (idx >= startIdx && idx < endIdx) {
                    thumb.classList.remove('hidden');
                    if (idx === currentIndex) {
                        thumb.classList.add('center');
                    }
                } else {
                    thumb.classList.add('hidden');
                }
            });
        };

        const navigateGallery = (direction) => {
            if (direction === 'next') {
                currentIndex = (currentIndex + 1) % thumbs.length;
            } else {
                currentIndex = (currentIndex - 1 + thumbs.length) % thumbs.length;
            }
            renderGallery();
        };

        prevBtn && prevBtn.addEventListener('click', () => navigateGallery('prev'));
        nextBtn && nextBtn.addEventListener('click', () => navigateGallery('next'));

        // Wheel event for scroll navigation on images only
        const galleryImages = gallery.querySelectorAll('.card-gallery img');
        galleryImages.forEach(img => {
            img.addEventListener('wheel', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const direction = e.deltaY > 0 ? 'next' : 'prev';
                navigateGallery(direction);
            }, { passive: false });
        });

        renderGallery();
    });
});

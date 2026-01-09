document.addEventListener('DOMContentLoaded', function() {
    const hasDropdowns = document.querySelectorAll('.has-dropdown');
    const pages = document.querySelectorAll('.page');
    const SCROLL_KEY_PREFIX = 'kaos-scroll-';
    const loadingOverlay = document.querySelector('.loading-overlay');

    // Sequential scroll reveal setup
    const scrollRevealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.2,
        rootMargin: '0px 0px -60px 0px'
    });

    const pageRevealConfigs = {
        home: [
            { selector: '.hero-content', baseDelay: 0, step: 80 },
            { selector: '.home-about-content > *', baseDelay: 80, step: 70 },
            { selector: '.home-gallery-title', baseDelay: 220, step: 60 },
            { selector: '.home-gallery-item', baseDelay: 260, step: 60 },
            { selector: '.cta-left > *', baseDelay: 420, step: 50 },
            { selector: '.cta-right', baseDelay: 450, step: 0 }
        ],
        tatuadores: [
            { selector: '.team-hero .hero-content', baseDelay: 0, step: 80 },
            { selector: '.team-profile-card', baseDelay: 120, step: 70 },
            { selector: '.team-logo-divider', baseDelay: 160, step: 60 },
            { selector: '.team-profile-card .btn-portfolio', baseDelay: 220, step: 50 }
        ],
        anilladora: [
            { selector: '.team-hero .hero-content', baseDelay: 0, step: 80 },
            { selector: '.team-logo-divider', baseDelay: 100, step: 60 },
            { selector: '.team-profile-card', baseDelay: 160, step: 60 },
            { selector: '.team-profile-card .btn-portfolio', baseDelay: 220, step: 50 }
        ],
        'portfolio-tailor': [
            { selector: '.page-content > *:not(.portfolio-gallery-wrap)', baseDelay: 0, step: 70 },
            { selector: '.portfolio-gallery .gallery-item, .portfolio-gallery-wrap .masonry-item', baseDelay: 180, step: 50 }
        ],
        'portfolio-carrie': [
            { selector: '.page-content > *:not(.portfolio-gallery-wrap)', baseDelay: 0, step: 70 },
            { selector: '.portfolio-gallery .gallery-item, .portfolio-gallery-wrap .masonry-item', baseDelay: 180, step: 50 }
        ],
        'portfolio-greka': [
            { selector: '.page-content > *:not(.portfolio-gallery-wrap)', baseDelay: 0, step: 70 },
            { selector: '.portfolio-gallery .gallery-item, .portfolio-gallery-wrap .masonry-item', baseDelay: 180, step: 50 }
        ]
    };

    const scrollRevealTargetsByPage = new Map();

    const getOrCreateScrollRevealTargets = (pageId) => {
        if (scrollRevealTargetsByPage.has(pageId)) {
            return scrollRevealTargetsByPage.get(pageId);
        }

        const config = pageRevealConfigs[pageId];
        const pageElement = document.getElementById(pageId);
        if (!config || !pageElement) {
            return [];
        }

        const collected = new Set();
        const targets = [];

        config.forEach(({ selector, baseDelay = 0, step = 60 }) => {
            const elements = pageElement.querySelectorAll(selector);
            elements.forEach((element, index) => {
                if (collected.has(element)) return;
                collected.add(element);

                if (!element.hasAttribute('data-scroll-reveal')) {
                    element.setAttribute('data-scroll-reveal', '');
                }

                const isHomePage = pageId === 'home';
                const isPortfolioPage = pageId.startsWith('portfolio-');
                const isMobileViewport = window.innerWidth <= 768;
                const shouldAccelerate = isMobileViewport && (isHomePage || isPortfolioPage);
                const delayFactor = shouldAccelerate ? 0.55 : 1;
                const rawDelay = (baseDelay + index * step) * delayFactor;
                const delayCap = isHomePage ? 500 : (isPortfolioPage ? 520 : 650);
                const delay = Math.min(rawDelay, delayCap);
                element.style.setProperty('--scroll-seq-delay', `${Math.round(delay)}ms`);
                element.dataset.scrollRevealDelay = delay;
                targets.push(element);
            });
        });

        scrollRevealTargetsByPage.set(pageId, targets);
        return targets;
    };

    const resetScrollRevealForPage = (pageId) => {
        const targets = getOrCreateScrollRevealTargets(pageId);
        targets.forEach(target => {
            target.classList.remove('is-visible');
            scrollRevealObserver.unobserve(target);
            scrollRevealObserver.observe(target);
        });
    };

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
        let targetPageId = hash;
        let targetPageElement = document.getElementById(hash);

        if (!targetPageElement) {
            targetPageId = 'home';
            targetPageElement = document.getElementById(targetPageId);
        }

        if (!targetPageElement) {
            return;
        }

        pages.forEach(page => {
            page.classList.remove('active');
        });

        targetPageElement.classList.add('active');
        currentPageId = targetPageId;
        
        document.querySelectorAll('.has-dropdown').forEach(item => {
            item.classList.remove('active');
        });

        resetScrollRevealForPage(targetPageId);
        
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
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const mobileMenuOverlay = document.querySelector('.mobile-menu-overlay');
    const mobileSubmenuWrapper = document.querySelector('.mobile-submenu-wrapper');
    const mobileSubmenuList = mobileSubmenuWrapper ? mobileSubmenuWrapper.querySelector('.mobile-submenu-list') : null;
    const mobileSubmenuTitle = mobileSubmenuWrapper ? mobileSubmenuWrapper.querySelector('.mobile-submenu-title') : null;
    const mobileSubmenuBack = mobileSubmenuWrapper ? mobileSubmenuWrapper.querySelector('.mobile-submenu-back') : null;

    const isMobileViewport = () => window.innerWidth <= 768;

    const closeMobileMenu = () => {
        if (!mobileMenuToggle || !navMenu) return;
        mobileMenuToggle.setAttribute('aria-expanded', 'false');
        navMenu.classList.remove('is-open');
        document.body.classList.remove('mobile-menu-open');
        if (mobileMenuOverlay) {
            mobileMenuOverlay.classList.remove('is-visible');
        }
        if (mobileSubmenuWrapper) {
            mobileSubmenuWrapper.setAttribute('aria-hidden', 'true');
        }
        if (navMenu) {
            navMenu.classList.remove('show-submenu');
        }
    };

    const openMobileMenu = () => {
        if (!mobileMenuToggle || !navMenu) return;
        mobileMenuToggle.setAttribute('aria-expanded', 'true');
        navMenu.classList.add('is-open');
        document.body.classList.add('mobile-menu-open');
        if (mobileMenuOverlay) {
            mobileMenuOverlay.classList.add('is-visible');
        }
    };

    const closeMobileSubmenu = () => {
        if (mobileSubmenuWrapper) {
            mobileSubmenuWrapper.setAttribute('aria-hidden', 'true');
        }
        if (navMenu) {
            navMenu.classList.remove('show-submenu');
        }
    };

    if (mobileMenuToggle && navMenu) {
        mobileMenuToggle.addEventListener('click', () => {
            const isExpanded = mobileMenuToggle.getAttribute('aria-expanded') === 'true';
            if (isExpanded) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });

        if (mobileMenuOverlay) {
            mobileMenuOverlay.addEventListener('click', closeMobileMenu);
        }

        if (mobileSubmenuBack) {
            mobileSubmenuBack.addEventListener('click', (event) => {
                event.preventDefault();
                closeMobileSubmenu();
            });
        }

        window.addEventListener('resize', () => {
            if (!isMobileViewport()) {
                closeMobileMenu();
            }
        });

        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && mobileMenuToggle.getAttribute('aria-expanded') === 'true') {
                closeMobileMenu();
            }
        });
    }
    
    // Sidebar navigation
    const handleNavClick = (linkElement, event) => {
        event.preventDefault();
        const targetPage = linkElement.getAttribute('data-page');
        window.location.hash = targetPage;
        if (isMobileViewport()) {
            closeMobileMenu();
        }
        closeMobileSubmenu();
    };

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            handleNavClick(this, e);
        });
    });
    
    // Category navigation
    categoryLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const targetPage = this.getAttribute('data-page');
            window.location.hash = targetPage;
        });
    });
    
    // Dropdown menu functionality
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            if (isMobileViewport()) {
                e.preventDefault();
                const submenuItems = this.nextElementSibling?.querySelectorAll('a[data-page]');
                if (!submenuItems || submenuItems.length === 0 || !mobileSubmenuList || !mobileSubmenuTitle) return;

                mobileSubmenuTitle.textContent = this.textContent.trim();
                mobileSubmenuList.innerHTML = '';
                submenuItems.forEach(item => {
                    const li = document.createElement('li');
                    const link = document.createElement('a');
                    link.href = '#';
                    link.dataset.page = item.dataset.page;
                    link.textContent = item.textContent.trim();
                    link.addEventListener('click', (evt) => {
                        handleNavClick(link, evt);
                    });
                    li.appendChild(link);
                    mobileSubmenuList.appendChild(li);
                });

                navMenu.classList.add('show-submenu');
                if (mobileSubmenuWrapper) {
                    mobileSubmenuWrapper.setAttribute('aria-hidden', 'false');
                }
            } else {
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

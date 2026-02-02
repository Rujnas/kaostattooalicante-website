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
        ],
        blog: [
            { selector: '.page-content > *', baseDelay: 0, step: 70 },
            { selector: '.blog-card', baseDelay: 160, step: 55 }
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

    const BLOG_PAGE_ID = 'blog';
    const BLOG_FEED_URL = 'https://medium.com/@sastreg86/feed';
    const BLOG_MAX_ITEMS = 6;
    const BLOG_CACHE_KEY = 'kaos-medium-feed-v3';
    const BLOG_CACHE_TTL_MS = 30 * 60 * 1000;
    const BLOG_OG_IMAGE_CACHE_KEY = 'kaos-medium-og-image-v1';
    const BLOG_OG_IMAGE_CACHE_TTL_MS = 24 * 60 * 60 * 1000;

    const formatBlogDate = (dateValue) => {
        if (!dateValue) return '';
        const date = new Date(dateValue);
        if (Number.isNaN(date.getTime())) return '';
        return date.toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric' });
    };

    const stripHtmlToText = (value) => {
        if (!value) return '';
        const wrapper = document.createElement('div');
        wrapper.innerHTML = value;
        return (wrapper.textContent || wrapper.innerText || '').trim();
    };

    const pickLargestFromSrcset = (srcset) => {
        if (!srcset) return '';
        const parts = srcset
            .split(',')
            .map(part => part.trim())
            .filter(Boolean);

        if (parts.length === 0) return '';
        const last = parts[parts.length - 1];
        return (last.split(/\s+/)[0] || '').trim();
    };

    const isUsableImageUrl = (url) => {
        if (!url) return false;
        const value = String(url).trim();
        if (!value) return false;
        if (value.startsWith('data:')) return false;
        return true;
    };

    const isProbablyContentImageUrl = (url) => {
        if (!isUsableImageUrl(url)) return false;
        const value = String(url).toLowerCase();
        if (value.includes('/_/stat')) return false;
        if (value.includes('pixel')) return false;
        return true;
    };

    const getMediumItemImage = (item) => {
        if (!item) return '';
        const content = item.content || item.description || '';

        if (content) {
            const template = document.createElement('template');
            template.innerHTML = content;

            const images = Array.from(template.content.querySelectorAll('img'));
            for (const img of images) {
                const candidates = [
                    img.getAttribute('data-src'),
                    img.getAttribute('data-original'),
                    pickLargestFromSrcset(img.getAttribute('srcset')),
                    img.getAttribute('src')
                ];

                for (const candidate of candidates) {
                    if (isProbablyContentImageUrl(candidate)) return String(candidate).trim();
                }
            }
        }

        if (isUsableImageUrl(item.thumbnail)) return item.thumbnail;
        return '';
    };

    const getCachedBlogFeed = () => {
        try {
            const raw = localStorage.getItem(BLOG_CACHE_KEY);
            if (!raw) return null;
            const parsed = JSON.parse(raw);
            if (!parsed || !parsed.timestamp || !Array.isArray(parsed.items)) return null;
            if (Date.now() - parsed.timestamp > BLOG_CACHE_TTL_MS) return null;
            return parsed.items;
        } catch {
            return null;
        }
    };

    const getCachedOgImage = (link) => {
        if (!link) return '';
        try {
            const raw = localStorage.getItem(BLOG_OG_IMAGE_CACHE_KEY);
            if (!raw) return '';
            const parsed = JSON.parse(raw);
            if (!parsed || typeof parsed !== 'object') return '';

            const entry = parsed[link];
            if (!entry || typeof entry !== 'object') return '';
            if (!entry.ts || !entry.url) return '';
            if (Date.now() - entry.ts > BLOG_OG_IMAGE_CACHE_TTL_MS) return '';
            return entry.url;
        } catch {
            return '';
        }
    };

    const setCachedOgImage = (link, url) => {
        if (!link || !url) return;
        try {
            const raw = localStorage.getItem(BLOG_OG_IMAGE_CACHE_KEY);
            const parsed = raw ? JSON.parse(raw) : {};
            const next = parsed && typeof parsed === 'object' ? parsed : {};
            next[link] = { ts: Date.now(), url };
            localStorage.setItem(BLOG_OG_IMAGE_CACHE_KEY, JSON.stringify(next));
        } catch {
            // ignore
        }
    };

    const fetchMediumOgImage = async (postUrl) => {
        if (!postUrl) return '';
        const cached = getCachedOgImage(postUrl);
        if (cached) return cached;

        const proxyUrl = `https://api.allorigins.win/raw?url=${encodeURIComponent(postUrl)}`;
        const response = await fetch(proxyUrl, { cache: 'no-store' });
        if (!response.ok) return '';

        const html = await response.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const meta = doc.querySelector('meta[property="og:image"], meta[name="og:image"], meta[name="twitter:image"]');
        const imageUrl = meta ? (meta.getAttribute('content') || '') : '';
        if (!isUsableImageUrl(imageUrl)) return '';

        setCachedOgImage(postUrl, imageUrl);
        return imageUrl;
    };

    const setCachedBlogFeed = (items) => {
        try {
            localStorage.setItem(BLOG_CACHE_KEY, JSON.stringify({ timestamp: Date.now(), items }));
        } catch {
            // ignore
        }
    };

    const fetchMediumFeed = async () => {
        const apiUrl = `https://api.rss2json.com/v1/api.json?rss_url=${encodeURIComponent(BLOG_FEED_URL)}`;
        const response = await fetch(apiUrl, { cache: 'no-store' });
        if (!response.ok) {
            throw new Error(`Medium feed error (${response.status})`);
        }
        const data = await response.json();
        if (!data || data.status !== 'ok' || !Array.isArray(data.items)) {
            throw new Error('Medium feed invalid');
        }
        return data.items;
    };

    const sanitizeMediumHtml = (html) => {
        const template = document.createElement('template');
        template.innerHTML = html || '';

        template.content.querySelectorAll('script, iframe').forEach(node => node.remove());
        template.content.querySelectorAll('[style]').forEach(node => node.removeAttribute('style'));

        return template.innerHTML;
    };

    const openBlogArticleModal = ({ title, pubDate, link, html, imageUrl }) => {
        const overlay = document.createElement('div');
        overlay.className = 'blog-modal';
        overlay.innerHTML = `
            <div class="blog-modal-content" role="dialog" aria-modal="true" aria-label="${(title || 'Artículo').replace(/"/g, '&quot;')}">
                <button class="blog-modal-close" type="button" aria-label="Cerrar">&times;</button>
                <div class="blog-modal-header">
                    <h2 class="blog-modal-title">${title || ''}</h2>
                    <div class="blog-modal-meta">
                        <span class="blog-modal-date">${formatBlogDate(pubDate)}</span>
                        <a class="blog-modal-medium" href="${link || '#'}" target="_blank" rel="noopener">Leer en Medium</a>
                    </div>
                </div>
                <div class="blog-modal-article"></div>
            </div>
        `;

        const normalizeImageUrlForCompare = (url) => {
            if (!url) return '';
            const raw = String(url).trim();
            if (!raw) return '';
            try {
                const parsed = new URL(raw);
                return `${parsed.origin}${parsed.pathname}`;
            } catch {
                return raw.split('?')[0];
            }
        };

        const getBestImgSrc = (img) => {
            if (!img) return '';
            const candidates = [
                img.getAttribute('data-src'),
                img.getAttribute('data-original'),
                pickLargestFromSrcset(img.getAttribute('srcset')),
                img.getAttribute('src')
            ];
            for (const candidate of candidates) {
                if (isUsableImageUrl(candidate)) return String(candidate).trim();
            }
            return '';
        };

        const articleEl = overlay.querySelector('.blog-modal-article');
        if (articleEl) {
            const sanitized = sanitizeMediumHtml(html);
            const template = document.createElement('template');
            template.innerHTML = sanitized;

            const normalizedCover = normalizeImageUrlForCompare(imageUrl);
            const hasCoverAlready = Boolean(imageUrl) && Array.from(template.content.querySelectorAll('img')).some(img => {
                const best = getBestImgSrc(img);
                return normalizeImageUrlForCompare(best) === normalizedCover;
            });
            const shouldAddCover = Boolean(imageUrl) && !hasCoverAlready;

            articleEl.innerHTML = '';
            if (shouldAddCover) {
                const cover = document.createElement('div');
                cover.className = 'blog-modal-cover';

                const img = document.createElement('img');
                img.src = imageUrl;
                img.alt = title ? `Imagen de ${title}` : 'Imagen del artículo';
                img.loading = 'lazy';
                img.decoding = 'async';
                cover.appendChild(img);

                articleEl.appendChild(cover);
            }

            articleEl.appendChild(template.content);
            articleEl.querySelectorAll('img').forEach(img => {
                const currentSrc = img.getAttribute('src') || '';
                if (!isProbablyContentImageUrl(currentSrc)) {
                    const best = getBestImgSrc(img);
                    if (isUsableImageUrl(best)) {
                        img.setAttribute('src', best);
                    }
                }

                if (!img.getAttribute('loading')) img.setAttribute('loading', 'lazy');
                img.setAttribute('decoding', 'async');
            });
            articleEl.querySelectorAll('a').forEach(anchor => {
                anchor.setAttribute('target', '_blank');
                anchor.setAttribute('rel', 'noopener');
            });
        }

        const closeBtn = overlay.querySelector('.blog-modal-close');

        const close = () => {
            window.removeEventListener('keydown', onKeyDown);
            overlay.style.opacity = '0';
            document.body.classList.remove('blog-modal-open');
            setTimeout(() => {
                if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
            }, 250);
        };

        const onKeyDown = (event) => {
            if (event.key === 'Escape') {
                close();
            }
        };

        closeBtn && closeBtn.addEventListener('click', close);
        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) close();
        });
        window.addEventListener('keydown', onKeyDown);

        document.body.appendChild(overlay);
        document.body.classList.add('blog-modal-open');
        requestAnimationFrame(() => {
            overlay.style.opacity = '1';
        });
    };

    const renderBlogFeed = (items) => {
        const blogPage = document.getElementById(BLOG_PAGE_ID);
        if (!blogPage) return;

        const statusEl = blogPage.querySelector('.blog-status');
        const gridEl = blogPage.querySelector('.blog-grid');
        if (!statusEl || !gridEl) return;

        gridEl.innerHTML = '';

        const sliced = (items || []).slice(0, BLOG_MAX_ITEMS);
        if (sliced.length === 0) {
            statusEl.textContent = 'No hay publicaciones disponibles ahora mismo.';
            return;
        }

        statusEl.textContent = '';

        sliced.forEach(item => {
            const card = document.createElement('article');
            card.className = 'blog-card';
            card.tabIndex = 0;
            card.setAttribute('role', 'button');

            const extractedImageUrl = getMediumItemImage(item);
            const cachedOgImageUrl = extractedImageUrl ? '' : getCachedOgImage(item.link);
            const imageUrl = extractedImageUrl || cachedOgImageUrl || '';
            if (imageUrl) card.dataset.coverUrl = imageUrl;

            const openFromCard = () => {
                const coverUrl = card.dataset.coverUrl || imageUrl;
                openBlogArticleModal({
                    title: item.title || '',
                    pubDate: item.pubDate,
                    link: item.link,
                    html: item.content || item.description || '',
                    imageUrl: coverUrl
                });
            };

            card.addEventListener('click', () => {
                openFromCard();
            });

            card.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openFromCard();
                }
            });

            const media = document.createElement('div');
            media.className = 'blog-card-media';
            if (imageUrl) {
                const img = document.createElement('img');
                img.src = imageUrl;
                img.alt = item.title || 'Publicación de Medium';
                img.loading = 'lazy';
                media.appendChild(img);
            }
            
            const tapHint = document.createElement('span');
            tapHint.className = 'tap-hint';
            tapHint.setAttribute('aria-hidden', 'true');
            tapHint.textContent = '👆';
            media.appendChild(tapHint);

            const body = document.createElement('div');
            body.className = 'blog-card-body';

            const title = document.createElement('h3');
            title.className = 'blog-card-title';
            title.textContent = item.title || '';

            const meta = document.createElement('div');
            meta.className = 'blog-card-meta';
            meta.textContent = formatBlogDate(item.pubDate);

            const excerpt = document.createElement('p');
            excerpt.className = 'blog-card-excerpt';
            const rawText = stripHtmlToText(item.description || item.content || '');
            excerpt.textContent = rawText.length > 160 ? `${rawText.slice(0, 160).trim()}...` : rawText;

            const cta = document.createElement('a');
            cta.className = 'blog-card-cta';
            cta.textContent = 'Leer en Medium';
            cta.href = item.link;
            cta.target = '_blank';
            cta.rel = 'noopener';
            cta.addEventListener('click', (event) => {
                event.stopPropagation();
            });
            cta.addEventListener('keydown', (event) => {
                event.stopPropagation();
            });

            body.appendChild(title);
            body.appendChild(meta);
            body.appendChild(excerpt);
            body.appendChild(cta);

            card.appendChild(media);
            card.appendChild(body);
            gridEl.appendChild(card);

            if (!imageUrl && item.link) {
                fetchMediumOgImage(item.link)
                    .then(fetchedUrl => {
                        if (!isUsableImageUrl(fetchedUrl)) return;
                        if (!card.isConnected) return;
                        if (card.dataset.coverUrl) return;

                        card.dataset.coverUrl = fetchedUrl;
                        if (!media.querySelector('img')) {
                            const img = document.createElement('img');
                            img.src = fetchedUrl;
                            img.alt = item.title || 'Publicación de Medium';
                            img.loading = 'lazy';
                            media.appendChild(img);
                        }
                    })
                    .catch(() => {
                        // ignore
                    });
            }
        });
    };

    const ensureBlogFeedLoaded = async ({ force = false } = {}) => {
        const blogPage = document.getElementById(BLOG_PAGE_ID);
        if (!blogPage) return;

        const statusEl = blogPage.querySelector('.blog-status');
        const gridEl = blogPage.querySelector('.blog-grid');
        if (!statusEl || !gridEl) return;

        if (!force) {
            const cached = getCachedBlogFeed();
            if (cached) {
                renderBlogFeed(cached);
                scrollRevealTargetsByPage.delete(BLOG_PAGE_ID);
                resetScrollRevealForPage(BLOG_PAGE_ID);
                return;
            }
        }

        statusEl.textContent = 'Cargando publicaciones...';
        gridEl.innerHTML = '';

        try {
            const items = await fetchMediumFeed();
            setCachedBlogFeed(items);
            renderBlogFeed(items);
            scrollRevealTargetsByPage.delete(BLOG_PAGE_ID);
            resetScrollRevealForPage(BLOG_PAGE_ID);
        } catch (error) {
            statusEl.textContent = 'No se pudieron cargar las publicaciones. Puedes verlas directamente en Medium.';
            gridEl.innerHTML = '';
        }
    };

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

    const hideLoadingOverlay = () => {
        document.body.classList.remove('is-loading');
        if (loadingOverlay) {
            loadingOverlay.classList.add('is-hidden');
        }
        restoreScrollPosition(currentPageId);
    };

    // Hide overlay when page loads
    window.addEventListener('load', () => {
        setTimeout(hideLoadingOverlay, 300);
    });

    // Fallback: hide overlay after max 5 seconds even if load event doesn't fire
    setTimeout(() => {
        if (document.body.classList.contains('is-loading')) {
            console.warn('Loading timeout reached, forcing overlay hide');
            hideLoadingOverlay();
        }
    }, 5000);
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
        const logoHandler = function(e) {
            e.preventDefault();
            e.stopPropagation();
            window.location.hash = 'home';
        };
        logoLink.addEventListener('click', logoHandler);
        logoLink.addEventListener('touchend', logoHandler, { passive: false });
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

        // Load videos in the active page (for preload="none" videos)
        const pageVideos = targetPageElement.querySelectorAll('video[preload="none"]');
        pageVideos.forEach(video => {
            if (video.readyState === 0) {
                video.load();
            }
        });

        resetScrollRevealForPage(targetPageId);

        if (targetPageId === BLOG_PAGE_ID) {
            ensureBlogFeedLoaded();
        }
        
        if (shouldScrollTop) {
            window.scrollTo(0, 0);
        }
    }

    // Initial page load should preserve scroll position
    showPageFromHash({ shouldScrollTop: false });
    
    // Listen for hash changes (navigate to sections)
    window.addEventListener('hashchange', () => showPageFromHash({ shouldScrollTop: true }));

    // Parallax scrolling effect (exclude home-gallery background)
    // Disabled on mobile devices for better performance and compatibility
    const isMobile = window.matchMedia('(max-width: 768px)').matches || 
                     /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    
    // Mobile video optimization: pause videos not in viewport
    if (isMobile) {
        const allVideos = document.querySelectorAll('video');
        
        const videoObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const video = entry.target;
                if (entry.isIntersecting) {
                    video.play().catch(() => {});
                } else {
                    video.pause();
                }
            });
        }, { threshold: 0.1 });
        
        allVideos.forEach(video => {
            video.pause();
            videoObserver.observe(video);
        });
    }
    
    if (!isMobile) {
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
    }
    
    // Team profile modal
    const openTeamModal = ({ name, specialty, description, image, portfolio }) => {
        const overlay = document.createElement('div');
        overlay.className = 'team-modal';
        overlay.innerHTML = `
            <div class="team-modal-content" role="dialog" aria-modal="true" aria-label="${(name || 'Miembro del equipo').replace(/"/g, '&quot;')}">
                <button class="team-modal-close" type="button" aria-label="Cerrar">&times;</button>
                <div class="team-modal-body">
                    <div class="team-modal-media">
                        <div class="team-modal-photo">
                            <img src="${image || ''}" alt="${name ? `Retrato de ${name}` : 'Miembro del equipo'}" class="${name === 'Tailor' ? 'tailor-modal-img' : ''}" loading="lazy" decoding="async">
                        </div>
                    </div>
                    <div class="team-modal-info">
                        <p class="team-modal-eyebrow">${specialty || ''}</p>
                        <h2 class="team-modal-name">${name || ''}</h2>
                        <p class="team-modal-description">${description || ''}</p>
                        <div class="team-modal-actions">
                            ${portfolio ? `<a href="${portfolio}" class="btn btn-primary team-modal-portfolio-btn" data-page="${portfolio.replace('#', '')}">Ver Portfolio</a>` : ''}
                            <button class="btn btn-secondary team-modal-close-btn" type="button">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const closeBtn = overlay.querySelector('.team-modal-close');
        const closeCta = overlay.querySelector('.team-modal-close-btn');
        const portfolioBtn = overlay.querySelector('.team-modal-portfolio-btn');
        
        const close = () => {
            window.removeEventListener('keydown', onKeyDown);
            overlay.style.opacity = '0';
            document.body.classList.remove('team-modal-open');
            setTimeout(() => overlay.remove(), 250);
        };
        
        const onKeyDown = (event) => {
            if (event.key === 'Escape') close();
        };
        
        closeBtn && closeBtn.addEventListener('click', close);
        closeCta && closeCta.addEventListener('click', close);
        portfolioBtn && portfolioBtn.addEventListener('click', close);
        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) close();
        });
        window.addEventListener('keydown', onKeyDown);
        
        document.body.appendChild(overlay);
        document.body.classList.add('team-modal-open');
        requestAnimationFrame(() => {
            overlay.style.opacity = '1';
        });
    };
    
    const teamCards = document.querySelectorAll('.team-profile-card[data-member-name]');
    teamCards.forEach(card => {
        const button = card.querySelector('.team-card-cta');
        const handleClick = (event) => {
            event.preventDefault();
            event.stopPropagation();
            openTeamModal({
                name: card.dataset.memberName,
                specialty: card.dataset.memberSpecialty,
                description: card.dataset.memberDescription,
                image: card.dataset.memberImage,
                portfolio: card.dataset.memberPortfolio
            });
        };
        
        if (button) {
            button.addEventListener('click', handleClick);
        }
    });
    
    // Image lightbox for care images
    const openImageLightbox = ({ imageSrc, title }) => {
        const overlay = document.createElement('div');
        overlay.className = 'image-lightbox';
        overlay.innerHTML = `
            <div class="image-lightbox-content">
                <button class="image-lightbox-close" type="button" aria-label="Cerrar">&times;</button>
                <img src="${imageSrc}" alt="${title || 'Imagen'}">
                ${title ? `<p class="image-lightbox-title">${title}</p>` : ''}
            </div>
        `;
        
        const closeBtn = overlay.querySelector('.image-lightbox-close');
        
        const close = () => {
            window.removeEventListener('keydown', onKeyDown);
            overlay.style.opacity = '0';
            setTimeout(() => overlay.remove(), 250);
        };
        
        const onKeyDown = (event) => {
            if (event.key === 'Escape') close();
        };
        
        closeBtn && closeBtn.addEventListener('click', close);
        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) close();
        });
        window.addEventListener('keydown', onKeyDown);
        
        document.body.appendChild(overlay);
        requestAnimationFrame(() => {
            overlay.style.opacity = '1';
        });
    };
    
    const careImageCards = document.querySelectorAll('.care-image-card');
    careImageCards.forEach(card => {
        card.addEventListener('click', () => {
            openImageLightbox({
                imageSrc: card.dataset.image,
                title: card.dataset.title
            });
        });
    });
    
    // Dibujos gallery lightbox
    const dibujosGalleryItems = document.querySelectorAll('.dibujos-gallery-item');
    dibujosGalleryItems.forEach(item => {
        item.addEventListener('click', () => {
            openImageLightbox({
                imageSrc: item.dataset.image,
                title: ''
            });
        });
    });
    
    // FAQ modal
    const openFaqModal = ({ question, answer }) => {
        const overlay = document.createElement('div');
        overlay.className = 'faq-modal';
        overlay.innerHTML = `
            <div class="faq-modal-content" role="dialog" aria-modal="true">
                <button class="faq-modal-close" type="button" aria-label="Cerrar">&times;</button>
                <h3 class="faq-modal-question">${question || ''}</h3>
                <p class="faq-modal-answer">${answer || ''}</p>
            </div>
        `;
        
        const closeBtn = overlay.querySelector('.faq-modal-close');
        
        const close = () => {
            window.removeEventListener('keydown', onKeyDown);
            overlay.style.opacity = '0';
            setTimeout(() => overlay.remove(), 250);
        };
        
        const onKeyDown = (event) => {
            if (event.key === 'Escape') close();
        };
        
        closeBtn && closeBtn.addEventListener('click', close);
        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) close();
        });
        window.addEventListener('keydown', onKeyDown);
        
        document.body.appendChild(overlay);
        requestAnimationFrame(() => {
            overlay.style.opacity = '1';
        });
    };
    
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(item => {
        item.addEventListener('click', () => {
            openFaqModal({
                question: item.dataset.question,
                answer: item.dataset.answer
            });
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
        navMenu.classList.remove('styles-submenu-scroll');
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
            navMenu.classList.remove('styles-submenu-scroll');
        }
    };

    if (mobileMenuToggle && navMenu) {
        const toggleMobileMenuHandler = (e) => {
            e.preventDefault();
            e.stopPropagation();
            const isExpanded = mobileMenuToggle.getAttribute('aria-expanded') === 'true';
            if (isExpanded) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        };
        mobileMenuToggle.addEventListener('click', toggleMobileMenuHandler);
        mobileMenuToggle.addEventListener('touchend', toggleMobileMenuHandler, { passive: false });

        if (mobileMenuOverlay) {
            mobileMenuOverlay.addEventListener('click', closeMobileMenu);
            mobileMenuOverlay.addEventListener('touchend', closeMobileMenu, { passive: true });
        }

        if (mobileSubmenuBack) {
            const submenuBackHandler = (event) => {
                event.preventDefault();
                event.stopPropagation();
                closeMobileSubmenu();
            };
            mobileSubmenuBack.addEventListener('click', submenuBackHandler);
            mobileSubmenuBack.addEventListener('touchend', submenuBackHandler, { passive: false });
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
        const navLinkHandler = function(e) {
            e.preventDefault();
            e.stopPropagation();
            handleNavClick(this, e);
        };
        link.addEventListener('click', navLinkHandler);
        link.addEventListener('touchend', navLinkHandler, { passive: false });
    });
    
    // Category navigation
    categoryLinks.forEach(link => {
        const categoryHandler = function(e) {
            e.preventDefault();
            e.stopPropagation();
            const targetPage = this.getAttribute('data-page');
            window.location.hash = targetPage;
        };
        link.addEventListener('click', categoryHandler);
        link.addEventListener('touchend', categoryHandler, { passive: false });
    });
    
    // Dropdown menu functionality
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        const dropdownHandler = function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (isMobileViewport()) {
                const submenuItems = this.nextElementSibling?.querySelectorAll('a[data-page]');
                if (!submenuItems || submenuItems.length === 0 || !mobileSubmenuList || !mobileSubmenuTitle) return;

                if (navMenu) {
                    navMenu.classList.remove('styles-submenu-scroll');
                }

                const isStylesSubmenu = this.textContent.trim().toLowerCase() === 'estilos';
                if (isStylesSubmenu && navMenu) {
                    navMenu.classList.add('styles-submenu-scroll');
                }

                mobileSubmenuTitle.textContent = this.textContent.trim();
                mobileSubmenuList.innerHTML = '';
                submenuItems.forEach(item => {
                    const li = document.createElement('li');
                    const link = document.createElement('a');
                    link.href = '#';
                    link.dataset.page = item.dataset.page;
                    link.textContent = item.textContent.trim();
                    const linkHandler = (evt) => {
                        evt.preventDefault();
                        evt.stopPropagation();
                        handleNavClick(link, evt);
                    };
                    link.addEventListener('click', linkHandler);
                    link.addEventListener('touchend', linkHandler, { passive: false });
                    li.appendChild(link);
                    mobileSubmenuList.appendChild(li);
                });

                navMenu.classList.add('show-submenu');
                if (mobileSubmenuWrapper) {
                    mobileSubmenuWrapper.setAttribute('aria-hidden', 'false');
                }
            } else {
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
        };
        
        toggle.addEventListener('click', dropdownHandler);
        toggle.addEventListener('touchend', dropdownHandler, { passive: false });
    });
    
    const galleryItems = document.querySelectorAll('.gallery-item, .card-gallery img, .gallery-main img, .portfolio-section .masonry-item img, .fineline-gallery .masonry-item');
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

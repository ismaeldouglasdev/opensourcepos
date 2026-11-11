/**
 * Navigation Cache - PJAX-like smooth navigation with caching
 * Intercepts main nav links, loads via AJAX, caches responses
 */

(function() {
    'use strict';

    var cache = new Map();
    var cacheTimestamps = new Map();
    var CACHE_TTL = 5 * 60 * 1000; // 5 minutes
    var isNavigating = false;

    // Selectors for main navigation links (catch-all; exclusion is done below
    // by path so a catch-all selector can never re-include an excluded page)
    var navSelectors = [
        '.nav.navbar-nav a[href]',
        '.simple-mode-toggle a[href]'
    ].join(', ');

    // Pages that must ALWAYS do a full browser reload: they use bootstrap-table
    // / complex inline JS whose initialization does not survive a nav-cache
    // content swap (blank page). Checked by path prefix, not by selector,
    // because '.nav.navbar-nav a[href]' matches every nav link.
    var excludedPatterns = [
        '/items', '/customers', '/suppliers', '/sales', '/receivings', '/reports',
        '/config', '/giftcards', '/expenses', '/employees', '/cash', '/debtors'
    ];

    function isExcluded(href) {
        var path = href.indexOf(window.location.origin) === 0
            ? href.substring(window.location.origin.length)
            : href;
        for (var i = 0; i < excludedPatterns.length; i++) {
            if (path.indexOf(excludedPatterns[i]) === 0) return true;
        }
        return path.indexOf('/logout') !== -1 || path.indexOf('/login') !== -1;
    }

    // Main content container to replace
    var contentSelector = '.container > .row';

    // Show loading indicator
    function showLoading() {
        var loader = document.getElementById('nav-loader');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'nav-loader';
            loader.style.cssText = 'position:fixed;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--os-primary),#2dd4bf);z-index:9999;transform:scaleX(0);transform-origin:left;transition:transform 0.2s ease;';
            document.body.appendChild(loader);
        }
        loader.style.transform = 'scaleX(1)';
    }

    function hideLoading() {
        var loader = document.getElementById('nav-loader');
        if (loader) {
            loader.style.transform = 'scaleX(0)';
        }
    }

    // Extract main content from HTML response
    function extractContent(html, url) {
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');

        // Get the main content
        var content = doc.querySelector(contentSelector);
        if (!content) {
            // Fallback: try to find any .container content
            content = doc.querySelector('.container');
        }

        // Get page title
        var title = doc.title;

        // Get scripts that need to be executed
        var scripts = Array.from(doc.querySelectorAll('script:not([src])')).map(function(s) { return s.textContent; });
        var scriptSrcs = Array.from(doc.querySelectorAll('script[src]')).map(function(s) { return s.src; });

        return {
            html: content ? content.innerHTML : html,
            title: title,
            scripts: scripts,
            scriptSrcs: scriptSrcs,
            url: url
        };
    }

    // Execute inline scripts
    function executeScripts(scripts) {
        scripts.forEach(function(script) {
            try {
                // eslint-disable-next-line no-eval
                eval(script);
            } catch (e) {
                console.warn('Nav-cache script error:', e);
            }
        });
    }

    // Load external scripts
    function loadScripts(srcs, callback) {
        var loaded = 0;
        var total = srcs.length;

        if (total === 0) {
            callback();
            return;
        }

        srcs.forEach(function(src) {
            var existing = document.querySelector('script[src="' + src + '"]');
            if (existing) {
                loaded++;
                if (loaded === total) callback();
                return;
            }

            var script = document.createElement('script');
            script.src = src;
            script.onload = function() {
                loaded++;
                if (loaded === total) callback();
            };
            script.onerror = function() {
                loaded++;
                if (loaded === total) callback();
            };
            document.head.appendChild(script);
        });
    }

    // Update active nav link
    function updateActiveNav(url) {
        document.querySelectorAll('.nav.navbar-nav li').forEach(function(li) {
            li.classList.remove('active');
        });

        var path = new URL(url, window.location.origin).pathname;
        document.querySelectorAll('.nav.navbar-nav a').forEach(function(a) {
            var href = a.getAttribute('href');
            if (href && (path === href || (href !== '/' && path.indexOf(href) === 0))) {
                a.parentElement.classList.add('active');
            }
        });
    }

    // Navigate to URL
    function navigate(url, pushState) {
        if (isNavigating) return Promise.resolve();
        isNavigating = true;

        // Check cache
        var cached = cache.get(url);
        var timestamp = cacheTimestamps.get(url);
        var now = Date.now();

        if (cached && timestamp && (now - timestamp) < CACHE_TTL) {
            // Use cached version
            applyContent(cached, url, pushState);
            isNavigating = false;
            return Promise.resolve();
        }

        // Show loading
        showLoading();

        return fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-Nav-Cache': 'true'
            },
            credentials: 'same-origin'
        })
        .then(function(response) {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.text();
        })
        .then(function(html) {
            var content = extractContent(html, url);
            cache.set(url, content);
            cacheTimestamps.set(url, Date.now());
            applyContent(content, url, pushState);
        })
        .catch(function(err) {
            console.error('Nav-cache error:', err);
            // Fallback to normal navigation
            window.location.href = url;
        })
        .finally(function() {
            hideLoading();
            isNavigating = false;
        });
    }

    // Apply content to page
    function applyContent(content, url, pushState) {
        var container = document.querySelector(contentSelector);
        if (container) {
            container.innerHTML = content.html;
        }

        document.title = content.title;

        if (pushState !== false) {
            history.pushState({ navCache: true }, '', url);
        }

        updateActiveNav(url);

        // Execute inline scripts
        executeScripts(content.scripts);

        // Load external scripts
        loadScripts(content.scriptSrcs, function() {
            // Trigger events for other scripts
            window.dispatchEvent(new CustomEvent('navCache:load', { detail: { url: url } }));
            $(document).trigger('navCache:load', url);
        });

        // Scroll to top
        window.scrollTo(0, 0);
    }

    // Handle popstate (back/forward buttons)
    window.addEventListener('popstate', function(e) {
        if (e.state && e.state.navCache) {
            var url = window.location.href;
            var cached = cache.get(url);
            if (cached) {
                applyContent(cached, url, false);
            } else {
                navigate(url, false);
            }
        }
    });

    // Intercept navigation clicks
    function interceptClicks() {
        document.addEventListener('click', function(e) {
            var link = e.target.closest('a[href]');
            if (!link) return;

            var href = link.getAttribute('href');
            if (!href) return;

            // Skip external links, anchors, downloads, target=_blank, etc.
            if (
                href.indexOf('http') === 0 && href.indexOf(window.location.origin) !== 0 ||
                href.indexOf('#') === 0 ||
                link.target === '_blank' ||
                link.hasAttribute('download') ||
                link.classList.contains('no-nav-cache') ||
                e.ctrlKey || e.metaKey || e.shiftKey
            ) {
                return;
            }

            // Only intercept main navigation links
            if (!link.matches(navSelectors)) return;
            if (isExcluded(href)) return;

            e.preventDefault();
            navigate(href, true);
        }, true); // Use capture phase
    }

    // Preload on hover
    var preloadTimers = {};
    function preloadOnHover() {
        document.addEventListener('mouseover', function(e) {
            var link = e.target.closest('a[href]');
            if (!link || !link.matches(navSelectors)) return;

            var href = link.getAttribute('href');
            if (!href || isExcluded(href) || cache.has(href)) return;

            // Debounce preload
            clearTimeout(preloadTimers[href]);
            preloadTimers[href] = setTimeout(function() {
                fetch(href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-Nav-Cache': 'true' },
                    credentials: 'same-origin'
                })
                .then(function(r) { return r.text(); })
                .then(function(html) {
                    var content = extractContent(html, href);
                    cache.set(href, content);
                    cacheTimestamps.set(href, Date.now());
                })
                .catch(function() {});
            }, 100);
        }, true);

        document.addEventListener('mouseout', function(e) {
            var link = e.target.closest('a[href]');
            if (!link) return;
            var href = link.getAttribute('href');
            if (href && preloadTimers[href]) {
                clearTimeout(preloadTimers[href]);
            }
        }, true);
    }

    // Initialize
    function init() {
        // Cache current page
        var currentUrl = window.location.href;
        var container = document.querySelector(contentSelector);
        if (container) {
            cache.set(currentUrl, {
                html: container.innerHTML,
                title: document.title,
                scripts: [],
                scriptSrcs: [],
                url: currentUrl
            });
            cacheTimestamps.set(currentUrl, Date.now());
        }

        interceptClicks();
        preloadOnHover();

        console.log('Nav-cache initialized');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose for debugging
    window.navCache = {
        cache: cache,
        navigate: navigate,
        clear: function() { cache.clear(); cacheTimestamps.clear(); }
    };
})();
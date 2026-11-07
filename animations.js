(function () {
    var selector = '.main-story, .story, .news-row, .quick-item, .side-box, .category-head, .comment, .stat, .panel, .article-main > *';
    var seen = new WeakSet();
    var counter = 0;

    var observer = 'IntersectionObserver' in window ? new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 }) : null;

    function prepare(el) {
        if (!observer || seen.has(el) || el.closest('#loadedList')) {
            return;
        }
        seen.add(el);
        el.classList.add('reveal');
        el.style.setProperty('--d', (counter % 4) * 90 + 'ms');
        counter++;
        observer.observe(el);
    }

    document.querySelectorAll(selector).forEach(prepare);

    var list = document.getElementById('loadedList');
    if (list) {
        new MutationObserver(function (changes) {
            var index = 0;
            changes.forEach(function (change) {
                change.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1 && node.classList.contains('news-row')) {
                        node.style.setProperty('--i', index++);
                    }
                });
            });
        }).observe(list, { childList: true });
    }

    document.querySelectorAll('.stat-number').forEach(function (el) {
        var end = parseInt(el.textContent, 10);
        if (isNaN(end) || end < 1) {
            return;
        }
        var start = null;
        el.textContent = '0';
        function tick(time) {
            if (start === null) {
                start = time;
            }
            var progress = Math.min((time - start) / 1000, 1);
            el.textContent = Math.round(end * (1 - Math.pow(1 - progress, 3)));
            if (progress < 1) {
                requestAnimationFrame(tick);
            }
        }
        requestAnimationFrame(tick);
    });

    if (document.querySelector('.article-text')) {
        var bar = document.createElement('div');
        bar.className = 'read-progress';
        document.body.appendChild(bar);
        var update = function () {
            var room = document.documentElement.scrollHeight - window.innerHeight;
            bar.style.transform = 'scaleX(' + (room > 0 ? Math.min(window.scrollY / room, 1) : 0) + ')';
        };
        window.addEventListener('scroll', update, { passive: true });
        update();
    }
})();
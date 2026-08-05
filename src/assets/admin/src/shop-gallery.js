(function () {
    "use strict";

    var initialize = function (gallery) {
        if (gallery.dataset.sxGalleryReady === "1") {
            return;
        }

        var slides = Array.prototype.slice.call(gallery.querySelectorAll("[data-sx-gallery-slide]"));
        var thumbs = Array.prototype.slice.call(gallery.querySelectorAll("[data-sx-gallery-thumb]"));
        var previous = gallery.querySelector("[data-sx-gallery-previous]");
        var next = gallery.querySelector("[data-sx-gallery-next]");
        var current = 0;

        if (!slides.length) {
            return;
        }

        var show = function (index, focusThumb, scrollThumb) {
            current = (index + slides.length) % slides.length;
            slides.forEach(function (slide, slideIndex) {
                var active = slideIndex === current;
                slide.classList.toggle("is-active", active);
                slide.setAttribute("aria-hidden", active ? "false" : "true");
            });
            thumbs.forEach(function (thumb, thumbIndex) {
                var active = thumbIndex === current;
                thumb.classList.toggle("is-active", active);
                thumb.setAttribute("aria-current", active ? "true" : "false");
                if (active && scrollThumb) {
                    thumb.scrollIntoView({block: "nearest", inline: "nearest"});
                    if (focusThumb) {
                        thumb.focus();
                    }
                }
            });
        };

        thumbs.forEach(function (thumb, index) {
            thumb.addEventListener("click", function () {
                show(index, false, false);
            });
        });
        if (previous) {
            previous.addEventListener("click", function () {
                show(current - 1, false, true);
            });
        }
        if (next) {
            next.addEventListener("click", function () {
                show(current + 1, false, true);
            });
        }

        gallery.addEventListener("keydown", function (event) {
            if (event.key === "ArrowLeft" || event.key === "ArrowRight") {
                event.preventDefault();
                show(current + (event.key === "ArrowRight" ? 1 : -1), true, true);
            }
        });

        gallery.dataset.sxGalleryReady = "1";
        show(0, false, false);
    };

    var initializeAll = function (root) {
        var scope = root && root.querySelectorAll ? root : document;
        Array.prototype.forEach.call(scope.querySelectorAll("[data-sx-shop-gallery]"), initialize);
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", function () {
            initializeAll(document);
            observe(document.body);
        });
    } else {
        initializeAll(document);
        observe(document.body);
    }

    function observe(root) {
        if (!root || !window.MutationObserver) {
            return;
        }
        new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                Array.prototype.forEach.call(mutation.addedNodes || [], function (node) {
                    if (node.nodeType === 1) {
                        if (node.matches && node.matches("[data-sx-shop-gallery]")) {
                            initialize(node);
                        }
                        initializeAll(node);
                    }
                });
            });
        }).observe(root, {childList: true, subtree: true});
    }
})();

<script type="text/javascript">
        window.$ = function (e, t, l) {
            var n = {"#": "getElementById", ".": "getElementsByClassName", "@": "getElementsByName", "=": "getElementsByTagName", "*": "querySelectorAll"}[e[0]], m = (t === l ? document : t)[n](e.slice(1));
            return m.length < 2 ? m[0] : m
        };
        window.Element.prototype.css = function (prop, value) {
            if (value) {
                this.style[prop] = value;
                return this;
            } else {
                return this.style[prop];
            }
        };
        window.NodeList.prototype.css = function (prop, value) {
            this.each(function (el) {
                el.css(prop, value);
            });
            return this;
        };
        window.Element.prototype.on = function (eventType, callback) {
            eventType = eventType.split(' ');
            for (var i = 0; i < eventType.length; i++) {
                this.addEventListener(eventType[i], callback);
            }
            return this;
        };
    </script>

<script type="text/javascript">
    $('#settingsForm').on('submit', function (e) {
        window.print();
        e.preventDefault();
    });

    setInterval(function () {
        var f = new FormData($('#settingsForm'));
        if (f.get('border')) {
            [].forEach.call($('.label'), function (el) {
                el.classList.add('border1');
            });
        } else {
            [].forEach.call($('.label'), function (el) {
                el.classList.remove('border1');
            });
        }
        if (f.get('perpage')) {
            [].forEach.call($('.label'), function (el) {
                el.classList.add('perpage');
            });
            [].forEach.call($('.label'), function (el) {
                el.css('margin-left', '0mm');
                el.css('margin-top', '0mm');
                el.css('margin-bottom', '0mm');
                el.css('margin-right', '0mm');
            });
        } else {
            [].forEach.call($('.label'), function (el) {
                el.classList.remove('perpage');
            });
            var left = f.get('left');
            var right = f.get('right');
            var bottom = f.get('bottom');
            var top = f.get('top');
            [].forEach.call($('.label'), function (el) {
                el.css('margin-left', left + 'mm');
                el.css('margin-top', top + 'mm');
                el.css('margin-bottom', bottom + 'mm');
                el.css('margin-right', right + 'mm');
            });
        }
    }, 400);

    setTimeout(function() {
        window.print();
    }, 1000);

</script>
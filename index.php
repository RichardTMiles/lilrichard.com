<?php

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {

    $word = htmlentities(ltrim(ltrim($_SERVER['REQUEST_URI'], '\\'), '/'));

    $Inverted = json_decode(file_get_contents('websearch/database/inverted.json'), true);

    if (array_key_exists($word, $Inverted)) {
        print $word . ' => ';
        print_r($Inverted[$word]);
    } else {
        if (empty($word)) {
            print 'Please enter a query to start searching';
        } else {
            print "$word was not found in the collection!" . PHP_EOL;
        }
    }
    return 0;
}

?>
<!DOCTYPE HTML>
<!--
	Identity by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>
<head>
    <title>Richard Tyler Miles</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <!--[if lte IE 8]>
    <script src="/assets/js/html5shiv.js"></script><![endif]-->
    <link rel="stylesheet" href="/assets/css/main.css"/>
    <!--[if lte IE 9]>
    <link rel="stylesheet" href="/assets/css/ie9.css"/><![endif]-->
    <!--[if lte IE 8]>
    <link rel="stylesheet" href="/assets/css/ie8.css"/><![endif]-->
    <noscript>
        <link rel="stylesheet" href="/assets/css/noscript.css"/>
    </noscript>
</head>
<script>
    /*
     HTML5 Shiv v3.6.2 | @afarkas @jdalton @jon_neal @rem | MIT/GPL2 Licensed
     */
    (function (l, f) {
        function m() {
            var a = e.elements;
            return "string" == typeof a ? a.split(" ") : a
        }

        function i(a) {
            var b = n[a[o]];
            b || (b = {}, h++, a[o] = h, n[h] = b);
            return b
        }

        function p(a, b, c) {
            b || (b = f);
            if (g) return b.createElement(a);
            c || (c = i(b));
            b = c.cache[a] ? c.cache[a].cloneNode() : r.test(a) ? (c.cache[a] = c.createElem(a)).cloneNode() : c.createElem(a);
            return b.canHaveChildren && !s.test(a) ? c.frag.appendChild(b) : b
        }

        function t(a, b) {
            if (!b.cache) b.cache = {}, b.createElem = a.createElement, b.createFrag = a.createDocumentFragment, b.frag = b.createFrag();
            a.createElement = function (c) {
                return !e.shivMethods ? b.createElem(c) : p(c, a, b)
            };
            a.createDocumentFragment = Function("h,f", "return function(){var n=f.cloneNode(),c=n.createElement;h.shivMethods&&(" + m().join().replace(/\w+/g, function (a) {
                b.createElem(a);
                b.frag.createElement(a);
                return 'c("' + a + '")'
            }) + ");return n}")(e, b.frag)
        }

        function q(a) {
            a || (a = f);
            var b = i(a);
            if (e.shivCSS && !j && !b.hasCSS) {
                var c, d = a;
                c = d.createElement("p");
                d = d.getElementsByTagName("head")[0] || d.documentElement;
                c.innerHTML = "x<style>article,aside,figcaption,figure,footer,header,hgroup,main,nav,section{display:block}mark{background:#FF0;color:#000}</style>";
                c = d.insertBefore(c.lastChild, d.firstChild);
                b.hasCSS = !!c
            }
            g || t(a, b);
            return a
        }

        var k = l.html5 || {}, s = /^<|^(?:button|map|select|textarea|object|iframe|option|optgroup)$/i,
            r = /^(?:a|b|code|div|fieldset|h1|h2|h3|h4|h5|h6|i|label|li|ol|p|q|span|strong|style|table|tbody|td|th|tr|ul)$/i,
            j, o = "_html5shiv", h = 0, n = {}, g;
        (function () {
            try {
                var a = f.createElement("a");
                a.innerHTML = "<xyz></xyz>";
                j = "hidden" in a;
                var b;
                if (!(b = 1 == a.childNodes.length)) {
                    f.createElement("a");
                    var c = f.createDocumentFragment();
                    b = "undefined" == typeof c.cloneNode ||
                        "undefined" == typeof c.createDocumentFragment || "undefined" == typeof c.createElement
                }
                g = b
            } catch (d) {
                g = j = !0
            }
        })();
        var e = {
            elements: k.elements || "abbr article aside audio bdi canvas data datalist details figcaption figure footer header hgroup main mark meter nav output progress section summary time video",
            version: "3.6.2",
            shivCSS: !1 !== k.shivCSS,
            supportsUnknownElements: g,
            shivMethods: !1 !== k.shivMethods,
            type: "default",
            shivDocument: q,
            createElement: p,
            createDocumentFragment: function (a, b) {
                a || (a = f);
                if (g) return a.createDocumentFragment();
                for (var b = b || i(a), c = b.frag.cloneNode(), d = 0, e = m(), h = e.length; d < h; d++) c.createElement(e[d]);
                return c
            }
        };
        l.html5 = e;
        q(f)
    })(this, document);

</script>
<script src="/jQuery.min.js"></script>
<script>
    function search(url) {
        $.get(url, (data) => {
            $("#results").html(data);
            $("#me").html('')
        })
    }
</script>
<body class="is-loading">

<!-- Wrapper -->
<div id="wrapper">


    <!-- Main -->
    <section id="main">
        <!-- div class="input-group input-group-sm">
            <input id="search" type="text" class="form-control" onkeyup="search(this.value)">
            <span id="results" class="input-group-btn"></span>
        </div -->
        <div id="me">
            <header>
                <span class="avatar"><img src="/images/me.jpg" alt=""/></span>
                <h1>Richard Tyler Miles</h1>
                <h2>Computer Scientist</h2>
                <h4>Full Stack Developer</h4>
            </header>

            <br/>

            <footer>
                <ul class="icons">
                    <li><a href="https://github.com/RichardTMiles" class="fa-github">GitHub</a></li>
                    <li><a href="https://www.linkedin.com/in/Richardtmiles/" class="fa-linkedin">linkedin</a></li>
                    <li><a href="http://www.carbonphp.com/" class="fa-terminal">Featured Projects</a></li>
                    <li><a href="https://www.instagram.com/rich4miles/" class="fa-instagram">Instagram</a></li>
                    <li><a href="mailto:Richard@Miles.Systems" target="_top" class="fa-envelope">Email</a></li>
                </ul>
            </footer>

        </div>

        <!--hr /-->
    </section>

</div>


<!-- Scripts -->
<!--[if lte IE 8]>
<script src="assets/js/respond.min.js"></script><![endif]-->
<script>
    if ('addEventListener' in window) {
        window.addEventListener('load', function () {
            document.body.className = document.body.className.replace(/\bis-loading\b/, '');
        });
        document.body.className += (navigator.userAgent.match(/(MSIE|rv:11\.0)/) ? ' is-ie' : '');
    }

</script>

</body>
</html>
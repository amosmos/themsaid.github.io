<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{$siteDescription}}">

    <title>{{$siteName}} @yield('pageTitle')</title>

    <link href='https://cdnjs.cloudflare.com/ajax/libs/normalize/3.0.3/normalize.min.css' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,300' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="/libs.css">
    <link rel="stylesheet" href="/prism.css">
    <link rel="stylesheet" href="/style.css">
</head>

<body>

<div class="container">
    <div class="row main-row">
        <div class="col-md-8 main-content">
            @yield('body')
        </div>

        <div class="col-md-3 col-md-offset-1 sidebar">
            <div class="avatar">
                <a href="/"><img src="http://s7.postimg.org/weovsp0qz/themsaid.jpg"></a>
            </div>

            <h1>themsaid</h1>

            <p>Mohamed Said is a web developer [PHP, Laravel, Vue.js] & iOS beginner.</p>

            <a href="https://github.com/themsaid">@github</a> ·
            <a href="https://twitter.com/themsaid">@twitter</a> ·
            <a href="mailto:themsaid@gmail.com">@email</a>
        </div>
    </div>

    <p class="footer">
        © Mohamed Said 2016 · Built with <a href="http://themsaid.github.io/katana/">Katana</a>
    </p>
</div>

<script src="/prism.js"></script>

<script>!function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location) ? 'http' : 'https';
        if (!d.getElementById(id)) {
            js = d.createElement(s);
            js.id = id;
            js.src = p + '://platform.twitter.com/widgets.js';
            fjs.parentNode.insertBefore(js, fjs);
        }
    }(document, 'script', 'twitter-wjs');</script>
<script>(function (i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function () {
                    (i[r].q = i[r].q || []).push(arguments)
                }, i[r].l = 1 * new Date();
        a = s.createElement(o), m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        m.parentNode.insertBefore(a, m)
    })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');
    ga('create', 'UA-61501903-1', 'auto');
    ga('send', 'pageview');
</script>
</body>
</html>
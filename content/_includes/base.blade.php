<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{$siteDescription}}">

    <title>{{$siteName}} @yield('pageTitle')</title>

    <link href="@url('/feed.rss')" rel="alternate" type="application/rss+xml" title="Mohamed Said - themsaid"/>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/normalize/3.0.3/normalize.min.css' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,300' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="/libs.css">
    <link rel="stylesheet" href="/prism.css">
    <link rel="stylesheet" href="/style.css">
</head>

<body>

<div class="container">
    <div class="row main-row">
        <div class="col-md-8 col-md-offset-2 main-content">
            <div class="row intro" style="margin-bottom: 70px">
                <div class="col-md-2">
                    <div class="avatar">
                        <a href="/"><img src="http://s16.postimg.org/5nstumeut/Toim_KRp_Y.jpg"></a>
                    </div>
                </div>
                <div class="col-md-10">
                    <h1 style="margin: 0 0 10px 0">themsaid</h1>

                    <p>
                        Hi! I'm <strong>Mohamed Said</strong>. I work at <a href="https://twitter.com/laravelphp">@laravelphp</a> on weekdays
                        and practise freediving on weekends. Find me as themsaid
                        <a href="https://twitter.com/themsaid">@twitter</a> and <a href="https://github.com/themsaid">@github</a>.
                    </p>
                </div>
            </div>

            @yield('body')

            <p class="footer">
                © Mohamed Said 2017 · Built with <a href="http://themsaid.github.io/katana/">Katana</a>
            </p>
        </div>
    </div>
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
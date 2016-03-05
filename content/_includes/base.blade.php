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

</body>
</html>
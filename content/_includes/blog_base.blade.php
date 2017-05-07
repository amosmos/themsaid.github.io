@extends('_includes.base')

@section('body')

    <div class="container">
        <div class="row main-row">
            <div class="col-md-8 col-md-offset-2 main-content">
                <div class="row intro {{$currentUrlPath=='/'?'home':''}}">
                    <div class="col-md-2">
                        <div class="avatar">
                            <a href="/"><img src="http://s16.postimg.org/5nstumeut/Toim_KRp_Y.jpg"></a>
                        </div>
                    </div>
                    <div class="col-md-10">
                        <h1 style="margin: 0 0 10px 0">themsaid</h1>

                        <p>
                            Hi! I'm <strong>Mohamed Said</strong>. I work at
                            <a href="https://twitter.com/laravelphp">@laravelphp</a> on weekdays
                            and practise freediving on weekends. Find me as themsaid
                            <a href="https://twitter.com/themsaid">@twitter</a> and
                            <a href="https://github.com/themsaid">@github</a>.
                        </p>
                    </div>
                </div>

                @yield('blog_body')

                <p class="footer">
                    © Mohamed Said 2017 · Built with <a href="http://themsaid.github.io/katana/">Katana</a>
                </p>
            </div>
        </div>
    </div>
@stop
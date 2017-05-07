@extends('_includes.base')

@section('body')
    <div class="container">
        <div class="row main-row">
            <div class="col-md-8 col-md-offset-2 main-content">
                <div class="row">
                    <div class="col-md-10">
                        <p>
                            <span style="font-size: 30px">Hello!</span> I'm Mohamed Said. I work at
                            <a href="https://twitter.com/laravelphp">@laravelphp</a> on weekdays
                            and practise freediving on weekends.
                        </p>
                    </div>
                    <div class="col-md-2">
                        <img src="http://s16.postimg.org/5nstumeut/Toim_KRp_Y.jpg" style="width:100%">
                    </div>
                </div>

                <p style="margin-top: 20px;">Every week I'd love to spend 30 minutes talking to a few members of the laravel community, we can talk about everything development related, maybe I can help you
                    solve some technical problems or understand some parts of the framework, maybe you can help me as well.</p>

                <p>
                    So, if you have the time please
                    <a href="mailto:themsaid@gmail.com">send me an email</a> & let me know who you are and what topics you're interested to discuss.
                </p>


                @yield('body')
            </div>
        </div>
    </div>
@stop
@extends('_includes.blog_base')

@section('blog_body')

    <a href="/">Back to home</a>

    <h1 class="blog-post-title">@yield('post::title')</h1>

    @yield('post_body')

    <span class="post-footer">—————————————&nbsp&nbspThat's all folks&nbsp&nbsp—————————————</span>

    <div class="text-center">
        <a href="https://twitter.com/share"
           class="twitter-share-button"
           data-text="{{str_limit($__env->yieldContent('post::title'),95)}}"
           data-via="themsaid">Tweet</a>
        &nbsp&nbsp&nbsp
        <a href="https://twitter.com/intent/tweet?screen_name=themsaid"
           class="twitter-mention-button"
           data-related="themsaid">Tweet to @themsaid</a>
    </div>
@stop
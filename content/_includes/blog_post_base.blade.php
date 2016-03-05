@extends('_includes.base')

@section('body')

    <a href="/">Back to home</a>

    <h1 class="blog-post-title">@yield('post::title')</h1>

    @yield('post_body')

@stop
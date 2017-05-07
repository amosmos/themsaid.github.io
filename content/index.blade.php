@extends('_includes.blog_base')

@section('pageTitle', '- Mohamed Said')

@section('blog_body')

    @foreach($paginatedBlogPosts as $post)
        <div class="blog-post">
            <h3><a href="{{$post->path}}">{{$post->title}}</a></h3>
            <p>{{$post->brief}}</p>
        </div>
    @endforeach

    @include('_includes.blog_paginator')
@stop
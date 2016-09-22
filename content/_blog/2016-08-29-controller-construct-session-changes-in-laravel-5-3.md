---
view::extends: _includes.blog_post_base
view::yields: post_body
post::title: Controller Construct Session Changes in Laravel 5.3
pageTitle: Controller Construct Session Changes in Laravel 5.3
post::brief: Back in laravel 5.2, a developer was able to interact with the session directly in a controller constructor. However, this has changed in laravel 5.3.
---

Back in laravel 5.2, a developer was able to interact with the session directly in a controller constructor. However, this has changed in laravel 5.3.

The difference between how the 5.3 & 5.2 handle an incoming request is that in 5.2 the request goes through 3 pipelines:

1. The global middleware pipeline
2. The route middleware pipeline
3. The controller middleware pipeline

In 5.3 the request goes through only 2 Pipelines:

1. The global middleware pipeline
2. The route & controller middleware in 1 stack

In this post I wrote for [Laravel News](https://laravel-news.com) I explain the effect of this difference and how to deal with it:

<a href="https://laravel-news.com/2016/08/controller-construct-session-changes-in-laravel-5-3/">
<img src="http://s10.postimg.org/gyz46kb09/Screen_Shot_2016_09_22_at_9_11_17_AM.png" width="100%">
</a>

https://laravel-news.com/2016/08/controller-construct-session-changes-in-laravel-5-3/
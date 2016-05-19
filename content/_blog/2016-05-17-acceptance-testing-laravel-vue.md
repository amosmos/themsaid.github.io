---
view::extends: _includes.blog_post_base
view::yields: post_body
post::title: A look into acceptance Testing a Laravel and Vue.js Application
post::brief: In this post I'm sharing my experience with testing a Laravel-Vue based application, in my short journey I've tried NightwatchJs, PHPUnit Selenium Extension, WebDriver, and Codeception.
pageTitle: - Acceptance Testing a Laravel and Vue.js Application
---

A few weeks ago I started writing tests for a relatively large application, writing tests for an application at a mature state is always thought to be hard but I think it's easier, things are already shaped and you know what exactly you need to test.

I wrote about my experience on writing acceptance tests for the project, it has multiple complex screens built using Vue.js, and as you know testing Javascript driven interfaces is not an easy thing to do, in this post I talk about my journey from using laravel's built in Crawler-based testing library to using Selenium server for automating user interactions.

You can read about that on .Dev, a Medium publication dedicated to  developer news, tutorials, interviews and tools:

<a href="https://dotdev.co/acceptance-testing-a-laravel-and-vue-js-application-4160b8e96156"><img style="width:100%" src="http://s32.postimg.org/cirdmrwsl/Screen_Shot_2016_05_19_at_2_34_32_PM.png"></a>
---
view::extends: _includes.blog_post_base
view::yields: post_body
post::title: Laravel 5.3 Notifications
post::brief: 
---

```php
class ServerReady extends Notification
{
    public function via($notifiable)
    {
        return ['mail'];
    }

    public function message()
    {
        $this->application('Acme')
            ->line('Your server is ready!')
            ->action('Upload your files', 'https://acme.com/upload')
            ->line('Can\'t wait to see your cool project up and running.');
    }
}
```


```php
class ServerDestroyed extends Notification
{
    public function via($notifiable)
    {
        return ['mail'];
    }

    public function message()
    {
        $this->application('Acme')
            ->error()
            ->line('Your server was destroyed!')
            ->action('Download Backup', 'https://acme.com/download')
            ->line('We\'re deeply sorry for your loss.');
    }
}
```

If now application provided?
$this->app['config']['app.name']
$this->app['config']['app.logo']

are used


---



# Mohamed Said

I'm a software developer from Cairo, Egypt. Spending most of my time building web applications with Laravel, VueJS, MySQL, and HTML/CSS.

I'm also an occasional writer on my [blog](http://themsaid.com/) and [Medium space](https://medium.com/@themsaid), I write about software development and testing as well as my point of view regarding several issues.
## Previous work Experience:The most important projects I’ve worked on so far are the following:### nafham.comIt’s the biggest online education website in Egypt, it was built using laravel 4.0### jumpsuite.ioIt’s an online marketplace that connects personal trainers with their clients, and allow people to search for a trainer. It was built using laravel 5.0 and Vue.jsThe backend has controllers for managing training programs, generating analytics, and handling customer support. An API is also provided for the operation of a mobile application.### foodics.comFooidcs is a restaurant management system built using laravel 5.2 and Vue.js, it’s basically an application that helps restaurant owners manage all the aspects of their business. The system is currently serving more than 100 restaurant in the Middleast.During my past experience I’ve been handling the entire development process from idea to deployment, most of the time I was the only developer in the team however since I joined foodics 2 years ago I’ve been managing a team of fronted, backend, and mobile developers.## Education:My field of Education was Mechanical Engineering, however I dropped out 2 years ago as I got myself more involved in the software development industry. ## Open Source Projects:#### LaravelI’ve been contributing to laravel since January 2016, currently having 74 merged Pull Request in different areas of the framework.#### Laravel PackagesCurrently maintaining multiple laravel open-source packages- https://github.com/themsaid/laravel-model-transformer- https://github.com/themsaid/laravel-mail-preview- https://github.com/themsaid/laravel-multilingual- https://github.com/themsaid/laravel-langman#### A Static Site GeneratorMaintaining a Blade-based static site generator named “Katana”: https://github.com/themsaid/katana## My Interests:The most interesting activity I do other than coding is swimming, I’m not a professional athlete but I take all the opportunities I get to jump into the water.I love Trance music, House music and Eminem oldies.As for movies, unlike many other coders I’m not so much into Start Trek or Star Wars, maybe because they weren’t very popular in Egypt by the time I started watching movies.However I love a lot of movies including: Cast Away, Braveheart, The Termintor, Intersteller, and all Marvel movies.## Contacts**Github Profile:** https://github.com/themsaid**Twitter Profile:** https://twitter.com/themsaid
**Email:** themsaid@gmail.com
**Website**: http://themsaid.com


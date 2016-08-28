---
view::extends: _includes.blog_post_base
view::yields: post_body
post::title: Laravel core team changes how you access sessions in a controller constructor
post::brief: 
---

Back in laravel 5.2 a developer was able to interact with the session directly in a controller constructor, however this has changed in laravel 5.3.

The difference between how 5.3 & 5.2 handle an incoming request is that in 5.2 the request goes through 3 pipelines:

1. The global middleware pipleline
2. The route middleware pipeline
3. The controller middleware pipeline

By default the global middlware only contains a check for maintenance mode, the route middleware is that you assign to a route in your routes.php file, and by default a web group is assigned to all web routes that contains several middlware including the middleware that starts the session.

And then finally Laravel instantiates your controller to check for controller middleware, at this point the request is all set up during the first and second pipelines, that's why we were able to use sessions and auth in the controller constructor, because the request is totally ready for it.

In 5.3 the request goes through only 2 Pipelines:

1. The global middleware pipeline
2. The route & controller middleware in 1 stack

So laravel collects all route specific middlewares first before running the request through the pipeline, and while collecting the controller middleware an instance of the controller is created, thus the constructor is called, however at this point the request isn't ready yet, and that's where the change in behaviour you notice in 5.3 exists.

The reason for this change is as [Taylor described](https://github.com/laravel/framework/issues/15072#issuecomment-242769373):

> It's very bad to use session or auth in your constructor as no request has happened yet and session and auth are INHERENTLY tied to an HTTP request. You should receive this request in an actual controller method which you can call multiple times with multiple different requests. By forcing your controller to resolve session or auth information in the constructor you are now forcing your entire controller to ignore the actual incoming request which can cause significant problems when testing, etc.

### Using sessions in your constructor in 5.3

Maybe it was a bad practise to use the constructor of the controller to access session variables, however it looks like a good chunk of laravel developers were using this approach and considered the constructor as an inline middeware where they can check if the user can access certain methods in this controller or not.

So the core team came up with a nice solution for this matter, it's described in the upgrade guide as follow:

> As an alternative, you may define a Closure based middleware directly in your controller's constructor. Before using this feature, make sure that your application is running Laravel 5.3.4 or above:

Here's the example from the docs:

```php
<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->projects = Auth::user()->projects;

            return $next($request);
        });
    }
}
```
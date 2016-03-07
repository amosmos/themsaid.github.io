@extends('_includes.blog_post_base')

@section('post::title', 'Coming up in laravel 5.2.23')
@section('post::brief')
By the time of writing this post laravel has 911 contributors on GitHub, some of them are actively adding awesome stuff to the framework on daily basis. This is a wrap up for the new stuff in laravel 5.2.23.
@stop
@section('pageTitle')- @yield('post::title')@stop

@section('post_body')

@yield('post::brief')

@markdown

## in_array validation rule

Array validation in laravel is just awesome, I myself replaced a lot of code in some of the current projects with just a few lines for validation. In 5.2.23 a new rule is added to help with validating that a value of a key exists in another related key:

```php
Validator::make(
	[
		'devices' => [['user_id' => 1], ['user_id' => 2]],
		'users' => [['id' => 1, ['id' => 2]]]
	],
	['devices.*.user_id' => 'in_array:users.*.id']
);
```

This rule will make sure all `user_id` values exists in the users array.

## Arr::first() & Arr::last() now have the callback optional
Previously the callback was required as a second argument for these methods, now it is optional:

```php
$array = [100, 200, 300];

// (NEW) This will return 100
Arr::first($array);

// Same as
array_first($array);

// (NEW) This will return 300
Arr::last($array);

// Same as
array_last($array);

// (You still can) do this and return 200
Arr::first($array, function ($key, $value) {
	return $value >= 150;
});
```

## Specifying more than one middleware at a time

When adding controller middlewares, you're now able to register multiple middlewares in the same statement.

```php
$this->middleware(['auth', 'subscribed'], ['only' => ['getCandy']]);
```

## New Blade directives @@php, @@endphp, and @@unset

The `@@php` directives will allow you to write PHP statements with style:

```html
@@php($count = 1)

@@php(++ $count)
```

```html
@@php
	$now = new DateTime();

	$environment = isset($env) ? $env : "testing";
@enphp
```

The `@@unset` is just a wrapper for `unset()`.

```html
@@unset($count)
```

## Ability to override core blade directives
Prior to 5.2.23 it was not possible to extend Blade and overwrite a core directive, now your extension will overwrite any core blade directive.

## Ability to escape blade directives
Now you can escape compilation for a blade directive by prepending a `@`, just like what we used to do to escape compiling echos:


```html
// output: <?php continue; ?>
@@continue

// output: @@continue
@@@continue
```

## New mail driver for SparkPost

<blockquote class="twitter-tweet" data-lang="en"><p lang="en" dir="ltr">The next release of Laravel 5.2 will contain a mail driver for <a href="https://twitter.com/SparkPost">@SparkPost</a> thanks to a community contribution! 📫</p>&mdash; Taylor Otwell (@taylorotwell) <a href="https://twitter.com/taylorotwell/status/706660698605006849">March 7, 2016</a></blockquote>
<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>


## + hidden gem

Did you know you can already do this?

```php
User::whereNameAndEmail('jon', 'jon@theWall.com')->first();
        
User::whereNameAndEmailOrPhone('jon', 'jon@theWall.com', '123321')->first();

DB::table('users')->whereEmailOrUsername('mail@mail.com', 'themsaid')->first();
```

@endmarkdown
@stop
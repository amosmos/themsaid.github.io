@extends('_includes.blog_post_base')

@section('post::title', 'Coming up in laravel 5.2.23')
@section('post::brief')
By the time of writing this post, laravel has 911 contributors on GitHub, some of them are actively adding awesome
stuff to the framework on a daily basis. This is a wrap up for the new stuff in laravel 5.2.23.
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
Arr::first($array); /** same for **/ array_first($array);


// (NEW) This will return 300
Arr::last($array); /** same for **/ array_last($array);

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

The `@@php` directive will allow you to write PHP statements with style:

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

`@@unset` is just a wrapper for `unset()`.

```html
@@unset($count)
```

## Ability to override core blade directives
Prior to 5.2.23 it was not possible to extend Blade and override a core directive, now your extension will overwrite any core blade directive.

## Ability to escape blade directives
Now you can escape compilation for a blade directive by prepending a `@`, just like what we used to do to escape compiling echos:


```html
// output: <?php continue; ?>
@@continue

// output: @@continue
@@@continue
```

You may also use the new `@@verbatim` to prevent Blade from compiling multiple lines:

```html
@@verbatim
	@@if @{{ $var }} @@endif
@@endverbatim
```

## New mail driver for SparkPost

<blockquote class="twitter-tweet" data-lang="en"><p lang="en" dir="ltr">The next release of Laravel 5.2 will contain a mail driver for <a href="https://twitter.com/SparkPost">@SparkPost</a> thanks to a community contribution! 📫</p>&mdash; Taylor Otwell (@taylorotwell) <a href="https://twitter.com/taylorotwell/status/706660698605006849">March 7, 2016</a></blockquote>
<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>

## New monthlyOn() method for scheduling commands

```php
$schedule->call(function () {
	DB::table('shopping_list')->delete();
})->monthlyOn(4, '12:00');
```

## New isLocale() method

```php
// Instead of this
if (app()->getLocale() == 'en')

// You can do that
if (app()->isLocale('en'))
```

<a name="mysql-json-fields"></a>
## Querying MySQL 5.7 & Postegres json fields fluently with the query builder

With the release of MySQL 5.7, a new column type `JSON` was introduced , in laravel 5.2.23 you're able to query values from a json field as fluent as usual:

Let's say you have a users table with a `name` column of type JSON, the column has the following value:

```json
{"en":"name","ar":"nom"}
```

You can query the json values using the following syntax:

```php
User::where('name->en', 'name')->get();

// You may dive deep in the JSON string using the `->` operator.
User::where('contacts->phone->home', 1234);
```

### Notes for MySQL

The output of these queries will be a JSON string (`"name"` and not `name`), so you'll need to use `json_decode()` before display.

<div class="alert alert-warning">To use this syntax make sure your JSON keys are valid <a href="http://www.ecma-international.org/ecma-262/5.1/#sec-7.6">ECMAScript identifier names</a>.</div>

<a name="see-and-dont-see"></a>
## seeElement() and dontSeeElement() test helpers

While you have this element:

```html
<image width="100" height="50">
```

You may run the following tests and get a success:

```php
$this->seeElement('image', ['width' => 100, 'height' => 50]);

$this->dontSeeElement('image', ['class' => 'video']);
```


## + hidden gem #1

Did you know you can already do this?

```php
User::whereNameAndEmail('jon', 'jon@theWall.com')->first();

User::whereNameAndEmailOrPhone('jon', 'jon@theWall.com', '123321')->first();

DB::table('users')->whereEmailOrUsername('mail@mail.com', 'themsaid')->first();
```

## + hidden gem #2

```php
// Instead of this:
if(!$item){
	abort(404);
}

// You can do that:
abort_unless($item);

// You may also have something like this:
abort_if($item->is_hidden);
```

@endmarkdown
@stop

---
view::extends: _includes.blog_post_base
view::yields: post_body
post::title: New in Laravel 5.4 - Localization using JSON files
pageTitle: New in Laravel 5.4 - Localization using JSON files
post::brief: Laravel 5.4 introduces a new method of localization, it works by reading from JSON files instead of PHP files.
---

In previous version of Laravel, localization was done by converting language keys to plain values based on the application current locale, this was done by using a syntax like this:

```php
{{ trans('catalogue.products.save') }}
```

Using this key laravel will start looking for a `catalogue.php` file inside your `resources/lang/en` directory, and then look into the file for a `save` key that looks like this:

```php
[
	// ...
	"products" => [
		"save" => "Save Product"
	]
	// ...
]
```

This is fine for simple language strings, but it gets really nasty when you need to translate a paragraph or a sentence:

```php
{{ trans('catalogue.error_product_must_have_atleast_one_size') }}
```

Coming up with a descriptive key name for the translation is challenging, because you don't want to change that key in the future and you also want to know what this key is about when you look at it out of its context.

### What's new in Laravel 5.4

```php
{{ __("A product must have at least one size") }}
```

That's what's new, just write translation lines in your preferred language and laravel will look for translations in a `resources/lang/de.json` file, the file would look like this:

```json
{
	"A product must have at least one size": "Das Produkt muss mindestens eine Größe haben."
}
```

If a translation line wasn't found in the language file the same key is returned:

```php
{{ __("A key that doesn't exist") }}

// Will print "A key that doesn't exist"
```

And here's how you can pass parameters:

```php
{{ __('Product ":p" must be member of :g group.', [$product->name, $group->name]) }}
```

It's also worth mentioning that parameters replacement will also happen if the key wasn't found in a language file, that means you don't really have to create a language file for your application's main language.


### About saving translations in JSON files

Saving language lines in JSON format makes it really easy for you to manage the translations using your IDE, in one file, you only need to search for the line that matches the one you see in the interface and update it as you wish.

It also opens the door for package maintainers to build easy tools to handle translations.


### Final Thoughts
This is an early draft of the functionality, I'm really interested to know what you think about it and see if we can make it even better.
---
view::extends: _includes.blog_post_base
view::yields: post_body
post::title: Validation considerations in Laravel 5.4
post::brief: In this post I share a common issue with validating optional form field in Laravel 5.4
---

Laravel 5.4 introduces two new built-in middleware `ConvertEmptyStringsToNull` and `TrimStrings`, the first middleware transforms request parameters with empty
string as a value to `null`, the latter trims the leading and trailing white space from the values of request parameters.

So for example if we have `?age=&email=%20jon@snow.com`, in laravel 5.3 the value of "age" would be an empty string `""` and the value of "email" would be `[ ]jon@snow.com`,
but in laravel 5.4 the value of "age" would be `null` and the value of "email" would be `jon@snow.com`.

> Notice the leading space in the email value when we test using laravel 5.3

 ## Considerations while using the Validator

 While using the validator in laravel 5.4, you should put in mind that empty strings passed from a request object will be converted to null,
 that means the following rule will fail:

 ```php
 "age" => "integer"
 ```

That's because the value of age from the previous example is `null`, in order to make it pass you need to add the `nullable` flag to your rules set:

```php
"age" => "nullable|integer"
```

## Conclusion

If you decided to use the `ConvertEmptyStringsToNull` you need to make sure you use the `nullable` flag on all your optional keys.
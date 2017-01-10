---
view::extends: _includes.blog_post_base
view::yields: post_body
post::title: Conditional Validation Rules
post::brief: In this post I'd like to highlight a trick related to conditionally adding validation rules, this trick is specially important since I've seen many people on Laravel's GitHub repository opening issues complaining about an un-expected validator behaviour...
---

Laravel's validation library is very powerful and easy to use, using a few keystrokes you can build a strong defence around your application, preventing invalid user input from
corrupting the application flow and potentially introducing bugs.

In this post I'd like to highlight a trick related to conditionally adding validation rules, this trick is specially important since I've seen many people on
Laravel's GitHub repository opening issues complaining about an un-expected validator behaviour:

Let's say you have a contact form where you have a set of predefined subjects the user can select from, he can also select `custom` to provide his own message subject,
a simple validation for this could be:

```php
validator($data,
    [
        'subject' => 'required_if:type,custom|min:10'
    ]
);
```

This will make sure the subject field is required only if the type is custom, it also ensures the user inputs a value not less than 10 characters, *however*; having the
given rules, what do you think the result of validation will be like in case the type is **not** custom? Here's the error you'll receive:

```php
array:1 [
  "subject" => array:1 [
    0 => "The subject must be at least 10 characters."
  ]
]
```

If this result doesn't make sense then let me tell you that the problem is that you assumed the validator will skip validation entirely for the field if the type is not custom,
but that's not how it works, the conditional rule `required_if` implies that the field is only **required** if the type is custom, so when the type is not custom the field won't
be required, but the rest of the rules are independent from this condition so the validator will still run these checks.

This confusion occurs because when a required rule fails validation is skipped so no other checks are run:

```php
validator($data,
    [
        'subject' => 'required|min:10'
    ]
);
```

In this example, if the subject is not given the required rule will fail and no other checks will run, so the `min` check will be skipped.

So you need to understand the difference between a `required` rule failing and a conditional required rule being skipped, failing ones makes the validator stop further checks
but skipped ones doesn't have the same effect.

## How to conditionally add validation rules

Enter one of the most unpopular yet powerful validator methods, `sometimes()`, this method accepts three arguments:

- A filed name, or an array of field names.
- A rule definition, of an array of rules.
- A callback.

You can use this magical method to conditionally add rules to the validator, all you need to do is to specify the successful condition by returning true from within the callback.

```php
$validator = validator($data,
    [
        'subject' => 'required_if:type,custom'
    ]
);

$validator->sometimes('subject', 'min:10', function($data){
   return $data->type == 'custom';
});
```

Using the above example the validator will only run the `min` check if `$data->type` is equal to `custom`, which is exactly what we need.

Using sometimes you may pass any number of fields as well:

```php
$validator->sometimes(['parent_name', 'parent_email'], 'required', function($data){
   return $data->age < 16;
});
```

You can also append multiple rules:

```php
$validator->sometimes(['teacher_email', 'parent_email'], 'required|email', function($data){
   return $data->age < 16;
});
```


## There you go!

You've just discovered one more trick that makes validating user input much easier, here's one final example for a trick I use to make distinction between validating
user input in a creation operation and update operation:

```php
$validator = validator($data,
    [
        'name' => 'required',
        'age' => 'integer|min:16',
    ]
);


$validator->sometimes(['name', 'age'], 'sometimes', function($data) use ($user){
   return $user->exists;
});
```

Yes you've read it correct `->sometimes(...., 'sometimes'`, what I do here is that I'm adding the `sometimes` rule to the fields only if the model already exists, that way
the fields will be required on creation, and will be required on updating **only** if they're provided.

To eliminate the closure I also have something like this:

```php
$validator->sometimes(['name', 'age'], 'sometimes', evaluate($user->exists));
```

And here's the definition of the `evaluate()` helper method:

```php
function evaluate($condition)
{
    return function () use ($condition) {
        return $condition;
    };
}
```
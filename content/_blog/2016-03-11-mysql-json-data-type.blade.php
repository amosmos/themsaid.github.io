@extends('_includes.blog_post_base')

@section('post::title', 'MySQL JSON data type decoded')
@section('post::brief')
In this post we're going to explore the new MySQL 5.7 JSON Data Type. While diving into the topic we're
going to use Laravel's fluent query builder.
@stop
@section('pageTitle')- @yield('post::title')@stop

@section('post_body')

@yield('post::brief')

@markdown

```sql
CREATE TABLE `products` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`name` JSON,
`specs` JSON,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
```

And now we insert some values:

```sql
INSERT INTO products VALUES(
    null,
    '{"en": "phone", "it": "telefono"}',
    '{"colors": ["black", "white", "gold"], "size": {"weight": 1, "height": 1}}'
);

INSERT INTO products VALUES(
    null,
    '{"en": "screen", "it": "schermo"}',
    '{"colors": ["black", "silver"], "size": {"weight": 2, "height": 3}}'
);

INSERT INTO products VALUES(
    null,
    '{"en": "car", "it": "auto"}',
    '{"colors": ["red", "blue"], "size": {"weight": 40, "height": 34}}'
);
```

# Reading JSON values

Using a simple syntax, here's how we can read JSON values:

```sql
select
name->"$.en" as name,
specs->"$.size.weight" as weight,
specs->"$.colors" as colors
from products;
```

The result of this query is:

<table class="table table-bordered table-condensed">
    <tr>
        <td><strong>name</strong></td>
        <td><strong>weight</strong></td>
        <td><strong>colors</strong></td>
    </tr>
    <tr>
        <td>"phone"</td>
        <td>1</td>
        <td>["black", "white", "gold"]</td>
    </tr>
    <tr>
        <td>"screen"</td>
        <td>2</td>
        <td>["black", "silver"]</td>
    </tr>
    <tr>
        <td>"car"</td>
        <td>40</td>
        <td>["red", "blue"]</td>
    </tr>
</table>

As you may have noticed the results are produced as JSON strings, that means you'll need to decode them
before display.

```php
json_decode( Products::selectRaw('name->"$.en" as name')->first()->name )
```

### About the syntax

Querying JSON fields is done via the `->` operator, at the left hand side of the operator we add the column name,
the right hand side we place the path syntax.

The Path syntax uses a leading `$` to represent the JSON document followed by selectors that indicate more specific parts
of the document. Here are the different paths to extract data:

- `specs->"$.colors"` returns the array of colors.
- `specs->"$.colors[0]"` returns a JSON string `"black"`.
- `specs->"$.non_existing"` returns NULL.
- `specs->"$.\"key name with space\""` if the key contains spaces.

If the key is not a valid <a href="http://www.ecma-international.org/ecma-262/5.1/#sec-7.6">ECMAScript identifier</a> then
it must be quoted inside the path.


### Using Wildcards

You may also use wild cards to query JSON values, imagine we have the following data:

```json
{"name": "phone", "price": 400, "sizes": [3, 4, 5]}
```

<table class="table table-bordered table-condensed">
    <tr>
        <td style="min-width:200px"><strong>Syntax</strong></td>
        <td style="min-width:200px"><strong>Output</strong></td>
        <td><strong>Notes</strong></td>
    </tr>
    <tr>
        <td>specs->"$.*"</td>
        <td>["phone", [3, 4, 5], [{"name": "black"}, {"name": "gold"}]]</td>
        <td></td>
    </tr>
    <tr>
        <td>specs->"$.sizes[*]"</td>
        <td>[3, 4, 5]</td>
        <td>Same as $.sizes</td>
    </tr>
    <tr>
        <td>specs->"$.colors**.name"</td>
        <td>["black", "gold"]</td>
        <td>The "prefix**suffix" syntax will query for all paths that begin with the prefix and ends with the suffix.</td>
    </tr>
</table>

# Querying JSON values

It works the same as in regular MySQL columns, now that we know how to write a valid path we'll be able to query
and/or sort JSON values, here are various examples:

```sql
select name->"$.en" from products where name->"$.en" = "phone";

select name->"$.en" from products where name->"$.en" IN ("phone");

select specs->"$.size.weight" from products where specs->"$.size.weight" BETWEEN 1 AND 10;

select * from products ORDER BY name->"$.en";
```

# MySQL JSON Data Type and laravel

If you're using laravel 5.2.23 or above you'll be able to use the fluent query builder to query JSON data types:

```php
Product::where('name->en', 'car')->first();

Product::whereIn('specs->size->weight', [1, 2, 3])->get();

Product::select('name->en')->orderBy('specs->size->height', 'DESC')->get();
```

If not then you can use raw statements:

```php
Product::whereRaw('name->"$.en"', 'car')->first();
```

# Conclusion
In many cases developers prefer a NoSQL database for specific features, flexibility, or performance, however SQL databases
are preferred by many developers and a lot of large companies rely on it building high performance web applications, so it
happens a lot that one needs to use MySQL + (Mongo|Redis|etc...) but this adds complexity to the stack. With the
introduction of a JSON data type in MySQL, it became sort of a SQL-NoSQL hybrid database.

@endmarkdown
@stop

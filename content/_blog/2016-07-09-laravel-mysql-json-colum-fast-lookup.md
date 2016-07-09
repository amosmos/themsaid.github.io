---
view::extends: _includes.blog_post_base
view::yields: post_body
post::title: Laravel/MySQL JSON documents faster lookup using generated columns
pageTitle: - Laravel/MySQL JSON documents faster lookup using generated columns
post::brief: laravel is shipped with a built-in support for JSON database fields, in this post we'll discover how to achieve faster lookup
---

laravel 5.3 is shipped with built-in support for updating and querying JSON type database fields, the support currently fully covers MySQL 5.7 JSON type fields updates and lookups, this allows us to have the following:

```php
DB::table('users')->where('meta->favorite_color', 'red')->get();
```

This will bring all users who have a favorite color of red, here's a sample table structure:

<table class="table table-bordered table-condensed">
<tr>
	<td><strong>id</strong></td>
	<td><strong>name</strong></td>
	<td><strong>meta</strong></td>
</tr>
<tr>
	<td>1</td>
	<td> Melisandre </td>
	<td>{"favorite_color": "red", "religion": "R'hllor, the Lord of Light"}</td>
</tr>
</table>

You may also update the field like that:

```php
DB::table('users')
    ->where('id', 1)
    ->update(['meta->origin' => 'Asshai']);
```

[Matt Stauffer](https://twitter.com/stauffermatt) wrote up a post about the new features that you can [check here](https://mattstauffer.co/blog/new-json-column-where-and-update-syntax-in-laravel-5-3).

In this post I'd like to show you how we can achieve faster lookups for data stored in JSON-type fields using MySQL generated columns.

As mentioned in MySQL manual:

> JSON columns cannot be indexed. You can work around this restriction by creating an index on a generated column that extracts a scalar value from the JSON column.

Let's see how we may create a generated column to store users favorite color for later indexing:

```sql
ALTER TABLE users 
ADD meta_favorite_color VARCHAR(50) 
    AS (JSON_UNQUOTE(meta->"$.favorite_color"));
```

This will create a virtual column that'll be generated with every read operation, now let's add an index:

```sql
ALTER TABLE users 
ADD INDEX (meta_favorite_color);
```

The next time you query users' favorite colors you can point MySQL to scan the indexed column instead of the data stored in the JSON column.

```php
DB::table('users')->where('meta_favorite_color', 'red')->get();
```

### Using database migrations

You can achieve the effect of the sql commands mentioned above using laravel database migrations as well:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('meta_favorite_color')->virtualAs('JSON_UNQUOTE(meta->"$.favorite_color")');
    $table->index('meta_favorite_color');
});
```

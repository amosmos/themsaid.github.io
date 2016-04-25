---
view::extends: _includes.blog_post_base
view::yields: post_body
post::title: Conditionally add instructions to laravel's query builder
post::brief: While using the query builder, sometimes you need to add more instructions based on specific conditions, in this post I'm going to share with you how to accomplish this without breaking method chaining.
pageTitle: - Conditionally add instructions to laravel's query builder
---

```php
$results = DB::table('orders')
	->where('branch_id', Auth::user()->branch_id)
	->get();
```

So you're using laravel's query builder to get all orders that belong to a specific branch, but you want to allow the user to filter the results by customer **only** if he wants to, so you do this:

```php
$results = DB::table('orders')
	->where('branch_id', Auth::user()->branch_id);
	
if($request->customer_id){
	$results->where('customer_id', $request->customer_id);
}

$results = $results->get();
```

You get what you want, but is that the most eloquent approach to accomplish the task? Of course it's not, breaking the chain affects readability so bad, specially when you have a lot of conditions.

Fortunately starting laravel v5.2.27 we can to do the following:

```php
$results = DB::table('orders')
	->where('branch_id', Auth::user()->branch_id)
	->when($request->customer_id, function($query) use ($request){
		return $query->where('customer_id', $request->customer_id);
	})
	->get();
```

The instructions in the closure given to `when()` will only be applied if the first argument is evaluated to `true`, that way you'll be able to write complex conditional instructions without having to break the chain.

What's even more readable is having something like this:

```php
$results = DB::table('orders')
	->where('branch_id', Auth::user()->branch_id)
	->if($request->customer_id, 'customer_id', '=', $request->customer_id)
	->get();
```

Here you eliminate the need for using a closure since your conditional instruction is a simple `where` statement.

The `if()` method is not part of the core, so we may register it using a Macro:

```php
use Illuminate\Database\Query\Builder;

Builder::macro('if', function ($condition, $column, $operator, $value) {
    if ($condition) {
        return is_callable($column)
               ? call_user_func($column, $this)
               : $this->where($column, $operator, $value);
    }

    return $this;
});
```
---
view::extends: _includes.blog_post_base
view::yields: post_body
post::title: Better readability using enumerators
post::brief: This post is about using an Enumerated-type-like in your PHP code for better readability.
---

Let's assume we're building a system for monitoring a restaurant orders, an order can be "Dine In" or "Takeaway", and each order should have a status of "New", "Served", or "Cancelled".

We create two columns in the `orders` database table for the `type` and `status` as `TINYINT` and map the possible values as follows:

- 1 = Dine In
- 2 = Takeaway

- 1 = New
- 2 = Served
- 3 = Cancelled

Later on you might need to do something like this:

```php
if($order->type == 1){
    $order->validateTableNumber();
}
```

You probably find it easy to relate that `1` means the order is of type "Dine In", but give it sometime and you'll need to look at the docs to understand what's going on.

You might do:

```php
if($order->isDineIn()){
    $order->validateTableNumber();
}
```

But you'll have to create a method for each type and status, and you'll still need to pass the integer value sometimes:

```php
DB::table('orders')->whereType(1)->get();
```

## Enter Enums

Enums is a data type consisting of a set of named values, an example in C:

```c
enum Days
{ 
  Monday, 
  Tuesday, 
  Wednesday 
};
```

Unfortunately there's no native enum support in PHP, if that wasn't the case we would have used something like:

```php
enum OrderTypes{
	DineIn,
	TakeAway;
}
```

And use it like this:

```php
if($order->type == OrderTypes::DineIn){
    $order->validateTableNumber();
}

DB::table('orders')->whereType(OrderTypes::DineIn)->get();
```

See? much better readability, and with an IDE support you'll be able to easily write your code without having to memorise these values.

Let's see how we can achieve something similar in PHP:

```php
Order{
	const TYPE_DINE_IN = 1;
	const TYPE_TAKEAWAY = 2;
	
	const STATUS_NEW = 1;
	const STATUS_SERVED = 2;
	const STATUS_CANCELLED = 3;
}
```

Using this approach you'll be able to do something like:

```php
if($order->type == Order::TYPE_DINE_IN){
    $order->validateTableNumber();
}
```

And moreover, an IDE will give you hints about possible constants you may use.


## Bonus treat/trait

I wrote a trait that'll allow me to do something like:

```php
Order::enums('TYPE');
// returns: ["TYPE_DINE_IN" => 1, "TYPE_TAKEAWAY" => 2]

Order::enums('TYPE');
// returns all class constants

Order::isValidEnumValue(32, 'TYPE');
// Check if the given value `32` exists in the `TYPE` group.

Order::isValidEnumKey('TYPE_PICKUP');
// Check if the given key `TYPE_PICKUP` exists.
```

Here's the full trait code:


```php
trait HasEnums
{
    /**
     * The array of enumerators of a given group.
     *
     * @param null|string $group
     * @return array
     */
    static function enums($group = null)
    {
        $constants = (new ReflectionClass(get_called_class()))->getConstants();

        if ($group) {
            return array_filter($constants, function ($key) use ($group) {
                return strpos($key, $group.'_') === 0;
            }, ARRAY_FILTER_USE_KEY);
        }

        return $constants;
    }

    /**
     * Check if the given value is valid within the given group.
     *
     * @param mixed $value
     * @param null|string $group
     * @return bool
     */
    static function isValidEnumValue($value, $group = null)
    {
        $constants = static::enums($group);

        return in_array($value, $constants);
    }

    /**
     * Check if the given key exists.
     *
     * @param mixed $key
     * @return bool
     */
    static function isValidEnumKey($key)
    {
        return array_key_exists($key, static::enums());
    }
}
```

---

If you find this useful and decided to enhance the trait, please write your comments on this gist: https://gist.github.com/themsaid/593a1972adbe35150e730c0ad3632cad
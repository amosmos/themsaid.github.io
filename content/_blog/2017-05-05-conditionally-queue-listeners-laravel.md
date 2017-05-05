---
view::extends: _includes.blog_post_base
view::yields: post_body
post::title: Conditionally queue event listeners in laravel
post::brief: On how to push a queued listener to queue based on a specific condition, this might be a smart way to avoid exhausting your workers on unnecessary jobs.
---

Here's the situation, you're building an online store that should handle thousands of orders every day, one of the cool things about this store is that customers who make
purchases above 10K should receive a gift coupon, let's see how we can implement such thing in laravel:

On every new purchase a `NewPurchase` event is dispatched, we can easily setup as much listeners as we want to this event from within our `EventServiceProvider`:

```php
class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NewPurchase::class => [
            SendInvoice::class,
            UpdateMetrics::class,
            SendShippingOrder::class,
            SendGiftCoupon::class,
        ],
    ];
}
```

Now for every new purchase a `SendGiftCoupon` will be called, it might look like this:


```php
class SendGiftCoupon
{
    public function handle($event)
    {
        $customer = $event->customer;

        if ($customer->purchases >= 10000 && ! $customer->wasGifted()){
            $coupon = Coupon::createForCustomer($customer);

            Mail::to($customer)->send(new CouponGift($customer));
        }
    }
```

This listener will check if the customer should receive the gift coupon, creates the coupon if so, and finally sends it to him vai email.

The problem is that we don't want the customer to wait for all these steps to complete, it'd be better if we just process the order and send him to a nice YAY! screen
while we do all these stuff in the background, for that we'll queue the listener by implementing the `ShouldQueue` interface:

```php
class SendGiftCoupon implements ShouldQueue
{
    // ...
}
```

It looks like we're done here, for every new purchase the listener will check if the customer should receive the coupon & then gets this job done.

## Problem

How many customers will reach the 10K purchase milestone? does it make sense to push a Job to queue for every single purchase while there's a huge chance that this
job will just do nothing at all? IMHO this is a waste of resources, you might end up filling your queue with thousands of unnecessary jobs.

## Idea

It might be a good thing if we can check for that condition before queueing the listener, fortunately that's very easy in laravel, all we need to do is add a `queue` method
to the listener class:

```php
public function queue($queue, $job, $data)
{
    $event = unserialize($data['data'])[0]);

    if ($event->customer->purchases < 10000 || $event->customer->wasGifted()) {
        return;
    }

    $queue->push($job, $data);
}
```

Now before pushing the listener to queue we'll check if the customer should receive the coupon, and only if that's true we'll push that listener into the queue.

## Conclusion

Instead of having our queue filled with thousands of unnecessary jobs that might be delaying more important ones we'll just push the job only if needed.
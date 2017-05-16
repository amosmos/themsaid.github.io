---
view::extends: _includes.blog_post_base
view::yields: post_body
post::title: Using Laravel Mailables and Notifications as Event Listeners
post::brief: On how to use Laravel Mailables and Notifications as Event listeners, which eliminates the need to create an extra listener class that only sends the notification/mailable.
---

Most of the time we send alerts to our app users when a specific event happens, for example we send an invoice on a new purchase or a
welcome email on user signup, and to do this we need to listen to that event like so:

```php
class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NewPurchase::class => [
            SendInvoice::class,
        ]
    ];
}
```

Then inside our `SendInvoice` listener we would do something like this:

```php
class SendInvoice implements ShouldQueue
{
    public function handle(NewPurchase $event)
    {
        $event->order->customer->notify(new SendInvoiceNotification($event->order));
    }
}
```

## Idea

Instead of having to create a class for the listener and another for the notification/mailable, wouldn't it be cool if we can register the Notification/Mailable as an event
listener? something like:

```php
class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NewPurchase::class => [
            SendInvoiceNotification::class,
        ]
    ];
}
```

What happens when we do so is that the Event Dispatcher will create an instance of our `SendInvoiceNotification` class and try to call the `handle()` method, taking this
into consideration we can use the handle method in the Notification class to send the notification itself:

```php
class SendInvoiceNotification extends Notification implements ShouldQueue
{
    public $order;

    /**
     * Handle the event here
     */
    public function handle(NewPurchase $event)
    {
        $this->order = $event->order;

        app(\Illuminate\Contracts\Notifications\Dispatcher::class)
                ->sendNow($event->order->customer, $this);
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('Your order reference is #'.$this->order->reference);
    }
}
```

<div class="alert alert-warning">
Notice that we use <strong>sendNow()</strong> so that we prevent queuing the notification as the listener itself was queued before since we implement
the ShouldQueue interface.
</div>

## What about Mailables?

Here's how we can achieve the same using a mailable:

```php
class SendInvoiceNotification extends Mailable implements ShouldQueue
{
    public $order;

    /**
     * Handle the event here
     */
    public function handle(NewPurchase $event)
    {
        $this->order = $event->order;

        $this->to($event->order->customer)
             ->send(app(\Illuminate\Contracts\Mail\Mailer::class));
    }

    public function build()
    {
        return $this->subject('Order #'.$this->order->reference)->view('invoice');
    }
}
```

## Conclusion

While some may consider this an anti-SRP hack I think it's a pretty neat implementation that you can use in your application to prevent creating an extra listener class,
your call :)
---
view::extends: _includes.blog_post_base
view::yields: post_body
post::title: Mobile push notifications, Telegram, Web notifications, and more custom channels for Laravel 5.3
post::brief: 
---

Pusher, a realtime communication platform, has recently introduced an all-new FREE service for application developers, it's a unified API to send native Push Notifications to iOS and Android devices.

Using native Push Notifications allows you to communicate with your users even when the app is closed or inactive, this has a great effect on user engagement.

Being able to send iOS and Android Push Notifications using a unified API saves the hassle of having to apply multiple setup procedures. All you have to do is to upload your APNS certificate for iOS devices or add your GCM API key for android devices, and that's it.

Now since [Laravel 5.3 will be shipped with a notification system](https://laravel-news.com/2016/08/laravel-notifications-easily-send-quick-updates-through-slack-sms-email-and-more/) that includes a Nexmo SMS driver, a Mail driver, and a few others with the ability to include custom drivers, now that we have this system built-in we can use the new Pusher service from inside our Laravel applications.

All we need to do is to create a custom driver and that's it, we'll be able to send Push notifications to our Mobile devices right away.

With the help of [Freek Van der Herten](https://twitter.com/@freekmurze) and [Marcel Pociot](https://twitter.com/marcelpociot) we managed to build an easy to use driver for Pusher Push Notifications.

Using this driver you'll be able to send push notifications like this:

```php
class AccountApproved extends Notification
{
    public function via($notifiable)
    {
        return [Channel::class];
    }

    public function toPushNotification($notifiable)
    {
        return (new Message())
            ->iOS()
            ->badge(1)
            ->sound('success')
            ->body("Your {$notifiable->service} account was approved!");
    }
}
```

We believe that this driver will allow you to send Push Notifications with a laravel-style sort of code syntax, as fluent as possible.

You may check it out here:

https://github.com/laravel-notification-channels/pusher-push-notifications

## Building custom drivers

There are many platforms that send notifications, and with the new system in Laravel 5.3 we believe that it's a good idea to collect all the custom drivers in a single place, just as socialiteproviders.github.io is a one-stop-shop for all Socialite Providers, for that we created a GitHub organisation where we'll collect and host all custom drivers:

https://github.com/laravel-notification-channels

We've already received contributions for a telegram channel, web notifications channel, OneSignal, Pushover, and more.

It's still in the making but contributions are most welcomed, if you have an idea for a custom channel, please [check this skeleton repo](https://github.com/laravel-notification-channels/skeleton) where you can use as boilerplate.
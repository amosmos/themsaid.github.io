---
view::extends: _includes.blog_post_base
view::yields: post_body
post::title: A look at Laravel 5.3 Notification System
post::brief: 
---

While Laravel is already shipped with many built-in solutions for many application development problems, it still presents new ones with every new release, Laravel 5.3 is no exception.

One of the neat features of Laravel 5.3 is the all-new notification system. Being a built-in feature, notifications will never be a hard task to build again. Let's see how easy it is to build and send a notification:

```php
class NewPost extends \Illuminate\Notifications\Notification
{
	public function __construct($post)
    {
        $this->post = $post;
    }
    
    public function via($notifiable)
    {
        return ['database'];
    }

	public function toArray($notifiable)
	{
		return [
			'message' => 'A new post was published on Laravel News.',
			'action' => url($this->post->slug)
		];
	}
}
```

All you need to do now is to send the notification to the selected users:

```php
$user->notify(new NewPost($post));
```

## Creating Notifications

Laravel 5.3 is shipped with a new console command for creating notifications:

```
php artisan make:notification NewPost
```

This will create a new class in `app/Notifications`, each notification class contains a `via()` method as well as different methods for building notifications for different channels.

Using the `via()` method you can specifying the channels you'd like this particular notification to be sent through, check this example for the official documentation website:

```php
public function via($notifiable)
{
    return $notifiable->prefers_sms ? ['sms'] : ['mail', 'database'];
}
```

The via method receives a `$notifiable` instance, which is the model you're sending the notification to, in most cases it'll be the user model but it's not limited to that.

Available channels are: `mail`, `nexmo`, `database`, `broadcast`, and `slack`.

### Formatting Email Notifications

You can format how notifications are sent through different channels, for instance let's take a look at formatting a mail notification:

```php
public function toMail($notifiable)
{
    return (new MailMessage)
    			   ->subject('New Post!')
                ->line('A new post was published on Laravel News!')
                ->action('Read Post', url($this->post->slug))
                ->line('Please don\'t forget to share.');
}
```

This will create a mail message using a nice built-in responsive template that you can publish using the `vendor:publish` console command.

The notification system will automatically look for an `email` property in your model, however you can customise this behaviour by defining a `routeNotificationForMail` in your Model and return the email address this Model will be contacted on.

### Formatting Nexmo SMS Notifications

Same as in the case of an email notification, you need to define a `toNexmo` method in your notification class:

```php
public function toNexmo($notifiable)
{
    return (new NexmoMessage)
		->from(123456789)
		->content('New post on Laravel News!');
}
```

In the case of Nexmo notifications, laravel will look for a `phone_number` property in the model. You can override that by defining a `routeNotificationForNexmo` method.

You can set a global `from` number in your nexmo configuration file, that way you won't have to provide a `from` number in each notification.

### Formatting Database Notifications

To Format a database notification you may define a `toDatabase` method:

```php
public function toDatabase($notifiable)
{
    return new DatabaseMessage([
    	'message' => 'A new post was published on Laravel News.',
		'action' => url($this->post->slug)
    ]);
}
```

To start using the database channel you may read the full documentation on the [official website](https://laravel.com/docs/master/notifications#database-notifications).


## Sending Notifications

You can send notifications using the `notify()` method on your mode, this method exists on the `Notifiable` trait which you'll need to add to your Model.

```php
$user->notify(new NewPost($post));
```

You can also use the Notification facade, this will allow you to send notifications to multiple notifiables at the same time:

```
Notification::send(User::all(), new NewPost($post));
```

## Queueing Notifications

You can easily queue sending notifications by using the `ShouldQueue` interface on the Notification class and including the `Queueable` trait.

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewPost extends Notification implements ShouldQueue
{
    use Queueable;
}
```
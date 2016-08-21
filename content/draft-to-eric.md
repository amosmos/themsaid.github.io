---
view::extends: _includes.blog_post_base
view::yields: post_body
post::title: Grant Types in Laravel Passport
post::brief: 
---

OAuth2 is a security framework that controls access to protected areas of an application, it's mainly used to control how different clients consume an API insuring they have the proper permissions to access the requested resources.

[Laravel Passport](https://laravel.com/docs/master/passport) is a full OAuth2 server implementation, it was built to make it easy to apply authentication over an API for laravel-based web applications. 

# Terminology

Before going any further, we need to understand the following definitions:

### Client
This is the application trying to consume our API, creating clients in Passport is done via this console command:

```bash
php artisan passport:client
```

Every client will have a key, name, secret, redirect URI, and a user (Application Creator/Owner).

### Resource Owner
This is the entity (User) that owns the data a client is trying to consume.

### Resource Server
That's our API, it may have public data that doesn't require an owner permission to read, and other private data that requires an owner permission.

Public endpoints can be, for example, the endpoint for searching tweets, that doesn't require a specific resource owner permission.

On the other hand, an endpoint that posts tweets on behalf of a user is a private endpoint, interacting with such endpoints requires a permission from the resource owner.

### Scope
It's a permission to access certain data, or perform a certain action.

You may define scopes using `Passport::tokensCan()` method inside your `AuthServiceProvider`.

```php
Passport::tokensCan([
    'read-tweets' => 'Read all tweets',
    'post-tweet' => 'Post new tweet',
]);
```

### Grant
It's the method used to get an access token.

### Access token
That's the token an app (client) needs to communicate with the server (API).

# Authorizing third-party apps

First we need to create a test app using the following command:

```bash
php artisan passport:client
```

Passport will prompt asking you for the user ID, app name, and the redirect URI.

Now that we have the client registered we can now get an access token using the "Authorization Code Grant".

This type of grants works by pointing the browser to the authorization server where the user can log in to his account and grant access to the app, once an access is given the app shall send another request  asking for an access token, using this token the app will be able to make further requests.

For most of the cases you'll be using this grant type to allow all kind of applications to consume your laravel-based API private endpoints, this includes server-side apps, JavaScript apps, & native mobile apps.

## Step 1: Asking for permission

From the client app, you'll need to point the user to `http://resources.dev/oauth/authorize?client_id={CLIENT_ID}&redirect_uri={URI}&response_type=code&scope={SCOPE}`

Using the correct `CLIENT_ID` & `URI` as in the client created by passport.

You can list the scopes as a space separated list of permissions you'd like to get from the resource owner, for example:

```bash
read-tweets post-tweets follow-others
```

Now if Passport was installed correctly such that the routes are published in your `AuthServiceProvider`, if all is well the above request will show a nice screen asking the user to give permission to the app, the screen will list all the scopes the app is asking for.

In case the user denied access, Passport will redirect the user to the given `redirect_uri` with `error=access_denied` in the URL.

However, if the user approved access, Passport will redirect to the `redirect_uri` with `code={authorization_code_here}`.

## Step 2: Getting an access token

Now that we have the Authorization Code, we need to send a `POST` request to `http://resources.dev/oauth/token` to get the access token, the body of the request should contain the following:

- `grant_type`: `authorization_code`
- `client_id`: the one created by Passport
- `client_secret`
- `redirect_uri`
- `code`: The given Authorization Code

The response is going to be a JSON object with the following keys:

```json
{
    "token_type": "Bearer",
    "expires_in": 3155673600,
    "access_token": "eyJ0eXAiOiJKV1QiL....",
    "refresh_token": "XslU/K6lFZShiGxF1dPyC4ztIXBx9W1g..."
}
```

## Refreshing an access token

By default the `access_token` won't expire before a 100 years, if you don't mind this then you don't need to save the refresh token, if otherwise you'd like the access_tokens to have a short life then you need to tell Passport about that:

```php
Passport::tokensExpireIn(Carbon::now()->addDays(15));

Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));
```

If your tokens are short-lived, then the client needs to save the refresh_token in order to use it later to issue a new access token.

To refresh an access token the client needs to make a request to `http://resources.dev/oauth/token` with the following parameters:

- `grant_type`: `refresh_token `
- `client_id`: the one created by Passport
- `client_secret`
- `refresh_token`
- `scope`

# Authorizing first-party apps

If you're authorising a trusted app of your own there's no need for such a long road to get an access token, you only need to ask the user to provide a username/email & password in order for the app to get an access token. This type of grants is called `Password grant`.

You need to check your database to grab the password client created by Passport.

To get an access token for a first-party app you need to make a `POST` request to `http://your-app.com/oauth/token` with the following parameters:

- `grant_type`: `password`
- `client_id`:
- `client_secret`
- `username`
- `password`
- `scope`

The response is going to be a JSON object with the following keys:

```json
{
    "token_type": "Bearer",
    "expires_in": 3155673600,
    "access_token": "eyJ0eXAiOiJKV1QiL....",
    "refresh_token": "XslU/K6lFZShiGxF1dPyC4ztIXBx9W1g..."
}
```

# Authorizing an app manually

Passport is also shipped with a way to create access tokens manually, this is useful in multiple situations such as testing during development or maybe if you allow authenticating users on a third-party application via their mobile number instead of a login web form.

For example, a third party app may show a phone field for the user, when filled a service on your server sends an SMS to that number with an access code, the user will input this code upon reception in which the app will exchange with an access token from your server.

To create an access token:

```php
$token = $user->createToken('Pizza App', ['place-orders', 'list-orders'])->accessToken;
```
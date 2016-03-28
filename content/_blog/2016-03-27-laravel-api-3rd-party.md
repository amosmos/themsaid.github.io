---
view::extends: _includes.blog_post_base
view::yields: post_body
post::title: Building an API for 3rd party applications
post::brief: APIs are cool, & laravel can handle all the coolness you may desire. Here we talk about building an API for third party applications and allow them to communicate with your application on behalf of users.
pageTitle: - Building an API for 3rd party applications - laravel
---
@yield('post::brief')

## Planning ahead

Our application gives third-party applications the ability to read/write data, we only need the applications we know about to be able to connect, we also need to be able to deactivate an application at any point of time, here's a list of all the specs:

1. Issue an application authentication token.
2. Deactivate an application so that it may not make requests.
3. Allow our users to log in from a 3rd party app.
4. Allow our users to log out from an app.

## New	Laravel installation

For the purpose of this post we're going to install a fresh copy of laravel to run our project "Valhalla":

```shell
composer create-project laravel/laravel valhalla
```

## Preparing files

Update `app/Http/routes.php`:

```php
$router->group(['prefix' => 'api/v1'], function ($router) {
    // Applications Authentication...
    $router->post('/auth/app', 'Api\AuthController@authenticateApp');

    // Users Authentication...
    $router->post('/auth/user', 'Api\AuthController@authenticateUser')->middleware('auth.api.app');
    $router->post('/auth/user/logout', 'Api\AuthController@logoutUser')->middleware('auth.api.user');

    // Testing routes...
    $router->get('/application-data', 'Api\HomeController@appData');
    $router->get('/user-data', 'Api\HomeController@userData');
});

// authorize an application for user data...
$router->get('/authorize', 'HomeController@showAuthorizationForm')->middleware('web');
$router->post('/authorize', 'HomeController@authorizeApp')->middleware('web');
```

---

Create `app/Http/Controllers/Api/AuthController.php`

```php
class AuthController extends Controller
{
    public function authenticateApp(Request $request){}

    public function authenticateUser(Request $request){}
    
    public function logoutUser(Request $request){}
}
```

---

Create `app/Http/Controllers/Api/HomeController.php`

```php
class HomeController extends Controller
{
    public function appData(Request $request){}

    public function userData(Request $request){}
}
```

---

Create `app/Http/Controllers/HomeController.php`

```php
class HomeController extends Controller
{
    public function authorizeApp(Request $request){}
}
```

---

We'll also need to create an `Application` Model:

```shell
php artisan make:model Application
```

## Preparing the database

For this application we need a `users` table and a `applications` table, the users table migration is available out of the box with each laravel installation, so we're only going to create an `applications` table:

```shell
php artisan make:migration create_applications_table --create=applications
```

Here's the structure of our new table:

```php
Schema::create('applications', function (Blueprint $table) {
    $table->increments('id');
    $table->string('name');
    $table->string('key')->unique();
    $table->string('secret');
    $table->tinyInteger('is_active')->unsigned()->default(1);
    $table->timestamps();
});
```

We'll also need to build a pivot table to manage users authorized apps:

```shell
php artisan make:migration create_application_user_table --create=application_user
```

```php
Schema::create('application_user', function (Blueprint $table) {
    $table->integer('application_id')->unsigned();
    $table->integer('user_id')->unsigned();
    $table->string('Authorization_code')->nullable();

    $table->primary(['application_id', 'user_id']);

    $table->foreign('application_id')->references('id')->on('applications')->onDelete('cascade');
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
});
```

Now let's add some data, we're going to register an application "Asgard Connect" with proper key and secret:

```sql
insert into `applications` (name, key, secret) values ('Asgard Connect', '111222333', 'aaabbbccc');
```

We're going to add a new user as well:

```sql
insert into `users` (name, email, password) values ('Loki', 'loki@asgard.com', '$2y$10$QfBGX14wKpXT/zA1gR.FZ.A12nrXzbtfki8wfqfwG.irvAWAYE9dC');
```

# Application Authentication

In order for us to identify and authenticate apps trying to communicate with our API, we assigned a `key` & `secret` for each one, apps use a hashed version of this pair to generate an authentication token.

We're going to use [JSON Web tokens](https://jwt.io/) in this application, I believe it's a perfect approach that combines between security and simplicity. For this we're going to pull this JWT PHP library:

```shell
composer require firebase/php-jwt
```

## Requesting a token

In order for an app to acquire a token, it must base64 encode the app key & secret pair separated by a colon, for example using PHP and our demo app credentials:

```php
base64_encode('111222333:aaabbbccc');

// Results: MTExMjIyMzMzOmFhYWJiYmNjYw==
```

The app then sends a `POST` request to `/auth/app` with the following authorization header:

```bash
Authorization: Basic MTExMjIyMzMzOmFhYWJiYmNjYw==
```

In our `AuthController@authenticateApp` method, here's how we handle the request:

```php
public function authenticateApp(Request $request)
{
    $credentials = base64_decode(
        Str::substr($request->header('Authorization'), 6)
    );

    try {
        list($appKey, $appSecret) = explode(':', $credentials);

        $app = Application::whereKeyAndSecret($appKey, $appSecret)->firstOrFail();
    } catch (\Throwable $e) {
        return response('invalid_credentials', 400);
    }

    if (! $app->is_active) {
        return response('app_inactive', 403);
    }

    return response([
        'token_type' => 'Bearer',
        'access_token' => $app->generateAuthToken(),
    ]);
}
```

The auth token is generated in `Application@generateAuthToken`:

```php
public function generateAuthToken()
{
    $jwt = \Firebase\JWT::encode([
        'iss' => 'valhalla',
        'sub' => $this->key,
        'iat' => time(),
        'exp' => time() + (5 * 60 * 60),
    ], 'w5yuCV2mQDVTGmn3');

    return $jwt;
}
```

Using the Firebase\JWT library we created a token and signed it with a secret, this secret may be saved as an environment value, however we just added it there for simplification.

A JWT contains the following claims:

- `iss` The issuer of the token, in this case it's our application "valhalla".
- `sub` The subject of the token, which is the app trying to authenticate.
- `iat` The time the token was issued.
- `exp` Token expiration time. Here the token will expire after 5 hours.

The response of the authentication request will be the following:

```json
{
    "token_type": "Bearer",
    "access_token": "eyJ0eXAiO~~~.eyJpc3MiO~~~.MSzBigimzWrc9DlZZduh~~~"
}
```

The app shall store this token in order for it to send it back with every request later on.

## Making requests

To create a request that requires an application to be authenticated, you have to send the authentication token via the `Authorization` header:

```shell
Authorization: Bearer eyJ0eXAiO~~~.eyJpc3MiO~~~.MSzBigimzWrc9DlZZduh~~~
```

But first, let's create a middleware to check for a valid token:

```shell
php artisan make:middleware ApiAppAuth
```

Then we handle the incoming request:

```php
public function handle($request, Closure $next)
{
    $authToken = $request->bearerToken();

    try {
        // Check the validation implementation in the next method
        $this->payloadIsValid(
        	  // JWT::decode accepts the token string as the first argument, the 
        	  // key used to sign the token, and finally a list of supported 
        	  // verification algorithms.
            $payload = (array) JWT::decode($authToken, 'w5yuCV2mQDVTGmn3', ['HS256'])
        );

        $app = Application::whereKey($payload['sub'])->firstOrFail();
    } catch (\Firebase\JWT\ExpiredException $e) {
        return response('token_expired', 401);
    } catch (\Throwable $e) {
        return response('token_invalid', 401);
    }
    
    if (! $app->is_active) {
        return response('app_inactive', 403);
    }
	
	 // Once we get an instance of the authenticated application, we pass
	 // it to the Request object as an input. This will allow us to use
	 // the application data in all routes actions.
	 $request->merge(['__authenticatedApp' => $app]);

    return $next($request);
}

private function payloadIsValid($payload)
{
    $validator = Validator::make($payload, [
        'iss' => 'required|in:valhalla',
        'sub' => 'required',
    ]);

    if (! $validator->passes()) {
        throw new \InvalidArgumentException;
    }
}
```

Now we need to register the middleware in `Http/Kernel.php`:

```php
protected $routeMiddleware = [
	'auth.api.app' => \App\Http\Middleware\ApiAppAuth::class,
	// ... list of other middlewares.
];
```

And finally register the middleware on the `/application-data` route:

```php
$router->get('/application-data', 'Api\HomeController@appData')->middleware('auth.api.app');
```

Now when you visit `/api/v1/application-data` and provide the right authorization header you receive a 200 response, the response body can be setup in `HomeController@appData`, here we only send the JSON representation of the authenticated application:

```php
public function appData(Request $request)
{
    return $request->__authenticatedApp;
}
```

In case the token was invalid a 401 response will be received, or if the application was inactive a 403 response will be received.

### Dealing with expired tokens

In case the given token was expired, the response will be `token_expired` with a 401 response code, the device trying to authenticate needs to detect for such response and re-issue a new token in order to be able to make requests.

### Deactivating an Application

If, at any point of time, you'd like a specific application to be deactivated so that I may not communicate with your API, you simply change the `is_active` key of this application to 0, the rest is taken care of by the `auth.api.app` middleware.

Once an app is not active, response to all its requests will be `app_inactive` with a response code `403`.

# User Authentication

In Valhalla we allow applications to make user-less requests where they may request general information that are not user specific, but we also would like these applications to allow users to log into their Valhalla account and give the app a permission to make specific requests on behalf of them.

However we don't want our users to share their account credentials with these apps, if a user wants to use Valhalla from a 3rd party app he has to come to Valhalla, identify himself, and give Valhalla a permission to share data with that app. Here's how we're going to implement that:

1. User opens the 3rd party app.
2. Clicks on "Log In".
3. Gets redirected to a URL on Valhalla's website.
4. User logs in using correct credentials.
5. User gets redirected to an app-specific URI with a code.
6. App uses this code to request a user authentication token.
7. App uses this token in future requests.

## Authorization

In `app/Http/Controllers/HomeController@showAuthorizationForm` we'll check for valid Authorization parameters & display a log-in form:

```php
public function authorizeApp(Request $request)
{
    $validator = Validator::make($request->all(), [
        'app_key' => 'required|exists:applications,key,is_active,1',
        'redirect_uri' => 'required:active_url',
    ]);

    if (! $validator->passes()) {
        return view('authorize-app')->withInvalid('true');
    }

    $app = Application::whereKey($request->app_key)->first();

    return view('authorize-app', compact('app'));
}
```

If the request is not valid we'll display the view and pass a `$invalid` variable, if valid we'll display the view and pass an instance of the app trying to issue the token.

The view (`resources/views/authorize-app.blade.php`) has a simple form:

```html
@@if(isset($invalid))
	Invalid Authorization request.
@@else
	<div class="error"> authorize "@{{$app->name}}" to use your data. </div>
	
	@@if(session('message'))
        <div class="error"> @{{session('message')}} </div>
    @@endif

	<form method="POST" action="@{{ url('/authorize') }}">
		@{!! csrf_field() !!}
		<input type="hidden" name="app_key" value="@{{ request('app_key') }}">
		<input type="hidden" name="redirect_uri" value="@{{ request('redirect_uri') }}">
		
		<input type="email" name="email">
		<input type="password" name="password">
		
		<button type="submit">authorize</button>
	</form>
@@endif
```

Now the user has to fill in his email & password and click on "authorize" to send a post request to Valhalla indicating that the user is willing to share his data with the 3rd party application.

### Handling user Authorization permission

Once the user submits the form, the `HomeController@authorizeApp` action will be executed:

```php
public function authorizeApp(Request $request)
{
	 // Validate Authorization parameters...
    $validator = Validator::make($request->all(), [
        'app_key' => 'required|exists:applications,key,is_active,1',
        'redirect_uri' => 'required:active_url',
    ]);

    if (! $validator->passes()) {
        return redirect()->back()->withMessage('Invalid Authorization parameters.');
    }
	
	 // Check user credentials...
    if (! Auth::validate($request->only(['email', 'password']))) {
        return redirect()->back()->withMessage('Wrong credentials.');
    }

    $app = Application::whereKey($request->app_key)->first();

    $user = User::whereEmail($request->email)->first();
	 
	 // Generate an Authorization code for the application...
    $pivotData = ['Authorization_code' => $code = sha1($app->id.':'.$user->id.str_random())];
	 
	 // Update the database record...
    if ($app->users->contains($user)) {
        $app->users()->updateExistingPivot($user->id, $pivotData);
    } else {
        $app->users()->attach($user->id, $pivotData);
    }
	 
	 // Redirect to the defined redirect_uri with the code...
    return redirect()->away($request->redirect_uri.'?code='.$code);
}
```

Now if the user provided the right credentials a redirect response will be returned pointing the browser to `redirect_uri?code=6bc02273a757569a0237`, the application then has to catch the code in the uri in order to issue an Authorization token to get the user data.

### Issuing an authentication token

Back to `app\Http\Controllers\Api\AuthController.php`, we're going to implement the action to issue an authentication token for the user.

```php
public function authenticateUser(Request $request)
{
    $code = $request->json('code');

    $app = $request->__authenticatedApp;

    if (! $code || ! $user = $app->users()->wherePivot('Authorization_code', $code)->first()) {
        return response('invalid_code', 400);
    }

    $app->users()->updateExistingPivot($user->id, ['Authorization_code' => null]);

    return response([
        'token_type' => 'Bearer',
        'access_token' => $user->generateAuthToken($app),
    ]);
}
```

Here's how we generate a token for the user in `app\User.php`:

```php
public function generateAuthToken(Application $app)
{
    $jwt = JWT::encode([
        'iss' => $app->key,
        'sub' => $this->email,
        'iat' => time(),
        'jti' => sha1($app->key.$this->email.time()),
    ], 'w5yuCV2mQDVTGmn3');

    return $jwt;
}
```
The `jti` is a unique identifier for the token, we'll use it later to log the user out and forbid the use of this token.

**Notice** that this time the issuer is an application, so we pass the application key for further reference. Also as you can see the user token will never expire, later on we will implement a log-out mechanism.

To generate a token, an application has to send the code to `/auth/user` in a json body and receive the token in return.

## Making requests

To make a request that requires a user to be authenticated, you have to send the **user** authentication token via the `Authorization` header:

```shell
Authorization: Bearer eyJ0eXAiO~~~.eyJpc3MiO~~~.MSzBigimzWrc9DlZZduh~~~
```

But first, let's create a middleware to check for a valid token:

```shell
php artisan make:middleware ApiUserAuth
```

Then we handle the incoming request:

```php
public function handle($request, Closure $next)
{
    $authToken = $request->bearerToken();

    try {
        $this->payloadIsValid(
            $payload = (array) JWT::decode($authToken, 'w5yuCV2mQDVTGmn3', ['HS256'])
        );

        $app = Application::whereKey($payload['iss'])->firstOrFail();

        $user = User::whereEmail($payload['sub'])->firstOrFail();
    } catch (\Throwable $e) {
        return response('token_invalid', 401);
    }

    if (! $app->is_active) {
        return response('app_inactive', 403);
    }

    $request->merge(['__authenticatedApp' => $app]);

    $request->merge(['__authenticatedUser' => $user]);

    return $next($request);
}

private function payloadIsValid($payload)
{
    $validator = Validator::make($payload, [
        'iss' => 'required',
        'sub' => 'required',
        'jti' => 'required',
    ]);

    if (! $validator->passes()) {
        throw new \InvalidArgumentException;
    }
}
```

Now we need to register the middleware in `Http/Kernel.php`:

```php
protected $routeMiddleware = [
	'auth.api.user' => \App\Http\Middleware\ApiUserAuth::class,
	// ... list of other middlewares.
];
```

And finally register the middleware on the `/user-data` route:

```php
$router->get('/user-data', 'Api\HomeController@appData')->middleware('auth.api.user');
```

Now when you visit `/api/v1/user-data` and provide the right authorization header you receive a 200 response, the response body can be setup in `HomeController@userData`:

```php
public function userData(Request $request)
{
    return [
        'app' => $request->__authenticatedApp,
        'user' => $request->__authenticatedUser,
    ];
}
```

## Logging a user out of an application

The user now is able to use the application to manage his own data, however we need a way to allow him to log out, for this we'll create a `tokens_cemetery` table that'll hold user tokens that are dead and may not be used anymore:

```shell
php artisan make:migration create_tokens_cemetery_table --create=tokens_cemetery
```

And here's the schema:

```php
Schema::create('tokens_cemetery', function (Blueprint $table) {
    $table->string('token_id');
});
```

Then we modify the `Api\AuthController@logoutUser` action:

```php
public function logoutUser(Request $request)
{
    $token = $request->bearerToken();

    DB::table('tokens_cemetery')->insert(['token_id' => $token]);

    return response('token_deceased');
}
```

And finally we update the `ApiUserAuth` middleware to check for a deceased token and send the proper response:

```php
// Add After:
// if (! $app->is_active) {
//    return response('app_inactive', 403);
// }

if (DB::table('tokens_cemetery')->whereTokenId($payload['jti'])->first()) {
    return response('token_deceased', 403);
}

$request->merge(['__authTokenId' => $payload['jti']]);
```

Now if the user logged out, any further communication using the token will result a 403 token_deceased response, the app shall ask the user to re-authorize in order to make further requests on his behalf.

# Closing Thoughts

You may have noticed that during the process we've issued two different types of tokens, one for user authentication and another for application authentication, while writing our documentation for the API we need to give the developers a hint about what endpoints require app-only tokens and others that require user tokens.

Done! You may now invite application developers to visit Valhalla and create applications for the platform, and you have the control to deactivate any of the applications at any point of time.

You've also secured your users data but at the same time gave them the ability to access any of the cool third party applications built for your platform.

---

Note: If you found any loophole or security risk in this approach please contact me on twitter :)
 
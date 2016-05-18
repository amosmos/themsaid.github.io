---
view::extends: _includes.blog_post_base
view::yields: post_body
post::title: A look into acceptance testing a Laravel-Vue application
post::brief: In this post I'm sharing my experience with testing a Laravel-Vue based application, in my short journey I've tried NightwatchJs, PHPUnit Selenium Extension, WebDriver, and Codeception.
pageTitle: - Acceptance testing a Laravel-Vue application
---

During the past few months, myself and a team of developers have been building and maintaining a relatively large application, it's an all-in-one shop management system for retail and restaurant businesses.

The application consists of a number of separate modules working together, and so far I'm convinced that we have a very clean structure and the code is neat and well written, it's built using Laravel and Vue JS, so inspired by how neat their cores are we built the application using similar standards.

At the beginning of the project we made a decision that many will find bad, we decided not to write tests at all, just spend all the time building and releasing as many features as the business needs, we only had an eye on code quality, readability and performance. However as the application got popular we started receiving a lot of feature requests and bug reports from clients, and we started to feel the system growing and that it's hard to have an eye on everything while making changes, so a couple of weeks ago we started to write tests.

The good thing about writing tests at a more mature stage of the project is that many of the basic parts of the application are settled down, no much changes happening, so deciding the shape of tests can happen in a more solid way, testing too early, in my opinion, leads to the need of changing tests whenever you change functionality, so it's work * 2.

Anyway, this post is about a single type of tests, I think it's the most interesting one.

# Acceptance Testing

Acceptance testing is when you simulate an end user going through your application and trying the different parts, you usually do acceptance testing all the time while developing the application, you open the browser and start using the application as a user until you get what you expect.

Laravel is shipped with a great test trait called `InteractsWithPages`, using this trait you can simulate user actions by clicking on stuff, filling forms, check the presence of different elements on the screen, and more. You can [discover the full capabilities of this trait here](https://laravel.com/docs/5.2/testing#interacting-with-your-application). 

However, this testing library uses a so called PHP Browser, it's basically a DOM crawler that reads the response coming from the server and simulates a user interaction on it, it's fast and is fine in most cases but it has a major drawback, it can't interact with JavaScript driven interfaces, which means we can't rely on it to test our Vue.js powered interfaces.

## Selenium WebDriver

Selenium WebDriver is a browser automation framework, it works by opening an actual browser and using the browser’s built-in support for automation to interact with the different elements on the screen, using selenium we may test exactly as actual users would use the web application including interaction with JavaScript components.

Writing tests for WebDriver can be done in several programming languages, for us the options were Javascript and PHP, and since the team already has a frontend developer in house we decided to write our tests in JavaScript (this changed later) since he's already familiar with it.

## Official JavaScript Driver

Here's a sample usage:

```javascript
var webdriver = require('selenium-webdriver');
var driver = new webdriver.Builder().forBrowser('chrome').build();

driver.get('themsaid.com');
driver.findElement(webdriver.By.css('#projectLink')).click();
driver.wait(webdriver.until.until.titleIs('Project'), 1000);
driver.quit();
```

This test apparently opens `themsaid.com` in chrome, clicks on an element, and expects to see the title of the page changes. You may run this test using this command:

```shell
node tests/themsaidtest.js
```

The problem with this driver is that it's not very readable, being used to frameworks like laravel or vue makes you expect every library to be as fluent, also there's no built-in test runner, that means we'll have to write our own script for running tests or have to run them one by one.

## Nightwatch.js

```javascript
module.exports = {
  'Check for element' : function (client) {
    client.url('themsaid.com')
          .click('#projectLink')
          .assert.title('Project')
          .end();
  }
};
```

Much better syntax, [Nightwatch.js](http://nightwatchjs.org/) also has a built-in test runner, all you need to do to run your tests is:

```shell
nightwatch
```

You may also run single tests, by group, and by tag. So yeah, that was our framework of choice, however there are many other alternatives like [Dalek.js](http://dalekjs.com) and [WebDriver i/o](http://webdriver.io).

## The thing with a Javascript based test frameworks

Using Nightwatch.js we could configure our test suite to test against multiple browsers, take screenshots on failure, and log all the steps for better understanding of what's going on while every step is being executed in the browser. However there are a couple of stuff that we needed to sort out before proceeding:

1. Resetting the database when needed before tests.
2. Adding initial data to the database for each test case.
3. Authenticating a user for the test suite to be able to access protected routes.
4. Using the testing DB rather than our development database.

Most tutorials show you just the basic stuff and let you figure out all the rest, in our case we already have a large web application with multiple entities, user types, access levels, etc... so our tests weren't as simple as any of the ones mentioned in the tutorials we found online, to be able to perform a realistic test we need to fill the database with a lot of content based on each test, we also need to be able to pass this initial data somehow to our nightwatch tests to use it while testing.

For resetting the database we thought of adding a route that's not accessible in the production environment, inside this route we reset the database, and maybe send some POST parameters via the test suite to add initial data.

Regarding authentication we had another route for authenticating a specific user to avoid having to actually log the user in through the browser which is an additional step that'll make the tests slower than they actually are.

Then we got stuck for a while on how to notify laravel to run our tests on the test environment instead of our local environment, we end up using cookies for this, we set a cookie `selenium_cookie`, use a middleware on every request to check for its existence, and switch the default database.

All of the above seem to be working, but having to jump between PHP and JS to pass initial data was slowing our flow and making the process more complicated, we wanted to have the same flexibility of our PHPUnit tests where we create initial data and use it directly in the test method without jumping between different files. So, we finally decided to look for a PHP solution.

## PHPUnit Selenium extension

Using the [selenium extension for PHPUnit](https://phpunit.de/manual/3.7/en/selenium.html) we can actually set initial data and write test steps in one place, for example we create a user using model factories and then we use that user email and password to test our log in screen:

```php
putenv('DB_DATABASE=test_database');

$user = factory(User::class)->create(['password'=>'500']);

$this->url('/login');

$this->byName('username')->value($user->username);

$this->byName('password')->value(500);

$this->byCssSelector('form.loginForm')->submit();

$this->assertRegExp('/Welcome, '.$user->name.'!/', $this->byCssSelector('h2.welcomeMessage')->text());
```

All you need to do is to pull this extension via composer and [download Selenium Server](http://docs.seleniumhq.org/download/).

```shell
composer require --dev phpunit/phpunit-selenium
```

In your acceptance test classes you need to extend `PHPUnit_Extensions_Selenium2TestCase` instead of laravel's test case, you also need to create an instance of laravel to be used in your tests.

This option is very good actually, however the API is a little bit old and not very well documented, in the official documentation they point you to the [test case used to test the extension](https://github.com/giorgiosironi/phpunit-selenium/blob/master/Tests/Selenium2TestCaseTest.php) for you to find all the possible actions and how to use them.

## Codeception

For a frontend developer who never used PHP before it was a challenging task to be asked to write tests in PHP, so we wanted to make sure everything is easy to figure out and that the API to be used is fluent and human readable.

[Codeception](http://codeception.com/) is an all-in-one testing framework that you can use to write unit tests, functional tests, acceptance tests, and API tests. The creators of Codeception pride themselves on having all the test methods written in a descriptive manner so that just by looking at the test body you can get a clear understanding of what is being tested and how it is performed, take a look at this example from the Codeception website:


```php
$I->wantTo('create wiki page');
$I->amOnPage('/');
$I->click('Pages');
$I->click('New');
$I->see('New Page');
$I->fillField('title', 'Hobbit');
$I->fillField('body', 'By Peter Jackson');
$I->click('Save');
$I->see('page created'); // notice generated
$I->see('Hobbit','h1'); // head of page of is our title
$I->seeInCurrentUrl('pages/hobbit');
```
Given all the conclusions from previous experiments and looking at how rich Codeception's documentation is as well as the fluent/readable API we decided to use it as our framework of choice for acceptance testing the application.

### Installation and setup

To start using Codeception we need to pull it via composer:

```shell
php composer.phar require "codeception/codeception:*"
```

After everything is downloaded, run the following command:

```shell
php vendor/bin/codecept bootstrap
```

This will create multiple folders in your laravel's test directory, for our case we removed everything except the `acceptance` folder as we only need Codeception to do acceptance testing, we also changed the location of the `_output`, `_data`, and `_support` to be inside the acceptance folder.

To start using Codeception we also need to run the Selenium Server, so go ahead and [download it](http://docs.seleniumhq.org/download/), also download the [Chrome Driver](https://sites.google.com/a/chromium.org/chromedriver/downloads) if you're testing using chrome.

To keep things in one place we created a `bin` folder inside `tests/acceptance` where we keep the selenium server and chrome driver executables.

We changed the configuration in `codecept.yml` to the following:

```yml
actor: Tester
paths:
    tests: tests
    log: tests/acceptance/_output
    data: tests/acceptance/_data
    support: tests/acceptance/_support
    envs: tests/_envs
settings:
    bootstrap: _bootstrap.php
    colors: true
    memory_limit: 1024M
extensions:
    enabled:
        - Codeception\Extension\RunFailed
```

We also modified `tests/acceptance.suite.yml` to use the chrome driver:

```yml
class_name: AcceptanceTester
modules:
    enabled:
        - WebDriver:
            url: http://project-name.dev
            browser: chrome
        - \Helper\Acceptance
```

Until now Codeception knows nothing about laravel, to fix that we included the following inside `tests/acceptance/_bootstrap.php`, this file will be executed before the test suite:

```php
require __DIR__.'/../../bootstrap/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';

putenv('DB_DATABASE=testing_database');

$app->instance('request', new \Illuminate\Http\Request);

$app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();
```

### Detecting Codeception tests in laravel

We wanted to know when Selenium is driving our laravel application rather than a normal browser to be able to switch the database being used, until now we couldn't find any solid solution to detect Selenium requests so we decided to set a cookie at the beginning of Selenium session and detect it using a middleware on laravel.

Here's how the middleware's handle method looks like:

```php
public function handle($request, Closure $next)
{
    if (app()->isLocal() && isset($_COOKIE['selenium_request'])) {
        config(['database.default' => 'mysql_testing']);

        if (isset($_COOKIE['selenium_auth'])) {
            Auth::loginUsingId((int) $_COOKIE['selenium_auth']);
        }
    }

    return $next($request);
}
```

Here we check for the existence of a cookie named `selenium_request` and switch the database connection to our testing database, also to avoid having to authenticate the user on every selenium test we use another cookie `selenium_auth` to pass an ID of a user to be authenticated outof the box.

### Initialising Codeception tests

As we mentioned before, we need to set a cookie on each new Selenium session, to do this we can add a custom action into `acceptance/_support/AcceptanceTester.php`:

```php
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    public function init($loginUsingId = null)
    {
        $this->amOnPage('/');
        
        $this->setCookie('selenium_request', 'true');

        if ($loginUsingId) {
            $this->setCookie('selenium_auth', (string) $loginUsingId);
        }
    }
}
```

That's pretty much everything we need to do, now let's write our first test.


## First Test

Unlike other tutorials which show only basic stuff, I'll show you a good part of a real test case on a complex screen:

```php
// Resetting the database by cloning the structure of the actual local database
exec('mysqldump -u '.env('DB_USERNAME').' -d my_project | mysql -u '.env('DB_USERNAME').' my_project_testing');

// ************************************
// Filling the database with initial data
// ************************************
$company = factory(Company::class)->create();

$business = factory(Business::class)->create(['company_id' => $company->id]);

$branch = factory(Branch::class)->create(['company_id' => $company->id, 'business_id' => $business->id]);

$owner = factory(User::class)->create(['type' => 3, 'company_id' => $branch->company_id]);

$product1 = factory(Product::class)->create(['business_id' => $branch->business_id,]);
$size11 = $product1->sizes()->save(factory(ProductSize::class)->make(['business_id' => $branch->business_id]));
$size12 = $product1->sizes()->save(factory(ProductSize::class)->make(['business_id' => $branch->business_id]));

// ************************************
// Test started here
// ************************************
$I = new AcceptanceTester($scenario);
$I->init($owner->id);

$I->wantTo('Test Online order making');
$I->amOnPage('/orders/item');
$I->waitForElementVisible("#tab1 .product[data-id={$product1->hid}]");

$I->expect('A modal will open when I click on a product');
$I->click("#tab1 .product[data-id={$product1->hid}]");
$I->waitForElement('#addProductModal[aria-hidden="false"]');

$I->expect('Price in modal to be equal to first product size');
$I->waitForElementVisible('.productFinalPrice');
$I->see($size11->price, '.productFinalPrice');

$I->expect('Price in modal changes when I select another size');
$I->seeElement('span[data-size-id='.$size12->hid.']');
$I->click('span[data-size-id='.$size12->hid.']');
$I->see($size12->price, '.productFinalPrice');

$I->expect('Price in modal changes when I change quantity');
$I->seeElement('input[name=newProduct_quantity]');
$I->fillField('[name=newProduct_quantity]', $productQty = 3);
$I->see($size12->price * $productQty, '.productFinalPrice');
$I->fillField('[name=newProduct_quantity]', $productQty = 2);
$I->see($size12->price * $productQty, '.productFinalPrice');

$I->expect('Price in modal changes when I change size while qty > 1');
$I->click('span[data-size-id='.$size11->hid.']');
$I->see($size11->price * $productQty, '.productFinalPrice');

// ....
```

See, everything is in one place and the API is very human friendly, with the help of a backend developer we can set up the test initial data and let the frontend developer use Codeception's documentation as a guide to write tests.

## Running tests

Before running tests we need to start the selenium server:

```shell
java -jar tests/acceptance/bin/selenium.jar -Dwebdriver.chrome.driver=tests/acceptance/bin/chromedriver
```

We keep this shell instance open and run our tests from a new tab:

```shell
vendor/bin/codecept run
```

A browser window will open and you can watch interactions happening live as if an actual user is using it.

# Conclusion

If you're testing non-javascript driven interfaces then you may use laravel's built-in PHP Browser based testing library, it's very powerful and the API is very readable as well. However if you need to test javascript driven interfaces then selenium is what you should be using.

In our case, we decided to go for a PHP selenium library to be able to have everything into one place, but you're free to use a JavaScript library if you want.
---
view::extends: _includes.blog_post_base
view::yields: post_body
post::title: Building and testing Artisan Commands
post::brief: In this post we're going to discuss building and testing console commands that involve interaction from the user side.
pageTitle: - Building and testing Artisan Commands
---

<img src="http://s30.postimg.org/buel0wn5t/post.gif">
<br>
While building the [Laravel Langman](https://github.com/themsaid/laravel-langman) package I was facing some difficulties trying to figure out how to test a console command that interacts with the user via questions, most of the tutorials and blog posts I found online were just calling the command and passing arguments/options using the `artisan()` method that comes with laravel's default test suite, however I didn't stumble upon a tutorial that covers user interaction with the console after calling the command.

In this post I'm going to share how I tested such type of commands, but let's first see how powerful artisan commands can be.

## Building a console command

First we'll need to create a new command by calling:

```shell
php artisan make:console ConfigManager --command=config:manage
```

This will create a new class in `app/Console/Commands` with the following content:

```php
class ConfigManager extends Command
{
    protected $signature = 'config:manage';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        //
    }
}
```

Our command will be used to manage laravel's configuration files, using this command we'll be able to read config files as well as update/add keys.

For us to be able to use the command we're going to make sure we have the right setup for the signature, laravel introduces an easy and simple way of defining a command signature, here are examples of the different cases:

```php
command:name {file} // Required Argument
command:name {file?} // Optional Argument
command:name {file=app} // Optional Argument with default value
command:name {file} {--no-creation} // An option with true or false
command:name {file} {--key=} // An option that requires a value
command:name {file} {--key=default} // Accepts a value with a default
```

For the ConfigManager command we only need an optional argument for the file name, so let's set the `$signature` to the following:

```php
protected $signature = 'config:manage {file?}';
```

It's also a good idea to set a description for the command:

```php
protected $description = 'Manage application config files.';
```

Here's the full content of our command with description of each part of the code:

<style type="text/css">
  .gist-file
  .gist-data {max-height: 500px;}
</style>
<script src="https://gist.github.com/themsaid/d2e7dc55c23e1da80cd9d5de47889d67.js"></script>

By reading the code you'll notice there are some interesting methods being called, such as `ask()`, `table()`, `confirm()`, `choice()`, etc...

These methods are part of the powerful Artisan Console provided by laravel, which is built on top of the [Symfony Console component](http://symfony.com/doc/current/components/console/introduction.html), let's discover some of the output methods.

```php
// Print a text output with a predefined style.
$this->info('');
$this->warn('');
$this->error('');

// Print a raw text output.
$this->line('');

// You may style your output like this:
$this->line('<fg=yellow;bg=magenta>Yellow text with a Magenta background.</> and a <options=bold>Bold Text</>.');
```

There's also a table component:

```php
$this->table(
  ['Key', 'Value'], 
  [
    ['database.name', 'homestead'], 
    ['database.username' => 'forge']
  ]
);
```

The first argument is an array of table headers, the second argument is an array of table rows, each item is an array itself with a value for each column.

## Interactivity

Here's the most interesting part, Artisan Commands can ask the user to provided specific pieces of information using a predefined methods that cover all the use cases an application might need, let's take a look at what's available:

```php
// Asking a yes/no question with a default value 'false', the value
// provided by the user is saved into a variable that can be
// used later to decide the flow.
$canDelete = $this->confirm('Are you sure you want to delete?', false);

// Asking for a string value, if no value provided the default one 
// `themsaid` will be taken. In case no default value was
// provided the output of as() will be 'null'.
$username = $this->ask('Provide a username', 'themsaid');

// Asking for a string value and provide some auto-completion, the
// user can still provide any value he wants.
$this->anticipate('Favourite color', ['green', 'yellow', 'blue']);

// Asking for a string value and provide some auto-completion, the
// user MUST select from the provided choises.
$this->choice('Favourite character', ['Jon', 'Arya', 'Daenerys']);

// Asking for a string but never show the user input in the console.
$username = $this->secret('Provide a password');
```

Go ahead and playaround with these methods and see the possibilities they provide.

## Testing a Console Command

Let's create a new test class:

```shell
php artisan make:test CommandTest
```

Inside our file we'll first write what we want to test in plain text:

```php
class CommandTest extends TestCase
{
    public function testItErrorsIfFileNotFound()
    {
        // Run config:manage not_found
        // Receive "The provided config file was not found!"
    }

    public function testItAsksForFileIfNotProvidedAsArgument()
    {
        // Run config:manage
        // Get asked to select a file from existing list
    }

    public function testItDisplaysATableOfFileContent()
    {
        // Run config:manage file
        // See correct records in table
    }

    public function testItCreatesNewFileWhenNeeded()
    {
        // Run config:manage file
        // Get asked "Would you like to create a new file?"
        // Answer yes
        // Get asked to provide file name "File name [ex: facebook]"
        // Provide a new file name
        // Get message "file was created successfully"
        // File created
    }

    public function testItErrorsWhileCreatingAnExistingFile()
    {
        // Run config:manage file
        // Get asked "Would you like to create a new file?"
        // Answer yes
        // Get asked to provide file name "File name [ex: facebook]"
        // Provide and existing file
        // Get error "File already exists!"
        // File is never touched
    }

    public function testItPrintsAMessageAndExitsIfNoFileNeeded()
    {
        // Run config:manage file
        // Get asked "Would you like to create a new file?"
        // Answer no
        // Get message "No file was created!"
    }
}
```

To be able to properly test the command we'll need to create a temporary directory acting as the `config` directory. We'll also need to resolve our own instance of `Illuminate\Contracts\Console\Kernel` to be able to add a new method, we will talk about the new method shortly when we discuss our flow of testing the command, for now we'll just add these methods:

<div class="alert alert-info">
<strong>Update 2016-04-21</strong><br>
Starting Laravel `5.2.30` you don't need to register a custom Kernel instance as the registerCommand() method is now included in the core by default, you may skip that step.</div>

```php
protected function setUp()
{
    parent::setUp();
    
    $this->app->singleton('Illuminate\Contracts\Console\Kernel', TestKernel::class);

    mkdir(__DIR__.'/CommandTestTemp');
}

public function tearDown()
{
    parent::tearDown();

    exec('rm -rf '.__DIR__.'/CommandTestTemp');
}
```

At the end of the Test class we'll add a new class with 1 method:

```php
class TestKernel extends \Illuminate\Foundation\Console\Kernel
{
    public function registerCommand($command)
    {
        $this->getArtisan()->add($command);
    }
}
```

Using this methods we'll be able to register the command to Console\Kernel when we need, later when the command is called, the instance we pass using this method will be the one used.

### Our first test case

```php
public function testItErrorsIfFileNotFound()
{
    // First we register a partial mock of the command, in this test case
    // we only need to mock the error() method and leave the rest
    // of the methods as is.
    $command = m::mock('\App\Console\Commands\ConfigManager[error]',[new \Illuminate\Filesystem\Filesystem()]);

    // We expect the method to be called with a specific string indicating
    // that the config file we're trying to read doesn't exist.
    $command->shouldReceive('error')->once()->with('The provided config file was not found!');

    // Now we register our mocked command instance in Console Kernel.
    $this->app['Illuminate\Contracts\Console\Kernel']->registerCommand($command);

    // Calling the command will run the mocked version of the command.
    // Notice how we pass the "file" command argument, that's how
    // arguments and options are passed to the artisan() test
    // helper method. Also we added '--no-interaction' to
    // prevent the application from expecting an actual
    // user input.
    $this->artisan('config:manage', ['file' => 'not_found', '--no-interaction' => true]);
}
```

So we create a partial mock from the command, mocking only the methods that indicate a console interaction we are concerned about, in this case it's the `error()` method, we add an expectation that this method will be called with the first argument containing a string "The provided config file was not found!".

Now the command will try to look for the a file called "not_found" which does not exist causing the `error()` method to be called:

```php
if (! in_array($file, $configFiles)) {
    return $this->error('The provided config file was not found!');
}
```

Using the same principle we can test a user interaction with the console, let's look at our second test:

```php
public function testItAsksForFileIfNotProvidedAsArgument()
{
    // We add two dummy files in the temporary test directory
    touch(__DIR__.'/CommandTestTemp/one.php');
    touch(__DIR__.'/CommandTestTemp/two.php');

    // We pass the path to the test directory as the second argument
    // of the constructor of the mocked command.
    $command = m::mock(
        '\App\Console\Commands\ConfigManager[choice]',
        [new \Illuminate\Filesystem\Filesystem(), __DIR__.'/CommandTestTemp']
    );

    // We expect the choice method to be called asking us to select one
    // of the two files existing in the config path.
    $command->shouldReceive('choice')
    		  ->once()
    		  ->with('Please select a file', ['one', 'two'])
    		  ->andReturn('two');

    $this->app['Illuminate\Contracts\Console\Kernel']->registerCommand($command);
    
    // Now we call the command and don't provide a "file" argument.
    $this->artisan('config:manage', ['--no-interaction' => true]);
}
```

So we mock the command, register the mocked version in Kernel, add our expectations for method calls, and pretend the user response in the form of return values.

---

I personally love building console commands whenever possible, I prefer dealing with the console rather than views, so if the task I want to accomplish is to be run by only me or someone who has access to the console I just write a command instead of having to build views, controllers, etc...

It's also cool to feel like having a conversation with your application, answering its questions and giving instructions about what to do next, there's this console command that copies data from an account to another based on specific question, I actually wrote all of the questions as real live ones:

```php
> Good afternoon Sir, what account would you like to copy today?
> This account has 212 orders, would you like to copy all of it or just a portion?
> 212 orders were copied, now what about the messages, should I copy them too?
> .....
```

See? It feels good, it's more realistic than clicking and dragging and dropping and scrolling ........
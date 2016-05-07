---
view::extends: _includes.blog_post_base
view::yields: post_body
post::title: The magic behind Laravel Valet 
post::brief: Laravel Valet was launched yesterday, I started testing projects I run locally on it and it just works! This post is me trying to understand how Valet is built & what makes it work.
pageTitle: - Building Laravel Valet
---

So yesterday [Taylor Otwell](https://twitter.com/taylorotwell) and [Adam Wathan](https://twitter.com/adamwathan) released [Laravel Valet](https://laravel.com/docs/5.2/valet), it's simply a tool that helps OS X users easily run their websites locally for development purposes, without the need to configure anything each time a new project needs to be created.

The idea behind valet is that it configures PHP's built-in web server to always run in the background when the operating system starts, then it proxies all requests to a given domain to point to your localhost 127.0.0.1

## Installation

Installing valet is very easy, you need to have [Homebrew](http://brew.sh/) installed and updated to the latest version, once confirmed install PHP 7.0 if it's not already installed, you may do this via `brew install php70`. You'll also need to [install composer](https://getcomposer.org/doc/00-intro.md#globally).

Now you need to run the following command:

```shell
composer global require laravel/valet
```

To be able to use valet's binary file globally, you need to make sure `~/.composer/vendor/bin` directory is in your system's `PATH`, go ahead and check that:

```shell
echo $PATH
```

Finally, run the following command:

```shell
valet install
```

## How it works

The `install` command does the following things:

1. Installs an OS X daemon to run PHP's built-in server at system boot.
2. Creates the configuration file for valet as well as a sample driver.
3. Installs `Dnsmasq` and configures it to respond to all `.dev` requests.
4. Configures OS X to send all `.dev` requests to `127.0.0.1`.

### OS X Daemons
A daemon is a program running in the background without requiring user input, the job the daemon should run is described in a property list XML file, here's a sample one:

```plist
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple Computer//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
        <key>Label</key>
        <string>com.apple.Spotlight</string>
        <key>ProgramArguments</key>
        <array>
            <string>/System/Library/CoreServices/Spotlight.app/Contents/MacOS/Spotlight</string>
        </array>
        <key>KeepAlive</key>
        <true/>
</dict>
</plist>
```

This job runs your mac's Spotlight and makes sure it keeps running so that it keeps indexing the filesystem changes for fast lookup on demand.

On the other hand, Laravel Valet installs the following job:

```plist
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>Label</key>
    <string>com.laravel.valetServer</string>
    <key>ProgramArguments</key>
    <array>
        <string>/usr/local/bin/PHP</string>
        <string>-S</string>
        <string>127.0.0.1:80</string>
        <string>/Users/mac/.composer/vendor/laravel/valet/server.php</string>
    </array>
    <key>RunAtLoad</key>
    <true/>
    <key>StandardErrorPath</key>
    <string>/tmp/com.laravel.valetServer.err</string>
</dict>
</plist>
```

The program this job runs is as simple as:

```shell
/usr/local/bin/PHP -S 127.0.0.1:80 /Users/mac/.composer/vendor/laravel/valet/server.php
```

This command starts PHP's built-in server using Valet's `server.php` as the router script.

The daemon is configured to run the program once the daemon is loaded, which is at system boot.

> Please note that paths to PHP and Valet will be different on your system.

Valet adds this file to `/Library/LaunchDaemons/com.laravel.valetServer.plist` which is where your application's daemons are saved.

Later on, Valet will use the `launchctl` command to tell the Mac's daemon to load the new file as it won't pick the changes immediately by default.

### Creating Valet's configuration files

This task is fairly simple, Valet creates a new directory `~/.valet` and adds an initial `config.json` file to it as well as a `Drivers` directory, we'll talk about `Drivers` shortly.

During this step, Valet also ensures that the created configuration files and directories have the correct permissions.

The initial content of the config file is as follows:

```json
{
    "domain": "dev",
    "paths": []
}
```

### Installing Dnsmasq

Dnsmasq is a lightweight DNS server, it accepts incoming requests and answers them from a local system or forwards them to a real DNS server. Laravel Valet uses Dnsmasq to listen to all `.dev` requests and resolve `127.0.0.1` in response.

Valet makes sure that `dnsmasq` is installed or installs it otherwise using `brew`, it then creates a custom config file for `dnsmasq` in `.valet/dnsmasq.conf` and tells `dnsmasq` about it.

The content of the file is as follows:

```
address=/.dev/127.0.0.1
```

Now that we have the DNS server configured, Valet will instruct the system to use it for all `.dev` queries, this is done by creating a resolver for the `.dev` domain in `/etc/resolver`, in this directory you may create a new file for each domain resolver, in our case it'll be `dev`.

So here's the final content of the `/etc/resolver/dev` file:

```
nameserver 127.0.0.1
```

Here dev is the domain name we configured Dnsmasq to respond to and 127.0.0.1 is the IP address of the server we'll use.

After all this is configured, Valet restarts Dnsmasq to be able to recognise the changes:

```shell
sudo brew services restart dnsmasq
```

It also restarts the daemon job we created above.

# Serving Websites

When we set up PHP's built in server to run on boot, we also passed Valet's server.php to act as the router to requests coming to the server, in this file all the magic happens:

**First**, Valet extracts the site name from `$_SERVER['HTTP_HOST']` so that when you hit `myAwesomeApp.dev` the site name value will be `myAwesomeApp`.

**Second**, it searches for any directories found inside the paths defined in the `config.json` file that matches the given site name. In this file, an array of paths is saved like this:

```json
{
    "domain": "dev",
    "paths": [
        "\/Users\/mac\/company-sites",
        "\/Users\/mac\/personal-projects"
    ]
}
```

Here Valet will look for a `myAwesomeApp` directory in `/Users/mac/company-sites` and `/Users/mac/personal-projects`, and in case no directory was found a 404 response will be given.

**Third**, the `ValetDriver` class will be used to determine which driver should handle the incoming request, more on drivers later.

If no driver found, a 404 response will be given.

**Finally**, it responds to the request by serving a static file or presenting a path to the index file based on the selected driver's configurations.

Long story short, the `server.php` file decides which driver should respond to the request and presents the results to the server.

# Understanding drivers

A driver is responsible for generating a proper response to incoming requests, at the beginning of the request Valet loops over every driver asking if it can handle the request, the first driver to answer "yes" is the winning one.

Valet looks for drivers in two locations:

- Drivers in `~/.valet/Drivers`
- Drivers downloaded by default with Valet

> `~/.valet/Drivers` is where you can configure any custom drivers you want.

When Valet checks for a driver's ability to serve the request it runs the `serves()` method and checks if the return value is true.

So, for every driver a `serves()` method should exist, here's the one for Laravel's driver:

```php
public function serves($sitePath, $siteName, $uri)
{
    return file_exists($sitePath.'/public/index.php') &&
           file_exists($sitePath.'/artisan');
}
```

This simply checks for the existence of a `/public/index.php` and a `/artisan` file, if found then Valet assumes that this is a laravel website and so the LaravelDriver shall be used.

After finding the driver, Valet will run the `mutateUri()` method of the driver, this method gives you the ability to alter the incoming URI so that you may run all your future driver logic with respect to custom settings. You may think of it as rewrite rule, here's an example for dealing with requests coming to `blog.dev` while the actual site files live in `sites/blog/public_html`.

```php
public function mutateUri($uri)
{
    return rtrim('/public_html'.$uri, '/');
}
```

Now a request like `blog.dev/assets/the-red-woman.gif` will serve `sites/blog/public_html/the-red-woman.gif`.

### Serving static files
Valet is currently able to find files that should be served, the next step is determining if that file is static or not, every driver should have a `isStaticFile()` method, WordPressDriver's method looks like this:

```php
public function isStaticFile($sitePath, $siteName, $uri)
{
    if (file_exists($sitePath.$uri) &&
        ! is_dir($sitePath.$uri) &&
        pathinfo($sitePath.$uri)['extension'] != 'php') {
        return $sitePath.$uri;
    }

    return false;
}
```

Here, all found paths that aren't directories or with a `.php` application is identified as static files, the method returns the full path to the file in case it should be considered static, otherwise it returns false.

Once a file is found to be static, Valet serves it as a response using the following method:

```php
public function serveStaticFile($staticFilePath, $sitePath, $siteName, $uri)
{
    $mimes = require(__DIR__.'/../mimes.php');

    header('Content-Type: '.$mimes[pathinfo($staticFilePath)['extension']]);

    readfile($staticFilePath);
}
```

### Responding to non-static requests
For every driver, a `frontControllerPath()` should exist, it shall return the full path to the file acting as the front controller of all the requests, for example here's the return value of the method in the LaravelDriver:

```php
return $sitePath.'/public/index.php';
```

---

Here we are now, with another awesome product from Laravel. Now all you need to do to start using valet after installation is running `valet park` from inside the directory where you keep all your projects, for more information about using Valet please refer to the documentation website:

https://laravel.com/docs/5.2/valet

---

## Closing Thoughts

Laravel Valet is said to be a "Minimalist Development Environment" and some people recommend that it should only be used for demos and small project prototypes, however I actually tested all my ongoing projects on it and they were all running perfectly.

For sure I still have to test it for a while before completely abandoning Homestead, but from what I can see now Valet is just the most useful thing released by Laravel after Laravel itself (and some would say Spark, but I haven't tried it yet), it simply makes the process of running a development version of my apps very easy, saves a lot of machine resources, and makes my dev. projects run much faster.
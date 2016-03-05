@extends('_includes.blog_post_base')

@section('post::title', 'Deployment via git and Bitbucket')
@section('post::brief', 'On how to automate deployment for your project via git and Bitbucket.')
@section('pageTitle')- @yield('post::title')@stop

@section('post_body')

    @markdown
    Every time I have a new project that needs to be deployed via git I forget how I did the previous time, this article is a guide on how to deploy bia git using bitbucket.

    ## First steps
    1. We have installed a repo on bitbucket and comitted some files, the repo ssh address should be like `git@bitbucket.org:username/repo-name.git`
    2. We have ssh access with sudo privileges to the live server we are going to deploy to
    3. We are at the root directory of the server `~`

    ## Generating ssh keys
    We'll generate ssh keys for our user account and the www-data account to allow both to perform a `git pull` command.

    ```bash
    ssh-keygen -t rsa
    ```
    1. Keep the default file path `/root/.ssh/id_rsa`
    2. keep the passphrase blank
    3. If there's a key already you may rewrite it if you want

    Now bring the newly created key by running:

    ```bash
    cat ~/.ssh/id_rsa.pub
    ```
    Copy the key and keep it aside.

    Now we need to generate a key for the `www-data` user, which is user used by nginx to access the server.

    ```bash
    # First we move to /var/www and create a .ssh directory
    cd /var/www
    mkdir .ssh

    # Now we'll create the ssh key inside this directory
    ssh-keygen -t rsa

    ```
    This time the key will be generated in `/var/www/.ssh/id_rsa`, now copy the key like the previous one.

    ```bash
    cat ~/../var/www/.ssh/id_rsa.pub

    # We need to give www-data write permissions over the generated key pair
    chgrp www-data ~/../var/www/.ssh/id_rsa.pub ~/../var/www/.ssh/id_rsa

    # Now we give the group a write access
    chmod 660 ~/../var/www/.ssh/id_rsa.pub ~/../var/www/.ssh/id_rsa
    ```


    Now that we have the two ssh public keys let's add them to Bitbucket.

    ### NOTICE:
    If you created keys with anything other than `id_rsa` you'll need to add them to your ssh-agent

    ```bash
    eval "$(ssh-agent -s)"
    ssh-add ~/path/to/custom_key
    ```

    ## Adding deployment keys to bitbucket
    1. Browser to the repository settings, select the Deployment Keys tab.
    2. Add a new key and call it `Human User` and paste the first key you generated here.
    3. Create a new key and call it `wwwdata User` and paste the second key we generated.

    ## Fetching the repository
    Now back to your server, head to the directory where you want to clone the repository, here we'll assume it's called cite

    ```bash
    cd /site

    # Run these commands
    git init
    git remote add origin git@bitbucket.org:[username]/[repo-name].git
    git pull origin master
    ```
    Now you've initialised a git repo and pulled the files from bitbuckedt.

    ## Connecting as www-data
    We are at the site directory

    ```bash
    sudo -u www-data git remote show origin
    ```

    You should see something like

    ```bash
    * remote origin
    Fetch URL: git@bitbucket.org:user/test.git
    Push URL: git@bitbucket.org:user/test.git
    HEAD branch: master
    Remote branch:
    master tracked
    Local ref configured for 'git push':
    master pushes to master (up to date)
    ```

    Now we know we can pull from the server, however we need to give www-data write access over the .git directory

    ```bash
    chgrp -R www-data .git
    chmod -R 770 .git
    ```

    We also need to allow www-data to write files in the project directory

    ```bash
    chgrp -R www-data site
    chmod -R 770 site
    ```

    Now we should be able to run

    ```bash
    sudo -u www-data git pull origin master
    ```

    ## Writing the deploy script
    I'm going to write the scrip in php, but you are free to use whatever language you want

    On your local machine, inside the project public directory create a deploy.php file with the following content

    ```php
    exec('cd /site/ && git pull origin master');
    ```

    Commit your changes and then pull from the server to upload the deploy.php file.

    Make sure the file is accessable from the browser `www.site.com / deploy.php - for example`, try making changes on your local machine, push to the repo, now point your browser to the deploy.php file. The script should be executed and the changes you made should be pulled.

    ## Registering a Hook
    Open the repository settings page on bitbucket and head to the Hooks tab.

    Add a new `POST` Hook and write the URL that leads to your deploy.php file.

    Now on every new commit, Bitbucket will hit the url you added to the Hook, the script will run, and the changes will reflect on the server immediately.

    @endmarkdown

@stop
@extends('_includes.blog_post_base')

@section('post::title', 'Faster git commits using bash functions')
@section('post::brief', 'A shortcut for git add --all, commit -m, and push')
@section('pageTitle')- @yield('post::title')@stop

@section('post_body')

    @markdown
    For fast pushing, this bash function will push to the current remote branch with a commit message.

    Usage:

    ```bash
    // While at the master branch, you call
    pushme adding a new class property

    // This is equal to:
    git add --all
    git commit -m 'adding a new class property'
    git push origin master
    ```

    # Setup
    Add the following to the `.bash_aliases` file in your root directory..

    ```bash
    function pushme {
        br=`git branch | grep "*"`

        params=''
        for i in $*;
        do
            params=" $params $d$i"
        done

        git add --all
        git commit -m "$params"
        git push origin ${br/* /}
    }
    ```
    @endmarkdown

@stop
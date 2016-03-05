@extends('_includes.blog_post_base')

@section('post::title', 'File and Directory permissions')
@section('post::brief', 'Here I’m going to explain the whole permissions issue, and I recommend you read about it if you are going to deal with servers.')
@section('pageTitle')- @yield('post::title')@stop

@section('post_body')

    @markdown
    I started dealing with servers a year ago, and I can tell that 90% of the time I spent hitting my head to a brick was because of a permission issue.

    Here I’m going to explain the whole permission issue, and I recommend you read about it if you are going to deal with servers.

    On a linux system, each file and directory has access rights to the owner, a group, and everyone else, each of them can read and/or write and/or execute(if it’s a program) that file.

    To be able to check the permissions of each file you can `ls -l` the parent directory, for each item the first portion represents the type of that item and the permissions, here’s an example:
    `-rwxrw-r--`

    The first “-” character represents that this item is a file, if it was a “d” it means it’s a directory. The other 9 characters represent the (read-write-execute) permissions for (owner-group-others).

    So for a file having these permissions `-rwxrw-r--`:

    * The Owner can read, write and execute.
    * Group members can read and write.
    * Everybody else can only read.


    # Changing permissions

    To change the permissions of a file or a directory we use the `chmod` command, and in order for us to understand how to change the permissions we need make something clear first:

    A computer translates the permission settings like this:

    ```
    rwx rw- r– = 111 110 100
    ```

    And in binary:

    111 = 7

    110 = 6

    101 = 5

    100 = 4

    So to change the permissions of a file, you can:

    ```bash
    chmod 764 filename
    ```

    After this operation, the permission settings of this file will be
    `rwxrw-r--`

    # Changing the owner of the file

    To change the owner of a file/directory you can use the **chown** command:`chown new_owner filename`

    You can also change the group ownership along with the owner

    ```bash
    chown new_owner:new_group filename
    ```

    @endmarkdown

@stop
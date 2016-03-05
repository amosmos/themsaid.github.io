@extends('_includes.blog_post_base')

@section('post::title', 'My First iOS application: Part 1')
@section('post::brief', ' What I am going to build is a very basic app called "Clozet", it is a closet organiser, you create sections and add items to each section so that you can come back and check what items you do have in your closet.')
@section('pageTitle')- @yield('post::title')@stop

@section('post_body')

    @markdown
    It's been a while since I started considering developing for iOS, I've been a programmer for long enough to realise that once you get the concepts of programming you can write using any programming language on any platform. What separates a programmer from another is the road they chose to take, I took the web route for more than 10 years, started as a kid with a bunch of "Macromedia Flash" websites, moving to
    based HTML pages, and finally moved to the land of Backend web development - the php route.

    I started reading iOS development books a few weeks before writing this article, began with Objective-c based on an advice from one of my fellows and then switched to swift, the difference is HUGE, and now after finally purchasing a macbook I'm able to start getting my hands on code.

    What I'm going to build is a very basic app called "Clozet", it's a closet organiser, you create sections and add items to each section so that you can come back and check what items you do have in your closet.

    I'm no expert in iOS at all, and by the time I'll publish these lines I'll be done with a tutorial in a great book called [iOS Apprentice](http://www.raywenderlich.com/85059/ios-apprentice-third-edition-part-3-now-available) which I recommend for everyone.

    ## Engine.start()
    To start we need to install Xcode, open it, and start a new project. Xcode will ask for the template you want to use and that would be "Single View Application".

    Now you need to fill some forms with the product name, organisation and identifier, language and device.

    For the language choose "Swift", "iPhone" for the device, and leave "Use Core Data" un-checked.

    Now choose where you want to save your project files.

    After saving, the project settings screen will show, you can read the options provided but most of them won't make sense to you "and currently me", we only need to un-check all the device orientation options but the "Portrait" one.

    ## The Main Storyboard
    On the left hand side you should see a side bar that contains a bunch of files, these are all your project files, you can move between files using the "Project Navigator".

    Open "Main.storyboard", what you see is the initial screen that appears to the user once he opens the app, a blank white screen.

    On the right hand side you should see another side bar "Utilities bar", it contains some extra options and at the bottom you should see the "Object Library", it's a list of components you can add to your story board.

    Use the search bar at the bottom of the library and search for "Label", now the list is filtered to show the Label component, drag and drop it inside the View Controller box (close to its left border) in the middle of the screen.

    Now double click on the label and write "Hello World!". done? great. Run the application using the play button located at the top left of the Xcode window, and that's it, this is your first app. If you know how to run the app on your phone you can use this app to see a hello world message on your iPhone whenever you want, of course that's not something very big but for me I was very proud of myself when I first wrote this nice app.

    ## Using Navigation & Table Views
    Now go to the Objects Library and search for "Table View Controller", drag the object and drop it somewhere in the canvas but not inside the box that contains the "Hello World" message.

    You now have two screens, one for the Hello World message, and the other for the table view, click on the "Hello World" screen and delete it.

    Go back to the Objects Library and search for "Navigation Controller", drag the object and paste it anywhere in the canvas.

    Yes it adds two screens so that you now have 3 screens inside the canvas, the navigation controller comes with a table view controller by default, select that table view and delete it so that we can later get the idea behind the association we just saw.

    Now align the two views horizontally with the navigation controller to the left and the table view to the right, if the screen is very crowded now, right click in the canvas and zoom to 25%.

    Click on the Navigation Controller, the right side bar "Utilities bar" contains some options the view has, you can browse the different option sections using the icon at the top of the side bar, now we need to click on the "Attributes inspector" icon and look for a checkbox that says "Is Initial View Controller", click to mark this option checked.

    In the story board, a slim arrow appears to the left of the navigation controller, it indicates that this screen is the first screen a user will see when he opens the app.

    Run the app and you should see a grey bar at the top with a black screen below, that grey bar is the navigation controller, it's a special screen used to give the user the ability to move between different screens of the app.

    The black screen is space, real space like the one Earth is travelling through. A navigation view controller is a special view that contains a stack of other views, it contains a main navigation bar at the top "the grey one" and a stack of views under each other located under the bar, the view that is pushed to this stack appears and replaces the current one, and when a view is popped from the stack the previous view appears.

    Now because this navigation view's stack is empty, it shows the black matter located in space, we'll need to add the table view controller to the stack of views this Navigation view has, to do that we'll need to add a segue of type "Relationship".

    press CTRl and drag from the Navigation View to the Table View, when you release the mouse a menu will appear, choose "root view controller". Now the table view we have is the root view of the navigation view.

    Run the app and you should see the grey with a table of white cells below it. COOL!

    ## Building the "closet sections screen"
    The Table view now contains a Prototype Cell and a navigation bar. Double click at the middle of the bar and write "Clozet", now Clozet will appear at the middle of the navigation bar when you run the app.

    A prototype cell is a static cell we use to build the look of our table and then use it to generate the cells that carry the real rows.

    For now we need to add a label inside the cell to show the section name. Grab the label from the Objects library and drop it inside the prototype cell, align it to the left.

    Resize the label to the full width of the cell, then click on the "Pin" button located at the bottom right side of the canvas.

    > You should see four icons, the second one from the left is the one.

    Using this window you can add constraints that rule the appearance of elements in the screen, we need to add constrains for the top, left, and right sides of the label so that the label takes the full width of the cell on all devices.

    Click on the red dotted arrow in the constraints screen for the top, left, and right side, and then select "Items of new constraints" from the "Update frames" section.

    > More on using [layout constraints](http://www.brianjcoleman.com/autolayout-xcode6/)

    Now that we have our prototype table cell ready, we need to add a button some where to allow users to add a new section, the best place for that is the navigation bar, so drag a "Bar Button" from the Objects Library and position it to the right side of the navigation bar in the table view screen.

    From the Attributes inspector inside the utilities bar, while the bar button is selected, change the Identifier to "Add".

    Perfect!

    ## Building the table view rows
    The prototype cell we have is used to build the cells we'll use for each row of data we provide, and in order to deal with this cell from the code we need to add an identifier to it.

    Select the prototype cell (Make sure the cell is selected and not the label), and from the attributes inspector write "Section" in the Identifier field. Now this prototype cell has an id of "Section"

    > To easily move between the elements in the canvas you can use the "Document Outline" bar, show it by clicking on the icon located at the bottom left side of the canvas.

    Now from the Project navigator (The left side bar of Xcode) select the file called "ViewController.swift"

    It is a simple class called "ViewController" that extends the "UIViewController" superclass, this class will be used to control the "Table View" we have in the storyboard, but first we need to give it a more descriptive name and also modify it's super class since we are using a table view.

    Rename the class to "SectionsViewController", and make it extend "UITableViewController", also remove all the methods inside the class. Now the code looks like this:

    ```swift
    import UIKit

    class SectionsViewController: UITableViewController {

    }
    ```

    Click on the "ViewController.swift" file from the project navigator, and rename the file to `SectionsViewController.swift`

    Now go to the storyboard and select the "Clozet Scene".

    > Each of the screens in the storyboard is called a scene, so now we have two scenes: 1- the Navigation Controller Scene. 2- the Clozet Scene.
    > The Clozet scene contains other sub views, you can see that in the Document Outline panel, to select the scene you need to click on the top level item of that scene inside the outline panel.
    > No it's called "Clozet" with a little yellow icon to the left.

    Now that you have the Clozet Scene selected, look at the right side panel and select the Identity inspector from the "Tabs menu at the top of the panel".

    In the Class option write "SectionsViewController", from now on this controller is responsible for all the elements inside this scene.

    Go back to "SectionsViewController.swift" and add the following method inside the class:

    ```swift
    override func viewDidLoad() {
    super.viewDidLoad()
    println("I control this view!")
    }
    ```
    Now run the app, once the table scene appears "I control this view!" will appear in Debug area at the bottom of the Xcode window.

    ### Adding test rows
    Now inside the SectionsViewController class remove `println("I control this view!")` and add the following methods.

    ```swift
    override func tableView(tableView: UITableView, numberOfRowsInSection section:Int) -> Int {
    return 3;
    }

    override func tableView(tableView: UITableView, cellForRowAtIndexPath indexPath: NSIndexPath) -> UITableViewCell {
    let cell = tableView.dequeueReusableCellWithIdentifier("Section") as UITableViewCell;

    return cell;
    }
    ```

    The first method tableView(numberOfRowsInSection) is called by the superclass UITableViewController on view load to determine the number of rows that the view needs to build, it expects a return type of Integer, we just return 3 for the purpose of testing.

    The second method tableView(cellForRowAtIndexPath) is called each time a cell is being built, so for this view the action will be called three times.
    Inside the method we have two local variables: **tableView** that holds the table view object & **indexPath** that carries the index of the current cell being built.

    On the tableView object we call the tableView.dequeueReusableCellWithIdentifier() method, this method uses an existing cell if there are available cells ready to be reused, or create a new one if there isn't.

    For performance reasons iOS reuses the cells created for the table but are not currently used instead of creating new cell objects for each row.

    Imagine if we have 100 rows of data, the screen only shows 10 and it loads more on scroll. Only 11 cells are created for this table view, 10 carrying the data that currently appear and 1 for the extra cell that will appear when you initially start scrolling, when a row disappears and a new row is about to come to the view the cell used to show the disappearing row will now used to hold the data of the new row. That way only a handful of cell objects will be created.

    tableView(cellForRowAtIndexPath) expects a return type of UITableViewCell, and that's what we provide by returning the cell local variable.

    Now run the app and you'll see 3 cells carrying a label.

    > The app will run fine, however a message will appear in the debug area. It's a bug in Xcode, we can fix it by adding `tableView.rowHeight = 44` to the viewDidLoad() method of the SectionsViewController class.

    ---

    ## Conclusion
    We now started our first iOS app, things can be very blurry at the beginning but by time it'll get more clear.

    During the next set of posts we'll continue building "Clozet" and see where things will go.

    I always believe that the best way to learn something is by trying to teach it.

    > If you can't explain it simply, you don't understand it well enough. --Albert Einstein

    So if something is not very clear here, it means I didn't get it very well yet. Hopefully by time I'll understand things more clearly.

    @endmarkdown

@stop
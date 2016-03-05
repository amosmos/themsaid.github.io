@extends('_includes.blog_post_base')

@section('post::title', 'My First iOS application: Part 4')
@section('post::brief', 'In the first 3 parts of the series we created a screen that lists the sections of "Clozet", with the ability to add new or edit/delete sections.')
@section('pageTitle')- @yield('post::title')@stop

@section('post_body')

    @markdown
    In the first 3 parts of the series we created a screen that lists the sections of "Clozet", with the ability to add new or edit/delete sections.

    You can swipe on a section name, and two actions buttons for editing and deleting will appear, when we click edit we are able to click cancel to get back to the sections screen without making any changes. However if a user clicked on "Delete" by mistake, the section will be deleted with no turning back, we need to alert the user for what he's about to do and ask him to confirm the deletion.

    Open SectionsViewController and head to the tableView:editActionsForRowAtIndexPath method.

    Modify the "delete" constant as follow:

    ```swift
    let delete = UITableViewRowAction(style: UITableViewRowActionStyle.Default, title: "Delete", handler: { (action, indexPath) -> Void in

    let alert = UIAlertController(title: "Please confirm", message: "Are you sure you want to delete this section?", preferredStyle: UIAlertControllerStyle.Alert)

    alert.addAction(UIAlertAction(title: "Cancel", style: UIAlertActionStyle.Cancel, nil))

    alert.addAction(UIAlertAction(title: "Delete", style: UIAlertActionStyle.Default, handler: {
    (action:UIAlertAction!) -> Void in

    self.sections.removeAtIndex(indexPath.row)
    tableView.deleteRowsAtIndexPaths([indexPath], withRowAnimation: .Automatic)
    }))

    self.presentViewController(alert, animated: true, completion: nil)
    })
    ```

    Now clicking on "Delete" will show an alert with two actions, one for cancellation and one for confirmation. Run the app to try it.

    ## Persisting data in the phone storage
    Until now we are dealing with an in-memory data storage, every time the app is terminated we'll lose all the data and that's not what we want our app to be like.

    To save the data of our application so that it's available even after app relaunch we need to create a data source object that handles saving and reading records on the device storage.

    Create a new swift file in the project root and call it "DataSource.swift", add the following code to it under the `import foundation` statement:

    ```swift
    class DataSource {
    let sections = [Section]()

    func dataFilePath() -> String {
    let paths = NSSearchPathForDirectoriesInDomains(.DocumentDirectory, .UserDomainMask, true) as [String]

    return paths[0].stringByAppendingPathComponent("Clozet.plist")
    }
    }
    ```

    The dataFilePath method extracts the path of the file we are going to save our data to, here it's called "Clozet.plist", and it'll be located inside the documents directory of our app.

    We'll archive our objects into this file on writing and unarchive them when we open the app after termination again, to be able to archive the "Section" object we need it to implement the NSCoding protocol, so open Section.swift and make the class implement the protocol `class Section: NSObject, NSCoding`, and add these two methods:

    ```swift
    func encodeWithCoder(aCoder: NSCoder) {
    // On encoding the object, save name property to a key called "name"
    aCoder.encodeObject(name, forKey: "name")
    }

    required init(coder aDecoder: NSCoder) {
    // On decoding, set the name instance variable to the value of the object with key "name"
    name = aDecoder.decodeObjectForKey("name") as String
    super.init()
    }
    ```

    Now open DataSource.swift and add the following:

    ```swift
    var sections = [Section]()

    init(){
    load()
    }

    func save() {
    let data = NSMutableData()
    let archiver = NSKeyedArchiver(forWritingWithMutableData: data)
    archiver.encodeObject(sections, forKey: "sections")
    archiver.finishEncoding()
    data.writeToFile(dataFilePath(), atomically: true)
    }

    func load() {
    let path = dataFilePath()
    if NSFileManager.defaultManager().fileExistsAtPath(path) {
    if let data = NSData(contentsOfFile: path) {
    let unarchiver = NSKeyedUnarchiver(forReadingWithData: data)
    sections = unarchiver.decodeObjectForKey("sections") as [Section]
    unarchiver.finishDecoding()
    }
    }
    }
    ```

    Now let's tell our controller to read from the data source instead of the in-memory data storage, open SectionsViewController.swift and add the following instance constant:

    ```swift
    var dataSource: DataSource!

    // Remove the sections instance variable
    // var sections = [Section]()
    ```

    Also remove this from viewDidLoad()

    ```swift
    sections = [
    Section(name: "Dresses"),
    Section(name: "Shoes"),
    Section(name: "Hand bags")
    ]
    ```

    Search for every call to the "sections" instance variable and replace it with `dataDource.sections`.

    Now when the app launches our data will be read from the clozet.plist file, however we still need to save the data a user enter so that it'll be available on the next application launch. We have two options here:

    1. Save the data on every update/addition of a new item
    2. Save the data once only if the app is about to be terminated

    Using NSKeyedArchiver is a bit expensive, specially if the app data became so large, and calling it on every update can consume memory, so we'll go for option # 2.

    open AppDelegate.swift and add the following constant inside the class:

    ```swift
    let dataSource = DataSource()
    ```

    Now look for the method called applicationWillTerminate and add the following line inside it:

    ```swift
    dataSource.save()
    ```

    No on the app launch, an instance of DataSource will be created, it'll be used to set and get the data of the application. Now we need to pass this object to the SectionViewController for it to be able to load the data.

    Inside the AppDeleggate there's a method called application:didFinishLaunchingWithOptions, add the following code inside it:

    ```swift
    let navigationController = window!.rootViewController as UINavigationController
    let controller = navigationController.viewControllers[0] as SectionsViewController

    controller.dataSource = dataSource

    return true
    ```

    This will pass the instance of DataSource to the SectionsViewController on app launch.

    Run the app, add some sections, modify maybe, and then click cmd+shift+h to send the app to the background, terminate the app from the simulator stop button.

    Now if you relaunch the app you'll see your data there in the way you saved it.

    @endmarkdown

@stop
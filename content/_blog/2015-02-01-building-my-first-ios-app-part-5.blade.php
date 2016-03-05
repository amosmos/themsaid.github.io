@extends('_includes.blog_post_base')

@section('post::title', 'Building my first iOS app: Part 5')
@section('post::brief', 'Wrapping up my first trial to build an iOS app.')
@section('pageTitle')- @yield('post::title')@stop

@section('post_body')

    @markdown
    In the first 4 parts of the series we added the following features to the app:

    1. User can view the list of sections
    2. User can add a new section
    3. User can edit sections
    4. User can delete sections
    5. Data is saved on the device so that it'll get loaded correctly even if the app was terminated


    ## Using the collection view controller
    In all the previous views we used the table view, now let's play around with the Collection View Controller.

    Open Main.storyboard and add a new Collection View Controller to the canvas.

    Click on the view controller, open the attributes inspector, and change the Controller title to "Section Items".

    Click on the Collection View, and in the attributes inspector change the background to White Colour. Also in the side inspector, set the Cell Width and Height to 250.

    Open the identity inspector and add "ItemsViewController" to the class field.

    Add a new Navigation Controller to the canvas, delete the accompanied table view controller, and build a segue between the new Navigation controller and the Collection controller and select "root view controller" from the menu. Now the collection view is on the top of the stack of views this navigation controller has.

    Create a new swift file and call it "Item.swift", save the file under the Models group.

    Add the following the newly created Item.swift file:

    ```swift
    class Item: NSObject, NSCoding{
    var name = ""
    var imageName: String

    init(name: String, imageName: String){
    self.name = name
    self.imageName = imageName

    super.init()
    }

    func encodeWithCoder(aCoder: NSCoder) {
    aCoder.encodeObject(name, forKey: "name")
    aCoder.encodeObject(imageName, forKey: "imageName")
    }

    required init(coder aDecoder: NSCoder) {
    name = aDecoder.decodeObjectForKey("name") as String
    imageName = aDecoder.decodeObjectForKey("imageName") as String
    super.init()
    }
    }
    ```

    Create a new iOS Cocoa Touch Class file inside the controllers group and name it "ItemsViewController" and make it extend the "UICollectionViewController" class.

    ```swift
    class ItemsViewController: UICollectionViewController {
    var section: Section!

    override func viewDidLoad() {
    super.viewDidLoad()

    title = "\(section.name) items"
    }

    override func collectionView(collectionView: UICollectionView, numberOfItemsInSection section: Int) -> Int {
    return 5
    }

    override func collectionView(collectionView: UICollectionView, cellForItemAtIndexPath indexPath: NSIndexPath) -> UICollectionViewCell {
    let cell = collectionView.dequeueReusableCellWithReuseIdentifier("Item", forIndexPath: indexPath) as UICollectionViewCell

    return cell
    }
    }
    ```

    ### Making the link
    Create a new segue between the prototype cell of the Clozet Scene and the Navigation controller of the Items scene, choose "show" from the dropdown.

    Click on the segue and in the attributes inspector add "ShowSection" as the identifier.

    Also click on the prototype cell of the collection view add the Reuse identifier "Item"

    Now open SectionsViewCotroller, and change the prepareForSegue method to be like this:

    ```swift
    override func prepareForSegue(segue: UIStoryboardSegue, sender: AnyObject?) {

    if segue.identifier == "AddSection" || segue.identifier == "EditSection"{
    let navigationController = segue.destinationViewController as UINavigationController
    let controller = navigationController.topViewController as AddEditSectionViewController

    controller.delegate = self

    if segue.identifier == "AddSection"{
    controller.section = nil
    }else if segue.identifier == "EditSection"{
    let cell = sender as UITableViewCell
    let indexPath = self.tableView.indexPathForCell(cell)!

    controller.section = dataSource.sections[indexPath.row]
    }

    }else if segue.identifier == "ShowSection"{
    let controller = segue.destinationViewController as ItemsViewController

    let cell = sender as UITableViewCell
    let indexPath = self.tableView.indexPathForCell(cell)!

    controller.section = dataSource.sections[indexPath.row]
    }
    }
    ```

    There's one final step to make this installation complete, we need to add the items variable to the Section object, it'll hold the items the section contains.

    Open Section.swift and modify the file as follow:

    ```swift
    class Section: NSObject, NSCoding{
    var name = ""
    var items = [Item]()

    init(name: String){
    self.name = name

    super.init()
    }

    func encodeWithCoder(aCoder: NSCoder) {
    aCoder.encodeObject(name, forKey: "name")
    aCoder.encodeObject(items, forKey: "items")
    }

    required init(coder aDecoder: NSCoder) {
    name = aDecoder.decodeObjectForKey("name") as String
    items = aDecoder.decodeObjectForKey("items") as [Item]
    super.init()
    }
    }
    ```

    Before you can run the app, open the iOS simulator and select "Reset content and settings" from the iOS Simulator menu.

    Now run the app, add new sections, click on any of them and a screen will show up with the navigation bar having the section name.

    Now running the app and clicking on a section will show 5 blue boxes each represents a cell in our UICollectionView.

    ## Adding items to the section
    In the sections items scene, add a new bar button at the right side of the navigation bar of the view.

    From the attributes controller, choose "Add" as the Identifier value.

    Now add a new navigation controller with its table view controller, and draw a segue between the newly added bar button and the navigation controller and select "present modally".

    Now clicking on the button will open the new table view under the navigation controller we just added.

    Select the newly created table view controller, and from the attributes inspector set the title to "Add/Edit Item", navigate to the identity inspector and set the Class to "AddEditItemViewController".

    Select the table view inside the scene and from the attributes inspector set Content to "Static Cells" and Style to "Grouped".

    Now select the "Table View Section" from the Document Outline, and set the Rows to 2.

    Now we have to table rows inside the view, for the first row add a text field. Click on the field and add a placeholder "Add Item name", also select the "None" border style. Add constrains so that it fills the whole row

    ### Creating the view controller class
    Now let's create that new AddEditItemViewController class, add a new Cocoa Touch class in the controllers group and make it extend the UITableViewController.

    Add a new outlet inside AddEditItemViewController and make the class look like this:

    ```swift
    class AddEditItemViewController: UITableViewController {
    @IBOutlet weak var textField: UITextField!

    override func viewDidLoad() {
    super.viewDidLoad()
    tableView.rowHeight = 44

    }
    }
    ```
    Connect the outlet to the text field of the Add/Edit item view in the storyboard.

    ---

    # stop.restart()
    After moving through the process of building Clozet I found out that there are some missing elements that I don't know about yet, like how to interact with the Camera for example, and so I decided I'll hold it for a while and start following a couple of other tutorials until I reach some solid understanding of the process of building an app, and then I'll come later and finish this app.

    @endmarkdown

@stop
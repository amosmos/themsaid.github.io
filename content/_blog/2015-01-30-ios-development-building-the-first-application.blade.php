@extends('_includes.blog_post_base')

@section('post::title', 'My First iOS application: Part 2')
@section('post::brief', 'At the end of the last post we were able to create the sections screen for "Clozet", a table view that has 3 dummy rows. Now we will start adding some real data and see how to build a screen a user can use to add more sections and edit existing ones.')
@section('pageTitle')- @yield('post::title')@stop

@section('post_body')

    @markdown
    At the end of the last post we were able to create the sections screen for "Clozet", a table view that has 3 dummy rows. Now we'll start adding some real data and see how to build a screen a user can use to add more sections and edit existing ones.

    ## Reading data
    First we'll save the data in memory, it'll be available as long as the application is running, once it's terminated the data is lost, however I want to focus first on the coding side of the project and then move my attention on the data, so an in-memory storage is good for now.

    Before we continue, let's tidy up our files a little, click on the project title in the project navigator and select "New Group", name it Models and create another group for controllers.

    The Models directory will hold all our entity classes, and the controllers will contain all the view controllers.

    Finally, move the SectionsViewController file to the Controllers group(directory).

    We need to create a class that represents a section, click on the Controllers group in the project navigator and choose, create new file.

    Choose a Swift File and make sure the iOS selection is selected (not OS X), now name the file "Section" and then save.

    Add the following class to the file

    ```swift
    class Section: NSObject{
    var name = ""

    init(name: String){
    self.name = name

    super.init()
    }
    }
    ```

    This will create a class named Section extending the NSOBject class, with an instance variable called "name" of type string, and an initialiser was added that accepts the section name as an argument.

    Now that we have the our Data Model ready, lets move to SectionsViewController and create initial sections the user will see once the screen is loaded.

    Inside the calls, add a new instance variable and declare it like so:

    ```swift
    var sections = [Section]()
    ```

    This will create an instance variable of type _Array of Section objects_.

    Now inside the viewDidLoad() method, make it look like this:

    ```swift
    override func viewDidLoad() {
    super.viewDidLoad()
    tableView.rowHeight = 44

    sections = [
    Section(name: "Dresses"),
    Section(name: "Shoes"),
    Section(name: "Hand bags")
    ]
    }
    ```

    Now we added a few Section objects to our sections instance variable.

    Since we now read from an array, we can update the tableView(numberOfRowsInSection) method to this:

    ```swift
    override func tableView(tableView: UITableView, numberOfRowsInSection section:Int) -> Int {
    // we count the number of objects inside the array
    return sections.count
    }
    ```

    And update tableView(cellForRowAtIndexPath) to be:

    ```swift
    override func tableView(tableView: UITableView, cellForRowAtIndexPath indexPath: NSIndexPath) -> UITableViewCell {
    let cell = tableView.dequeueReusableCellWithIdentifier("Section") as UITableViewCell;

    // We access the label inside the cell using cell.viewWithTag()
    // This method searches the cell object for child views
    // with tag 1000
    let label = cell.viewWithTag(1000) as UILabel

    // Now we update the value of the label text using the Section object
    // that is currently we are displaying the cell for
    label.text = sections[indexPath.row].name

    return cell;
    }
    ```

    We are calling a view with tag 1000, so let's assign that tag to the label view inside our cell.

    Open Main.storyboard, select the label inside the prototype cell and in the attributes inspector fill the tag field with "1000".

    Run the app and you should see the list of Sections you just added displayed inside the table.

    ## Adding a new section
    We need to build the screen where we can add/edit sections, it should appear once the user clicks on the PLUS sign (Add button) that is located at navigation bar of the sections screen.

    Move to the story board and add a new navigation controller beside the sections screen.

    CTRL drag from the Add button to the new navigation view and select "present modally". We created a segue between the Add button and the navigation screen, once the user clicks the button this screen will appear.

    Change the title in the navigation bar of the new screen to "Add\Edit section".

    Select the new table view from the Outline panel, the item in the panel is `Add/Edit Section Scene > Add/Edit Section > Table View`

    From the attributes inspector, change the Content attribute to "Static Cells" then delete the extra 2 cells that'll be created inside the view, we only need one to hold the text field of the section title. Also change the Style attribute to "Grouped".

    Now add a new Text Field inside the cell we have:

    - change the Border Style from the attributes inspector to None.
    - change the Return Key to "Done".
    - add a place holder "Section name"
    - make the "Auto-enable Return Key" checked

    Align the text field inside the cell like we did with the label in the sections screen.

    Now search the object library for a Bar Button, place one at the right side of the navigation bar inside the Add/Edit Section view, and one at the left.

    Select the button to the right and change it's Identifier in the attributes inspector to "Done", and the one at the left to "Cancel".


    ### Creating the AddEditSectionViewController
    Now that we have the new screen, we need to create the controller that'll handle it, create a new iOS Cocoa Touch class inside the Controllers folder and name it AddEditSectionViewController and make it extend the UITableViewController class.

    Open the storyboard, select the Add/Edit table view, in the identity inspector select "AddEditSectionViewController" as the Class.

    Inside AddEditSectionViewController.swift, add the following inside the class:

    ```swift
    override func viewDidLoad() {
    super.viewDidLoad()
    tableView.rowHeight = 44
    }
    ```
    Now if you run the app, clicking on the Add button will show the Add/Edit Section screen, and when you click on the text area you are able to write some text, clicking done doesn't have any effect yet, however do you notice that you can not press the submit button of the keyboard when the text field is empty but you can click on the done button located in the navigation bar? This will allow the user to add Sections with no titles, and that's something we don't want.

    To fix that we need to disable the done button if the text field is empty, we detect the state of the text field by checking the change of it's text each time the user perform any operation. We'll get to that in a while, however for now we need to be able to access the Done button object from our code, we do that using outlets.

    Inside AddEditSectionViewController add the following outlet right below the class declaration:

    ```swift
    @IBOutlet weak var doneButton: UIBarButtonItem!
    ```

    - `@IBOutlet` is a keyword used to declare an outlet
    - `weak` is an indication that the relation between the the controller object and the bar button object is weak, the controller doesn't OWN the button, it's ok if the button is destroyed.
    - `!` indicated that this is an optional variable, the value can be nil

    Now back to the story bard, select the Done button and from the connections inspector in the right side bar, search for "New referencing outlet" and you'll find a small circle to the right of the title, CTRL drag from this circle to the Add/Edit Section view controller in the outline panel, select the doneButton outlet from the drop down that'll appear. By doing this we have a pointer to the Done button object inside the AddEditSectionViewController class.

    Now create a new reference outlet that points to the textField object and name it textField.

    ### Understanding Delegates
    Now that we have access to the textField inside the controller, we need to be able to detect any change inside this text field.

    Fortunately the text field object calls a method textField:shouldChangeCharactersInRange:replacementString whenever a change happens. We need to have access to this method inside the controller, we do this by using delegates, we say that the text field delegates it's methods to another class that implements a certain protocol.

    So we need the AddEditSectionViewController class to implement the UITextFieldDelegate protocol, and tell the text field object that AddEditSectionViewController is now its delegate.

    ```swift
    class AddEditSectionViewController: UITableViewController, UITextFieldDelegate {
    ... // The rest of the class
    }
    ```

    Now the class implements the UITextFieldDelegate protocol, we switch to the story  board, select the text field, open the connections inspector and ctrl drag from the circle besides "delegate" to "Add/Edit Section" in the Outline panel.

    That's it, AddEditSectionViewController is now the delegate of the text field object, and we can implement the text field methods inside this controller. So let's add the following method to the controller:

    ```swift
    func textField(textField: UITextField, shouldChangeCharactersInRange range: NSRange, replacementString string: String) -> Bool {

    return true
    }
    ```

    This method is called whenever a change is about to happen, its attributes contain the textField object, the range of characters that is going to change, and the value added.

    We need to check if the new value is empty of not, but we'll have to build the new value from the attributes provided by this method. Inside the method add the following local constants.

    ```swift
    let oldText: NSString = textField.text
    let newText: NSString = oldText.stringByReplacingCharactersInRange(range, withString: string)
    ```

    The first constant holds the old value of the text field, while the second one holds the new value, we get the new value by calling the stringByReplacingCharactersInRange(range, string) method on the old value, it performs the replacement of the values in the range with the new values producing the final value of the text field.

    Now that we have the new value, we'll check if the value if enabled and only then we'll enable the done button, otherwise we'll disable it.

    Add the following line before the end of the return line of the method:

    ```swift
    doneButton.enabled = (newText.length > 0)
    ```

    Now the doneButton.value is true or false based on the newText.length

    We'll also need to add `doneButton.enabled = false` to viewDidLoad() so that the initial value of the done button is false.

    Run the app and you'll see the effect of what we just did.

    We can also enhance the user experience by selecting the textField by default when the view opens, this will make the user able to write in the text field instantly without bothering himself with tapping on it first, we do that by adding the following line to viewDidLoad()

    ```swift
    textField.becomeFirstResponder()
    ```

    ### Defining actions
    Now that we have the Add/Edit screen built, we need to manage how it'll perform its function. This screen is used to add or edit a section item, for now let's see how we are going to use it to add items.

    We have to actions that can happen, first the user clicks Done and an item will be added, or cancel and the screen will go away. For these actions we need to create the methods that'll handle them, inside AddEditSectionViewController add the following methods

    ```swift
    @IBAction func cancel(){

    }

    @IBAction func done(){

    }
    ```

    Open the story board and from the outline panel ctrl+drag from the Done button to the Add/Edit section, select "done" under the Sent Actions section. Do the same for the cancel button but this time select "cancel"

    Both actions affect the SectionsViewController, right? On cancellation the sections view should dismiss the model it showed, and on completion it should update itself by adding a new element to it's sections instance variable.

    Since the SectionsViewController is the one controlling these actions, AddEditSectionViewController should delegate these methods to the sections view controller, we do that by:

    1. Creating a protocol
    2. Make the SectionsViewController implement that protocol
    3. Implement the methods required inside the sections controller
    4. Tell AddEditSectionViewController that the sections controller is now its delegate

    Let's start by creating a protocol, above the declaration of AddEditSectionViewController add the following:

    ```swift
    protocol AddEditSectionViewControllerDelegate: class{
    func addEditSectionDidCancel(controller: AddEditSectionViewController)
    func addEditSectionDidCompleteAdding(controller: AddEditSectionViewController, section: Section)
    }
    ```

    A protocol was created that tells every class that'll implement it to implement the methods listed above.

    Let's make the SectionsViewController implement that protocol by doing so:

    ```swift
    class SectionsViewController: UITableViewController, AddEditSectionViewControllerDelegate {
    ... // The rest of the class
    }
    ```
    implement
    We also need to implement the two methods from the protocol inside the class:

    ```swift
    func addEditSectionDidCancel(controller: AddEditSectionViewController) {

    }

    func addEditSectionDidCompleteAdding(controller: AddEditSectionViewController, section: Section) {

    }
    ```

    Back to AddEditSectionViewController, ad the following instance variable:

    ```swift
    weak var delegate: SectionsViewController!
    ```

    In the storyboard, click on the segue connecting the Clozet screen to the navigation controller of the add/edit screen, from the attributes inspector write "AddSection" in the identifier.

    Inside SectionsViewController add this method:

    ```swift
    override func prepareForSegue(segue: UIStoryboardSegue, sender: AnyObject?) {
    if segue.identifier == "AddSection"{
    let navigationController = segue.destinationViewController as UINavigationController
    let controller = navigationController.topViewController as AddEditSectionViewController
    'controller.delegate = self
    }
    }
    ```

    This checks if the segue about to be performed is the "AddSection" segue, if so it'll get the navigation for AddEditSectionViewController object using segue.destinationViewController, using the navigation we get the controller using the topViewController property and assign the current instance of SectionsViewController as its delegate.

    > The topViewController property of a navigation holds a reference to the top most view inside the stack of the navigation controller sub views.

    Now SectionsViewController and AddEditSectionViewController can communicate using the protocol we built earlier.

    Let's implement the actions for the cancel and done buttons, open AddEditSectionViewController and modify the cancel() and done() actions to the following:

    ```swift
    @IBAction func cancel(){
    delegate?.addEditSectionDidCancel(self)
    }

    @IBAction func done(){
    let section = Section(name: textField.text)

    delegate?.addEditSectionDidCompleteAdding(self, section: section)
    }
    ```

    Now when the buttons are clicked, the corresponding methods will be called on the delegate.

    Back to SectionsViewController, implement these to methods like follow:

    ```swift
    func addEditSectionDidCancel(controller: AddEditSectionViewController) {
    dismissViewControllerAnimated(true, completion: nil)
    }

    func addEditSectionDidCompleteAdding(controller: AddEditSectionViewController, section: Section) {
    sections.append(section)

    dismissViewControllerAnimated(true, completion: nil)
    }
    ```

    Now if you run the app, click to add a new section, write the section name, and click done, the screen will disappear and the new section will be added to the list.

    However the done button at the top (in the navigation bar) is working, but the one of the built in keyboard isn't. We'll need to register a segue to the done() method of the controller.

    Open the story board, look for the text field and open the connections inspector, make a segue for the event called "Did End On Exit".

    ---

    We're now done with the feature of adding new sections to the screen. In the next post we'll see how to edit and delete sections along with other cool features for "Clozet".

    @endmarkdown

@stop
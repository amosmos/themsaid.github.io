@extends('_includes.blog_post_base')

@section('post::title', 'My First iOS application: Part 3')
@section('post::brief', 'In Part 1 and 2 of this series, we built an interface that lists the sections of "Clozet", also a bar button that leads to another modal screen that allows us to add a new section. No let us see how to allow the user to edit and delete sections')
@section('pageTitle')- @yield('post::title')@stop

@section('post_body')

    @markdown
    In Part 1 and 2 of this series, we built an interface that lists the sections of "Clozet", also a bar button that leads to another modal screen that allows us to add a new section. No let's see how to allow the user to edit and delete sections.

    First of all, create a segue between the prototype cell of the Sections List scene and the navigation Controller of the Add/Edit screen.

    We'll do that from the Connections inspector of the prototype cell, CTRL+drag from the selection connection to the navigation controller of the Add/Edit screen and select "present modally".

    Now click on the new created segue, and form the attributes inspector add this to the identifier field "EditSection".

    Now you're able to identify the segue from the SectionsViewController class, inside the prepareForSegue() method modify the code to look like so:

    ```swift
    override func prepareForSegue(segue: UIStoryboardSegue, sender: AnyObject?) {
    let navigationController = segue.destinationViewController as UINavigationController
    let controller = navigationController.topViewController as AddEditSectionViewController

    controller.delegate = self

    if segue.identifier == "AddSection"{
    controller.section = nil
    }else if segue.identifier == "EditSection"{
    let cell = sender as UITableViewCell
    let indexPath = self.tableView.indexPathForCell(cell)!

    controller.section = sections[indexPath.row]
    }
    }
    ```

    If the segue identifier is "EditSection" we path the selected section object to the AddEditSection controller.

    Now open AddEditSectionViewController and add a new instance constant.

    ```swift
    let section: Section!
    ```

    Now we have the selected section object inside the Add/Edit view controller, we can check if the section is nil then we are Adding a new section, and if the section has a value then we're editing a section.

    In viewDidLoad():

    ```swift
    override func viewDidLoad() {
    super.viewDidLoad()
    tableView.rowHeight = 44

    if section == nil{
    doneButton.enabled = false
    title = "Add Section"
    }else{
    title = "Edit \(section.name)"
    textField.text = section.name
    }

    textField.becomeFirstResponder()
    }
    ```

    This will set the title in the navigation controller based on the presence of a value inside the section instance variable, also the text inside the text field will be set.

    > title is an instance variable of UIViewController

    ## Handling the editing action
    Previously we created the AddEditSectionViewControllerDelegate protocol with two methods for the cancellation and addition actions, let's add a new method for the editing action:

    ```swift
    func addEditSectionDidCompleteEditing(controller: AddEditSectionViewController, section: Section)
    ```

    Great! inside the done() method of AddEditSectionViewController modify the code to this:

    ```swift
    @IBAction func done(){
    if self.section == nil{
    let section = Section(name: textField.text)

    delegate?.addEditSectionDidCompleteAdding(self, section: section)
    }else{
    let section = self.section
    section.name = textField.text

    delegate?.addEditSectionDidCompleteEditing(self, section: section)
    }
    }
    ```

    > `self.section` refers to the section instance variable, however section refers to the local constant

    Now inside SectionsViewController create the following method.

    ```swift
    func addEditSectionDidCompleteEditing(controller: AddEditSectionViewController, section: Section) {
    tableView.reloadData()

    dismissViewControllerAnimated(true, completion: nil)
    }
    ```

    This will reload the table view's data to show the updated Section object.

    ### A different approach
    Now when we click on the section name, the edit section screen appears, however shouldn't clicking on a section name show the items inside this sections? I guess that's what every user would expect.

    Let's take a different approach for the edit and delete actions.

    First of all, delete the segue you created before (EditSection) from the story board.

    Next implement these methods in SectionsViewController:

    ```swift
    override func tableView(tableView: UITableView, commitEditingStyle editingStyle: UITableViewCellEditingStyle, forRowAtIndexPath indexPath: NSIndexPath) {

    }

    override func tableView(tableView: UITableView, editActionsForRowAtIndexPath indexPath: NSIndexPath) -> [AnyObject]? {
    let edit = UITableViewRowAction(style: UITableViewRowActionStyle.Normal, title: "Edit", handler: { (action, indexPath) -> Void in
    println("Editing")
    })

    let delete = UITableViewRowAction(style: UITableViewRowActionStyle.Default, title: "Delete", handler: { (action, indexPath) -> Void in
    // Remove the Section that's at the current cell's index
    self.sections.removeAtIndex(indexPath.row)

    // Now delete the row with animation
    tableView.deleteRowsAtIndexPaths([indexPath], withRowAnimation: .Automatic)
    })

    edit.backgroundColor = UIColor.blueColor()
    delete.backgroundColor = UIColor.redColor()

    return [edit, delete]
    }
    ```

    Run the app and swipe (right > left) any of the cells in the view and two action buttons should appear, one for editing the section and the other for deleting it. Clicking on the edit button will remove the row.

    But what about editing? Now if we click the edit button "Editing" will be printed in our debug console, but we need to be able to perform a segue when the edit button is clicked, a segue thats shows the edit screen.

    In the story board create a segue from the "Clozet" view (The top most item in the tree inside the outline panel for the Clozet Scene) to the Navigation controller of the Add/Edit screen, choose "Present modally" from the menu.

    Now select the new segue and give it the identifier "EditSection".

    Back to SectionsViewController, modify the edit constant inside the tableView(editActionsForRowAtIndexPath) method with the following:

    ```swift
    let edit = UITableViewRowAction(style: UITableViewRowActionStyle.Normal, title: "Edit", handler: { (action, indexPath) -> Void in
    self.performSegueWithIdentifier("EditSection", sender: tableView.cellForRowAtIndexPath(indexPath))
    })
    ```

    This will trigger the EditSection that we implemented before.

    Run the app, swipe on a cell, click on edit, and the edit screen will appear.


    ---

    That's it for now, next we'll learn how to save the application data so that it's available even if the app is terminated.

    @endmarkdown

@stop




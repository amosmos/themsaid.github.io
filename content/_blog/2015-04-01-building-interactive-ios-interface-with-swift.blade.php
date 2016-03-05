@extends('_includes.blog_post_base')

@section('post::title', 'Building an interactive iOS App interface using Swift')
@section('post::brief', 'This is my third trial to build an interactive interface for an iOS app using swift, I was searching online on bechance for a design that involves some ideas I can try to implement and I found this lovely design of an app called yohago.')
@section('pageTitle')- @yield('post::title')@stop

@section('post_body')
    @markdown
    This is my third trial to build an interactive interface for an iOS app using swift, I was searching online on bechance for a design that involves some ideas I can try to implement and I found this lovely design of an app called yohago designed by [Patricia Campuzano](http://www.behance.net/designroot):

    ![yohago screen 1](https://m1.behance.net/rendition/modules/78870661/disp/ec00514c98e767cfb3c744bd7cc47bfc.jpg)

    ![yohago screen 2](https://m1.behance.net/rendition/modules/78870655/disp/b7aebe2f24d50d97ffe674d040f5efa3.jpg)

    ![yohago screen 1](https://m1.behance.net/rendition/modules/78870657/disp/63550eaa1a4165eef605b70a766dd1f1.jpg)

    ## The final product
    After hours of searching, try & error, and consulting friends I could implement parts of the interface and came up with the following output.

    ![The final product](https://github.com/themsaid/Clozet/raw/master/Project.gif)

    # The interesting parts
    I'm not going to discuss the whole code of the project, I'll just highlight some of the parts I found interesting and noteworthy, however you can review the whole source code [on github](https://github.com/themsaid/Clozet).

    ### Setting an image as the background of a view
    In the log in screen I wanted a background screen to fill the view bounds, I was able to accomplish that using the following code:

    ```swift
    UIGraphicsBeginImageContext(self.view.frame.size);
    UIImage(named: "splashBG")!.drawInRect(self.view.bounds)
    var backgroundImage = UIGraphicsGetImageFromCurrentImageContext();
    UIGraphicsEndImageContext();
    view.backgroundColor = UIColor(patternImage: backgroundImage!)
    ```
    The image is resized to the view bounds and then added as background to the view.

    ### Resizing a UITextField with rounded corners
    When I wanted to set the height and inset/padding values of the text fields after setting its border style to a rounded rectangle `field.borderStyle = UITextBorderStyle.RoundedRect` I was not able to change its height, I had to use a sub-class of UITextField to accomplish this.

    ```swift
    class loginScreenTextFields: UITextField {

    let padding = UIEdgeInsets(top: 0, left: 10, bottom: 0, right: 10);

    required init(coder aDecoder: NSCoder) {
    super.init(coder: aDecoder)

    self.layer.cornerRadius = 5.0
    }

    override func textRectForBounds(bounds: CGRect) -> CGRect {
    return self.newBounds(bounds)
    }

    override func placeholderRectForBounds(bounds: CGRect) -> CGRect {
    return self.newBounds(bounds)
    }

    override func editingRectForBounds(bounds: CGRect) -> CGRect {
    return self.newBounds(bounds)
    }

    private func newBounds(bounds: CGRect) -> CGRect {

    var newBounds = bounds
    newBounds.origin.x += padding.left
    newBounds.origin.y += padding.top
    newBounds.size.height -= (padding.top * 2) - padding.bottom
    newBounds.size.width -= (padding.left * 2) - padding.right
    return newBounds
    }
    }
    ```

    ## Applying a custom segue in swift
    As a challenge to myself I want to apply a custom transition when moving from the log in screen to the other screen, I set the segue type to `custom` and assigned a custom class to the segue.

    ```swift
    class CustomSegue: UIStoryboardSegue {

    override func perform() {
    var fromView = self.sourceViewController.view as UIView!
    var toView = self.destinationViewController.view as UIView!

    let offScreenHorizontalStart = CGAffineTransformMakeRotation(CGFloat(M_PI / 2))
    let offScreenHorizontalEnd = CGAffineTransformMakeRotation(CGFloat(-M_PI / 2))

    fromView.layer.anchorPoint = CGPoint(x:0, y:0)
    fromView.layer.position = CGPoint(x:0, y:0)

    UIApplication.sharedApplication().keyWindow?.insertSubview(toView, belowSubview: fromView)


    UIView.animateWithDuration(0.7, delay: 0.0, usingSpringWithDamping: 1.6, initialSpringVelocity: 0.0, options: nil, animations: {

    fromView.transform = offScreenHorizontalEnd

    }, completion: { finished in
    self.sourceViewController.presentViewController(self.destinationViewController as UIViewController, animated: false, completion: nil)

    })
    }

    }
    ```

    ## Customising the look of a UITabBarController
    I wanted to customise the default look of the UITabBarController by adding a background color, change icon and font sizes, add separators between the different tabs, etc...

    I had to iterate over all the items of the tab bar to apply the styles I need:

    ```swift
    for item in tabBar.items as [UITabBarItem] {
    item.setTitleTextAttributes(
    [
    NSFontAttributeName:UIFont.boldSystemFontOfSize(12),
    NSForegroundColorAttributeName:UIColor(red:0.56, green:0.6, blue:0.71, alpha:1)
    ],
    forState: UIControlState.Normal
    )

    item.setTitleTextAttributes(
    [
    NSForegroundColorAttributeName:UIColor.whiteColor()
    ],
    forState: UIControlState.Selected
    )

    item.setTitlePositionAdjustment(UIOffsetMake(0, -10))
    item.imageInsets = UIEdgeInsetsMake(-6, 0, 6, 0)

    if item.tag < 1004{
    let separatorXPosition = (itemWidth * CGFloat(item.tag - 1000)) - CGFloat(0.75)
    let separatorView = UIView(frame: CGRectMake(separatorXPosition, 0, 1.5, 80))
    separatorView.backgroundColor = UIColor(red:0.56, green:0.6, blue:0.71, alpha:1)

    tabBar.insertSubview(separatorView, atIndex: 1)
    }

    }
    ```

    ### Animating a side menu using UISwipeGestureRecognizer
    The menu to the right is a basic view with a table view as a child view, the interesting part about this menu is that part of it is hidden from the window, and once the user swipes left the menu appears in a nice animation.

    I built a separate .xib file for the menu, it included the UITableView with also custom cells. I added a UISwipeGestureRecognizer to the view as follow:

    ```swift
    var swipeLeft = UISwipeGestureRecognizer(target: menu, action: "animateMenu:")
    swipeLeft.direction = UISwipeGestureRecognizerDirection.Left
    menu.addGestureRecognizer(swipeLeft)
    ```

    Another gesture was added to handle closing the menu when the user swipes to the right of the screen. The animateMenu method looked like:

    ```swift
    if reconginzer.direction == UISwipeGestureRecognizerDirection.Left{
    delegate?.sideBarWillOpen?()
    self.openBar()
    }else{
    delegate?.sideBarWillClose?()
    self.closeBar()
    }
    ```

    ### Animating the side menu
    The animation is as simple as the following:

    ```swift
    UIView.animateWithDuration(
    0.6,
    delay: 0.0,
    usingSpringWithDamping: 0.6,
    initialSpringVelocity: 0.0,
    options: nil,
    animations: {
    self.frame.origin.x = UIScreen.mainScreen().bounds.width - self.frame.width + 20
    }, completion: {
    finished in
    self.tableView.userInteractionEnabled = true
    }
    )
    ```

    The table view interaction is forbidden as long as the menu is closed, so when the menu opens I enable it to allow the user to interact with the table.

    ### Using delegates
    In order to be able to interact with the main view controller I built a delegate for the Side bar that includes two optional methods:

    ```swift
    @objc protocol SideBarDelegate{
    optional func sideBarWillClose()
    optional func sideBarWillOpen()
    }
    ```

    I'd register the SideBar in the view controller using the following code.

    ```swift
    let rightSideMenuNib = NSBundle.mainBundle().loadNibNamed("RightSideMenu", owner: self, options: nil)[0] as RightSideMenu
    rightSideMenuNib.frame = CGRectMake(view.frame.width-40, 42, view.frame.width - 90, view.frame.height - 42)
    rightSideMenuNib.delegate = self
    view.addSubview(rightSideMenuNib)

    ```

    Now I was able to do actions on the view controller when the menu opens or closes, for example when the menu opens I hide the Plus icons that appears beside each image in screen 2, here's the code for that:

    ```swift
    func sideBarWillOpen() {
    tableView.userInteractionEnabled = false

    var cells = tableView.visibleCells() as [ListViewCell]
    for cell:ListViewCell in cells{
    UIView.animateWithDuration(0.2, animations: {
    cell.itemPlusButton.alpha = 0
    })
    }
    }
    ```

    ### Drawing a circular UIButton with a border in swift
    The Plus icon button the appears beside each image is drawn using the drawRect: method in a subclass of UIButton, the method looks like the following:

    ```swift
    override func drawRect(rect: CGRect) {
    // Drawing the border as a large circle
    var border = UIBezierPath(ovalInRect: rect)
    UIColor.whiteColor().setFill()
    border.fill()

    // Draw the main color for the button on top of the white border circle
    var path = UIBezierPath(ovalInRect: CGRectMake(rect.origin.x+2, rect.origin.x+2, rect.width-4, rect.height-4))
    UIColor(red:0.11, green:0.73, blue:0.65, alpha:1).setFill()
    path.fill()
    }
    ```

    ### Adding annotations to MKMapView
    Using MapKit I was able to place a map in screen 3, and to add annotations to the map I used this code:

    ```swift
    annotation.title = "TARDIS"
    annotation.subtitle = "Location where the TARDIS was seen"
    annotation.coordinate = CLLocationCoordinate2DMake(30.064071, 31.215664)
    mapView.addAnnotation(annotation)
    ```

    ### Setting the initial region of the map
    In the MapViewController I added the following code to set up the map initial region:

    ```swift
    currentLocation = CLLocationCoordinate2DMake(30.064071, 31.215664)

    let region = MKCoordinateRegionMakeWithDistance(currentLocation, distanceFromGround, distanceFromGround)
    mapView.setRegion(region, animated: true)
    ```

    ### Zooming in and out programatically
    In the view there are two buttons to allow the user to zoom in and out, here's how I implemented the code for this:

    ```swift
    @IBAction func zoomOutButtonClicked(sender: UIButton) {
    let span = MKCoordinateSpan(latitudeDelta: mapView.region.span.latitudeDelta*2, longitudeDelta: mapView.region.span.longitudeDelta*2)
    let region = MKCoordinateRegion(center: mapView.region.center, span: span)

    mapView.setRegion(region, animated: true)
    }

    @IBAction func zoomInButtonClicked(sender: UIButton) {
    let span = MKCoordinateSpan(latitudeDelta: mapView.region.span.latitudeDelta/2, longitudeDelta: mapView.region.span.longitudeDelta/2)
    let region = MKCoordinateRegion(center: mapView.region.center, span: span)

    mapView.setRegion(region, animated: true)
    }
    ```
    @endmarkdown

@stop
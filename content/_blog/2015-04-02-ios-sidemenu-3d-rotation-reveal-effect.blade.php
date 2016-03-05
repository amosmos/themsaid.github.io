@extends('_includes.blog_post_base')

@section('post::title', '[iOS] Implementing a 3D rotation reveal effect for a side menu')
@section('post::brief', '')
@section('pageTitle')- @yield('post::title')@stop

@section('post_body')
    @markdown
    ![The final product](https://github.com/themsaid/swift-sidemenu-3d/raw/master/Project2.gif)

    ## The Source code
    You can find the [complete source code on github](https://github.com/themsaid/swift-sidemenu-3d)

    > Design work used in this demo is captured from [a project on behance](https://www.behance.net/gallery/24185205/Dream-Yoga-App)

    ### Animating the whole view
    The main screen of the app may contain many subviews, in order for us to animate the whole view we will use a very interesting trick, we will capture the view, hide it, and use the captured image instead.

    So the first step in our effect would be capturing the View Controller's main view:

    ```swift
    func captureScreen() -> UIImage {
       UIGraphicsBeginImageContextWithOptions(view.frame.size, view.opaque, 0.0)
       view.layer.renderInContext(UIGraphicsGetCurrentContext())
       let image = UIGraphicsGetImageFromCurrentImageContext()
       UIGraphicsEndImageContext()

       return image;
    }
    ```

    Now we may add the new image view as a sub view `view.addSubview(screenImageView)` and then hide all other sub-views of the main view.

    ```swift
    for subView in view.subviews as [UIView]{
      if subView.tag < 2000{
          subView.hidden = true
      }
    }
    ```

    *Notice* We hide all the views except those with tag less than 2000, I made the tags for the screenImageView and the sideMenu view with tags greater than 2000 so that they keep appearing.

    ### Building the Side Menu
    I used a nib to create the menu and added it as a sub view:

    ```swift
    sideMenu = NSBundle.mainBundle().loadNibNamed("SideMenu", owner: self, options: nil)[0] as UIView
    sideMenu.tag = 2002
    sideMenu.alpha = 0 // because we don't want to show it yet, also to make a nice reveal animation
    sideMenu.frame.origin.y = -10 // to add a nice animation
    view.addSubview(sideMenu)
    ```

    ### Adding a background for the whole view
    Using the following piece of code I set the background of the main view:

    ```swift
    UIGraphicsBeginImageContext(self.view.frame.size);
    UIImage(named: "splashBG")!.drawInRect(self.view.bounds)
    var backgroundImage = UIGraphicsGetImageFromCurrentImageContext();
    UIGraphicsEndImageContext();
    view.backgroundColor = UIColor(patternImage: backgroundImage!)
    ```

    ### 3D Rotation
    The effect of revealing the menu is described in this code:

    ```swift
    var id = CATransform3DIdentity
    id.m34 =  -1.0 / 1000

    // Rotate the view on the Y axis in a anti-clockwise direction
    let rotationTransform = CATransform3DRotate(id, 0.5 * CGFloat(-M_PI_2), 0, 1.0, 0)

    // Transform the view to the right of the screen
    let translationTransform = CATransform3DMakeTranslation(screenImageView.frame.width * 0.2, 0, 0)

    // Combine these two transformations
    let transform = CATransform3DConcat(rotationTransform, translationTransform)

    UIView.animateKeyframesWithDuration(1, delay: 0, options: nil, animations: {

      UIView.addKeyframeWithRelativeStartTime(0, relativeDuration: 1/3, animations: {
          self.screenImageView.layer.transform = transform
          self.screenImageView.frame.size.height -= 200
          self.screenImageView.center.y += 100
      })

      UIView.addKeyframeWithRelativeStartTime(1/3, relativeDuration: 2/3, animations: {
          self.sideMenu.alpha = 1
          self.sideMenu.frame.origin.y = 0
      })

      },
      completion: {_ in}
    )
    ```
    @endmarkdown
@stop
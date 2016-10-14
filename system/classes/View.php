<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 视图类
 * 
 * @author Andy Cai (huayicai@gmail.com)
 * 
 */
class View
{
	const VIEW_EXT = '.html';
	
	protected static $_globalData = array();

	public static function factory($file = NULL, array $data = NULL)
	{
		return new View($file, $data);
	}

	protected static function capture($viewFilename, array $viewData)
	{
// 		print_r($viewData);die();
		// Import the view variables to local namespace
		extract($viewData, EXTR_SKIP);

		if (self::$_globalData)
		{
			// Import the global view variables to local namespace
			extract(self::$_globalData, EXTR_SKIP);
		}

		// Capture the view output
		ob_start();

		try
		{
			// Load the view within the current scope
			include($viewFilename);
		}
		catch (Exception $e)
		{
			// Delete the output buffer
			ob_end_clean();

			// Re-throw the exception
			throw $e;
		}

		// Get the captured output and close the buffer
		return ob_get_clean();
	}

	public static function set_global($key, $value = NULL)
	{
		if (is_array($key))
		{
			foreach ($key as $key2 => $value)
			{
				self::$_globalData[$key2] = $value;
			}
		}
		else
		{
			self::$_globalData[$key] = $value;
		}
	}
	public static function bind_global($key, & $value)
	{
		self::$_globalData[$key] =& $value;
	}
	
	// End 静态方法

	protected $_file;
	protected $_data = array();

	public function __construct($file = NULL, array $data = NULL)
	{
		if ($file !== NULL)
		{
			$this->setFilename($file);
		}

		if ($data !== NULL)
		{
			// Add the values to the current data
			$this->_data = $data + $this->_data;
		}
	}

	public function & __get($key)
	{
		if (array_key_exists($key, $this->_data))
		{
			return $this->_data[$key];
		} elseif (array_key_exists($key, self::$_globalData))
		{
			return self::$_globalData[$key];
		}
		else
		{
			throw new Yi_Exception('View variable is not set: :var',
				array(':var' => $key));
		}
	}

	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	public function __isset($key)
	{
		return (isset($this->_data[$key]) OR isset(self::$_globalData[$key]));
	}

	public function __unset($key)
	{
		unset($this->_data[$key], self::$_globalData[$key]);
	}

	public function __toString()
	{
		try
		{
			return $this->render();
		}
		catch (Exception $e)
		{
			// Display the exception message
			Yi_Exception::handler($e);

			return '';
		}
	}

	public function setFilename($file)
	{
		$path = Yi::themePath() . $file . self::VIEW_EXT;
		
		if ( ! is_file($path) )
		{
			throw new Yi_Exception("The requested view $path could not be found");
		}

		// Store the file path locally
		$this->_file = $path;

		return $this;
	}

	public function set($key, $value = NULL)
	{
		if (is_array($key))
		{
			foreach ($key as $name => $value)
			{
				$this->_data[$name] = $value;
			}
		}
		else
		{
			$this->_data[$key] = $value;
		}

		return $this;
	}

	public function bind($key, & $value)
	{
		$this->_data[$key] =& $value;

		return $this;
	}

	public function render($file = NULL)
	{
		if ($file !== NULL)
		{
			$this->setFilename($file);
		}

		if (empty($this->_file))
		{
			throw new Yi_Exception('You must set the file to use within your view before rendering');
		}

		// Combine local and global data and capture the output
		return self::capture($this->_file, $this->_data);
	}

} // End View


/*

Loading Views
View objects will typically be created inside a Controller using the View::factory method. Typically the view is then assigned as the Request::$response property or to another view.

?
public function action_about()
{
    $this->request->response = View::factory('pages/about');
}
When a view is assigned as the Request::$response, as in the example above, it will automatically be rendered when necessary. To get the rendered result of a view you can call the View::render method or just type cast it to a string. When a view is rendered, the view file is loaded and HTML is generated.

?
public function action_index()
{
    $view = View::factory('pages/about');
 
    // Render the view
    $about_page = $view->render();
 
    // Or just type cast it to a string
    $about_page = (string) $view;
 
    $this->request->response = $about_page;
}
Variables in Views
Once view has been loaded, variables can be assigned to it using the View::set and View::bind methods.

?
public function action_roadtrip()
{
    $view = View::factory('user/roadtrip')
        ->set('places', array('Rome', 'Paris', 'London', 'New York', 'Tokyo'));
        ->bind('user', $this->user);
 
    // The view will have $places and $user variables
    $this->request->response = $view;
}
The only difference between set() and bind() is that bind() assigns the variable by reference. If you bind() a variable before it has been defined, the variable will be created with a value of NULL.

You can also assign variables directly to the View object. This is identical to calling set();

?
public function action_roadtrip()
{
    $view = View::factory('user/roadtrip');
 
    $view->places = array('Rome', 'Paris', 'London', 'New York', 'Tokyo');
    $view->user = $this->user;
 
    // The view will have $places and $user variables
    $this->request->response = $view;
}
Global Variables
An application may have several view files that need access to the same variables. For example, to display a page title in both the header of your template and in the body of the page content. You can create variables that are accessible in any view using the View::set_global and View::bind_global methods.

?
// Assign $page_title to all views
View::bind_global('page_title', $page_title);
If the application has three views that are rendered for the home page: template, template/sidebar, and pages/home. First, an abstract controller to create the template will be created:

?
abstract class Controller_Website extends Controller_Template {
 
    public $page_title;
 
    public function before()
    {
        parent::before();
 
        // Make $page_title available to all views
        View::bind_global('page_title', $this->page_title);
 
        // Load $sidebar into the template as a view
        $this->template->sidebar = View::factory('template/sidebar');
    }
 
}
Next, the home controller will extend Controller_Website:

?
class Controller_Home extends Controller_Website {
 
    public function action_index()
    {
        $this->page_title = 'Home';
 
        $this->template->content = View::factory('pages/home');
    }
 
}
Views Within Views
If you want to include another view within a view, there are two choices. By calling View::factory you can sandbox the included view. This means that you will have to provide all of the variables to the view using View::set or View::bind:

?
// In your view file:
 
// Only the $user variable will be available in "views/user/login.php"
<?php echo View::factory('user/login')->bind('user', $user) ?>
The other option is to include the view directly, which makes all of the current variables available to the included view:

?
// In your view file:
 
// Any variable defined in this view will be included in "views/message.php"
<?php include Kohana::find_file('views', 'user/login') ?>
You can also assign a variable of your parent view to be the child view from within your controller. For example:

?
// In your controller:
 
public functin action_index()
{
    $view = View::factory('common/template);
 
    $view->title = "Some title";
    $view->body = View::factory('pages/foobar');
}
 
// In views/common/template.php:
 
<html>
<head>
    <title><?php echo $title></title>
</head>
 
<body>
    <?php echo $body ?>
</body>
</html>
Of course, you can also load an entire Request within a view:

?
<?php echo Request::factory('user/login')->execute() ?>

*/
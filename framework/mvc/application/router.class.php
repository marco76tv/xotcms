<?php

class router {
 /*
 * @the registry
 */
 private $registry;

 /*
 * @the controller path
 */
 private $path;
 private $pathsite;

 private $args = array();

 public $file;

 public $controller;

 public $action; 

 function __construct($registry) {
        $this->registry = $registry;
 }

 /**
 * @set controller directory path
 * @param string $path
 * @return void
 */
 function setPath($path) {
	/*** check if path i sa directory ***/
	if (is_dir($path) == false)	{
		throw new Exception ('<br>Invalid controller path: `' . $path . '`');
	}
	/*** set the path ***/
 	$this->path = $path;
}

function setPathSite($path) {
	/*** check if path i sa directory ***/
	if (is_dir($path) == false)	{
		throw new Exception ('<br>Invalid controller path: `' . $path . '`');
	}
	/*** set the path ***/
 	$this->pathsite = $path;
}


 /**
 * @load the controller
 * @access public
 * @return void
 */
 public function loader($controller=null, $action=null, $action_params = array()) {
	/*** check the route ***/
	if($controller==null){
		$this->getController();
	}else {
		$this->controller = $controller;
		$this->action = $action;
		$this->action_params = $action_params;
		$path0 = sitepath.'/admin/controller/'. $this->controller.'/';
		if(is_dir($path0)){
			$this->file = $path0.'/'.$this->controller.'.php';
		}else {
			$this->file = $this->path .'/'. $this->controller . '/'.$this->controller.'.php';
		}
		
	}
	
	/*** if the file is not there diaf ***/
	if (is_readable($this->file) == false) {
        die($this->file.' ---- '.__LINE__.'  '.__FILE__);
		$this->file = $this->path.'/error404.php';
        $this->controller = 'error404';
	}

	/*** include the controller ***/
	require_once($this->file);
    //echo '<br/>['.$this->file.']';
	/*** a new controller class instance ***/
	$class = $this->controller . 'Controller';
	$controller = new $class($this->registry);

	/*** check if the action is callable ***/
	if (is_callable(array($controller, $this->action)) == false){
		$action = 'index';
	}else{
		$action = $this->action;
	}
	//echo '<br/>'.$class.'-'.$this->action; die('<br>LINE:'.__LINE__.'<br>FILE:'.__FILE__);
	/*** run the action ***/
	//$this->controller = $controller;
	//$this->action = $action;
	//echo '<br/>'.__LINE__.__FILE__.' - '.$this->controller.' - '.$_GET['rt'];
	ob_start();
	$controller->$action($this->action_params);
	$out1 = ob_get_contents();
	ob_end_clean();
	//if (isset($this->registry->template-> extraecho)) {
	$this->registry->template-> extraecho = $out1;
	//}else {
	//	$this->registry->template-> extraecho = $out1;
	//}
	return $out1;
	
 }
//-------------------------------------------------------------------
public function esegui($controller, $action, $action_params = array()) {
		$path0 = sitepath.'/admin/controller/'. $controller.'/';
		if(is_dir($path0)){
			$this->file = $path0.'/'.$controller.'.php';
		}else {
			$this->file = $this->path .'/'. $controller . '/'.$controller.'.php';
		}
		require_once($this->file);
		$this->controller = $controller;
	$class = $controller . 'Controller';
	$controller_obj = new $class($this->registry);
	return $controller_obj->$action($action_params);
}
//-------------------------------------------------------------------
 
public function subloader($controller, $action, $action_params = array()) {
	//echo '<pre>';
	//echo '<hr/>Controller'; print_r($controller);
	//echo '<hr/>action'; print_r($action);
	//echo '<hr/>action_params'; print_r($action_params);

	//$_GET['rt'] = $controller.'/'.$action.'/'.implode('/', $action_params);
	//$old = $this->registry->template->extraecho;
	$this->registry->template->extraecho1=$this->loader($controller, $action, $action_params);
    //$this->registry->template-> extraecho = $old;
}


//--------------------------------------------------------------
public function subloader_old($controller, $action, $action_params = array()) {

	
	$path0 = sitepath.'/admin/controller/'. $controller.'/';
	//echo $path0;
	
	if(is_dir($path0)){
		$file = $path0.'/'.$controller.'.php';
	}else {
		$file = $this->path .'/'. $controller . '/'.$controller.'.php';
	}
	require_once($file);
    //echo '<br/>['.$this->file.']';
	/*** a new controller class instance ***/
	$class = $controller . 'Controller';
	$controller_obj = new $class($this->registry);
	/*** run the action ***/
	$this->controller = $controller;
	$this->action = $action;
	
	ob_start();
	$controller_obj->$action($action_params);
	$out1 = ob_get_contents();
	ob_end_clean();
	
	$this->registry->template-> extraecho1 = $out1;
	

	$this->registry->template-> help = $this->registry->template-> show('help/'.$action.'_tpl.php');
	//echo $action;
    //muori();
	//$this->registry->template->help = '['.__LINE__.']['.__FILE__.']';
	//$this->registry->template->smarty->assign('help',$controller.'-'.$action);
	
}  
 
//------------------------------------------------------------
 /**
 *
 * @get the controller
 *
 * @access private
 *
 * @return void
 *
 */
private function getController() {
	/*** get the route from the url ***/
	$route = (empty($_GET['rt'])) ? '' : $_GET['rt'];
	$this->action_params = array();
	if (empty($route)){
		$route = 'index';
	}else{
		/*** get the parts of the route ***/
		$parts = explode('/', $route);
		$this->controller = $parts[0];
		if(isset( $parts[1])){
			$this->action = $parts[1];
		}
		$this->action_params = array_slice($parts, 2);
		//echo '<pre>'; print_r(); echo '</pre>';
	}

	if (empty($this->controller)){
		$this->controller = 'index';
	}

	/*** Get action ***/
	if (empty($this->action)){
		$this->action = 'index';
	}
	/*** set the file path ***/
	//$this->file = $this->path .'/'. $this->controller . 'Controller.php';
	$path0 = sitepath.'/admin/controller/'. $this->controller.'/';
	if(is_dir($path0)){
		$this->file = $path0.'/'.$this->controller.'.php';
	}else {
		$this->file = $this->path .'/'. $this->controller . '/'.$this->controller.'.php';
	}
}


}


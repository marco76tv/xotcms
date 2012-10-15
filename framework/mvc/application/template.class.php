<?php

//http://pear.reversefold.com/dokuwiki/pear:db_dataobject_formbuilder:configuring_formbuilder
Class Template {

/*
 * @the registry
 * @access private
 */
private $registry, $dg;

public $primary_key;
public $hideControlPanel = false;
public $fieldstorender = array();
public $hiddenfields = array();
public $azioni = array();
public $azioniFromMenu = array();
public $azioniFromMenuFratelli= array();
public $defaults = array();
public $defaults_from_funtion = array();
public $query = array();
public $order_by = array();
public $id = '';
public $filter_html = '';
public $filter_sql = '';
public $load_tables = '';
public $params = '';
public $nome = '';
//public $extraecho = '';
//public $txt = '';
public $tablename = '';
public $id_new = '';
//public $id_padre = '';
public $perPage = 10;
public $smarty;
public $template_path = '';
public $template_url = '';
public $do = null;
public $adm_theme = 'Bootstrap'; //SimplaAdmin

public $msg_html = '';
/*
 * @Variables array
 * @access private
 */
private $vars = array();

/**
 * @constructor
 * @access public
 * @return void
 */
function __construct($registry) {
	$this->registry = $registry;
	require_once $this->registry->conf->settings['root']['conf']['SmartyPath'].'libs/Smarty.class.php';
	$pearpath=$this->registry->conf->settings['root']['conf']['PearPath'];
	$pathz = array($pearpath, get_include_path());
	
	set_include_path(implode(PATH_SEPARATOR, $pathz));
	
	$this->pearIncludes();
	
	
	
	$smarty = new Smarty;
    $smarty->compile_check = true;
	if (isset($_GET['debug'])) $smarty->debugging = true;
    else $smarty->debugging = false;
	$smarty->caching = false;
	$smarty->compile_dir = str_replace('//','/',$_SERVER['DOCUMENT_ROOT'] . '/templates_c');
	/*$smarty->registerResource("db", array(
										"db_get_template",
                                       'db_get_timestamp',
                                       "db_get_secure",
                                       "db_get_trusted"));
									   */
//	$smarty->registerResource("odt",  new Smarty_Resource_ODT()); 
									   
	$smarty->registerPlugin("function", 'form', array($this,'smarty_form'));
	$smarty->registerPlugin("function", 'get_sidebar', array($this, 'smarty_get_sidebar'));
	$smarty->registerPlugin("function", 'get_sidebar_var', array($this, 'smarty_get_sidebar_var'));
	$smarty->registerPlugin("function", 'get_topbar', array($this, 'smarty_get_topbar'));
	$smarty->registerPlugin("function", 'get_topbar_var', array($this,'smarty_get_topbar_var'));
	
	//$template_path = str_replace('\\', '/', __SITE_PATH . '/themes/SimplaAdmin');
	$template_path = str_replace('\\', '/', __SITE_PATH . '/themes/'.$this->adm_theme);
	$this->template_path = $template_path;
	$template_url = str_replace($_SERVER['DOCUMENT_ROOT'], '/', $template_path);
	$template_url = str_replace('//', '/', $template_url);
	$this->template_url = $template_url;
	
	$smarty->assign('template_url', $template_url);
	
	$siteurl=$_SERVER["HTTP_HOST"].'/'.$_SERVER["PHP_SELF"];
	$siteurl=str_replace('//','/',$siteurl);
	$smarty->assign('siteurl', $siteurl);
	
	
	$this->vars['template_path'] = $template_path;
	$this->vars['template_url'] = $template_url;
	//$this->__set('template_path', $template_path) 
	
	//$this->registry->smarty->template_dir = $template_path;
	$smarty->assign('currentUrl','?'.$_SERVER['QUERY_STRING']);
	
	$this->registry->smarty =  $smarty;
	$this->smarty = $smarty;
	//$this->setVars(); 
   
}
//-------------------------------------------------------------------
public function pearIncludes(){
	require_once 'HTML/QuickForm.php';
	require_once 'HTML/QuickForm/Renderer/QuickHtml.php';
	require_once 'Structures/DataGrid.php';
	require_once 'DB/DataObject/FormBuilder.php';
	
	require_once(xotpath."/includes/extPear/arrayxot.php");
	require_once(xotpath."/includes/extPear/jquery_datetime.php");
	require_once(xotpath."/includes/extPear/jquery_date.php");
	require_once(xotpath."/includes/extPear/jquery_autocomplete2fields.php");
	require_once(xotpath."/includes/extPear/jquery_tinymce.php");
	require_once(xotpath."/includes/extPear/listafields.php");
	require_once(xotpath."/includes/extPear/lista_tables.php");
	require_once(xotpath."/includes/extPear/selecttree.php");
    //die(__LINE__.__FILE__);
}


//-------------------------------------------------------------------
public function recorsiveParseMenu($menu,$allMenu) {
	while (list($k, $v) = each($menu)) {
		if(isset($allMenu[$v['id']])){
			//$menu[$k]['sub'] = $allMenu[$v['id']];
			$menu[$k]['sub'] = $this->recorsiveParseMenu($allMenu[$v['id']],$allMenu);
		}
	}
	return $menu;
	
}
//-----------------------------------------------------------------------------------------------------
public function smarty_get_topbar_var($params, & $smarty) {
	$areas = $this->registry->user->myAreas();
	$smarty->assign('_topbar_var', $areas);
	
	
  return ;
}


//-----------------------------------------------------------------------------------------------------
public function smarty_get_topbar($params, & $smarty) {
	$topmenu = ''; $html = '';
	if (isset($_SESSION['topmenu'])) {
		$topmenu = $_SESSION['topmenu'];
		//$this->smarty->assign('help', 'benvenuta nell\'area '.$topmenu);
		$html.= "<script >
		$(document).ready(function() {
			$('#topbar').hide();
		});
		</script>";
			
	}
	
	//$LU = $this->registry->user->LU;
	//$perm_user_id = $LU->getProperty('perm_user_id');
	//$html = '['.$perm_user_id.']';
	//$menu_do = DB_DataObject::factory('liveuser_area_admin_areas');
	$areas=$this->registry->user->myAreas();
	//echo '<pre>'; print_r($areas); echo '</pre>';

	$html.= '<div id="topbar">';
	while (list($k, $v) = each($areas)) {
		if ($topmenu == $v->area_id) {
		$html.='<li><a class="shortcut-button" href="?rt=menu/topmenu/'.$v->area_id.'"><span>
<img src="'.$this->vars['template_url'].'/resources/images/icons/'.$v->img.'" alt="icon" height="48" /><br />
<b>'.$v->area_define_name.'</b>
</span></a > </li> '.chr(13);
		}else{	
		$html.='<li><a class="shortcut-button" href="?rt=menu/topmenu/'.$v->area_id.'"><span>
<img src="'.$this->vars['template_url'].'/resources/images/icons/'.$v->img.'" alt="icon" height="48" /><br />
'.$v->area_define_name.'
</span></a > </li> '.chr(13);
		}
	}
	$html.= '</div>';
	$html.= '<a href="#" id="slick-hide">Nascondi</a> / ';
	$html.= '<a href="#" id="slick-show">Mostra</a>';
	$html.= "<script >
		$(document).ready(function() {
		// toggles the slickbox on clicking the noted link  
		$('#slick-hide').click(function() {
			$('#topbar').hide();
			return false;
		});
		$('#slick-show').click(function() {
			$('#topbar').show();
			return false;
		});
		
		});
	</script>";
	
	
	
	return $html;
	
	
}
//-----------------------------------------------------------------------------------------------------
/*
public function forceDB($tablename) {
	$options = &PEAR::getStaticProperty('DB_DataObject','options');
	$options = array(
		'database'          => 'mysql://marco:marco@localhost/'.$tablename,
		'schema_location'   => 'C:/AppServ/www/consolle/DataObjects/'.$tablename,
		'class_location'    => 'C:/AppServ/www/consolle/DataObjects/'.$tablename,
		'require_prefix'    => 'DataObjects/',
		'class_prefix'      => 'DataObjects_',
		'db_driver'         => 'MDB2', //Use this if you wish to use MDB2 as the driver
		'quote_identifiers' => true
	);
}
*/
//-----------------------------------------------------------------------------------------------------
public function smarty_get_sidebar_var($params, & $smarty) {
	if (!isset($_SESSION['area_id'])) {
		$smarty->assign('_sidebar_var', array());
		return ;
	}
	$menu_do = DB_DataObject::factory('_menu');
	$whereAdd = 'find_in_set("'.$_SESSION['area_id'].'",lista_aree) or (lista_aree is null or trim(lista_aree)="")';
	$whereAdd.= ' and visibility <= '.$this->registry->user->LU->getProperty('perm_type'); 
	$orderBy = 'id_padre,ordine asc';
	$sql = '(select * from '.$_SESSION['dbname'].'._menu where '.$whereAdd.' order by '.$orderBy.')';
	$menu_do->query($sql);
	$item = array();
	while ($menu_do->fetch()) {
		$item[] = clone($menu_do);
	}
	$datamenutmp = array();
	$nodo = array();
	while (list($k, $v) = each($item)) {
		$nodo[$v->id] = array('id'=>$v->id,'id_padre'=>$v->id_padre,'title'=>$v->nome,'url'=>'?rt=menu/gest/'.$v->id,'current'=>0,'sub'=>array());
		$datamenutmp[$v->id_padre][$v->id] = $nodo[$v->id];
	}
	$datamenu = array();
	if(!isset($datamenutmp[0])) return;
	$datamenu1 = $this->recorsiveParseMenu($datamenutmp[0], $datamenutmp);
	reset($datamenu1);
	while (list($k, $v) = each($datamenu1)) {
		while (list($k1, $v1) = each($v['sub'])) {
			if (strpos($_SERVER['REQUEST_URI'].'/', $v1['url'].'/')) {
				$datamenu1[$k]['current'] = 1;
				$datamenu1[$k]['sub'][$k1]['current'] = 1;
			}
		}
	}
	
	
	$smarty->assign('_sidebar_var', $datamenu1);
	return '';
}





//-----------------------------------------------------------------------------------------------------
public function smarty_get_sidebar($params, & $smarty) {
    // ALTER TABLE `menu`  ADD COLUMN `lista_aree` VARCHAR(250) NULL DEFAULT NULL AFTER `lista_figli`;
	if (!isset($_SESSION['area_id'])) return ;
	//if (!isset($_SESSION['dbname'])) return ;
	//$this->forceDB('sindacati');
	$menu_do = DB_DataObject::factory('_menu');
	//$menu_do->orderBy('ordine');
	if (PEAR::isError($menu_do)) {
		echo '<br/><b>LINE</b>'.__LINE__.'
		<br/> <b> FILE </b> '.__FILE__.'';
		die($menu_do->getMessage());
	}
	$whereAdd = 'find_in_set("'.$_SESSION['area_id'].'",lista_aree) or (lista_aree is null or trim(lista_aree)="")';
	$whereAdd.= ' and visibility <= '.$this->registry->user->LU->getProperty('perm_type'); 
	$orderBy = 'id_padre,ordine asc';
	//echo '<pre>'; print_r($_SESSION); echo '</pre>';
	
	
	$sql = '(select * from '.$_SESSION['dbname'].'._menu where '.$whereAdd.' order by '.$orderBy.')';
//	$sql.=' union (select * from liveuser_general.menu  where '.$whereAdd.' order by '.$orderBy.')';
	//print_r($menu_do);
	
	$menu_do->query($sql);
	//die('<br/><b>LINE:</b>'.__LINE__.'<br/><b>FILE:</b>'.__FILE__);
	
	//$menu_do->whereAdd($whereAdd);
	//$menu_do->orderBy($orderBy);
    //$menu_do->database('rischio');
	
	//$menu_do->_database_dsn = 'mysql://marco:marco@localhost/sindacati';
	//$menu_do->_database_dsn_md5 = md5($menu_do->_database_dsn);
	
	//echo '<pre>'; print_r($menu_do->getDatabaseConnection()); echo '</pre>';
	//echo '<pre>'; print_r($menu_do); echo '</pre>';
	//die('<br/><b>LINE:</b>'.__LINE__.'<br/><b>FILE:</b>'.__FILE__);
	//$item = $menu_do->fetchAll();
	//$item = $menu_do->find();
	$item = array();
	while ($menu_do->fetch()) {
		$item[] = clone($menu_do);
	}
	
	//echo '<pre>'; print_r($menu_do); echo '</pre>';
	//echo "SESS[".$_SESSION['tablename'].']';
	//echo '<pre>'; print_r($menu_do); echo '</pre>';
	
	$datamenutmp = array();
	$nodo = array();
	while (list($k, $v) = each($item)) {
		$nodo[$v->id] = array('id'=>$v->id,'id_padre'=>$v->id_padre,'title'=>$v->nome,'url'=>'?rt=menu/gest/'.$v->id,'sub'=>array());
		$datamenutmp[$v->id_padre][$v->id] = $nodo[$v->id];
	}
	$datamenu = array();
	if(!isset($datamenutmp[0])) return;
	$datamenu1 = $this->recorsiveParseMenu($datamenutmp[0], $datamenutmp);
	$html = '';
	reset($datamenu1);
	while (list($k, $el) = each($datamenu1)) {
		$top= '<li><a href="#" class="nav-top-item">'.$el['title'].'</a>'.chr(13);
		if (isset($el['sub'])) {
		    $sub= '<ul>'.chr(13);
			reset($el['sub']);
			while (list($k1, $el1) = each($el['sub'])) {
				if (strpos($_SERVER['REQUEST_URI'].'/', $el1['url'].'/')) {
					$top= '<li><a href="#" class="nav-top-item current">'.$el['title'].'</a>'.chr(13);
					$sub.= '<li><a class="current" href="'.$el1['url'].'" >'.$el1['title'].'</a></li>'.chr(13);
				}else{
					$sub.= '<li><a class="" href="'.$el1['url'].'" >'.$el1['title'].'</a></li>'.chr(13);
				}
			}
		    $sub.='</ul>';
		}
		$html.=$top.$sub.'</li>';
	}	
	return $html;
	//$this->registry->smarty->assign('datamenu', $datamenu1);
	//return $this->registry->smarty->fetch('sidebar_tpl.php');
	
	
}
//-----------------------------------------------------------------------------------------------------
public function smarty_form($params, & $smarty) {
	//global $global_FormX;
	//$formx=$global_FormX;
	$id = $params['id'];
	//return '---['.$id.']---';
	if (isset($params['defaults'])) {
		/*
		$pattern = '/(menu_id=[0-9]*)/';
		preg_match($pattern, $_SERVER["REQUEST_URI"], $matches);
		$pattern = '/(menu_id:[0-9]*)/';
		preg_match($pattern, $params['defaults'], $matches1);
		if (isset($matches[1]) && isset($matches1[1])) {
			$_SERVER["REQUEST_URI"]=str_replace($matches[1],str_replace(':','=',$matches1[1]),$_SERVER["REQUEST_URI"]);
		}
		*/
		$pattern = '/(menu\/gest\/)([0-9]*)/';
		preg_match($pattern, $_SERVER["REQUEST_URI"], $matches);
		//echo '<pre>'; print_r($matches); echo '</pre>';
		$pattern = '/menu_id:([0-9]*)/';
		preg_match($pattern, $params['defaults'], $matches1);
		//echo '<pre>'; print_r($matches1); echo '</pre>';
		if (isset($matches[1]) && isset($matches1[1])) {
			$_SERVER["REQUEST_URI"]=str_replace($matches[0],$matches[1].$matches1[1],$_SERVER["REQUEST_URI"]);
		}
	}
	
	$form = $this->createFormById($id);
	if(isset($params['defaults'])){
		$tmp = explode(';', $params['defaults']);
		$def=array();
		while (list($k, $v) = each($tmp)) {
			//list($k1, $v1) = 
			$tmp2 = explode(':', $v);
			//print_r($tmp2);
			//$def[$k1]=$v1;
			//echo '['.count($tmp2).']';
			if(count($tmp2)>1){
				$def[$tmp2[0]] = $tmp2[1];
			}
		}	
		$form->setDefaults($def);
	}
	
	//$smarty->data->form=$form;
//	$global_FormX->form = $form;
	//echo $form->toHtml();
	return $form->toHtml();
}
//----------------------------------------------------------------------------------------------------
public function createFormById($id_form, $method = 'post') {
	$do = DB_DataObject::factory('formx');
	$do->get($id_form);

	$form_data = $do->fetchAll();
	$form = new HTML_QuickForm($form_data[0]->nome, $method, $_SERVER["REQUEST_URI"]);
	
	$do1=DB_DataObject::factory('formx_element');
	$do1->id_form = $id_form;
	$formElement_data = $do1->fetchAll();
	while(list($k,$v)=each($formElement_data)){
		$el = HTML_QuickForm::createElement($v->tipo, $v->nome, $v->label,unserialize($v->options));
		$form->addElement($el);
	}
	$form->addElement('submit', null, 'invia');
	return $form;
}
//------------------------------------------------------------------------------------------------------
public function newTablename($params=null) { 
	$this->setVars();
	$do = DB_DataObject::factory($this->tablename);
	if(count($this->fieldstorender)>1){
		$do->fb_fieldsToRender = $this->fieldstorender;
	}
	reset($this->defaults);
	while (list($kd, $vd) = each($this->defaults)) {
		$do->$kd=$vd;	
	}
	$fb = & DB_DataObject_FormBuilder::create($do);
	$fb->submitText = 'Salva '.$this->tablename;
	$this->registry->conf->DataObjectSpecialType($do, $fb);
	
	//var_dump($this->hiddenfields); die();
	if (count($this->hiddenfields) > 0) $this->registry->conf->DataObjectCustomFields($do, $fb, $this->hiddenfields, 'hidden');
	//echo '<br/>['.__LINE__.']['.__FILE__.']';
	
	$fb->elementTypeMap['date'] = 'jquery_date';
	$fb->elementTypeMap['datetime'] = 'jquery_datetime';
	//echo '<br/>['.__LINE__.']['.__FILE__.']';
	$form = & $fb->getForm($_SERVER["REQUEST_URI"]);
	
	//echo '<pre>'; print_r($this->defaults); echo '</pre>';
	
	$form->setDefaults($_GET);
	
	$primary_key = $this->primary_key;
	if ($primary_key == "") {
		//echo '<pre>'; print_r($do->keys()); echo '</pre>';
		$keys = $do->keys();
		$primary_key = $keys[0];
	}	
	//echo '<br/>['.__LINE__.']['.__FILE__.']';
	if ($form->validate()) {
		//echo '<pre>'; print_r($this->smarty->tpl_vars['obj']->value); echo '</pre>';
		//echo '<pre>'; print_r($this->smarty->tpl_vars['act']->value); echo '</pre>';
		//muori();
		//muori();
		//$this->registry->conf->mdb2->setDatabase('events_db');
		//echo '<br/>['.__LINE__.']['.__FILE__.']';
		echo $form->process(array( & $fb, 'processForm'), false);
		
			$msg = 'Inserito con Successo '.$primary_key.' <b>'.$do->$primary_key.'</b><br/>Compila sotto per aggiungerne un altro NUOVO';
			$actionMenu = '<ol>';
			//echo '<br/>['.__LINE__.']['.__FILE__.']';
			mylog($do->$primary_key, $this->tablename, 'newTablename');
			//echo '<br/>['.__LINE__.']['.__FILE__.']';
			while (list($k, $v) = each($this->azioniFromMenuFratelli)) {
				$actionMenu .= '<li><a href="?rt=menu/gest/'.$v->id.'/'.$do->$primary_key.'" title="'.$v->nome.'">';     
				$actionMenu.= '<img src="'.$this->vars['template_url'].'/resources/images/icons/'.$v->img.'"/>';
				$actionMenu.= $v->nome.'</a> </li>';
			}
			$actionMenu .= '</ol>';
			
			//$this->registry->smarty->assign('txt', $actionMenu);
			$this->showMsg($msg.$actionMenu, 'success');
			$outHtml=$form->toHtml();
	
		$this->registry->smarty->assign('content', $outHtml);
		
		
		$objname = $this->smarty->tpl_vars['obj']->value;
		$act = "do_".$this->smarty->tpl_vars['act']->value;
		//$this->registry->router->subloader($objname, $act, $form->exportValues());
		if ($objname != "") {
			//$this->registry->router->subloader($objname, $act, $do->toArray());
			//echo '<h3>'.$objname.' - '.$act.'</h3>';
			//echo '<pre>'; print_r($do->toArray()); echo '</pre>';
			//muori();
			$this->registry->router->esegui($objname, $act, $do->toArray());
			
		}
		
		
		//echo '<br/>['.__LINE__.']['.__FILE__.']';
		return $do;
		
	//	$this->refresh();
	//	return $do;
	}else {
		echo '';
	}	
	
	$outHtml = $form->toHtml();
	
	$outHtml = str_replace('type="submit"', 'type="submit" class="btn btn-primary"', $outHtml);
	//die($outHtml);
	//echo '<br/>['.__LINE__.']['.__FILE__.']';
	$this->registry->smarty->assign('content', $outHtml);
	//echo '<br/>['.__LINE__.']['.__FILE__.']';
	return null;

}
//-----------------------------------------------------------------------------------------------------

//-----------------------------------------------------------------------------------------------------
public function editTablename($id=null) { 
	$this->setVars();
	if (is_array($id) && isset($id[0])) {
		$id = $id[0];
	}
	$do = DB_DataObject::factory($this->tablename);
	$this->do = &$do;
	$do->get($id);
	
	//echo '<pre>'; print_r($this->fieldstorender); echo '</pre>';
	if (count($this->fieldstorender) > 0) {
		if (isset($do->fb_crossLinks)) {
			reset($do->fb_crossLinks);
			while (list($k, $v) = each($do->fb_crossLinks)) {
				$this->fieldstorender[3]='__crossLink_'.$v['table'];
			}
		}
		$do->fb_fieldsToRender = $this->fieldstorender;
	}
	
	reset($this->defaults);
	while (list($kd, $vd) = each($this->defaults)) {
		$do->$kd=$vd;	
	}
	
	$fb = & DB_DataObject_FormBuilder::create($do);
	$this->registry->conf->DataObjectSpecialType($do, $fb);
	
	//var_dump($this->hiddenfields); die();
	if (count($this->hiddenfields) > 0) $this->registry->conf->DataObjectCustomFields($do, $fb, $this->hiddenfields, 'hidden');
	
	
	$fb->elementTypeMap['date'] = 'jquery_date';
	$fb->elementTypeMap['datetime'] = 'jquery_datetime';
	$fb->submitText = 'Salva';
	
	$form = & $fb->getForm($_SERVER["REQUEST_URI"]);
	if ($form->validate()) {
		echo $form->process(array( & $fb, 'processForm'), true);
		if ($id != null) {
			
			$primary_key = $this->primary_key;
			if ($primary_key == "") {
				//echo '<pre>'; print_r($do->keys()); echo '</pre>';
				$keys = $do->keys();
				$primary_key = $keys[0];
			}	
			$this->showMsg('Aggiornato con Successo '.$primary_key.' '.$do->$primary_key, 'success');
			mylog($do->$primary_key, $this->tablename, 'editTablename');
		}else {
			$msg = 'Inserito con Successo id <b>'.$do->id.'</b><br/>Compila sotto per aggiungerne un altro NUOVO';
			$actionMenu = '<ol>';
			
			mylog($do->id, $this->tablename, 'editTablename');
			
			while (list($k, $v) = each($this->azioniFromMenuFratelli)) {
				$actionMenu .= '<li><a href="?rt=menu/gest/'.$v->id.'/'.$do->id.'" title="'.$v->nome.'">';     
				$actionMenu.= '<img src="'.$this->vars['template_url'].'/resources/images/icons/'.$v->img.'"/>';
				$actionMenu.= $v->nome.'</a> </li>';
			}
			$actionMenu .= '</ol>';
			
			//$this->registry->smarty->assign('txt', $actionMenu);
			$this->showMsg($msg.$actionMenu, 'success');
			
		}
		$outHtml = $form->toHtml();
		$this->registry->smarty->assign('content', $outHtml);
		
		//$objname = $this->smarty->tpl_vars['obj']->value;
		//$act = "do_".$this->smarty->tpl_vars['act']->value;
		//echo '<pre>'; print_r($this->registry->router); echo '</pre>';
		$objname = $this->registry->router->controller;
		$act = "do_".$this->registry->router->action;
		
		
		//$this->registry->router->subloader($objname, $act, $form->exportValues());
		if ($objname != "") {
			//$this->registry->router->subloader($objname, $act, $do->toArray());
			$this->registry->router->esegui($objname, $act, $do->toArray());
		}
		
		
		
		return $do;
	//	$this->refresh();
	//	return $do;
	}
	$outHtml = $form->toHtml();
//	$outHtml = str_replace('type="submit"', 'type="submit" class="button"', $outHtml);
	//$outHtml=str_replace('type="s")
	$outHtml = str_replace('type="submit"', 'type="submit" class="btn btn-primary"', $outHtml);
	//die($outHtml);
	$this->registry->smarty->assign('content', $outHtml);
	
	return $outHtml ;
}
//-----------------------------------------------------------------------------------------------------
public function showMsg($msg, $type) {
	//attention,information,success,error
	switch($this->adm_theme) {
		case 'SimplaAdmin':
			$html='<div class="notification '.$type.' png_bg">
				<a href="#" class="close"><img src="'.$this->vars['template_url'].'/resources/images/icons/cross_grey_small.png" title="Close this notification" alt="close" /></a>
				<div>
					'.$msg.' 
				</div>
			</div> ';
		break;
		case 'Bootstrap':
			$html = '<div class="alert alert-'.$type.'">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<strong> '.strtoupper($type).' ! </strong> '.$msg.'
					</div>';
		break;
		default:
			$html = 'template admin non riconosciuto';
		break;
	}
	$this->msg_html.= $html;
	$this->registry->smarty->assign('msg', $this->msg_html);
	return $html;

}
//------------------------------------------------------------------------------------------------------



//-----------------------------------------------------------------------------------------------------
public function showArray() {
	if (count($this->vars['myArray']) == 0) {
		$msg = 'No Risultati';
		$this->showMsg($msg, 'error');
		return; 
	}	
	$dg = new Structures_DataGrid($this->perPage);
	$dg->bind($this->vars['myArray']); // the Magic
	if (isset($_GET['xls'])) {
		$data = $this->vars['myArray'];
		exportArray2XLS('export-'.session_id(), $data);
	}	

	
	
	$this->dg = $dg;
	//return "ciao";
	$this->showDG();
}
//-----------------------------------------------------------------------------------------------------
function serialize2ArrayEval($str) {
	if (is_array($str)) {
		return $str; //elemento gia' tradotto
	}
	$defaults = unserialize($str);
	if(is_array($defaults)){
		while (list($ko, $vo) = each($defaults)) {
			$defaults[$ko] = $this->smarty->fetch("eval:".$vo);
		}
		return $defaults;
	}else {
		echo '<h3>Serializzato male '.__LINE__.__FILE__.'</h3>';
	}
}

//-----------------------------------------------------------------------------------------------------
public function setVars() {
	//echo '<pre>'; print_r($this->defaults); echo '</pre>';
	//echo '<pre>'; print_r($_POST); echo '</pre>';
	reset($_POST);
	while(list($k,$v)=each($_POST)){
		$_SESSION[$k]=$v;
	}
	reset($_SESSION);
	while(list($k,$v)=each($_SESSION)){
		$this->registry->smarty->assign($k, $v);
	}
	$uri_arr = explode('rt=menu', $_SERVER['REQUEST_URI']);
	$uri_back = '';
	if(count($uri_arr)>2){
		array_pop($uri_arr);
		$uri_back = substr(implode('rt=menu',$uri_arr),0,-1);
	}
	$this->registry->smarty->assign('uri_back', $uri_back);
	
	$LU = $this->registry->user->LU;
	
	$this->registry->smarty->assign('user_ente', $LU->getProperty('ente'));
	$this->registry->smarty->assign('user_matr', $LU->getProperty('matr'));
	$this->registry->smarty->assign('user_nome', $LU->getProperty('cognome').' '.$LU->getProperty('nome'));
	$this->registry->smarty->assign('user_handle', $LU->getProperty('handle'));
	$this->registry->smarty->assign('user_groups', implode(',',$this->registry->user->myGroups()));
	
	/*
	// Load variables
	foreach ($this->vars as $key => $value){
		//$$key = $value;
		//$this->registry->smarty->assign($key, $this->smarty->fetch("eval:".$value));
		$this->registry->smarty->assign($key, $value);
	}
	*/
	
	//$this->txt=$this->smarty->fetch("eval:".$this->txt);
	
	if (!is_array($this->fieldstorender)) $this->fieldstorender = explode(',', $this->fieldstorender);
	if (!is_array($this->hiddenfields)) $this->hiddenfields = explode(',', $this->hiddenfields);
	
	if (!is_array($this->load_tables)) $this->load_tables = explode(',', $this->load_tables);
	
	if (isset($this->load_tables[0]) && $this->load_tables[0] != "") $this->tablename = $this->load_tables[0];
	//echo '<pre>'; print_r($this->defaults); echo '</pre>'; 
	//die('<br/><b>LINE :</b>'.__LINE__.'<br/><b>FILE :</b>'.__FILE__);
	//echo '<pre>'; print_r($this->registry->template->defaults); echo '</pre>'; 
	/*
	if (!is_array($this->defaults)) {
		//echo $this->defaults;
		$defaults = unserialize($this->defaults);
		if(is_array($defaults))
			while (list($ko, $vo) = each($defaults)) {
				//echo '<br/>'.$ko.'='.$vo;
				//$defaults[$ko] = $this->smarty->fetch("eval:".$vo);
				$defaults[$ko] = $this->smarty->fetch("eval:".$vo);
				//echo $this->registry->smarty->fetch("eval:".$vo);
			}
			
		$this->defaults=$defaults;
	}
	*/
	//echo '<pre>'.__LINE__.__FILE__; print_r($this->defaults); echo '</pre>';
	$this->defaults = $this->serialize2ArrayEval($this->defaults);
	//----------------------------------------------------------------
	
	//----------------------------------------------------------------
	/*
	if (!is_array($this->query)) {
		//echo '<br/>LINE:'.__LINE__.'<br/>FILE:'.__FILE__.'<br/>Value['.$this->query.']'; die();
		$query = unserialize($this->query);
		if (is_array($query))
		while (list($ko, $vo) = each($query)) {
			$query[$ko] = $this->smarty->fetch("eval:".$vo);
		}
			
		$this->query=$query;
	}
	*/
	$this->order_by = $this->serialize2ArrayEval($this->order_by);
	
	$this->query = $this->serialize2ArrayEval($this->query);
	
	
	//-- mette l'ordine che voglio io :)
	if (is_array($this->query)) {
		$this->load_tables = array_unique(array_merge(array_keys($this->query), $this->load_tables));
	}	
	
	// Load variables
	foreach ($this->vars as $key => $value){
		//$$key = $value;
		//$this->registry->smarty->assign($key, $this->smarty->fetch("eval:".$value));
		$this->registry->smarty->assign($key, $value);
	}

	if ($this->tablename == 'menu') $this->tablename = '_menu';
	//if (!isset($_SESSION['defaults'])) $_SESSION['defaults'] = array();
	//$_SESSION['defaults'] = array_merge($_SESSION['defaults'], $this->defaults);
	//echo '<pre>'.__LINE__.__FILE__; print_r($this->defaults); 
	//print_r($_SESSION['defaults']); 
	// debug_print_backtrace();  echo '</pre>';
	//var_dump(debug_backtrace());
	

}
//-----------------------------------------------------------------------------------------------------
public function deleteTablename($id=null) {
	
//*
	$id_list = implode(',', $id);
    if (isset($_GET['delete'])) {
		$this->setVars();
		//if (is_array($id) && isset($id[0])) $id = $id[0];
		$do = DB_DataObject::factory($this->tablename);
		print_r($do->whereAdd('find_in_set(id,"'.$id_list.'")'));
		//$do->find();
		print_r($do->delete(DB_DATAOBJECT_WHEREADD_ONLY));
		$this->showMsg('cancellato '.$this->tablename.' ['.$id_list.']', 'success');
		$outHtml = '<script>
		document.location.href = "'.$_SESSION["HTTP_REFERER"].'";
		</script > Attendere ..';
		
	}else {	
	//echo '<pre>'; print_r($_SERVER); echo '</pre>';
	$_SESSION["HTTP_REFERER"] = $_SERVER["HTTP_REFERER"];
	$outHtml = '<script>
	if (confirm("Sicuro di voler eliminare per sempre ['.$this->tablename.']['.$id_list.']?")) {
		document.location.href="./?rt='.$_GET['rt'].'&delete=1";
	}else {
		document.location.href="'.$_SERVER["HTTP_REFERER"].'";
	};
	</script > Attendere ..';
	}
	//*/
	//phpinfo();
	//echo '<pre>'; print_r($this->registry->template->id_padre); echo '</pre>';
	//$outHtml = implode(',', $id);
	$this->registry->smarty->assign('content', $outHtml);
}
//-------------------------------------------------------------------------------------------------------
public function showQuery($sql) {
	$dg = new Structures_DataGrid($this->perPage);
	$error = $dg->bind($sql, array('dsn' => $this->registry->conf->dsn));
	if (PEAR::isError($error)) {
		die('<br/><b>SQL: </b>'.$sql.'<br/>
		<br/> <b>LINE: </b>'.__LINE__.'<br/>
		<b> FILE: </b>'.__FILE__.'<br/>
		<b> MSG: </b>'.$error->getMessage() . '<br />
		<b> Info :</b>' . $error->getDebugInfo());
	}
	$this->dg = $dg;
	$this->showDG();
	
}
//-----------------------------------------------------------------------------------------------------
public function xlsTablename($params = null) {
	ini_set("memory_limit", "256M");
	ini_set("max_execution_time", "3150");
	
	$this->setVars();
	$do = DB_DataObject::factory($this->tablename);
	if(count($this->fieldstorender)>1){
		$do->fb_fieldsToRender = $this->fieldstorender;
	}
	//echo '<pre>'; print_r($this->query); echo '</pre>';
	
	if (isset($this->query[$this->tablename])) {
		//echo '<h3>whereadd['.$this->query[$this->tablename].']</h3>';
		$do->whereAdd($this->query[$this->tablename]);	
	}
	
	$fields = array_keys($do->table());
	$ris = $do->fetchAll();
	$ris1 = array_render($ris, $fields);
	exportArray2XLS($this->registry->template->nome.'_'.session_id(), $ris1);
	
	
	//echo '<pre>'; print_r($this->registry->template->nome); echo '</pre>';
	/*
	for ($i = 0; $i < count($back); $i++) {
		echo '<br/>'.$i.' ] '.$back[$i]['function'];
	}	
	
	echo 'ciao';
	*/
	
}


//-----------------------------------------------------------------------------------------------------
public function getLinksX($tbl1) {
	$ris = array();
	//$do_options = PEAR::getStaticProperty('DB_DataObject', 'options');
	$do = DB_DataObject::factory($tbl1);
	//$file_links = $do_options['schema_location'].'/'.$do->_database.'.links.ini';
	//$links_array = parse_ini_file($file_links, true);
	
	if(isset($this->order_by[$tbl1]))	$do->orderBy($this->order_by[$tbl1]);
	if (isset($this->query[$tbl1])) {
		//echo '<br/>'.$this->query[$tbl1];
		$do->whereAdd($this->query[$tbl1]);
	}
	$do->find();
	while ($do->fetch()) {
		//$ris[$do->id]=$this->getLinksDoX($do);
		$ris[$do->id]=getLinksDoX($do);
	}
	
	return $ris;

}


//-----------------------------------------------------------------------------------------------------
//-- direttamento collego le varie tabelle e passo i risultati al template
//-----------------------------------------------------------------------------------------------------
public function showTablenames2TPL($params = null) {
	$this->setVars();
	$tbl1=$this->load_tables[0];
   
	
	$ris = $this->getLinksX($tbl1);
	
	
	reset($this->load_tables);
	//echo '<pre>'; print_r($this->load_tables); echo '</pre>';
	//while (list($k, $v) = each($this->load_tables)) {
	$ntables = count($this->load_tables);
	for ($i = 0; $i < $ntables; $i++) {
		$v = $this->load_tables[$i];
		//echo '<h3>'.$tbl1.' - '. $v.'</h3>';
		//echo '<pre>'; print_r($this->load_tables); echo '</pre>';
		if ($v != $tbl1) {
			$ris = $this->collegaTblPadreTblFiglio($tbl1, $v,$ris);
		}
	}
	//echo '<h3>'.count($ris).' Risultati!!!</h3>';
	$this->showMsg(count($ris).' Risultati ', 'info');
	
	$this->registry->smarty->assign('_data', $ris);
	//echo '<pre>'; print_r($ris); echo '</pre>'; muori();
	
}

//-------------------------------------------------------
public function collegaTblPadreTblFiglio($tbl1, $tbl2,$ris) {
	$do0 = DB_DataObject::factory($tbl1);
	//echo '['.$tbl2.']';
	$do0->whereAdd($this->query[$tbl1]);
	$do1 = DB_DataObject::factory($tbl2);
	if(isset($this->order_by[$tbl2]))	$do1->orderBy($this->order_by[$tbl2]);
	//echo '<h3>ordino per '.$this->order_by[$tbl2].'</h3>';
	//$do->fb_fieldsToRender=array('id_approvaz');
	$do1->joinAdd($do0);
	//echo '<pre>';print_r($do1); echo '</pre>';
	//INNER JOIN trasferte  ON (trasferte.id=approvaz_trasferte.id_trasferte)
	$join = $do1->_join;
	//echo '<hr/>'.$tbl1.'   '.$tbl2;
	//echo '<hr/>'.$join;
	//die;
	
	$pos = strrpos($join,$tbl2);
	$extkey = substr($join, $pos + strlen($tbl2) + 1 );
	$pos = strpos($extkey, ')');
	$extkey = substr($extkey,0,$pos);
	//echo '<h3>['.$pos.']'.$extkey.'</h3>';
	$do1->selectAdd();
	$do1->selectAdd($tbl2.'.*');
	
	$do1->find();
	//$ris[$do1->$extkey]['_'.$tbl2] = $this->getLinksDoX($do1);//$do1->toArray();//get_object_vars($do1);
	
	while($do1->fetch()){
		$do1->getLinks();
		$subkey1 = $do1->$extkey;
		//if (isset($ris->$subkey1)) {
		    //echo '<br/>SI'.$do1->$extkey;
			$subkey2 = '_'.$tbl2;
			//echo '<br/>'.$subkey1.'  -  '.$subkey2;
			if (!isset($ris[$subkey1]->$subkey2)) $ris[$subkey1]->$subkey2 = array();
			//$ris[$subkey1]->$subkey2[] = 'piip';
			$tmp = & $ris[$subkey1]->$subkey2;
			$tmp[] = getLinksDoX($do1);//$do1->toArray();//get_object_vars($do1);
			
		//}else {
			//echo '<br/>NO'.$do1->$extkey;
		//}
	}
	
	return $ris;

}
//-----------------------------------------------------------------------------------------------------
public function showTablenameEditable($params = null) {
    require_once 'HTML/QuickForm/DHTMLRulesTableless.php';
	require_once 'HTML/QuickForm/Renderer/Tableless.php';
	require_once 'Pager.php';
	if(isset($_GET['pageID'])){$pageID  = $_GET['pageID'];}else{$pageID  = 1;}
	if (isset($_GET['nrecords'])) { $nrecords = $_GET['nrecords']; } else { $nrecords = 5; }
	$orderBy = '';
	if (isset($_GET['orderBy']) && isset($_GET['direction'])) {
		$orderBy = $_GET['orderBy'].' '.$_GET['direction'];
	}
	
	
	require_once xotpath."/includes/extPear/HTML_QuickForm_Renderer_Xot.php";
	
	$this->setVars();
	//$dg = new Structures_DataGrid($this->perPage);
	if ($this->tablename == '') {
		$msg = ' assegna template->tablename ';
		$this->showMsg($msg, 'error');
		return;
	}
	$do = DB_DataObject::factory($this->tablename);
	if (PEAR::isError($do)) {
		die('['.__LINE__.']['.__FILE__.']'.$do->getMessage());
	}
	$keys = $do->keys();
	$this->primary_key = $keys[0];
	//echo '['.$this->primary_key.']';
	$do->getMyDateField("%d/%M/%Y %H:%i");
	if(count($this->fieldstorender)>1){
		$do->fb_fieldsToRender = $this->fieldstorender;
	}
//	if (count($this->hiddenfields) > 0) $this->registry->conf->DataObjectCustomFields($do, null, $this->hiddenfields, 'hidden');
	
	if (isset($this->query[$this->tablename])) {
		//echo '<h3>whereadd['.$this->query[$this->tablename].']</h3>';
		$do->whereAdd($this->query[$this->tablename]);	
	}
	$this->htmlfilterdo($do);
	if ($this->filter_sql != '') {
		$do->whereAdd($this->filter_sql);
	}
	
	if ($orderBy != '') {
		$do->orderBy($orderBy);
	}elseif (isset($this->order_by[$this->tablename])) {
		//echo $this->order_by[$this->tablename];
		$do->orderBy($this->order_by[$this->tablename]);
	}
	//echo '<pre>'; print_r($this->order_by); echo '</pre>';
	
	
	
	$tpl = ''; 
	if ($this->id_new != '' && $this->id_new !=0) {
			$tpl.= '<a href="?rt=menu/gest/'.$this->id_new;
			if(isset($this->params[1])) $tpl .= '/'.$this->params[1];
			$tpl.='" title="Aggiungi Nuovo"><img src="'.$this->vars['template_url'].'/resources/images/icons/new.png"/><b>Aggiungi Nuovo '.$this->tablename.'</b></a>';
			$tpl.= '<br/><br/><br/>';
	}
	echo $tpl;
	
	$nris = $do->count();
	if ($nris == 0) {
		echo '<h3>NO RISULTATI</h3>';
		return;
	}
	
	$params = array(
		'mode' => 'Sliding', 
		'perPage' => $nrecords, 
		'delta' => 2, 
		'totalItems' => $nris
	);
			
    $pager = &Pager::factory($params);
    list($from, $to) = $pager->getOffsetByPageId();
	
	$caption = '<b>'.$do->count().' Righe  from '.$from.' to '.$to.'</b>';
	echo $caption;
	$do->limit($from - 1, $nrecords); 
	$do->find();
	$keys = $do->keys();
	$this->primary_key = $keys[0];
	
	
	//echo '['.$this->primary_key.']';
	$fb = array();
	$form = null;
	$i = 0;
	while ($do->fetch()) {
		$fb_tmp = & DB_DataObject_FormBuilder::create($do);
		$fb_tmp->elementNamePrefix = 'form_'.$i.'_';
		$fb_tmp->formHeaderText = $this->actionColumnCallback(array('record' => $do->toArray()));  //'<a href="">Cancella</a>';
		$fb_tmp->elementTypeMap['date'] = 'jquery_date';
		$fb_tmp->elementTypeMap['datetime'] = 'jquery_datetime';
		
		if (count($this->hiddenfields) > 0) $this->registry->conf->DataObjectCustomFields($do, $fb_tmp, $this->hiddenfields, 'hidden');
		//$this->registry->conf->DataObjectCustomFields($do, $fb_tmp, array($keys[0]), 'text'); // nel caso di liste
		if ($i == $to - $from) {
		//	$fb_tmp->submitText = 'Salva '.$table;;
		//	$fb_tmp->createSubmit = true;
		//	$fb_tmp->useAccessors = true;
		//	$fb_tmp->linkNewValue = true;
			$fb_tmp->createSubmit = false;
		}else {
			$fb_tmp->createSubmit = false;
		}
		$fb_tmp->hidePrimaryKey = false;
		$fb_tmp->linkDisplayLevel = 2;
		$fb_tmp->fieldAttributes = array($keys[0] => array('readonly' => '1','size'=>3,'style'=>'color:gray;width:50px'));
		if($i>0)$fb_tmp->useForm($form);
		$form = & $fb_tmp->getForm($_SERVER["REQUEST_URI"]);
		//$form->elementNamePrefix = $fb_tmp->elementNamePrefix;
		$fb[$i] = & $fb_tmp;
		$i++;
	}
	
	//$form->addElement('submit', 'op', 'Go!');
	
	$nform = count($fb);
	
	
	if ($form->validate()) {
		for ($i = 0; $i < $nform;$i++){
			$form->process(array( & $fb[$i], 'processForm'), false);
			
			//echo '<pre>'; print_r($fb[$i]); echo '</pre>';
		}
		$this->showMsg('Salvato', 'success');
		//$form->freeze();
		$objname = $this->smarty->tpl_vars['obj']->value;
		$act = "do_".$this->smarty->tpl_vars['act']->value;
		//$this->registry->router->subloader($objname, $act, $form->exportValues());
		//if($objname!="") $this->registry->router->subloader($objname, $act, $do->toArray());
		$this->registry->router->esegui($objname, $act, $form->exportValues());
		
		
		
	}
	
	
	$renderer = new HTML_QuickForm_Renderer_Xot();
	$renderer->do = $do;
	$renderer->hiddenfields = $this->hiddenfields;
	$renderer->pager = $pager;
	$form->accept($renderer);
	$outHtml = $renderer->toHtml();
	$outHtml = str_replace('<table', '<table class="table table-striped table-bordered table-condensed"', $outHtml);
	echo $outHtml; 
}
//-----------------------------------------------------------------------------------------------------
public function showTablename($params = null) {

	$dg = new Structures_DataGrid($this->perPage);
	
	
	$this->setVars();
	$do = DB_DataObject::factory($this->tablename);
	
	
	$keys = $do->keys();
	$this->primary_key = $keys[0];
	//echo '['.$this->primary_key.']';
	$do->getMyDateField("%d/%M/%Y %H:%i");
	if(count($this->fieldstorender)>1){
		$do->fb_fieldsToRender = $this->fieldstorender;
	}
	
	if (isset($this->query[$this->tablename])) {
		//echo '<h3>whereadd['.$this->query[$this->tablename].']</h3>';
		$do->whereAdd($this->query[$this->tablename]);	
	}
	$this->htmlfilterdo($do);
	if ($this->filter_sql != '') {
		$do->whereAdd($this->filter_sql);
	}	
	//if(isset($_POST['__filter']) && 
	
	
	$nris = $do->count();
	
	if (isset($_GET['xls'])) {
			$do_clone = clone($do);
			$data = $do_clone-> fetchAll();
			exportArray2XLS('export-'.session_id(), array_render($data,$this->fieldstorender));
	}	
	
	//echo '<pre>'; print_r($do); echo '</pre>'; die('<br/>LINE:'.__LINE__.'<br/>FILE:'.__FILE__);
	$datagridOptions = array(
		'link_property' => 'fb_linkDisplayFields',
		'link_level' => 1,
		'link_keep_key' => false);
	
	$error = $dg->bind($do, $datagridOptions, 'DataObject');
	//$error = $dg->bind($do);
	
	/*
	$dg->setDataSourceOptions(array(
'sort_property' => 'id_commissioni',
'link_property' => 'fb_linkDisplayFields',
'link_level' => 3));
	*/
	//die('aa');
	if (PEAR::isError($error)) {
		die('
		<br/> <b>LINE: </b>'.__LINE__.'<br/>
		<b> FILE: </b>'.__FILE__.'<br/>
		<b> MSG: </b>'.$error->getMessage() . '<br />
		<b> Info :</b>' . $error->getDebugInfo());
	}
//	echo '<pre>'; print_r($dg); echo '</pre>';
	//$this->primary_key = 'id';
//	echo '[['.$do->count().']]';
	//echo $do->count();
	if ($nris == 0) {
		$tpl = '';
		if ($this->id_new != '' && $this->id_new !=0) {
			$tpl.= '<a href="?rt=menu/gest/'.$this->id_new;
			if(isset($this->params[1])) $tpl .= '/'.$this->params[1];
			$tpl.='" title="Aggiungi Nuovo"><img src="'.$this->vars['template_url'].'/resources/images/icons/new.png"/><b>Aggiungi Nuovo '.$this->tablename.'</b></a>';
			$tpl.= '<br/><br/><br/>';
		}
		$tpl.= '<h3>Nessuna Voce Presente</h3>';
		$this->registry->smarty->assign('content', $tpl);
	}else {
	//	echo '<h3>'.$nris.' Risultati</h3>';
		
		$this->dg = $dg;
		

		return $this->showDG();
	}
}
//------------------------------------------------------------------------------------------------------
public function htmlfilterdo($do) {
/*
	print_r($do->table());
	 'id'     =>  1  // == DB_DATAOBJECT_INT
//     'name'   =>  2  // == DB_DATAOBJECT_STR
//     'bday'   =>  6  // == DB_DATAOBJECT_STR + DB_DATAOBJECT_DATE
//     'last'   =>  14 // == DB_DATAOBJECT_STR + DB_DATAOBJECT_DATE + DB_DATAOBJECT_TIME
//     'active' =>  17 // == DB_DATAOBJECT_INT + DB_DATAOBJECT_BOOL
//     'desc'   =>  34 // == DB_DATAOBJECT_STR + DB_DATAOBJECT_TXT
//     'photo'  =>  64 // == DB_DATAOBJECT_STR + DB_DATAOBJECT_BLOB 
*/
	$fields = array();
	$tmp = $do->table();
	reset ($tmp);
	while (list($k, $v) = each($tmp)) {
		$fields[$k] = $k;
	}
	$tmp1 = array('=', ' LIKE ','<', '>', '<=', '>=');
	$op = array();
	while (list($k, $v) = each($tmp1)) {
		$op[$v] = $v;
	}
	
	$form = new HTML_QuickForm('showDG_filter', 'post', $_SERVER['REQUEST_URI']);
	$group = array();
	//$form->addElement('header', null, 'Filtra');
	$group[] =& HTML_QuickForm::createElement('select', 'field0', 'a', $fields);
	$group[] = & HTML_QuickForm::createElement('select', 'op', 'b', $op);
	$group[] =& HTML_QuickForm::createElement('text', 'field1', 'c');
	$group[] =& HTML_QuickForm::createElement('submit', 'vai', 'FILTRA !');
	$form->addGroup($group, '__filter', 'Filtro', '&nbsp;');
	$this->filter_html = $form->ToHtml();
	
	if (isset($_POST['__filter']) && isset($_POST['__filter']['field1']) && strlen($_POST['__filter']['field1']) > 0) {
	   $filter = $_POST['__filter']['field0'].$_POST['__filter']['op'].'"'.$_POST['__filter']['field1'].'"';
	   $this->filter_sql=$filter;
	}
	
	//echo '<pre>'; print_r($_POST); echo '</pre>';
	
	
}
//------------------------------------------------------------------------------------------------------

//------------------------------------------------------------------------------------------------------
public function showDG() {
	
	// prepare the form and the QuickHtml renderer
	$form = new HTML_QuickForm('showDG_form', 'post', $_SERVER['REQUEST_URI']);
	$renderer = new HTML_QuickForm_Renderer_QuickHtml();
	// add action selectbox and submit button to the form
	$azioni = $this->azioni;
	$azioni[] = '--Seleziona Azione--';
	reset($this->azioniFromMenu);
	//echo '<pre>'; print_r($this->azioniFromMenu); echo '</pre>'; die(__LINE__.__FILE__);
	while (list($k, $v) = each($this->azioniFromMenu)) {
			if($this->id_new != $v->id){
				//$ris.= '<a title="'.$v->nome.'"  href="?rt=menu/gest/'.$v->id.'/'.$record[$this->primary_key].'">';
				//$ris.= '<img src="'.$this->vars['template_url'].'/resources/images/icons/'.$v->img.'"/></a>&nbsp;'.chr(13);
				$href = 'menu/gest/'.$v->id.'';
				$azioni[$href] = $v->nome;
			}
		
	}
	
	//http://localhost/appuntamenti/admin/index.php?rt=menu/gest/46
	//http://localhost/appuntamenti/admin/index.php?rt=menu/gest/46
	
	
	$form->addElement('select', 'action', 'choose', $azioni);
	$form->addElement('submit', 'submit', 'Esegui Azione !');
	//$dg = new Structures_DataGrid(10);
	//$dg->bind($this->vars['myArray']); // the Magic
	$dg = $this->dg;
	//$do = $this->do ;
	$column = new Structures_DataGrid_Column(' ', 'idList', null,array('width' => '10'));
	$dg->addColumn($column);
	$dg->generateColumns();
	$dg->addColumn(new Structures_DataGrid_Column(null, null, null, array('width' => '10'), null,  array($this, 'actionColumnCallback()'),'first'));

	$p_options = array(
      'pagerOptions' => array(
        'mode' => 'Sliding',
	//	'perPage'  => 10,
        'delta' => 2,
        'httpMethod' => 'GET',
      )
    );
	ob_start();
	$dg->render('Pager', $p_options);
	$pagingHtml = ob_get_contents();
	ob_end_clean();
	ob_start();
	//print_r($this->do->table());
	
	/*
	// render sort form
    $hsf_options = array(
      'directionStyle' => 'radio',
      'textSubmit'     => 'Sort Grid'
    );
    $dg->render('Console', $hsf_options);
	//$dg->render('HTMLEditForm', $hsf_options);
	$pagingHtml.= ob_get_contents();
	ob_end_clean();
	//*/
	//echo $pagingHtml;

	$rendererOptions = array('form'         => $form,
                         'formRenderer' => $renderer,
                         'inputName'    => 'idList',
                         'primaryKey'   => $this->primary_key,  // da testare URGE
						// 'headerAttributes' => array('class'=>'table table-striped table-bordered table-condensed'),
						// 'columnAttributes' =>  array('style'=>'border:1px solid red;'),
						 'oddRowAttributes' =>(array ("style" => "background-color:lightgreen;")),
                        );

	// use a template string for the form
	$tpl = '';
	if ($this->id_new != '' && $this->id_new !=0) {
		$do1 = DB_DataObject::factory('_menu');
		$do1->get($this->id_new);
		$tpl.= '<a href="'.$_SERVER['REQUEST_URI'].'&rt=menu/gest/'.$this->id_new;
		if(isset($this->params[1])) $tpl .= '/'.$this->params[1];
		$tpl.='" title="Aggiungi Nuovo"><img src="'.$this->vars['template_url'].'/resources/images/icons/new.png"/><b>Aggiungi '.$do1->nome.' </b></a>';
		$tpl.= '<br/><br/><br/>';
	}
	// generate the HTML table and add it to the template string
	$tpl .= $dg->getOutput('CheckableHTMLTable', $rendererOptions);
	//$tpl .= $dg->getOutput(null, $rendererOptions);
	if (PEAR::isError($tpl)) {
		die($tpl->getMessage() . '<br />' . $tpl->getDebugInfo());
	}

	// add the HTML code of the action selectbox and the submit button to the
	// template string
	$tpl .= $renderer->elementToHtml('action');
	$tpl .= $renderer->elementToHtml('submit');
	$tpl.= $pagingHtml;
	

	// we're now ready to output the form (toHtml() adds the <form> / </form>
	// pair to the template)
	/* For every odd <tr> elements */
	
	
	$str = $renderer->toHtml($tpl);
	// traduto tutte le date in italiano 
	$pattern = '/[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]/';
	preg_match_all($pattern, $str, $matches);
	while(list($k, $v) = each($matches[0])) {
		$dateIT = date('d/m/Y', strtotime($v));
		$str = str_replace($v, $dateIT, $str);
	}
	/////////////////////////////////////
	$pattern = '/>([A-Z_]*)</i';
	preg_match_all($pattern, $str, $matches);
    if(isset($matches[0]))
	while (list($k, $v) = each($matches[0])) {
		$str = str_replace($v, str_replace('_', ' ', $v), $str);
	}
	////////////////////////////////////
	
	$puls = '<a href="'.$_SERVER['REQUEST_URI'].'&xls=1"><img src="'.$this->vars['template_url'].'/resources/images/icons/excel_icon.png"/></a>';
	/*
	*/
	$myjs='<script>
	function showSelectedValues() {
	
  alert($("input[type=checkbox]:checked").map(
     function () { return this.value; } ).get().join("/"));
	 return false;
}
</script>';

	$myjs = '';
	$outHtml = $puls.$str;
	
		// if the form was submitted and the data is valid, show the submitted data
	if ($form->isSubmitted() && $form->validate()) {
		//var_dump($this->registry);
		
		$vals = $form->getSubmitValues();
		//var_dump($vals); die(__LINE__.__FILE__);
		//$_GET['rt'] = $this->registry->router->controller.'/'.$vals['action'].'/'.implode('/',array_unique($vals['idList']));
		//$this->registry->router->loader();
		if(isset($vals['action'])){
			$_GET['rt'] = $vals['action'].str_replace('//','/','/'.implode('/', array_unique($vals['idList'])));
			//die($_GET['rt']);
			//$this->registry->router->loader();
			$outHtml = '<script>
				document.location.href="./?rt='.$_GET['rt'].'";
			</script > ';
		}
	}
	
	$outHtml = str_replace('<table>', '<table class="table table-striped table-bordered table-condensed">', $outHtml);
	
	
	$this->registry->smarty->assign('content', $this->filter_html.$outHtml);
	return $outHtml;

}
//-----------------------------------------------------------------------------------------------------
public function actionColumnCallback($params) {
	//echo '<pre>'; print_r($params); echo '</pre>';
	extract($params);
	//echo '<h3>'.$this->primary_key.'</h3>';
	
	$ris = '';
	$edit_img ='<img src="'.$this->vars['template_url'].'/resources/images/icons/pencil.png" alt="Edit" />';
	$delete_img = '<img src="'.$this->vars['template_url'].'/resources/images/icons/cross.png" alt="Delete" />';	
	$setting_img = '<img src="'.$this->vars['template_url'].'/resources/images/icons/hammer_screwdriver.png" alt="Edit Meta" />';
	reset($this->azioni);
	while (list($k, $v) = each($this->azioni)) {
		$href = $_SERVER['REQUEST_URI'].'&rt='.$this->registry->router->controller.'/'.$k.'/'.$record[$this->primary_key];
		switch($k) {
			case 'edit':
				$ris.= '<a href="'.$href.'" >'.$edit_img.'</a>';
			break;
			case 'delete':
				$ris.= '<a href="'.$href.'" onclick="return confirm(\"Sicuro di Cancellare ?\");">'.$delete_img.'</a>';
			break;
			default:
				$ris.= '<a href="'.$href.'" >'.$v.'</a>';
			break;
		}	
	}
	
	reset($this->azioniFromMenu);
	while (list($k, $v) = each($this->azioniFromMenu)) {
		if (!isset($record[$this->primary_key])) {
			$ris.= '<b class="color:darkred"> No primary key </b>';
		}else {
			if($this->id_new != $v->id){
				$ris.= '<a title="'.$v->nome.'"  href="'.$_SERVER['REQUEST_URI'].'&rt=menu/gest/'.$v->id.'/'.$record[$this->primary_key].'">';
				$ris.= '<img width="20" height="20" hspace="12px" src="'.$this->vars['template_url'].'/resources/images/icons/'.$v->img.'"/></a>'.chr(13);
			}
		}
		
	}
		
	return $ris;
}




//----------------------------------------------------------------------------------------------------
 /**
 * @set undefined vars
 * @param string $index
 * @param mixed $value
 * @return void
 */
 public function __set($index, $value) {
        $this->vars[$index] = $value;
 }


function show($name = null) {
	/*
	if ($name == null) $name = $this->registry->router->controller.'/'.$this->registry->router->action;
	$path = __SITE_PATH . '/views' . '/' . $name . '.php';
	
	if (file_exists($path) == false){
		throw new Exception('Template not found in '. $path);
		return false;
	}
	*/
	//echo '<pre>'; print_r($this->registry->user); echo '</pre>';
	$this->setVars();
	/*
	$LU = $this->registry->user->LU;
	
	$this->registry->smarty->assign('user_ente', $LU->getProperty('ente'));
	$this->registry->smarty->assign('user_matr', $LU->getProperty('matr'));
	$this->registry->smarty->assign('user_handle', $LU->getProperty('handle'));
	
	// Load variables
	foreach ($this->vars as $key => $value){
		//$$key = $value;
		$this->registry->smarty->assign($key, $value);
	}
	*/
    if (is_dir(sitepath . '/admin/views/'.$this->registry->router->controller)) {
		$this->registry->smarty->setTemplateDir(sitepath . '/admin/views/'.$this->registry->router->controller);
		//echo '[0]';
	}else{     
		$this->registry->smarty->setTemplateDir(__SITE_PATH . '/views/'.$this->registry->router->controller);
		//echo '[1]';
	}/*
	if (!is_file($this->registry->smarty->template_dir.'/'.$this->registry->router->action.'_tpl.php')) {
		echo '<br /> Controller ['.$this->registry->router->controller.']';
		echo '<br/> TEMPLATE [';print_r($this->registry->smarty->template_dir); echo '/'.$this->registry->router->action.'_tpl.php'.']';
		echo '<br/> non esiste ..';
		//die('')
	}*/
	$this->registry->smarty->assign('template_dir', $this->registry->smarty->getTemplateDir());
	$this->registry->smarty->assign('tpl', $this->registry->router->action.'_tpl.php');
	$helpfile = 'help/'.$this->registry->router->action.'_'.$this->id.'_tpl.php';
	if(is_file($this->registry->smarty->template_dir[0].'/'.$helpfile)){
		$this->registry->smarty->assign('help', $this->registry->smarty->fetch($helpfile));
	}
	//$this->registry->smarty->display($this->registry->router->action.'_tpl.php');
	if ($name != null) {
		$tpl_dir = $this->registry->smarty->getTemplateDir();
	    if(is_file($tpl_dir[0].'/'.$name)){
			//echo '<pre>[';print_r($this->registry->smarty->getTemplateDir()); echo ']</pre>';
			$this->registry->outHtml = $this->registry->smarty->fetch($name);
		}else {
			echo 'NON ESISTE IL FILE <pre>['; print_r($this->registry->smarty->getTemplateDir()); echo ']</pre>/'.$name;
		}	
	}else {
		if ( !$this->template_exists($this->registry->router->action.'_tpl.php') ) {
			//echo 'pe pe pe pe ';
			$this->registry->smarty->setTemplateDir(__SITE_PATH . '/views/index');
			$this->registry->outHtml = $this->registry->smarty->fetch('generic_tpl.php');
		}else{
			$this->registry->outHtml = $this->registry->smarty->fetch($this->registry->router->action.'_tpl.php');
		}
	}
	
	//echo '['.$this->registry->outHtml.']';
	
	return $this->registry->outHtml;
}
////////----------------------------------------------------------------
public function template_exists($tpl) {
	$tpl_dirs = $this->registry->smarty->getTemplateDir();
	reset($tpl_dirs);
	while (list($k, $v) = each($tpl_dirs)) {
		 if (is_file($v.'/'.$tpl)) {
			return true;
		 }
	}
	return false;
}

//-----------------------------------------------------------------------
function controlpanel() {		
	$this->registry->smarty->assign('template_url', $this->template_url);
	$this->registry->smarty->assign('content', $this->registry->outHtml);
	$this->registry->smarty->assign('useronline', $this->registry->user->useronline);
	$this->registry->smarty->setTemplateDir($this->template_path);
	$this->registry->smarty->display('controlpanel_tpl.php');
}
//-----------------------------------------------------------------------
}

//-----------------------------------------------------------------------



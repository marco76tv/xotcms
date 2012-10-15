<?php

Abstract Class baseController {

/*
 * @registry object
 */
protected $registry;
public $form;

function __construct($registry) {
	$this->registry = $registry;
	$this->form = new HTML_QuickForm('baseControllerForm', 'POST', $_SERVER["REQUEST_URI"]);
}

/**
 * @all controllers must contain an index method
 */
abstract function index($params);
}


<?php

class Engine_Controller extends Engine_Interface {
	protected $view;
	protected $model;
	
	protected $action;
	protected $args;
	protected $get;
	protected $post;
	
	protected $folders = null;
	
	function __construct() {
		parent::__construct();

		$this->view = $this->registry['view'];
		
		$this->model = new Engine_Model();
        
        $this->action = $this->registry["action"];
        $this->args = $this->registry["args"];
        $this->get = $this->registry["get"];
        $this->post = $this->registry["post"];

		$this->folders = $this->view->getFolders();
    }

	public function __call($name = null, $args = null) {
		$this->view->setTitle("404");
		
        $this->view->page404();
	}
}
?>

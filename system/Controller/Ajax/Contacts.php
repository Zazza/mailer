<?php
class Controller_Ajax_Contacts extends Engine_Ajax {
	private $contacts;
	
	public function __construct() {
		parent::__construct();
		$this->contacts = new Model_Contacts();
	}
	
	public function getTree($params) {
		$tree = $this->contacts->getGroups();
	
		echo $this->view->render("contacts_structure", array("tree" => $tree));
	}
	
	public function addTree($params) {
		$name = htmlspecialchars($params["name"]);
	
		$this->contacts->addGroup($name);
	}
	
	public function delGroup($params) {
		$id = $params["id"];
	
		$this->contacts->delGroup($id);
	}
	
	public function editGroup($params) {
		$id = $params["id"];
		$name = htmlspecialchars($params["name"]);
	
		$this->contacts->editGroup($id, $name);
	}
	
	public function getGroupName($params) {
		$id = $params["id"];
	
		$cat = $this->contacts->getGroupName($id);
		 
		echo $cat;
	}
	
	public function getInfo($params) {
		$email = $params["email"];
		
		$contact = $this->contacts->getContact($email);
		
		echo $this->view->render("contacts_contact", array("email" => $email, "contact" => $contact));
	}
}
?>
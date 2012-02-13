<?php
class Controller_Contact extends Engine_Controller {

	function index() {
		$mcontacts = new Model_Contacts();
		
		$groups = $mcontacts->getGroups();
		$fields = $mcontacts->getContactFields();
		
		$msg = null;

		if (isset($this->args[0])) {
			if ($this->args[0] == "add") {
				$this->view->setTitle("Новый контакт");
				
				if (isset($this->post["submit"])) {
					$mcontacts->addContact($this->post);
					
					$msg = "Контакт сохранён!";
				}
				
				if (isset($this->get["email"])) {
					$email = $this->get["email"];
				} else {
					$email = null;
				}
				$this->view->contacts_addcontact(array("msg" => $msg, "email" => $email, "groups" => $groups, "post" => $this->post, "fields" => $fields));
			} elseif ($this->args[0] == "edit") {
				$this->view->setTitle("Правка контакта");
				
				if (isset($this->post["submit"])) {
					$mcontacts->editContact($this->post);
					
					$msg = "Контакт сохранён!";
					
					$post = $mcontacts->getContact($this->post["email"]);
					$email = $this->post["email"];
				} else {
					$post = $mcontacts->getContact($this->get["email"]);
					$email = $this->get["email"];
				}
				
				$this->view->contacts_editcontact(array("msg" => $msg, "email" => $email, "groups" => $groups, "post" => $post, "fields" => $fields));
			}
		} else {
			$this->view->setTitle("Контакты");
			
			if (isset($this->get["groups"])) {
				$contacts = $mcontacts->getContacts($this->get["groups"]);

				$this->view->contacts_groupcontacts(array("contacts" => $contacts));
			} else {
				$contacts = $mcontacts->getContacts();
				
				$this->view->contacts_contacts(array("groups" => $groups, "contacts" => $contacts));
			}			
			
		}

	}
}
?>
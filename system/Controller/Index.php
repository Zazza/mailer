<?php
class Controller_Index extends Engine_Controller {

	function index() {
		$this->view->setTitle("Входящая почта");
		
		$mailClass = new Model_Mail();

		$mails = $mailClass->getMails();

		$this->view->mail_index(array("mails" => $mails, "mailbox" => "Входящая почта"));
	}
}
?>
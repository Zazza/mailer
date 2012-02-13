<?php
class Controller_Send extends Engine_Controller {

	function index() {
		$mailClass = new Model_Mail();
		
		$this->view->setTitle("Отправленная почта");
		
		$mails = $mailClass->getOutMails();

		$this->view->mail_indexout(array("mails" => $mails, "mailbox" => "Отправленная почта"));
	}
}
?>
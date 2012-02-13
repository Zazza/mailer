<?php
class Controller_Get extends Engine_Controller {

	function index() {
		$mailClass = new Model_Mail();
		
		$mailbox = $mailClass->getMailboxFromId($_GET["folder"]);
		$this->view->setTitle("Почта: " . $mailbox);

		if (isset($_GET["folder"])) {
			$mailClass->getSortsByFolderId($_GET["folder"]);
			
			$mails = $mailClass->getMailsSort();
		}

		$this->view->mail_index(array("mails" => $mails, "mailbox" => $mailbox));
	}
}
?>
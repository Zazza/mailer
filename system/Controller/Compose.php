<?php
class Controller_Compose extends Engine_Controller {

	function index() {
		$mailClass = new Model_Mail();

		$this->view->setTitle("Новое сообщение");
		
		$mailboxes = $mailClass->getUserOutMailboxes();

		if (isset($_POST["submit"])) {
			
			$helpers = new Helpers_Helpers();
			
			$smtp = $mailClass->getOutMailbox($_POST["mailbox"]);
			
			$fromName = $this->registry["ui"]["name"] . " " . $this->registry["ui"]["soname"];
			
			if (!$err = $helpers->phpmailer($_POST, $smtp, $fromName)) {
				$mailClass->saveOutMail($_POST, $smtp);
				
				$this->view->refresh(array("timer" => "1", "url" => ""));
			} else {
				$this->view->mail_compose(array("err" => $err, "mailboxes" => $mailboxes, "post" => $_POST));
			}

			
		} else {
			$post = array();
			
			if ( (isset($_GET["action"])) and ($_GET["action"] == "reply") ) {
				if ( (isset($_GET["mid"])) and (is_numeric($_GET["mid"])) ) {
					$mail = $mailClass->getMailFromId($_GET["mid"]);
					
					$post["subject"] = "RE: " . $mail[0]["subject"];
					$post["to"] = $mail[0]["email"];
					
					$text = null; $html = null;
					for($i=0; $i<count($mail); $i++) {
						if ($mail[0]["type"] == "text") {
							$text = "<pre>> " . trim($mail[$i]["text"]) . "</pre>";
						}
						if ($mail[0]["type"] == "html") {
							$html = "> " . trim($mail[$i]["text"]);
						}
					}
					
					if ($html == null) { $post["textfield"] = $text; }
						else { $post["textfield"] = $html; }

					$post["textfield"] = str_replace("\r\n", "\r\n> ", $post["textfield"]);

					$post["textfield"] = $this->view->render("mail_reply", array("date" => $mail[0]["date"], "text" => $post["textfield"]));
					
					$post["email"] = $mail[0]["to"];
				}
			}
			
			$this->view->mail_compose(array("mailboxes" => $mailboxes, "post" => $post));
		}
	}
}
?>
<?php
class Controller_Boxes extends Engine_Controller {

	function index() {
		$this->view->setTitle("Настройки почты");
		
		$mailClass = new Model_Mail();
		$mailClass->uid = $this->registry["ui"]["id"];
		
		if ( (isset($this->args[0])) and ($this->args[0] == "add") ) {
			if (isset($_POST["submit"])) {
				$flag = true;
		
				if ($_POST["email"] == "") {
					$flag = false;
				}
				if ($_POST["in_server"] == "") {
					$flag = false;
				}
				if ($_POST["in_login"] == "") {
					$flag = false;
				}
				if ($_POST["in_password"] == "") {
					$flag = false;
				}
				if ($_POST["in_protocol"] == "") {
					$flag = false;
				}
				if ($_POST["in_port"] == "") {
					$flag = false;
				}
				if ($_POST["in_ssl"] == "") {
					$flag = false;
				}
				if ($_POST["out_server"] == "") {
					$flag = false;
				}
				if ($_POST["out_auth"] == 0) {
					$_POST["out_login"] = "";
					$_POST["out_password"] = "";
				}
				if ($_POST["out_auth"] == 1) {
					$_POST["out_login"] = $_POST["in_login"];
					$_POST["out_password"] = $_POST["in_password"];
				}
				if ($_POST["out_auth"] == 2) {
					if ($_POST["out_login"] == "") {
						$flag = false;
					}
					if ($_POST["out_password"] == "") {
						$flag = false;
					}
				}
				if ($_POST["out_port"] == "") {
					$flag = false;
				}
				if ($_POST["out_ssl"] == "") {
					$flag = false;
				}
		
				if ($flag) {
					$bid = $mailClass->addMailbox($_POST);
					$mailClass->addSignature($bid, $_POST["textfield"]);
						
					$this->view->refresh(array("timer" => "1", "url" => "boxes/"));
				} else {
					$this->view->profile_addmailbox(array("err" => true, "post" => $_POST));
				}
			} else {
				$post["clear"] = true;
				$this->view->profile_addmailbox(array("err" => false, "post" => $post));
			}
		} elseif (isset($_GET["email"])) {
			if (isset($_POST["submit"])) {
				$flag = true;
		
				if ($_POST["email"] == "") {
					$flag = false;
				}
				if ($_POST["in_server"] == "") {
					$flag = false;
				}
				if ($_POST["in_login"] == "") {
					$flag = false;
				}
				if ($_POST["in_password"] == "") {
					$flag = false;
				}
				if ($_POST["in_protocol"] == "") {
					$flag = false;
				}
				if ($_POST["in_port"] == "") {
					$flag = false;
				}
				if ($_POST["in_ssl"] == "") {
					$flag = false;
				}
				if ($_POST["out_server"] == "") {
					$flag = false;
				}
				if ($_POST["out_auth"] == 0) {
					$_POST["out_login"] = "";
					$_POST["out_password"] = "";
				}
				if ($_POST["out_auth"] == 1) {
					$_POST["out_login"] = $_POST["in_login"];
					$_POST["out_password"] = $_POST["in_password"];
				}
				if ($_POST["out_auth"] == 2) {
					if ($_POST["out_login"] == "") {
						$flag = false;
					}
					if ($_POST["out_password"] == "") {
						$flag = false;
					}
				}
				if ($_POST["out_port"] == "") {
					$flag = false;
				}
				if ($_POST["out_ssl"] == "") {
					$flag = false;
				}
		
				if ($flag) {
					$mailClass->editMailbox($_GET["email"], $_POST);
					$mailClass->editSignature($_GET["email"], $_POST["textfield"]);
						
					$this->view->refresh(array("timer" => "1", "url" => "boxes/"));
				} else {
					$this->view->profile_editmailbox(array("err" => true, "post" => $_POST));
				}
			} else {
				$mailbox = $mailClass->getMailbox($_GET["email"]);
				$signature = $mailClass->getSignature($_GET["email"]);
					
				$this->view->profile_editmailbox(array("post" => $mailbox, "signature" => $signature));
			}
		} else {
			$mailboxes = $mailClass->getUserMailboxes();
				
			$this->view->profile_listmailboxes(array("mailboxes" => $mailboxes));
		}
	}
}
?>
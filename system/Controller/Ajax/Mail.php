<?php
class Controller_Ajax_Mail extends Engine_Ajax {

	function getMail($params) {
		$mid = $params["mid"];

		$mailClass = new Model_Mail();

		$mail = $mailClass->getMailFromId($mid);

		$row["data"] = $this->view->render("mail_mail", array("mail" => $mail));
		$row["new"] = $mailClass->newMail;

		echo json_encode($row);
	}

	function getMailOut($params) {
		$mid = $params["mid"];

		$mailClass = new Model_Mail();

		$mail = $mailClass->getMailOutFromId($mid);

		echo $this->view->render("mail_mailout", array("mail" => $mail));
	}

	function delMail($params) {
		$mid = $params["mid"];

		$mailClass = new Model_Mail();

		$mail = $mailClass->delMail($mid);
	}
	
	function delMails($params) {
		$json = array();
	
		$json = json_decode($params["json"], true);
	
		$mailClass = new Model_Mail($this->registry);
	
		foreach($json as $part) {
			$mail = $mailClass->delMail($part);
		}
	}

	function delMailOut($params) {
		$mid = $params["mid"];

		$mailClass = new Model_Mail();

		$mail = $mailClass->delMailOut($mid);
	}
	
	function delMailsOut($params) {
		$json = array();
	
		$json = json_decode($params["json"], true);
	
		$mailClass = new Model_Mail($this->registry);
	
		foreach($json as $part) {
			$mail = $mailClass->delMailOut($part);
		}
	}

	function getMailboxes() {
		$mailClass = new Model_Mail();
			
		$mailboxes = $mailClass->getUserInMailboxes($this->registry["ui"]["id"]);
		foreach($mailboxes as $mailbox) {
			$data[] = $mailbox["email"];
		}
		echo json_encode($data);
	}

	function checkMboxes($params) {
		$mbox = $params["mbox"];
		
		$mailClass = new Model_Mail();
		
		$mailClass->uid = $this->registry["ui"]["id"];

		if (!$mailClass->checkMail($mbox)) {
			echo "false";
		} else {
			echo "true";
		}
	}

	function delMailbox($params) {
		$mailbox = $params["email"];

		$mailClass = new Model_Mail();

		$mailClass->delMailbox($mailbox);
	}

	function delSort($params) {
		$sid = $params["sid"];

		$mailClass = new Model_Mail();

		$mailClass->delSort($sid);
	}

	function delMailDir($params) {
		$fid = $params["fid"];

		$mailClass = new Model_Mail();

		$mailClass->delMailDir($fid);
	}

	function setDefault($params) {
		$mailbox = $params["email"];

		$mailClass = new Model_Mail();

		$mailClass->setDefault($mailbox);
	}
	
	function setRead($params) {
		$fid = $params["fid"];
		
		$mailClass = new Model_Mail();

		$mailClass->setRead($fid);
	}
	
	function clearFolder($params) {
		$fid = $params["fid"];
		
		$mailClass = new Model_Mail();

		$mailClass->clearFolder($fid);
	}
	
	function addContact($params) {
		$email = $params["email"];
		
		$contact = & $_SESSION["contact"];
		$contact["email"] = $email;
	}

	function writeMail($params) {
		$json = json_decode($params["json"]);
		
		$object = new Model_Object();
		
		$data = array();
		foreach($json as $key=>$val) {
			$email = null;
			
			$oid = mb_substr($key, 4, mb_strlen($key)-5);
			$email = $object->getEmailFromOid($oid);
			
			if ($email != null) {
				$data[] = $email;
			}
		}

		$mail = & $_SESSION["mail"];
		$mail["json"] = json_encode($data);
	}
	
	function getSign($params) {
		$bid = $params["bid"];
		
		$mailClass = new Model_Mail();
		
		$email = $mailClass->getEmailFromId($bid);
		$signature = $mailClass->getSignature($email);
		
		echo $signature;
	}
	
	function delContact($params) {
		$email = $params["email"];
		
		$mcontact = new Model_Contacts();
		$mcontact->delContact($email);
	}
}
?>
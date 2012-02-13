<?php
class Helpers_Helpers extends Engine_Helper {
	private $text = false;

    function phpmailer($_POST, $smtp = null, $fromName = null) {
        	$mailClass = new Model_Mail();
        
        	if ($smtp == null) {
        		$smtp = $settings->getMailbox();
        	}
    	
			$mailer = new Phpmailer_Phpmailer();
			
			$err = array();
			
			$mailer->SMTPDebug = 0;
			
			$mailer->CharSet = "utf-8";

			$mailer->IsSMTP();
			$mailer->Host = $smtp["server"];
			$mailer->Port = $smtp["port"];
			
			if ($smtp["ssl"] == "ssl") {
				$mailer->SMTPSecure = "ssl";
			}
			
			if ( ($smtp["login"]) and ($smtp["password"]) ) {
				$mailer->SMTPAuth = true;
				$mailer->Username = $smtp["login"];
				$mailer->Password = $smtp["password"];
			} else {
				$mailer->SMTPAuth = false;
			}
			
			$mailer->From = $smtp["email"];
			$mailer->FromName = $fromName;
			
			if ($_POST["to"] == null) {
				$err[] = "Не заданы адресаты";
			} else {
				$to = explode(",", $_POST["to"]);
				for($i=0; $i<count($to); $i++) {
					$mailer->AddAddress($to[$i]);
				}
			}

			if (isset($_POST["attaches"])) {
				foreach($_POST["attaches"] as $part) {
					$filename = mb_substr($part, mb_strrpos($part, DIRECTORY_SEPARATOR));
					
					if (substr($part, 0, 1) != "/") {
						$dir = $this->registry["path"]["upload"];
						$md5 = $mailClass->getAttachFileMD5($part);
					} else {
						if ( (isset($_POST["mail"])) and ($_POST["mail"]) ) {
							$dir = $this->registry["path"]["attaches"];
							$md5 = $mailClass->getFile($_POST["mail_id"], $filename);
						} else {
							$dir = $this->registry["path"]["upload"];
							$md5 = $mailClass->getFileMD5($part);
						}
					}

					$mailer->AddAttachment($this->registry["rootPublic"] . $dir . $md5, $filename);
				}
			}
			
			if (!$this->text) {
				$mailer->IsHTML(true);
				
				$mailer->Subject = $_POST["subject"];
				$mailer->Body = $_POST["textfield"];
				$mailer->AltBody = strip_tags($_POST["textfield"]);
			} else {
				$mailer->IsHTML(false);
				
				$mailer->Subject = base64_encode($_POST["subject"]);
				$mailer->Body = base64_encode($_POST["textfield"]);
			}			
			
			if ($_POST["textfield"] == null) { $err[] = "Пустое письмо"; };
						
			if (count($err) == 0) {

				if(!$mailer->Send()) {
					return $mailer->ErrorInfo;
				} else {
					return false;
				}
			} else {
				return $err;
			}
    }
}
?>
<?php
class Model_Mail extends Engine_Model {
	public $mbox;
	private $contact;
	
	function getContact() {
		return $this->contact;
	}
	
	function checkMail($mbox) {
		$mail_array = array();
		$new = array();

		$err = false;

		$mailbox = $this->getMailbox($mbox);

		if ($mailbox["in_protocol"] == "IMAP") {
			if ($connect = imap_open('{' . $mailbox["in_server"] . ':' . $mailbox["in_port"] . '/' . $mailbox["in_protocol"] . '/' . $mailbox["in_ssl"] . '}INBOX', $mailbox["in_login"], $mailbox["in_password"])) {

				$this->mbox = $mailbox["email"];

				$msg = imap_search($connect, "ALL");

				for($i=0; $i<count($msg); $i++) {
					if ($msg[$i] != null) {
						$mailStructure = new Model_MailStructure();

						$mailStructure->connect = $connect;
						$mailStructure->to = $mailbox["email"];
						$mailStructure->mes_num = $msg[$i];
						$mailStructure->uidl = imap_uid($connect, $msg[$i]);

						if (!$this->issetMailFromId($mailStructure->uidl, $mbox)) {

							$mailStructure->getHeader();
	
							$mail_array = $mailStructure->fetchMail();

							$mid = $this->saveMail($mail_array);

						} else {
							$mailStructure->getHeader();
							
							if (!$mailbox["clear"]) {

								$clear_date = date("YmdHis", mktime(date("H", $mailStructure->header->udate), date("i", $mailStructure->header->udate), date("s", $mailStructure->header->udate), date("m", $mailStructure->header->udate), date("d", $mailStructure->header->udate) + $mailbox["clear_days"], date("Y", $mailStructure->header->udate)));
								
								if ($clear_date < date("YmdHis")) {
									$del = imap_delete($connect, $msg[$i]);
								}
							}
						}
					}
				}

				imap_expunge($connect);
				imap_close($connect);
			} else {
				$err = true;
			}
		} else {
			$retval = 0;
			if (!$fp = @fsockopen($mailbox["in_server"], $mailbox["in_port"])) {
				$err = true;
			}

			if ($fp > 0) {
				$buf = fgets($fp, 1024);

				fputs($fp, "USER " . $mailbox["in_login"] . "\r\n");
				$buf = fgets($fp, 1024);

				fputs($fp, "PASS " . $mailbox["in_password"] . "\r\n");
				$buf = fgets($fp, 1024);

				fputs($fp, "STAT\r\n");
				$msg_num = fgets($fp, 1024);
				$msg_num = explode(" ", $msg_num);

				for($i=1; $i <= $msg_num[1]; $i++) {

					fputs($fp, "UIDL " . $i . "\r\n");
					$uidl = fgets($fp, 1024);
					$uidl = explode(" ", $uidl);

					if (!$this->issetMailFromId($uidl[2], $mbox)) {
						$new[$i] = $uidl[2];
					} else {
						if (!$mailbox["clear"]) {
							fputs($fp, "DELE " . $i . "\r\n");
							$buf = fgets($fp, 1024);
						}
					}
				}

				fputs($fp, "QUIT\r\n");
				$buf = fgets($fp,1024);

				fclose($fp);
			}
			
			if (count($new) > 0) {
				if (!$connect = imap_open('{' . $mailbox["in_server"] . ':' . $mailbox["in_port"] . '/' . $mailbox["in_protocol"] . '/' . $mailbox["in_ssl"] . '}INBOX', $mailbox["in_login"], $mailbox["in_password"])) {
					$err = true;
				}
			}
			
			foreach($new as $key=>$val) {
				$mailStructure = new Model_MailStructure();
				
				$mailStructure->connect = $connect;
				$mailStructure->to = $mailbox["email"];
				$mailStructure->mes_num = $key;
				$mailStructure->uidl = $val;
				
				$mailStructure->getHeader();

				$mail_array = $mailStructure->fetchMail();
				
				$mid = $this->saveMail($mail_array);
			}
			
			if ( (count($new) > 0) and (!$err) ) {
				imap_close($connect);
			}
		}

		if ($err) {
			return false;
		} else {
			return true;
		}
	}

	function saveMail($mail) {

        $sql = "INSERT INTO mail (`uidl`, `to`, `subject`, `date`, `personal`, `email`) VALUES (:mid, :to, :subj, :date, :personal, :email)";
        
        $res = $this->registry['db']->prepare($sql);
		$param = array(":mid" => $mail["uid"], ":to" => $mail["to"], ":subj" => $mail["subject"], ":date" => $mail["date"], ":personal" => $mail["personal"], ":email" => $mail["mailbox"] . "@" . $mail["host"]);
		$res->execute($param);
		
		$mid = $this->registry['db']->lastInsertId();
		
		foreach($mail["body"] as $part) {
			if ( (isset($part["text"])) and ($part["text"] != "") ) {
				$sql = "INSERT INTO mail_text (mid, type, text) VALUES (:mid, :type, :text)";
		        
		        $res = $this->registry['db']->prepare($sql);
				$param = array(":mid" => $mid, ":text" => $part["text"], ":type" => $part["type"]);
				$res->execute($param);
			}
		}
		
		foreach($mail["attach"] as $key=>$part) {
			if ($part != "") {
				$sql = "INSERT INTO mail_attach (mid, md5, filename) VALUES (:mid, :md5, :filename)";
		        
		        $res = $this->registry['db']->prepare($sql);
				$param = array(":mid" => $mid, ":md5" => $key, ":filename" => $part);
				$res->execute($param);
			}
		}
		
		return $mid;
	}
	
	function issetMailFromId($uidl, $mbox) {
		
        $sql = "SELECT COUNT(id) AS count
        FROM mail
        WHERE uidl = :id AND `to` = :mbox";

        $res = $this->registry['db']->prepare($sql);
		$param = array(":id" => $uidl, ":mbox" => $mbox);
		$res->execute($param);
		$rows = $res->fetchAll(PDO::FETCH_ASSOC);
        $data = $rows[0]["count"];
        
        if ($data > 0) { return true; } else { return false; }
	}
	
	function getUserMailboxes() {
	    $sql = "SELECT id, `email`, `default`
        FROM users_mail
        GROUP BY `email`
        ORDER BY id";
        
        $res = $this->registry['db']->prepare($sql);
		$res->execute();
		$data = $res->fetchAll(PDO::FETCH_ASSOC);
        
        return $data;
	}
	
	function getMailbox($email) {
		$data["email"] = $email;
		
	    $sql = "SELECT `server`, `protocol`, `port`, `login`, `password`, `ssl`, `clear`, `clear_days`
        FROM users_mail
        WHERE `email` = :email AND `type` = 'in'
        LIMIT 1";
        
        $res = $this->registry['db']->prepare($sql);
		$param = array(":email" => $email);
		$res->execute($param);
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		if (count($row) > 0) {			
			$data["in_protocol"] = $row[0]["protocol"];
			$data["in_server"] = $row[0]["server"];
			$data["in_port"] = $row[0]["port"];
			$data["in_login"] = $row[0]["login"];
			$data["in_password"] = $row[0]["password"];
			$data["in_ssl"] = $row[0]["ssl"];
			$data["clear"] = $row[0]["clear"];
			$data["clear_days"] = $row[0]["clear_days"];
			
		    $sql = "SELECT `server`, `port`, `login`, `password`, `ssl`
	        FROM users_mail
	        WHERE `email` = :email AND `type` = 'out'
	        LIMIT 1";
		    
	        $res = $this->registry['db']->prepare($sql);
			$param = array(":email" => $email);
			$res->execute($param);
			$row = $res->fetchAll(PDO::FETCH_ASSOC);
			
			$data["out_port"] = $row[0]["port"];
			$data["out_server"] = $row[0]["server"];
			$data["out_ssl"] = $row[0]["ssl"];
			if ($row[0]["login"] == "") {
				$data["out_login"] = "";
				$data["out_password"] = "";
				$data["out_auth"] = 0;
			} else if ( ($row[0]["login"] == $data["in_login"]) and ($row[0]["password"] == $data["in_password"]) ) {
				$data["out_login"] = $row[0]["login"];
				$data["out_password"] = $row[0]["password"];
				$data["out_auth"] = 1;
			} else if ( ($row[0]["login"] != $data["in_login"]) or ($row[0]["password"] != $data["in_password"]) ) {
				$data["out_login"] = $row[0]["login"];
				$data["out_password"] = $row[0]["password"];
				$data["out_auth"] = 2;
			}
	
	        return $data;
		}
	}
	
	function getUserInMailboxes() {
	    $sql = "SELECT `email`, `server`, `protocol`, `port`, `login`, `password`, `ssl`
        FROM users_mail
        WHERE `type` = 'in'";
	    
        $res = $this->registry['db']->prepare($sql);
		$res->execute();
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		return $row;
	}
	
	function getUserOutMailboxes() {
	    $sql = "SELECT id, `email` AS `name`, `default`
        FROM users_mail
        WHERE `type` = 'out'";
	    
        $res = $this->registry['db']->prepare($sql);
		$res->execute();
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		return $row;
	}
	
	function getNumUnreadMails() {
		$sql = "SELECT COUNT(`read`) AS count
				FROM mail
				WHERE `read` = 0 AND date > 0";
	
		$res = $this->registry['db']->prepare($sql);
		$res->execute();
		$count = $res->fetchAll(PDO::FETCH_ASSOC);
	
		return $count[0]["count"];
	}
	
	function getOutMailbox($mid) {
	    $sql = "SELECT `email`, `server`, `port`, `login`, `password`, `ssl`
        FROM users_mail
        WHERE `type` = 'out' AND id = :mid
        LIMIT 1";
	    
        $res = $this->registry['db']->prepare($sql);
		$param = array(":mid" => $mid);
		$res->execute($param);
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		return $row[0];
	}
	
	function addMailbox($post) {
		if (!isset($post["clear"])) {
			$clear = 0;
			
			if ( (isset($post["clear_days"])) and (is_numeric($post["clear_days"])) ) {
				$clear_days = $post["clear_days"];
			} else {
				$clear_days = 0;
				$clear = 1;
			}
		} else {
			$clear_days = 0;
			$clear = 1;
		}
		
		$sql = "INSERT INTO users_mail (`type`, `email`, `server`, `protocol`, `port`, `login`, `password`, `ssl`, `clear`, `clear_days`) VALUES (:type, :email, :server, :protocol, :port, :login, :password, :ssl, :clear, :clear_days)";
		
        $res = $this->registry['db']->prepare($sql);
		$param = array(":type" => "in", ":email" => $post["email"], ":server" => $post["in_server"], ":protocol" => $post["in_protocol"], ":port" => $post["in_port"], ":login" => $post["in_login"], ":password" => $post["in_password"], ":ssl" => $post["in_ssl"], ":clear" => $clear, ":clear_days" => $clear_days);
		$res->execute($param);
		
		$bid = $this->registry['db']->lastInsertId();
		
		$sql = "INSERT INTO users_mail (`type`, `email`, `server`, `protocol`, `port`, `login`, `password`, `ssl`) VALUES (:type, :email, :server, :protocol, :port, :login, :password, :ssl)";
		
        $res = $this->registry['db']->prepare($sql);
		$param = array(":type" => "out", ":email" => $post["email"], ":server" => $post["out_server"], ":protocol" => "SMTP", ":port" => $post["out_port"], ":login" => $post["out_login"], ":password" => $post["out_password"], ":ssl" => $post["out_ssl"]);
		$res->execute($param);
		
		return $bid;
	}
	
	function editMailbox($email, $post) {
		if (!isset($post["clear"])) {
			$clear = 0;
			
			if ( (isset($post["clear_days"])) and (is_numeric($post["clear_days"])) ) {
				$clear_days = $post["clear_days"];
			} else {
				$clear_days = 0;
				$clear = 1;
			}
		} else {
			$clear_days = 0;
			$clear = 1;
		}
		
		$sql = "UPDATE users_mail
		SET `email` = :newemail, `server` = :server, `protocol` = :protocol, `port` = :port, `login` = :login, `password` = :password, `ssl` = :ssl, clear = :clear, clear_days = :clear_days
		WHERE type = :type AND `email` = :email";
		
        $res = $this->registry['db']->prepare($sql);
		$param = array(":type" => "in", ":email" => $email, ":newemail" => $post["email"], ":server" => $post["in_server"], ":protocol" => $post["in_protocol"], ":port" => $post["in_port"], ":login" => $post["in_login"], ":password" => $post["in_password"], ":ssl" => $post["in_ssl"], ":clear" => $clear, ":clear_days" => $clear_days);
		$res->execute($param);
		
		if ( (!isset($post["out_login"])) or ($post["out_login"] == "") ) { $post["out_login"] = ""; };
		if ( (!isset($post["out_password"])) or ($post["out_password"] == "") ) { $post["out_password"] = ""; };
		
		$sql = "UPDATE users_mail
		SET `email` = :newemail, `server` = :server, `protocol` = :protocol, `port` = :port, `login` = :login, `password` = :password, `ssl` = :ssl
		WHERE type = :type AND `email` = :email";
		
        $res = $this->registry['db']->prepare($sql);
		$param = array(":type" => "out", ":email" => $email, ":newemail" => $post["email"], ":server" => $post["out_server"], ":protocol" => "SMTP", ":port" => $post["out_port"], ":login" => $post["out_login"], ":password" => $post["out_password"], ":ssl" => $post["out_ssl"]);
		$res->execute($param);
	}
	
	function delMailbox($email) {
		$sql = "DELETE FROM users_mail WHERE `email` = :email";
		
        $res = $this->registry['db']->prepare($sql);
		$param = array(":email" => $email);
		$res->execute($param);
	}
	
	function getMailsSort() {
		$row = array();
		$sql_inc = null;
		$sql_where = null;
		$where = null;
		
		if (isset($this->sort_get_mail_type)) {
			for($i=0; $i<count($this->sort_get_mail_type); $i++) {
				if ($this->sort_get_mail_type[$i] == "to") {
					$sql_inc[$i] = "LEFT JOIN mail_sort AS ms". $i ." ON (ms". $i .".val = mail.to)";
					$sql_where[$i] = "(ms". $i .".action = 'move' AND ms". $i .".type = 'to' AND mail.to = '" . $this->sort_get_mail_val[$i] . "')";
				}
				if ($this->sort_get_mail_type[$i] == "from") {
					$sql_inc[$i] = "LEFT JOIN mail_sort AS ms". $i ." ON (ms". $i .".val = mail.email)";
					$sql_where[$i] = "(ms". $i .".action = 'move' AND ms". $i .".type = 'from' AND mail.email = '" . $this->sort_get_mail_val[$i] . "')";
				}
				if ($this->sort_get_mail_type[$i] == "subject") {
					$sql_inc[$i] = "LEFT JOIN mail_sort AS ms". $i ." ON (ms". $i .".val LIKE '%" . $this->sort_get_mail_val[$i] . "%')";
					$sql_where[$i] = "(ms". $i .".action = 'move' AND ms". $i .".type = 'subject' AND mail.subject LIKE '%" . $this->sort_get_mail_val[$i] . "%')";
				}
			}
		}
		$maxid = count($sql_inc)-1;
		
		for($i=0; $i<count($sql_inc); $i++) {
			if (isset($this->sort_get_mail_sort_id[$i+1])) {
				if ($this->sort_get_mail_sort_id[$i] == $this->sort_get_mail_sort_id[$i+1]) {
					if ($maxid != $i) {
						$where .= $sql_where[$i] . " AND ";
					} else {
						$where .= $sql_where[$i];
					}
				} else {
					if ($maxid != $i) {
						$where .= $sql_where[$i] . " OR ";
					} else {
						$where .= $sql_where[$i];
					}
				}
			} else {
				$where .= $sql_where[$i];
			}
		}
		
		if (count($sql_inc) > 0) {
			$sql_inc = implode($sql_inc, " ");
			$sql_where = "AND (" . $where . ")";
		
		    $sql = "SELECT DISTINCT(mail.id), mail.uidl, mail.read AS `read`, mail.to AS `to`, mail.subject AS `subject`, mail.date AS `date`, mail.timestamp AS `timestamp`, mail.personal AS `personal`, mail.email AS `email`
	        FROM mail 
	        " . $sql_inc . " 
	        WHERE mail.status = '0' " . $sql_where . " 
	        ORDER BY mail.date DESC";
		    
	        $res = $this->registry['db']->prepare($sql);
			$res->execute();
			$row = $res->fetchAll(PDO::FETCH_ASSOC);
			
			for($i=0; $i<count($row); $i++) {
				$sql = "SELECT mcv.val
						FROM mail_contacts_vals AS mcv
						LEFT JOIN mail_contacts AS mc ON (mc.id = mcv.cid)
						WHERE mc.email = :email";
				
				$res = $this->registry['db']->prepare($sql);
				$param = array(":email" => $row[$i]["email"]);
				$res->execute($param);
				$data = $res->fetchAll(PDO::FETCH_ASSOC);
				
				$string = null;
				foreach($data as $part) {
					$string = $string . $part["val"] . " ";
				}
				
				$row[$i]["personal"] = $string;
				
			    $sql = "SELECT COUNT(id) as count
		        FROM mail_attach
		        WHERE mid = :mid
		        LIMIT 1";
			    
		        $res = $this->registry['db']->prepare($sql);
				$param = array(":mid" => $row[$i]["id"]);
				$res->execute($param);
				$count = $res->fetchAll(PDO::FETCH_ASSOC);
				
				if ($count[0]["count"] != 0) {
					$row[$i]["attach"] = true;
				} else {
					$row[$i]["attach"] = false;
				}
			}
		}
		
		return $row;
	}
	
	function getObjOutMails($oid) {
		$row = array();
		
		$sql = "SELECT email FROM mail_contacts WHERE oid = :oid LIMIT 1";
		
		$res = $this->registry['db']->prepare($sql);
		$param = array(":oid" => $oid);
		$res->execute($param);
		$email = $res->fetchAll(PDO::FETCH_ASSOC);
		
		if (count($email) > 0) {
			$this->contact = $email[0]["email"];
			
			$sql = "SELECT mo.id AS id, mo.to AS `to`, mo.subject AS `subject`, mo.timestamp AS `date`, mo.email AS `email`
			        FROM mail_out AS mo
			        WHERE mo.to = :email
			        ORDER BY mo.timestamp DESC";
			 
			$res = $this->registry['db']->prepare($sql);
			$param = array(":email" => $this->contact);
			$res->execute($param);
			$row = $res->fetchAll(PDO::FETCH_ASSOC);
			
			$sort = $this->getSorts();
			
			for($i=0; $i<count($row); $i++) {
				if (isset($row[$i]["id"])) {
					$sql = "SELECT ov.val AS val
							FROM mail_contacts AS mc
							LEFT JOIN objects_vals AS ov ON (ov.oid = mc.oid) 
							WHERE mc.email = :email";
						
					$res = $this->registry['db']->prepare($sql);
					$param = array(":email" => $row[$i]["to"]);
					$res->execute($param);
					$personal = $res->fetchAll(PDO::FETCH_ASSOC);
			
					if (count($personal) > 0) {
						$temp = null;
						foreach($personal as $part) {
							$temp[] = $part["val"];
						}
							
						$row[$i]["personal"] = implode(" ", $temp);
					}
			
					$sql = "SELECT COUNT(id) as count
					        FROM mail_attach_out
					        WHERE mid = :mid
					        LIMIT 1";
					 
					$res = $this->registry['db']->prepare($sql);
					$param = array(":mid" => $row[$i]["id"]);
					$res->execute($param);
					$count = $res->fetchAll(PDO::FETCH_ASSOC);
			
					if ($count[0]["count"] != 0) {
						$row[$i]["attach"] = true;
					} else {
						$row[$i]["attach"] = false;
					}
				}
			}
			
			return $row;
		}
	}
	
	function getOutMails() {
		$row = array();
		
	    $sql = "SELECT mo.id AS id, mo.to AS `to`, mo.subject AS `subject`, mo.timestamp AS `date`, mo.email AS `email`
        FROM mail_out AS mo
        ORDER BY mo.timestamp DESC";
	    
        $res = $this->registry['db']->prepare($sql);
		$res->execute();
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		$sort = $this->getSorts();

		for($i=0; $i<count($row); $i++) {
			if (isset($row[$i]["id"])) {
				$sql = "SELECT mcv.val
				FROM mail_contacts AS mc
				LEFT JOIN mail_contacts_vals AS mcv ON (mcv.cid = mc.id)
				WHERE mc.email = :email";
				 
				$res = $this->registry['db']->prepare($sql);
				$param = array(":email" => $row[$i]["to"]);
				$res->execute($param);
				$personal = $res->fetchAll(PDO::FETCH_ASSOC);
				
				if (count($personal) > 0) {
					$temp = null;
					foreach($personal as $part) {
						$temp[] = $part["val"];
					}
					
					$row[$i]["personal"] = implode(" ", $temp);
				}
				
			    $sql = "SELECT COUNT(id) as count
		        FROM mail_attach_out
		        WHERE mid = :mid
		        LIMIT 1";
			    
		        $res = $this->registry['db']->prepare($sql);
				$param = array(":mid" => $row[$i]["id"]);
				$res->execute($param);
				$count = $res->fetchAll(PDO::FETCH_ASSOC);
				
				if ($count[0]["count"] != 0) {
					$row[$i]["attach"] = true;
				} else {
					$row[$i]["attach"] = false;
				}
			}
		}

		return $row;
	}
	
	function getMails() {
		$data = array();
		
	    $sql = "SELECT mail.id, mail.uidl, mail.read AS `read`, mail.to AS `to`, mail.subject AS `subject`, mail.date AS `date`, mail.timestamp AS `timestamp`, mail.personal AS `personal`, mail.email AS `email`
        FROM mail
        WHERE mail.status = '0'
        ORDER BY mail.date DESC";
	    
        $res = $this->registry['db']->prepare($sql);
		$res->execute();
		$row = $res->fetchAll(PDO::FETCH_ASSOC);

		$sort = $this->getSorts();

		for($i=0; $i<count($row); $i++) {
			for($j=0; $j<count($sort); $j++) {
				
				$flag = 0;
				
				for($k=0; $k<count($sort[$j]); $k++) {
					if ( ($sort[$j][$k]["type"] == "to") and ($sort[$j][$k]["action"] == "move") ) {
						if ($row[$i]["to"] == $sort[$j][$k]["val"]) {
							$flag++;
						}
					}
					if ( ($sort[$j][$k]["type"] == "from") and ($sort[$j][$k]["action"] == "move") ) {
						if ($row[$i]["email"] == $sort[$j][$k]["val"]) {
							$flag++;
						}
					}
					if ( ($sort[$j][$k]["type"] == "subject") and ($sort[$j][$k]["action"] == "move") ) {
						if (mb_strpos($row[$i]["subject"], $sort[$j][$k]["val"]) !== false) {
							$flag++;
						}
					}
				}
				
				if ($flag == count($sort[$j])) {
					$row[$i]["id"] = 0;
				}
			}
			
			if ( (isset($row[$i]["id"])) and ($row[$i]["id"] > 0) ) {
				$sql = "SELECT mcv.val
				FROM mail_contacts_vals AS mcv
				LEFT JOIN mail_contacts AS mc ON (mc.id = mcv.cid)
				WHERE mc.email = :email";
				
				$res = $this->registry['db']->prepare($sql);
				$param = array(":email" => $row[$i]["email"]);
				$res->execute($param);
				$data = $res->fetchAll(PDO::FETCH_ASSOC);

				$string = null;
				foreach($data as $part) {
					$string = $string . $part["val"] . " ";
				}

				$row[$i]["personal"] = $string;
				
			    $sql = "SELECT COUNT(id) as count
		        FROM mail_attach
		        WHERE mid = :mid
		        LIMIT 1";
			    
		        $res = $this->registry['db']->prepare($sql);
				$param = array(":mid" => $row[$i]["id"]);
				$res->execute($param);
				$count = $res->fetchAll(PDO::FETCH_ASSOC);
				
				if ($count[0]["count"] != 0) {
					$row[$i]["attach"] = true;
				} else {
					$row[$i]["attach"] = false;
				}
			}
		}
		
		for($i=0; $i<count($row); $i++) {
			if ($row[$i]["id"] != 0) {
				$data[] = $row[$i];
			}
		}
		
		return $data;
	}
	
	function getObjMails($oid) {
		$sql = "SELECT email FROM mail_contacts WHERE oid = :oid LIMIT 1";
		
		$res = $this->registry['db']->prepare($sql);
		$param = array(":oid" => $oid);
		$res->execute($param);
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		if (count($row) > 0) {
			$this->contact = $row[0]["email"];
			
		    $sql = "SELECT m.id AS id, m.read AS `read`, m.personal AS personal, m.to AS `to`, m.subject AS `subject`, m.timestamp AS `date`, m.email AS `email`
	        FROM mail AS m
	        WHERE m.email = :email
	        ORDER BY m.timestamp DESC";
		    
	        $res = $this->registry['db']->prepare($sql);
			$param = array(":email" => $this->contact);
			$res->execute($param);
			$row = $res->fetchAll(PDO::FETCH_ASSOC);
	
			return $row;
		}
	}
	
	function getMailOutFromId($id) {
	    $sql = "SELECT mail.id, mail.to AS `to`, mail.subject AS `subject`, mail.timestamp AS `timestamp`, mail.email AS `email`, t.type AS `type`, t.text AS `text`
        FROM mail_out AS mail
        LEFT JOIN mail_text_out AS t ON (t.mid = mail.id)
        WHERE mail.id = :id ORDER BY t.type";
	    
        $res = $this->registry['db']->prepare($sql);
		$param = array(":id" => $id);
		$res->execute($param);
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
	    $sql = "SELECT fs.filename
        FROM mail_attach_out AS a
        LEFT JOIN mail_out AS mail ON (a.mid = mail.id)
        LEFT JOIN fm_fs AS fs ON (fs.md5 = a.md5)
        WHERE mail.id = :id";
	    
        $res = $this->registry['db']->prepare($sql);
		$param = array(":id" => $id);
		$res->execute($param);
		$attaches = $res->fetchAll(PDO::FETCH_ASSOC);
		
		$row[0]["attach"] = $attaches;
		
		return $row;
	}
	
	function getMailFromId($id) {
		$row = array();
		
	    $sql = "SELECT mail.id, mail.to AS `to`, mail.subject AS `subject`, mail.date AS `date`, mail.timestamp AS `timestamp`, mail.personal AS `personal`, mail.email AS `email`, t.type AS `type`, t.text AS `text`
        FROM mail
        LEFT JOIN mail_text AS t ON (t.mid = mail.id)
        WHERE mail.id = :id ORDER BY t.type";
	    
        $res = $this->registry['db']->prepare($sql);
		$param = array(":id" => $id);
		$res->execute($param);
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		if (count($row) > 0) {		
			$sql = "SELECT `read` FROM `mail` WHERE `id` = :id LIMIT 1";
		    
	        $res = $this->registry['db']->prepare($sql);
			$param = array(":id" => $id);
			$res->execute($param);
			$read = $res->fetchAll(PDO::FETCH_ASSOC);
			
			if(!$read[0]["read"]) {
				$this->newMail = true;
				
			    $sql = "UPDATE `mail` SET `read` = 1 WHERE `id` = :id LIMIT 1";
			    
		        $res = $this->registry['db']->prepare($sql);
				$param = array(":id" => $id);
				$res->execute($param);
			} else {
				$this->newMail = false;
			}
			
		    $sql = "SELECT a.filename
	        FROM mail_attach AS a
	        LEFT JOIN mail ON (a.mid = mail.id)
	        WHERE mail.id = :id";
		    
	        $res = $this->registry['db']->prepare($sql);
			$param = array(":id" => $id);
			$res->execute($param);
			$attaches = $res->fetchAll(PDO::FETCH_ASSOC);
			
			$row[0]["attach"] = $attaches;
			
		    $sql = "SELECT mcv.val
	        FROM mail_contacts AS mc
	        LEFT JOIN mail_contacts_vals AS mcv ON (mcv.cid = mc.id)
	        WHERE mc.email = :email";
		    
	        $res = $this->registry['db']->prepare($sql);
			$param = array(":email" => $row[0]["email"]);
			$res->execute($param);
			$contact = $res->fetchAll(PDO::FETCH_ASSOC);
			
			$row[0]["contact"] = $contact;
			
			return $row;
		} else {
			return FALSE;
		}
	}
	
	function delMail($mid) {
	    $sql = "UPDATE `mail`
	    SET `subject` = '', `personal` = '', `email` = '', `status` = '1', `date` = '', `timestamp` = ''
        WHERE id = :mid
        LIMIT 1";
	    
        $res = $this->registry['db']->prepare($sql);
		$param = array(":mid" => $mid);
		$res->execute($param);
		
	    $sql = "SELECT `md5`, `filename`
        FROM mail_attach
        WHERE mid = :mid";
	    
        $res = $this->registry['db']->prepare($sql);
		$param = array(":mid" => $mid);
		$res->execute($param);
		$attaches = $res->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($attaches as $part) {
			$filename = $this->registry["rootPublic"] . $this->registry["path"]["attaches"] . $part["md5"];
			
			@unlink($filename);
		}
		
	    $sql = "DELETE FROM mail_attach
        WHERE mid = :mid";
	    
        $res = $this->registry['db']->prepare($sql);
		$param = array(":mid" => $mid);
		$res->execute($param);
		
	    $sql = "DELETE FROM mail_text
        WHERE mid = :mid";
	    
        $res = $this->registry['db']->prepare($sql);
		$param = array(":mid" => $mid);
		$res->execute($param);
	}
	
	function delMailOut($mid) {
	    $sql = "DELETE FROM mail_out
        WHERE id = :mid
        LIMIT 1";
	    
        $res = $this->registry['db']->prepare($sql);
		$param = array(":mid" => $mid);
		$res->execute($param);
		
	    $sql = "DELETE FROM mail_attach_out
        WHERE mid = :mid";
	    
        $res = $this->registry['db']->prepare($sql);
		$param = array(":mid" => $mid);
		$res->execute($param);
		
	    $sql = "DELETE FROM mail_text_out
        WHERE mid = :mid";
	    
        $res = $this->registry['db']->prepare($sql);
		$param = array(":mid" => $mid);
		$res->execute($param);
	}
	
	function getFile($mid, $filename) {
	    $sql = "SELECT a.md5 AS `md5`
        FROM mail_attach AS a
        LEFT JOIN mail ON (a.mid = mail.id)
        WHERE mail.id = :id AND a.filename = :filename
        LIMIT 1";
	    
        $res = $this->registry['db']->prepare($sql);
		$param = array(":id" => $mid, ":filename" => $filename);
		$res->execute($param);
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		if (count($row) > 0) {
			return $row[0]["md5"];
		} else {
			return FALSE;
		}
	}
	
	function getFileOut($mid, $filename) {
	    $sql = "SELECT a.md5 AS `md5`
        FROM mail_attach_out AS a
        LEFT JOIN mail_out AS mail ON (a.mid = mail.id)
        LEFT JOIN fm_fs AS fs ON (fs.md5 = a.md5)
        WHERE mail.id = :id AND fs.filename = :filename
        LIMIT 1";
	    
        $res = $this->registry['db']->prepare($sql);
		$param = array(":id" => $mid, ":filename" => $filename);
		$res->execute($param);
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		if (count($row) > 0) {
			return $row[0]["md5"];
		} else {
			return FALSE;
		}
	}
	
	function addFolder($folder) {
		$sql = "INSERT INTO mail_folders (folder) VALUES (:folder)";
	    
        $res = $this->registry['db']->prepare($sql);
		$param = array(":folder" => $folder);
		$res->execute($param);
	}
	
	function editFolder($fid, $folder) {
		$sql = "UPDATE mail_folders SET folder = :folder WHERE id = :fid LIMIT 1";
	    
        $res = $this->registry['db']->prepare($sql);
		$param = array(":folder" => $folder, ":fid" => $fid);
		$res->execute($param);
	}
	
	function getFolders() {
	    $sql = "SELECT id, `folder`
        FROM mail_folders
        ORDER BY id";
	    
        $res = $this->registry['db']->prepare($sql);
		$res->execute();
		$row = $res->fetchAll(PDO::FETCH_ASSOC);

		for($i=0; $i<count($row); $i++) {
			$unread = 0; $all = 0;
			
			$sort_sql = $this->getFolderSorts($row[$i]["id"]);

			if ($sort_sql != null) {
				$sql = "SELECT id, `read`
				FROM mail
				WHERE " . $sort_sql;

		        $res = $this->registry['db']->prepare($sql);
				$res->execute();
				$count = $res->fetchAll(PDO::FETCH_ASSOC);

				for($l=0; $l<count($count); $l++) {
					if (!$count[$l]["read"]) {
						$unread++;
					}
					$all++;
				}
			}

			$row[$i]["count"] = $unread;
			$row[$i]["all"] = $all;
		}
		
		$main = $this->getMails();
		$mainCount = 0; $allCount = 0;
		for($i=0; $i<count($main); $i++) {
			if (!$main[$i]["read"]) { $mainCount++; }
			$allCount++;
		}

		$this->registry["mainCount"] = $mainCount;
		$this->registry["allCount"] = $allCount;
		
		return $row;
	}
	
	function getFolderSorts($fid) {
		$arr_sql_inc = array();

		$sql = "SELECT sort_id
				FROM mail_sort AS ms
				WHERE folder_id = :id
				GROUP BY sort_id";
			
		$res = $this->registry['db']->prepare($sql);
		$param = array(":id" => $fid);
		$res->execute($param);
		$sort = $res->fetchAll(PDO::FETCH_ASSOC);
	
		for($j=0; $j<count($sort); $j++) {
			$sql = "SELECT `type`, `val`
					FROM mail_sort AS ms
					WHERE sort_id = :sort_id
					ORDER BY id";
	
			$res = $this->registry['db']->prepare($sql);
			$param = array(":sort_id" => $sort[$j]["sort_id"]);
			$res->execute($param);
			$ownsort = $res->fetchAll(PDO::FETCH_ASSOC);
	
			$num = 0; $sql_inc = array();
	
			for($k=0; $k<count($ownsort); $k++) {
					
				if ($ownsort[$k]["type"] == "to") {
					$sql_inc[] = " (`to` = '" . $ownsort[$k]["val"] . "' AND status = 0) ";
				} else if ($ownsort[$k]["type"] == "from") {
					$sql_inc[] = " (`email` = '" . $ownsort[$k]["val"] . "' AND status = 0) ";
				} else if ($ownsort[$k]["type"] == "subject") {
					$sql_inc[] = " (subject LIKE '%" . $ownsort[$k]["val"] . "%' AND status = 0) ";
				}
			}
			
			$arr_sql_inc[] = implode(" AND ", $sql_inc);
		}
		
		$arr_sql_inc = implode(" OR ", $arr_sql_inc);

		return $arr_sql_inc;
	}
	
	function getSorts() {
		$row = array();
		
		$sql = "SELECT sort_id FROM mail_sort GROUP BY sort_id ORDER BY id";
		
		$res = $this->registry['db']->prepare($sql);
		$res->execute();
		$data = $res->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($data as $part) {
		    $sql = "SELECT ms.id, ms.sort_id, ms.type, ms.val, ms.folder_id, mf.folder, ms.action
	        FROM mail_sort AS ms
	        LEFT JOIN mail_folders AS mf ON (mf.id = ms.folder_id)
	        WHERE ms.sort_id = :sort_id
	        ORDER BY ms.id DESC";
		    
	        $res = $this->registry['db']->prepare($sql);
			$param = array("sort_id" => $part["sort_id"]);
			$res->execute($param);
			$row[] = $res->fetchAll(PDO::FETCH_ASSOC);
		}
		
		return $row;
	}
	
	function getSort($id) {
	    $sql = "SELECT id, sort_id, `type`, `val`, folder_id, `task`, `action`
        FROM mail_sort
        WHERE sort_id = :id
        ORDER BY id";
	    
        $res = $this->registry['db']->prepare($sql);
		$param = array(":id" => $id);
		$res->execute($param);
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		for($i=0; $i<count($row); $i++) {
			if ($row[$i]["task"] != null) {
				$row[$i]["task"] = json_decode($row[$i]["task"], true);
			}
		}
		
		return $row;
	}
	
	function addSort($post) {
	    $sql = "SELECT MAX(sort_id) AS max FROM mail_sort";
	    
        $res = $this->registry['db']->prepare($sql);
		$res->execute();
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		$max = $row[0]["max"] + 1;
		
		if ($post["mail_action"] == "move") {
			if ( (isset($post["from"])) and ($post["from"] != null) ) {
				$sql = "INSERT INTO mail_sort (sort_id, type, val, folder_id, action) VALUES (:max, :type, :val, :fid, 'move')";
			    
		        $res = $this->registry['db']->prepare($sql);
				$param = array(":max"=> $max, ":type" => "from", ":val" => $post["from"], ":fid" => $post["folder"]);
				$res->execute($param);
			}
			if ( (isset($post["to"])) and ($post["to"] != null) ) {
				$sql = "INSERT INTO mail_sort (sort_id, type, val, folder_id, action) VALUES (:max, :type, :val, :fid, 'move')";
			    
		        $res = $this->registry['db']->prepare($sql);
				$param = array(":max"=> $max, ":type" => "to", ":val" => $post["to"], ":fid" => $post["folder"]);
				$res->execute($param);
			}
			if ( (isset($post["subject"])) and ($post["subject"] != null) ) {
				$sql = "INSERT INTO mail_sort (sort_id, type, val, folder_id, action) VALUES (:max, :type, :val, :fid, 'move')";
			    
		        $res = $this->registry['db']->prepare($sql);
				$param = array(":max"=> $max, ":type" => "subject", ":val" => $post["subject"], ":fid" => $post["folder"]);
				$res->execute($param);
			}
		} elseif ($post["mail_action"] == "remove") {
			if ( (isset($post["from"])) and ($post["from"] != null) ) {
				$sql = "INSERT INTO mail_sort (sort_id, type, val, action) VALUES (:max, :type, :val, 'remove')";
			    
		        $res = $this->registry['db']->prepare($sql);
				$param = array(":max"=> $max, ":type" => "from", ":val" => $post["from"]);
				$res->execute($param);
			}
			if ( (isset($post["to"])) and ($post["to"] != null) ) {
				$sql = "INSERT INTO mail_sort (sort_id, type, val, action) VALUES (:max, :type, :val, 'remove')";
			    
		        $res = $this->registry['db']->prepare($sql);
				$param = array(":max"=> $max, ":type" => "to", ":val" => $post["to"]);
				$res->execute($param);
			}
			if ( (isset($post["subject"])) and ($post["subject"] != null) ) {
				$sql = "INSERT INTO mail_sort (sort_id, type, val, action) VALUES (:max, :type, :val, 'remove')";
			    
		        $res = $this->registry['db']->prepare($sql);
				$param = array(":max"=> $max, ":type" => "subject", ":val" => $post["subject"]);
				$res->execute($param);
			}
		} elseif ($post["mail_action"] == "task") {
			if ( (isset($post["from"])) and ($post["from"] != null) ) {
				$sql = "INSERT INTO mail_sort (sort_id, type, val, task, action) VALUES (:max, :type, :val, :task, 'task')";
			    
		        $res = $this->registry['db']->prepare($sql);
				$param = array(":max"=> $max, ":type" => "from", ":val" => $post["from"], ":task" => json_encode($post));
				$res->execute($param);
			}
			if ( (isset($post["to"])) and ($post["to"] != null) ) {
				$sql = "INSERT INTO mail_sort (sort_id, type, val, task, action) VALUES (:max, :type, :val, :task, 'task')";
			    
		        $res = $this->registry['db']->prepare($sql);
				$param = array(":max"=> $max, ":type" => "to", ":val" => $post["to"], ":task" => json_encode($post));
				$res->execute($param);
			}
			if ( (isset($post["subject"])) and ($post["subject"] != null) ) {
				$sql = "INSERT INTO mail_sort (sort_id, type, val, task, action) VALUES (:max, :type, :val, :task, 'task')";
			    
		        $res = $this->registry['db']->prepare($sql);
				$param = array(":max"=> $max, ":type" => "subject", ":val" => $post["subject"], ":task" => json_encode($post));
				$res->execute($param);
			}
		}
	}
	
	function delSort($sid) {
		$sort = $this->getSort($sid);
		
	    $sql = "DELETE FROM mail_sort
        WHERE sort_id = :sid";
	    
        $res = $this->registry['db']->prepare($sql);
		$param = array(":sid" => $sid);
		$res->execute($param);
	}

	function delMailDir($fid) {
		$sql = "DELETE FROM mail_folders WHERE id = :fid";

        $res = $this->registry['db']->prepare($sql);
		$param = array(":fid" => $fid);
		$res->execute($param); echo 'fff';
	}
	
	function getMailboxFromId($id) {
		$sql = "SELECT folder
		        FROM mail_folders
		        WHERE id = :id";
		 
		$res = $this->registry['db']->prepare($sql);
		$param = array(":id" => $id);
		$res->execute($param);
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		return $row[0]["folder"];
	}
	
	function getSortsByFolderId($fid) {
	    $sql = "SELECT ms.sort_id, ms.type, ms.val
        FROM mail_sort AS ms
        LEFT JOIN mail_folders AS mf ON (mf.id = ms.folder_id)
        WHERE ms.action = 'move' AND mf.id = :fid";
	    
        $res = $this->registry['db']->prepare($sql);
		$param = array(":fid" => $fid);
		$res->execute($param);
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($row as $part) {
			$this->sort_get_mail_type[] = $part["type"];
			$this->sort_get_mail_val[] = $part["val"];
			$this->sort_get_mail_sort_id[] = $part["sort_id"];
		}
	}
	
	function getSortByTo($val) {
		$row[0]["task"] = null;
		
	    $sql = "SELECT task
        FROM mail_sort
        WHERE type = 'to' AND val = :val";
	    
        $res = $this->registry['db']->prepare($sql);
		$param = array(":val" => $val);
		$res->execute($param);
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		return json_decode($row[0]["task"], true);
	}
	
	function getSortByFrom($val) {
		$row[0]["task"] = null;
		
	    $sql = "SELECT task
        FROM mail_sort
        WHERE type = 'from' AND val = :val";
	    
        $res = $this->registry['db']->prepare($sql);
		$param = array(":val" => $val);
		$res->execute($param);
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		return json_decode($row[0]["task"], true);
	}
	
	function getSortBySubject($val) {
		$row[0]["task"] = null;
		
	    $sql = "SELECT task
        FROM mail_sort
        WHERE type = 'subject' AND val = :val";
	    
        $res = $this->registry['db']->prepare($sql);
		$param = array(":val" => $val);
		$res->execute($param);
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		return json_decode($row[0]["task"], true);
	}
	
	function setDefault($mailbox) {
		$sql = "UPDATE users_mail
		SET `default` = 0";
		
        $res = $this->registry['db']->prepare($sql);
		$res->execute();
		
		$sql = "UPDATE users_mail
		SET `default` = 1
		WHERE email = :mailbox
		LIMIT 2";
		
        $res = $this->registry['db']->prepare($sql);
		$param = array(":mailbox" => $mailbox);
		$res->execute($param);
	}
	
	function saveOutMail($post, $smtp) {
		$sql = "INSERT INTO mail_out (`to`, `subject`, `timestamp`, `email`) VALUES (:to, :subject, NOW(), :email)";
		
        $res = $this->registry['db']->prepare($sql);
		$param = array(":to" => $post["to"], ":subject" => $post["subject"], ":email" => $smtp["email"]);
		$res->execute($param);
		
		$body_html = $post["textfield"];
		$body_text = strip_tags(str_replace("<br>", "\n", $post["textfield"]));
		
		$mid = $this->registry['db']->lastInsertId();
		
		$sql = "INSERT INTO mail_text_out (`mid`, `type`, `text`) VALUES (:mid, :type, :text)";
		
        $res = $this->registry['db']->prepare($sql);
		$param = array(":mid" => $mid, ":type" => "text", ":text" => $body_text);
		$res->execute($param);
		
		$sql = "INSERT INTO mail_text_out (`mid`, `type`, `text`) VALUES (:mid, :type, :text)";
		
        $res = $this->registry['db']->prepare($sql);
		$param = array(":mid" => $mid, ":type" => "html", ":text" => $body_html);
		$res->execute($param);
		
		if (isset($post["attaches"])) {
			foreach($post["attaches"] as $part) {

				if (substr($part, 0, 1) != "/") {
					$filename = mb_substr($part, 0, mb_strlen($part)-mb_strrpos($part, DIRECTORY_SEPARATOR));

					$sql = "SELECT `md5`
			        FROM fm_fs
			        WHERE `filename` = :filename AND pdirid = 1
			        LIMIT 1";
				    
			        $res = $this->registry['db']->prepare($sql);
					$param = array(":filename" => $filename);
					$res->execute($param);
					$row = $res->fetchAll(PDO::FETCH_ASSOC);
				} else {
					$filename = mb_substr($part, mb_strrpos($part, DIRECTORY_SEPARATOR) + 1, mb_strlen($part)-mb_strrpos($part, DIRECTORY_SEPARATOR));
					$path = mb_substr($part, 0, mb_strrpos($part, DIRECTORY_SEPARATOR));

					if ($path . "/" == $this->registry["rootPublic"] . $this->registry["path"]["upload"]) {
						$sql = "SELECT `md5`
				        FROM fm_fs
				        WHERE `filename` = :filename AND pdirid = 0
				        LIMIT 1";

				        $res = $this->registry['db']->prepare($sql);
						$param = array(":filename" => $filename);
						$res->execute($param);
						$row = $res->fetchAll(PDO::FETCH_ASSOC);
					} else {
						$path = mb_substr($path, mb_strrpos($path, DIRECTORY_SEPARATOR) + 1, mb_strlen($path)-mb_strrpos($path, DIRECTORY_SEPARATOR));
						
						$sql = "SELECT f.md5
				        FROM fm_fs AS f
				        LEFT JOIN fm_dirs AS d ON (f.pdirid = d.id)
				        WHERE f.filename = :filename AND d.name = :path
				        LIMIT 1";

						$res = $this->registry['db']->prepare($sql);
						$param = array(":filename" => $filename, ":path" => $path);
						$res->execute($param);
						$row = $res->fetchAll(PDO::FETCH_ASSOC);
					}
				}
				
				$sql = "INSERT INTO mail_attach_out (`mid`, `md5`) VALUES (:mid, :md5)";
					
				$res = $this->registry['db']->prepare($sql);
				$param = array(":mid" => $mid, ":md5" => $row[0]["md5"]);
				$res->execute($param);
			}
		}
	}
	
	function setRead($fid) {
		$row = array();
		$sql_inc = null;
		$sql_where = null;

		if ($fid != "main") {
			$this->getSortsByFolderId($fid);

			if (isset($this->sort_get_mail_type)) {
				for($i=0; $i<count($this->sort_get_mail_type); $i++) {
					if ($this->sort_get_mail_type[$i] == "to") {
						$sql_inc[$i] = "LEFT JOIN mail_sort AS ms". $i ." ON (ms". $i .".val = mail.to)";
						$sql_where[$i] = "(ms". $i .".type = 'to' AND mail.to = '" . $this->sort_get_mail_val[$i] . "')";
					}
					if ($this->sort_get_mail_type[$i] == "from") {
						$sql_inc[$i] = "LEFT JOIN mail_sort AS ms". $i ." ON (ms". $i .".val = mail.email)";
						$sql_where[$i] = "(ms". $i .".type = 'from' AND mail.email = '" . $this->sort_get_mail_val[$i] . "')";
					}
					if ($this->sort_get_mail_type[$i] == "subject") {
						$sql_inc[$i] = "LEFT JOIN mail_sort AS ms". $i ." ON (ms". $i .".val LIKE '%" . $this->sort_get_mail_val[$i] . "%')";
						$sql_where[$i] = "(ms". $i .".type = 'subject' AND mail.subject LIKE '%" . $this->sort_get_mail_val[$i] . "%')";
					}
				}
			}
	
			$sql_inc = implode($sql_inc, " ");
			$sql_where = "AND (" . implode($sql_where, " OR ") . ")";
		
		    $sql = "UPDATE
	        `mail` 
	        " . $sql_inc . " 
	        SET `read` = 1
	        WHERE mail.status = '0' " . $sql_where . " ";
		    
	        $res = $this->registry['db']->prepare($sql);
			$res->execute();
		} else {
		    $arr = $this->getMails();
		    
		    foreach($arr as $part) {
			    $sql = "UPDATE
		        `mail` 
		        SET `read` = 1
		        WHERE mail.status = '0' AND mail.id = :mid LIMIT 1";
			    
		        $res = $this->registry['db']->prepare($sql);
				$param = array(":mid" => $part["id"]);
				$res->execute($param);
		    }
		}
	}
	
	function clearFolder($fid) {
		$row = array();
		$sql_inc = null;
		$sql_where = null;

		if ($fid != "main") {
			$this->getSortsByFolderId($fid);
			$arr = $this->getMailsSort();
		    
		    foreach($arr as $part) {
			    $this->delMail($part["id"]);
		    }
		} else {
		    $arr = $this->getMails();
		    
		    foreach($arr as $part) {
			    $this->delMail($part["id"]);
		    }
		}
	}
	
	function getAttachFileMD5($filename) {
		$sql = "SELECT f.md5
        FROM fm_fs AS f
        WHERE f.filename = :filename AND f.pdirid = 1
        ORDER BY f.id DESC
        LIMIT 1";
		
		$res = $this->registry['db']->prepare($sql);
		$param = array(":filename" => $filename);
		$res->execute($param);
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		if (count($row) > 0) {
			return $row[0]["md5"];
		} else {
			return FALSE;
		}
	}
	
	function getFileMD5($filename) {
		$fname = mb_substr($filename, mb_strrpos($filename, DIRECTORY_SEPARATOR) + 1, mb_strlen($filename)-mb_strrpos($filename, DIRECTORY_SEPARATOR));
		
		$path = mb_substr($filename, 0, mb_strrpos($filename, DIRECTORY_SEPARATOR));

		if ($path . "/" == $this->registry["rootPublic"] . $this->registry["path"]["upload"]) {

			$sql = "SELECT f.md5
	        FROM fm_fs AS f
	        WHERE f.filename = :filename AND f.pdirid = 0
	        ORDER BY f.id DESC
	        LIMIT 1";
		    
	        $res = $this->registry['db']->prepare($sql);
			$param = array(":filename" => $fname);
			$res->execute($param);
			$row = $res->fetchAll(PDO::FETCH_ASSOC);
		} else {
			$path = mb_substr($path, mb_strrpos($path, DIRECTORY_SEPARATOR) + 1, mb_strlen($path)-mb_strrpos($path, DIRECTORY_SEPARATOR));

		    $sql = "SELECT f.md5
	        FROM fm_fs AS f
	        LEFT JOIN fm_dirs AS d ON (f.pdirid = d.id)
	        WHERE f.filename = :filename AND d.name = :path
	        ORDER BY f.id DESC
	        LIMIT 1";
		    
	        $res = $this->registry['db']->prepare($sql);
			$param = array(":filename" => $fname, ":path" => $path);
			$res->execute($param);
			$row = $res->fetchAll(PDO::FETCH_ASSOC);
		}

		if (count($row) > 0) {
			return $row[0]["md5"];
		} else {
			return FALSE;
		}
	}
	
	function getMailText($mid) {
		$result = array();
		
		$sql = "SELECT `type`, `text`
        FROM mail_text
        WHERE mid = :mid";
	    
        $res = $this->registry['db']->prepare($sql);
		$param = array(":mid" => $mid);
		$res->execute($param);
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		if (count($row) > 0) {
			$flag = false;
			foreach($row as $part) {
				if ($part["type"] == "html") { 
					$result = $part["text"];
					$flag = true;
				}
			}
			
			if (!$flag) { $result = $part["text"]; };
		}
		
		return $result;
	}
	
	function addSignature($bid, $signature) {
		$sql = "INSERT INTO users_signature (bid, `signature`) VALUES (:bid, :signature)";
		 
		$res = $this->registry['db']->prepare($sql);
		$param = array(":bid" => $bid, ":signature" => $signature);
		$res->execute($param);
	}
	
	function editSignature($email, $signature) {
		$sql = "SELECT id FROM users_mail WHERE type = 'in' AND email = :email LIMIT 1";
		$res = $this->registry['db']->prepare($sql);
		$param = array(":email" => $email);
		$res->execute($param);
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		$sql = "UPDATE users_signature SET `signature` = :signature WHERE bid = :bid LIMIT 1";
		 
		$res = $this->registry['db']->prepare($sql);
		$param = array(":bid" => $row[0]["id"], ":signature" => $signature);
		$res->execute($param);
	}
	
	function getSignature($email) {
		$sql = "SELECT us.signature
		FROM users_signature AS us
		LEFT JOIN users_mail AS um ON (um.id = us.bid)
		WHERE um.type = 'in' AND um.email = :email LIMIT 1";
			
		$res = $this->registry['db']->prepare($sql);
		$param = array(":email" => $email);
		$res->execute($param);
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		if (count($row) == 1) {
			return $row[0]["signature"];
		} else {
			return null;
		}
	}
	
	function getEmailFromId($id) {
		$sql = "SELECT email FROM users_mail WHERE id = :id LIMIT 1";
		$res = $this->registry['db']->prepare($sql);
		$param = array(":id" => $id);
		$res->execute($param);
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		return $row[0]["email"];
	}
}
?>
<?php
class Model_Contacts extends Engine_Model {
	function getGroups() {
		$sql = "SELECT id, `name`
		        FROM mail_contacts_groups
		        ORDER BY name";
			
		$res = $this->registry['db']->prepare($sql);
		$res->execute();
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
	
		return $row;
	}
	
	function addGroup($name) {
		$sql = "INSERT INTO mail_contacts_groups (`name`) VALUES (:name)";
			
		$res = $this->registry['db']->prepare($sql);
		$params = array(":name" => $name);
		$res->execute($params);
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
	}
	
	function delGroup($id) {
		$sql = "DELETE FROM mail_contacts_groups WHERE id = :id LIMIT 1";
			
		$res = $this->registry['db']->prepare($sql);
		$params = array(":id" => $id);
		$res->execute($params);
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
	}
	
	function editGroup($id, $name) {
		$sql = "UPDATE mail_contacts_groups SET `name` = :name WHERE id = :id";
			
		$res = $this->registry['db']->prepare($sql);
		$params = array(":id" => $id, ":name" => $name);
		$res->execute($params);
	}
	
	function getGroupName($id) {
		$sql = "SELECT `name`
		        FROM mail_contacts_groups
		        WHERE id = :id";
			
		$res = $this->registry['db']->prepare($sql);
		$params = array(":id" => $id);
		$res->execute($params);
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
	
		return $row[0]["name"];
	}
	
	function getContacts($group = 0) {
		$contacts = array();
		
		$sql = "SELECT id, `email`
		        FROM mail_contacts
		        WHERE gid = :gid";
			
		$res = $this->registry['db']->prepare($sql);
		$params = array(":gid" => $group);
		$res->execute($params);
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($row as $part) {
			$sql = "SELECT mcf.name, mcv.val
			        FROM mail_contacts_vals AS mcv
			        LEFT JOIN mail_contacts AS mc ON (mc.id = mcv.cid)
			        LEFT JOIN mail_contacts_fields AS mcf ON (mcf.id = mcv.fid)
			        WHERE mc.id = :id";
				
			$res = $this->registry['db']->prepare($sql);
			$params = array(":id" => $part["id"]);
			$res->execute($params);
			$data = $res->fetchAll(PDO::FETCH_ASSOC);
			
			$contacts[$part["id"]] = $data;
			$contacts[$part["id"]]["email"] = $part["email"];
		}

		return $contacts;
	}
	
	function getContactFields() {
		$sql = "SELECT id, `name`
		        FROM mail_contacts_fields";
		
		$res = $this->registry['db']->prepare($sql);
		$res->execute();
		$row = $res->fetchAll(PDO::FETCH_ASSOC);
		
		return $row;
	}
	
	function addContact($post) {
		$sql = "INSERT INTO mail_contacts (gid, email) VALUES (:gid, :email)";
		
		$res = $this->registry['db']->prepare($sql);
		$params = array(":gid" => $post["group"], "email" => $post["email"]);
		$res->execute($params);
		
		$cid = $this->registry['db']->lastInsertId();
		
		unset($post["email"]); unset($post["submit"]); unset($post["group"]);
		
		foreach($post as $key=>$val) {
			$sql = "INSERT INTO mail_contacts_vals (cid, fid, val) VALUES (:cid, :fid, :val)";
			
			$res = $this->registry['db']->prepare($sql);
			$params = array(":cid" => $cid, ":fid" => $key, ":val" => $val);
			$res->execute($params);
		}
	}
	
	function editContact($post) {
		$sql = "UPDATE mail_contacts SET gid = :gid WHERE email = :email  LIMIT 1";
	
		$res = $this->registry['db']->prepare($sql);
		$params = array(":gid" => $post["group"], "email" => $post["email"]);
		$res->execute($params);
	
		$sql = "SELECT id FROM mail_contacts WHERE email = :email LIMIT 1";
		
		$res = $this->registry['db']->prepare($sql);
		$params = array(":email" => $post["email"]);
		$res->execute($params);
		$cid = $res->fetchAll(PDO::FETCH_ASSOC);
		
		$cid = $cid[0]["id"];
	
		unset($post["email"]); unset($post["submit"]); unset($post["group"]);
	
		foreach($post as $key=>$val) {
			$sql = "UPDATE mail_contacts_vals SET val = :val WHERE cid = :cid AND fid = :fid";
				
			$res = $this->registry['db']->prepare($sql);
			$params = array(":cid" => $cid, ":fid" => $key, ":val" => $val);
			$res->execute($params);
		}
	}
	
	function delContact($email) {
		$sql = "SELECT id FROM mail_contacts WHERE email = :email LIMIT 1";
		
		$res = $this->registry['db']->prepare($sql);
		$params = array(":email" => $email);
		$res->execute($params);
		$cid = $res->fetchAll(PDO::FETCH_ASSOC);
		
		$sql = "DELETE FROM mail_contacts WHERE email = :email LIMIT 1";
		
		$res = $this->registry['db']->prepare($sql);
		$params = array(":email" => $email);
		$res->execute($params);
		
		$sql = "DELETE FROM mail_contacts_vals WHERE cid = :cid";
		
		$res = $this->registry['db']->prepare($sql);
		$params = array(":cid" => $cid[0]["id"]);
		$res->execute($params);
	}
	
	function getContact($email) {
		$sql = "SELECT id FROM mail_contacts WHERE email = :email LIMIT 1";
		
		$res = $this->registry['db']->prepare($sql);
		$params = array(":email" => $email);
		$res->execute($params);
		$cid = $res->fetchAll(PDO::FETCH_ASSOC);
		
		if ( (isset($cid[0]["id"])) and ($cid[0]["id"] > 0) ) {
			$sql = "SELECT mcv.fid, mcv.val, mcf.name
			FROM mail_contacts_vals AS mcv
			LEFT JOIN mail_contacts_fields AS mcf ON (mcf.id = mcv.fid)
			WHERE mcv.cid = :cid";
			
			$res = $this->registry['db']->prepare($sql);
			$params = array(":cid" => $cid[0]["id"]);
			$res->execute($params);
			$row = $res->fetchAll(PDO::FETCH_ASSOC);
			
			return $row;
		}
	}
}
?>
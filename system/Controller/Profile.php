<?php
class Controller_Profile extends Engine_Controller {
	public function index() {
		$this->view->setTitle("Профиль");
		
		if (isset($_POST['editprofile'])) {
			$data = $this->registry["ui"];
		
			$validate = new Model_Validate();
			$ui = new Model_Ui();
		
			$err = array();
			if ($_POST["login"] != $this->registry["ui"]["login"]) {
				if ($txt = $validate->login($_POST["login"])) {
					$err[] = $txt;
				};
			}

			if ($txt = $validate->name($_POST["name"])) {
				$err[] = $txt;
			};
			if ($txt = $validate->soname($_POST["soname"])) {
				$err[] = $txt;
			};
			if ($data["pass"] != $_POST["pass"]) {
				if ($txt = $validate->password($_POST["pass"])) {
					$err[] = $txt;
				};
			}
		
			if (count($err) == 0) {
		
				$uid = $ui->editUser($this->registry["ui"]["id"], $_POST["login"], $_POST["name"], $_POST["soname"]);
				if ($data["pass"] != $_POST["pass"]) {
					$ui->editUserPass($this->registry["ui"]["id"], $_POST["pass"]);
				}
		
				$this->view->refresh(array("timer" => "1", "url" => "profile/profile/"));
			} else {
				$this->view->profile(array("err" => $err, "post" => $_POST));
			}
		} else {
			$data = $this->registry["ui"];
		
			$this->view->profile(array("post" => $data));
		}
	}
}
?>
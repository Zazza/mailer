<?php
class Controller_Sort extends Engine_Controller {

	function index() {
		$mailClass = new Model_Mail();

		$this->view->setTitle("Правила обработки для почты");
		
		if (isset($_POST["submit"])) {
			$validate = new Model_Validate();
			
			$err = array();

			if (isset($_POST["checkbox_from"])) {
				if ($txt = $validate->email($_POST["from"])) { $err[] = $txt; }
			} else {
				$_POST["from"] = null;
			}
			if (isset($_POST["checkbox_to"])) {
				if ($txt = $validate->email($_POST["to"])) { $err[] = $txt; }
			} else {
				$_POST["to"] = null;
			}
			if (isset($_POST["checkbox_subject"])) {
				if ($_POST["subject"] == "") { $err[] = 'Поле "Тема" не может быть пустой'; }
			} else {
				$_POST["subject"] = null;
			}
			
			if ( (!isset($_POST["checkbox_from"])) and (!isset($_POST["checkbox_to"])) and (!isset($_POST["checkbox_subject"])) ) {
				$err[] = 'Не указано ни одного критерия для сортировки';
			}
			
			if (count($err) == 0) {
				$mailClass->addSort($_POST);
				
				$this->view->refresh(array("timer" => "1", "url" => "sort/"));
			} else {
				if (isset($_GET["mid"])) {
					$mail = $mailClass->getMailFromId($_GET["mid"]);
				}

				$this->view->mail_addsort(array("err" => $err, "mail" => $mail, "folders" => $this->folders));
			}

		} elseif (isset($_POST["edit_sort"])) {
			
			if ( (isset($_GET["id"])) and (is_numeric($_GET["id"])) ) {
				$param = $mailClass->getSort($_GET["id"]);

				$validate = new Model_Validate();
				
				$err = array();
	
				if ( (isset($_POST["from"])) and ($_POST["from"] != null) ) {
					if ($txt = $validate->email($_POST["from"])) { $err[] = $txt; }
						else { $sort["type"] = "from"; $sort["val"] = $_POST["from"]; $sort["folder_id"] = $_POST["folder"]; }
				} 
				if ( (isset($_POST["to"])) and ($_POST["to"] != null) ) {
					if ($txt = $validate->email($_POST["to"])) { $err[] = $txt; }
						else { $sort["type"] = "to"; $sort["val"] = $_POST["to"]; $sort["folder_id"] = $_POST["folder"]; }
				}
				if ( (isset($_POST["subject"])) and ($_POST["subject"] != null) ) {
					if ($_POST["subject"] == "") { $err[] = 'Поле "Тема" не может быть пустой'; }
						else { $sort["type"] = "subject"; $sort["val"] = $_POST["subject"]; $sort["folder_id"] = $_POST["folder"]; }
				}
				
				if ( (!isset($_POST["from"])) and (!isset($_POST["to"])) and (!isset($_POST["subject"])) ) {
					$err[] = 'Не указано ни одного критерия для сортировки';
				}
				
				if (count($err) == 0) {
					$mailClass->delSort($_GET["id"]);
					$mailClass->addSort($_POST);
					
					$this->view->refresh(array("timer" => "1", "url" => "sort/"));
				} else {
					$this->view->mail_editsort(array("err" => $err, "sort" => $param, "folders" => $this->folders));
				}
			}
		} elseif ( (isset($_GET["mid"])) or (isset($_GET["add"])) ) {
			$mail = array();
			
			if ( (isset($_GET["mid"])) and (is_numeric($_GET["mid"])) ) {
				$mail = $mailClass->getMailFromId($_GET["mid"]);
			}
			
			if ( (isset($_GET["id"])) and (is_numeric($_GET["id"])) ) {
				$mail = $mailClass->getSort($_GET["id"]);
			}

			$this->view->mail_addsort(array("mail" => $mail, "folders" => $this->folders));
		} elseif (isset($_GET["id"])) {
			if ( (isset($_GET["id"])) and (is_numeric($_GET["id"])) ) {
				$sort = $mailClass->getSort($_GET["id"]);

				$this->view->mail_editsort(array("sort" => $sort, "folders" => $this->folders));
			}
		} else {
			$list = $mailClass->getSorts();
			$this->view->mail_sorts(array("list" => $list));
		}		
	}
}
?>
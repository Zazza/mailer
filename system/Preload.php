<?php
class Preload extends Engine_Bootstrap {
    function run() {
        $view = new View_Index();
        $this->registry->set('view', $view);
		
		$ui = new Model_Ui();

		$loginSession = & $_SESSION["login"];
		if (isset($loginSession["id"])) {
			$ui->getInfo($loginSession);
			
			$mailClass = new Model_Mail();
			$folders = $mailClass->getFolders();
			
			$this->registry["view"]->setFolders($folders);
		} else {
			$login = new Controller_Login();
			$login->index();
			 
			exit();
		}
    }
}
?>
<?php
class View_Index extends Engine_View {

    private $content = null;
    private $folders = null;
    private $newMail = null;
    
    public function setLeftContent($text) {
        $this->leftBlock .= $text;
    }

	public function showPage() {
		$template = $this->main->loadTemplate("page.html");
		$template->display(array("registry" => $this->registry,
			"title" => $this->title,
	        "main_content" => $this->mainContent,
			"folders" => $this->folders,
			"newMail" => $this->newMail));
	}
	
	public function setContent($content) {
		$this->content .= $content;
	}
	
	public function setFolders($folders) {
		$mail = 0;
		foreach($folders as $part) {
			$mail += $part["count"];
		}
		$mail += $this->registry["mainCount"];
		if ($mail > 0) {
			$this->newMail = "<span style='color: green'>[" . $mail . "]</span>";
		} else {
			$this->newMail = "<span>[" . $mail . "]</span>";
		}
		
		$this->folders = $folders;
	}
	
	public function getFolders() {
		return $this->folders;
	}
}
?>

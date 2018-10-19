<?php
	
if(isset($_POST["json"])) {
	  file_put_contents($_POST["filepath"] . '/' . $_POST["filename"]. '_' . $_POST["userId"] . '.json', $_POST["json"]);
}




?>
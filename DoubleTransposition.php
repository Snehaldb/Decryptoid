<?php


	session_start();

	require_once "config.php";


	$cipher = check_input($_POST["cipher"]);
	$key = check_input($_POST["key"]);
	$text = check_input($_POST["encrypttext"]);
	$endecrypt = check_input($_POST["radio1"]);
	if(isset($_SESSION["loggedin"])){
		$email = $_SESSION['email'];
	}

	if ($_FILES['fileToUpload']['type'] != "" && $_POST["encrypttext"] != "") {
		$val = "false";
		$input_err = "Please upload either File or Text. Do not enter both";
		echo json_encode(array($val, $input_err));
	        
	}elseif ($_FILES['fileToUpload']['type'] == "" && $_POST["encrypttext"] == "") {
		$val = "false";
		$input_err = "Please upload File or enter Text";
		echo json_encode(array($val, $input_err));
 
	}
	else{
	    $cipherText;
		$plainText;
	    if ($text != "") {
	        $encrypttext = $text;  
	        $encrypttext = check_input($encrypttext);
	        $encrypttext_err = validate_text($encrypttext, $key);
	        if(isset($_SESSION["loggedin"])){
	        	$insertValues = insertDataMysql($link,$encrypttext, $cipher); 
	        }
	        if ($encrypttext_err  !== ""){
	        	$val = "false";
				$input_err = $encrypttext_err;
				echo json_encode(array($val, $input_err));
	        }else  {
		        if ($endecrypt == "encrypt") {
		            $val = "true";
					$input = encryptDT($encrypttext, $key);
					echo json_encode(array($val, $input));     	
		        } else{
		        	$val = "true";
					$input = decryptDT($encrypttext, $key);
					echo json_encode(array($val, $input));  
		        } 
	        }       
	         
	    }elseif ($_FILES['fileToUpload']['name'] != "") {
	        $upfilename = $_FILES['fileToUpload']['name'];
	        $upfilename_err = validate_file($upfilename, $key);
	        if ($upfilename_err != "") {
	        	$val = "false";
				$input_err = $upfilename_err;
				echo json_encode(array($val, $input_err));
	        }else {
	            $path = "./".basename($_FILES['fileToUpload']['name']);     
	            move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $path);
	            $upfilename = $_FILES['fileToUpload']['name'];
	              
	            if ($fh = fopen($upfilename, 'r')) {
	            	$filecontents = file_get_contents($upfilename);
	            	if(isset($_SESSION["loggedin"])){
	                	$insertValues = insertDataMysql($link,$filecontents, $cipher);
	                } 
					$filecontents = file_get_contents($upfilename);
					if ($endecrypt == "encrypt") {
		                $val = "true";
						$input = encryptDT($filecontents, $key);
						echo json_encode(array($val, $input));     	
		            } else{
		            	$val = "true";
						$input = decryptDT($filecontents, $key);
						echo json_encode(array($val, $input));  
		            }  
	  
	                fclose($fh);
	            }else{
	            	$val = "false";
					$input_err = "Unable to read file";
					echo json_encode(array($val, $input_err));
	            }     
	        }

	    }
   //readmyFile($link, $text); 
	}

	class Pair
	{
		public $Key;
		public $Value;
	}

	function compare($first, $second) {
		return strcmp($first->Value, $second->Value);
	}

	function ShiftIndexes($key)
	{
		$keyLen = strlen($key);
		$indexes = array();
		$sortedKey = array();
		$i;

		for ($i = 0; $i < $keyLen; ++$i) {
			$pair = new Pair();
			$pair->Key = $i;
			$pair->Value = $key[$i];
			$sortedKey[] = $pair;
		}

		usort($sortedKey, 'compare');
		$i = 0;

		for ($i = 0; $i < $keyLen; ++$i)
			$indexes[$sortedKey[$i]->Key] = $i;

		return $indexes;
	}

	function encryptDT($input, $key)
	{

		$keys = explode(" ", $key);
		$key1 = $keys[0];
		$key2 = $keys[1];
		$result1 = encryption($input, $key1);
		$result2 = encryption($result1, $key2);
		return $result2;
	}

	function decryptDT($input, $key)
	{
		$keys = explode(" ", $key);
		$key1 = $keys[0];
		$key2 = $keys[1];
		$result1 = decryption($input, $key1);
		$result2 = decryption($result1, $key2);
		return $result2;
	}


	function encryption($input, $key)
	{
		$output = "";
		$padChar = 'x';
		$len= strlen($input);
		$keyLen = strlen($key);
		$input = ($len % $keyLen == 0) ? $input : str_pad($input, $len - ($len % $keyLen) + $keyLen, $padChar, STR_PAD_RIGHT);
		$len = strlen($input);
		$totalCols = $keyLen;
		$totalRows = ceil($len / $totalCols);
		$rows = array(array());
		$cols = array(array());
		$sortedColChars = array(array());
		$currRow = 0; 
		$currCol = 0; 
		$i = 0; $j = 0;

		$shiftIndexes = ShiftIndexes($key);

		for ($i = 0; $i < $len; ++$i)
		{
			$currRow = $i / $totalCols;
			$currCol = $i % $totalCols;
			$rows[$currRow][$currCol] = $input[$i];
		}

		for ($i = 0; $i < $totalRows; ++$i)
			for ($j = 0; $j < $totalCols; ++$j)
				$cols[$j][$i] = $rows[$i][$j];

		for ($i = 0; $i < $totalCols; ++$i)
			for ($j = 0; $j < $totalRows; ++$j)
				$sortedColChars[$shiftIndexes[$i]][$j] = $cols[$i][$j];

		for ($i = 0; $i < $len; ++$i)
		{
			$currRow = $i / $totalRows;
			$currCol = $i % $totalRows;
			$output .= $sortedColChars[$currRow][$currCol];
		}

		return $output;
	}

	function decryption($input, $key)
	{
		$output = "";
		$keyLen = strlen($key);
		$len = strlen($input);
		$totalCols = ceil($len / $keyLen);
		$totalRows = $keyLen;
		$rows = array(array());
		$cols = array(array());
		$unsortedColChars = array(array());
		$currRow = 0; 
		$currCol = 0; 
		$shiftIndexes = ShiftIndexes($key);
		$i = 0; $j = 0;

		for ($i = 0; $i < $len; ++$i)
		{
			$currRow = $i / $totalCols;
			$currCol = $i % $totalCols;
			$rows[$currRow][$currCol] = $input[$i];
		}

		for ($i = 0; $i < $totalRows; ++$i)
			for ($j = 0; $j < $totalCols; ++$j)
				$cols[$j][$i] = $rows[$i][$j];

		for ($i = 0; $i < $totalCols; ++$i)
			for ($j = 0; $j < $totalRows; ++$j)
				$unsortedColChars[$i][$j] = $cols[$i][$shiftIndexes[$j]];

		for ($i = 0; $i < $len; ++$i)
		{
			$currRow = $i / $totalRows;
			$currCol = $i % $totalRows;
			$output .= $unsortedColChars[$currRow][$currCol];
		}

		return $output;
	}

	function insertDataMysql($link,$encrypttext,$cipher){
	 
    	$intext = mysqli_real_escape_string($link, $encrypttext);
		$timestamp = date("Y-m-d H:i:s");
		$cipher = mysqli_real_escape_string($link, $cipher);
		$email = $_SESSION['email'];
		$result = mysqli_query($link,"SELECT id FROM user_info where email = '$email'");
		$row = mysqli_fetch_row($result);
		$userid = $row[0];
		$sql = "INSERT INTO filedetails (userid, cipher, filecontent, currtimestamp) VALUES ('$userid', '$cipher','$intext', '$timestamp')";

		if(!mysqli_query($link, $sql)){
    
            echo "Something went wrong. Please try again later.";   
        }
      
    	mysqli_close($link);
	}


		//Validating functions
	function check_input($string){
	    if(get_magic_quotes_gpc()) 
	        $string = stripslashes($string);
	    return htmlentities($string);
	}
	function validate_text($encrypttext,$key){
		if ($key == "" || is_string($key) == FALSE  || strpos($key, " ") == FALSE) {
	        return "Please enter two alphabetic keys separated by a space <br>";
	    }else if (3 > strlen($key) || strlen($key) > 41) {
	        return "Each key should be between 1 to 20 characters long";
	    }
	    return "";
	}
	function validate_file($upfilename, $key){
		if ($key == "" || is_string($key) == FALSE || strpos($key, " ") == FALSE) {
	        return "Please enter two alphabetic keys separated by a space <br>";
	    }else if (3 > strlen($key) || strlen($key) > 41) {
	        return "Each key should be between 1 to 20 characters long";
	    }else if ($upfilename == "") {
	        return "Please upload file <br>";
	    }elseif ($_FILES['fileToUpload']['type'] != "text/plain"){
	        return "Upload text file only";
	    }
	    return "";
	}

?>
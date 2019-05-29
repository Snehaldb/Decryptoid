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
				$input = EncryptText($encrypttext, $key, $cipherText);
				echo json_encode(array($val, $input));     	
	        } else{
	        	$val = "true";
				$input = DecryptText($encrypttext, $key, $plainText);
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
				
				if ($endecrypt == "encrypt") {
	                $val = "true";
					$input = EncryptText($filecontents, $key, $cipherText);
					echo json_encode(array($val, $input));     	
	            } else{
	            	$val = "true";
					$input = DecryptText($filecontents, $key, $plainText);
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


function SubstitutionCipher($input, $oldAlphabet, $newAlphabet, &$output){
	$output = "";
	$inputLen = strlen($input);

	if (strlen($oldAlphabet) != strlen($newAlphabet))
		return false;

	for ($i = 0; $i < $inputLen; ++$i){
		$oldCharIndex = strpos($oldAlphabet, strtolower($input[$i]));

		if ($oldCharIndex !== false)
			$output .= ctype_upper($input[$i]) ? strtoupper($newAlphabet[$oldCharIndex]) : $newAlphabet[$oldCharIndex];
		else
			$output .= $input[$i];
	}

	return $output;
}

function EncryptText($input, $cipherKey, &$output){
	$plainKey = "abcdefghijklmnopqrstuvwxyz";
	return SubstitutionCipher($input, $plainKey, $cipherKey, $output); 
}

function DecryptText($input, $cipherKey, &$output){
	$plainKey = "abcdefghijklmnopqrstuvwxyz";
	return SubstitutionCipher($input, $cipherKey, $plainKey, $output);
}

//Validating functions
function check_input($string){
    if(get_magic_quotes_gpc()) 
        $string = stripslashes($string);
    return htmlentities($string);
}
function validate_text($encrypttext,$key){
	if ($key == "") {
        return "Please enter key <br>";
    }else if (strlen($key) < 26) {
        return "Key should be 26 characters long";
    }
    return "";
}
function validate_file($upfilename, $key){
	if ($key == "") {
        return "Please enter key <br>";
    }else if (strlen($key) < 26) {
        return "Key should be 26 characters long";
    }else if ($upfilename == "") {
        return "Please upload file <br>";
    }elseif ($_FILES['fileToUpload']['type'] != "text/plain"){
        return "Upload text file only";
    }
    return "";
}
?>
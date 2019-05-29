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
					$input = encryptDES($encrypttext, $key);
					echo json_encode(array($val, $input));     	
		        } else{
		        	$val = "true";
					$input = decryptDES($encrypttext, $key);
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
						$input = encryptDES($filecontents, $key);
						echo json_encode(array($val, $input));     	
		            } else{
		            	$val = "true";
						$input = decryptDES($filecontents, $key);
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


	function encryptDES($input, $cipherKey){
    	$result = DESAlgorithm($cipherKey, $input, 1);
    	return stringToHex($result);
  	}

  	function decryptDES($input, $cipherKey){
    	$result = DESAlgorithm($cipherKey, hexToString($input), 0);
    	return $result;
  	}

  function DESAlgorithm($key, $input, $encrypt) {

      $sp1 = array (0x1010400,0,0x10000,0x1010404,0x1010004,0x10404,0x4,0x10000,0x400,0x1010400,0x1010404,0x400,0x1000404,0x1010004,0x1000000,0x4,0x404,0x1000400,0x1000400,0x10400,0x10400,0x1010000,0x1010000,0x1000404,0x10004,0x1000004,0x1000004,0x10004,0,0x404,0x10404,0x1000000,0x10000,0x1010404,0x4,0x1010000,0x1010400,0x1000000,0x1000000,0x400,0x1010004,0x10000,0x10400,0x1000004,0x400,0x4,0x1000404,0x10404,0x1010404,0x10004,0x1010000,0x1000404,0x1000004,0x404,0x10404,0x1010400,0x404,0x1000400,0x1000400,0,0x10004,0x10400,0,0x1010004);
      $sp2 = array (-0x7fef7fe0,-0x7fff8000,0x8000,0x108020,0x100000,0x20,-0x7fefffe0,-0x7fff7fe0,-0x7fffffe0,-0x7fef7fe0,-0x7fef8000,-0x80000000,-0x7fff8000,0x100000,0x20,-0x7fefffe0,0x108000,0x100020,-0x7fff7fe0,0,-0x80000000,0x8000,0x108020,-0x7ff00000,0x100020,-0x7fffffe0,0,0x108000,0x8020,-0x7fef8000,-0x7ff00000,0x8020,0,0x108020,-0x7fefffe0,0x100000,-0x7fff7fe0,-0x7ff00000,-0x7fef8000,0x8000,-0x7ff00000,-0x7fff8000,0x20,-0x7fef7fe0,0x108020,0x20,0x8000,-0x80000000,0x8020,-0x7fef8000,0x100000,-0x7fffffe0,0x100020,-0x7fff7fe0,-0x7fffffe0,0x100020,0x108000,0,-0x7fff8000,0x8020,-0x80000000,-0x7fefffe0,-0x7fef7fe0,0x108000);
      $sp3 = array (0x208,0x8020200,0,0x8020008,0x8000200,0,0x20208,0x8000200,0x20008,0x8000008,0x8000008,0x20000,0x8020208,0x20008,0x8020000,0x208,0x8000000,0x8,0x8020200,0x200,0x20200,0x8020000,0x8020008,0x20208,0x8000208,0x20200,0x20000,0x8000208,0x8,0x8020208,0x200,0x8000000,0x8020200,0x8000000,0x20008,0x208,0x20000,0x8020200,0x8000200,0,0x200,0x20008,0x8020208,0x8000200,0x8000008,0x200,0,0x8020008,0x8000208,0x20000,0x8000000,0x8020208,0x8,0x20208,0x20200,0x8000008,0x8020000,0x8000208,0x208,0x8020000,0x20208,0x8,0x8020008,0x20200);
      $sp4 = array (0x802001,0x2081,0x2081,0x80,0x802080,0x800081,0x800001,0x2001,0,0x802000,0x802000,0x802081,0x81,0,0x800080,0x800001,0x1,0x2000,0x800000,0x802001,0x80,0x800000,0x2001,0x2080,0x800081,0x1,0x2080,0x800080,0x2000,0x802080,0x802081,0x81,0x800080,0x800001,0x802000,0x802081,0x81,0,0,0x802000,0x2080,0x800080,0x800081,0x1,0x802001,0x2081,0x2081,0x80,0x802081,0x81,0x1,0x2000,0x800001,0x2001,0x802080,0x800081,0x2001,0x2080,0x800000,0x802001,0x80,0x800000,0x2000,0x802080);
      $sp5 = array (0x100,0x2080100,0x2080000,0x42000100,0x80000,0x100,0x40000000,0x2080000,0x40080100,0x80000,0x2000100,0x40080100,0x42000100,0x42080000,0x80100,0x40000000,0x2000000,0x40080000,0x40080000,0,0x40000100,0x42080100,0x42080100,0x2000100,0x42080000,0x40000100,0,0x42000000,0x2080100,0x2000000,0x42000000,0x80100,0x80000,0x42000100,0x100,0x2000000,0x40000000,0x2080000,0x42000100,0x40080100,0x2000100,0x40000000,0x42080000,0x2080100,0x40080100,0x100,0x2000000,0x42080000,0x42080100,0x80100,0x42000000,0x42080100,0x2080000,0,0x40080000,0x42000000,0x80100,0x2000100,0x40000100,0x80000,0,0x40080000,0x2080100,0x40000100);
      $sp6 = array (0x20000010,0x20400000,0x4000,0x20404010,0x20400000,0x10,0x20404010,0x400000,0x20004000,0x404010,0x400000,0x20000010,0x400010,0x20004000,0x20000000,0x4010,0,0x400010,0x20004010,0x4000,0x404000,0x20004010,0x10,0x20400010,0x20400010,0,0x404010,0x20404000,0x4010,0x404000,0x20404000,0x20000000,0x20004000,0x10,0x20400010,0x404000,0x20404010,0x400000,0x4010,0x20000010,0x400000,0x20004000,0x20000000,0x4010,0x20000010,0x20404010,0x404000,0x20400000,0x404010,0x20404000,0,0x20400010,0x10,0x4000,0x20400000,0x404010,0x4000,0x400010,0x20004010,0,0x20404000,0x20000000,0x400010,0x20004010);
      $sp7 = array (0x200000,0x4200002,0x4000802,0,0x800,0x4000802,0x200802,0x4200800,0x4200802,0x200000,0,0x4000002,0x2,0x4000000,0x4200002,0x802,0x4000800,0x200802,0x200002,0x4000800,0x4000002,0x4200000,0x4200800,0x200002,0x4200000,0x800,0x802,0x4200802,0x200800,0x2,0x4000000,0x200800,0x4000000,0x200800,0x200000,0x4000802,0x4000802,0x4200002,0x4200002,0x2,0x200002,0x4000000,0x4000800,0x200000,0x4200800,0x802,0x200802,0x4200800,0x802,0x4000002,0x4200802,0x4200000,0x200800,0,0x2,0x4200802,0,0x200802,0x4200000,0x800,0x4000002,0x4000800,0x800,0x200002);
      $sp8 = array (0x10001040,0x1000,0x40000,0x10041040,0x10000000,0x10001040,0x40,0x10000000,0x40040,0x10040000,0x10041040,0x41000,0x10041000,0x41040,0x1000,0x40,0x10040000,0x10000040,0x10001000,0x1040,0x41000,0x40040,0x10040040,0x10041000,0x1040,0,0,0x10040040,0x10000040,0x10001000,0x41040,0x40000,0x41040,0x40000,0x10041000,0x1000,0x40,0x10040040,0x1000,0x41040,0x10001000,0x40,0x10000040,0x10040000,0x10040040,0x10000000,0x40000,0x10001040,0,0x10041040,0x40040,0x10000040,0x10040000,0x10001000,0x10001040,0,0x10041040,0x41000,0x41000,0x1040,0x1040,0x40040,0x10000000,0x10041000);
      $seed = array (4294967295,2147483647,1073741823,536870911,268435455,134217727,67108863,33554431,16777215,8388607,4194303,2097151,1048575,524287,262143,131071,65535,32767,16383,8191,4095,2047,1023,511,255,127,63,31,15,7,3,1,0);

      
      $keys = generateKeys($key);
      $val=0;
      $len = strlen($input);
      $part = 0;
      $iters = 3;

      if($encrypt)
      {
        $loop = array (0, 32, 2);
      }else{
        $loop = array (30, -2, -2);
      }


      $input .= (chr(0) . chr(0) . chr(0) . chr(0) . chr(0) . chr(0) . chr(0) . chr(0)); 

      $result = "";
      $tempresult = "";


      while ($val < $len) {
        $left = (ord($input[$val++]) << 24) | (ord($input[$val++]) << 16) | (ord($input[$val++]) << 8) | ord($input[$val++]);
        $right = (ord($input[$val++]) << 24) | (ord($input[$val++]) << 16) | (ord($input[$val++]) << 8) | ord($input[$val++]);

        $temp = (($left >> 4 & $seed[4]) ^ $right) & 0x0f0f0f0f; $right ^= $temp; $left ^= ($temp << 4);
        $temp = (($left >> 16 & $seed[16]) ^ $right) & 0x0000ffff; $right ^= $temp; $left ^= ($temp << 16);
        $temp = (($right >> 2 & $seed[2]) ^ $left) & 0x33333333; $left ^= $temp; $right ^= ($temp << 2);
        $temp = (($right >> 8 & $seed[8]) ^ $left) & 0x00ff00ff; $left ^= $temp; $right ^= ($temp << 8);
        $temp = (($left >> 1 & $seed[1]) ^ $right) & 0x55555555; $right ^= $temp; $left ^= ($temp << 1);

            $left = (($left << 1) | ($left >> 31 & $seed[31])); 
          $right = (($right << 1) | ($right >> 31 & $seed[31])); 


          for ($j=0; $j<$iters; $j+=3) {
            $end = $loop[$j+1];
            $inc = $loop[$j+2]; 
            for ($i=$loop[$j]; $i!=$end; $i+=$inc) { 
                $right1 = $right ^ $keys[$i]; 
                $right2 = (($right >> 4 & $seed[4]) | ($right << 28 & 0xffffffff)) ^ $keys[$i+1];

                $temp = $left;
                $left = $right;
                $right = $temp ^ ($sp2[($right1 >> 24 & $seed[24]) & 0x3f] | $sp4[($right1 >> 16 & $seed[16]) & 0x3f] | $sp6[($right1 >>  8 & $seed[8]) & 0x3f] | $sp8[$right1 & 0x3f] | $sp1[($right2 >> 24 & $seed[24]) & 0x3f] | $sp3[($right2 >> 16 & $seed[16]) & 0x3f] | $sp5[($right2 >>  8 & $seed[8]) & 0x3f] | $sp7[$right2 & 0x3f]);
            }
            $temp = $left; $left = $right; $right = $temp; 
        } 


        $left = (($left >> 1 & $seed[1]) | ($left << 31)); 
        $right = (($right >> 1 & $seed[1]) | ($right << 31)); 

        $temp = (($left >> 1 & $seed[1]) ^ $right) & 0x55555555; $right ^= $temp; $left ^= ($temp << 1);
        $temp = (($right >> 8 & $seed[8]) ^ $left) & 0x00ff00ff; $left ^= $temp; $right ^= ($temp << 8);
        $temp = (($right >> 2 & $seed[2]) ^ $left) & 0x33333333; $left ^= $temp; $right ^= ($temp << 2);
        $temp = (($left >> 16 & $seed[16]) ^ $right) & 0x0000ffff; $right ^= $temp; $left ^= ($temp << 16);
        $temp = (($left >> 4 & $seed[4]) ^ $right) & 0x0f0f0f0f; $right ^= $temp; $left ^= ($temp << 4);

        $tempresult .= (chr($left>>24 & $seed[24]) . chr(($left>>16 & $seed[16]) & 0xff) . chr(($left>>8 & $seed[8]) & 0xff) . chr($left & 0xff) . chr($right>>24 & $seed[24]) . chr(($right>>16 & $seed[16]) & 0xff) . chr(($right>>8 & $seed[8]) & 0xff) . chr($right & 0xff));

        $part += 8;
        if ($part == 512) 
        {
          $result .= $tempresult; 
          $tempresult = ""; 
          $part = 0;
        }
      } 

      return ($result . $tempresult);
  }

  
  function generateKeys($key) {

      $pc0  = array (0,0x4,0x20000000,0x20000004,0x10000,0x10004,0x20010000,0x20010004,0x200,0x204,0x20000200,0x20000204,0x10200,0x10204,0x20010200,0x20010204);
      $pc1  = array (0,0x1,0x100000,0x100001,0x4000000,0x4000001,0x4100000,0x4100001,0x100,0x101,0x100100,0x100101,0x4000100,0x4000101,0x4100100,0x4100101);
      $pc2  = array (0,0x8,0x800,0x808,0x1000000,0x1000008,0x1000800,0x1000808,0,0x8,0x800,0x808,0x1000000,0x1000008,0x1000800,0x1000808);
      $pc3  = array (0,0x200000,0x8000000,0x8200000,0x2000,0x202000,0x8002000,0x8202000,0x20000,0x220000,0x8020000,0x8220000,0x22000,0x222000,0x8022000,0x8222000);
      $pc4  = array (0,0x40000,0x10,0x40010,0,0x40000,0x10,0x40010,0x1000,0x41000,0x1010,0x41010,0x1000,0x41000,0x1010,0x41010);
      $pc5  = array (0,0x400,0x20,0x420,0,0x400,0x20,0x420,0x2000000,0x2000400,0x2000020,0x2000420,0x2000000,0x2000400,0x2000020,0x2000420);
      $pc6  = array (0,0x10000000,0x80000,0x10080000,0x2,0x10000002,0x80002,0x10080002,0,0x10000000,0x80000,0x10080000,0x2,0x10000002,0x80002,0x10080002);
      $pc7  = array (0,0x10000,0x800,0x10800,0x20000000,0x20010000,0x20000800,0x20010800,0x20000,0x30000,0x20800,0x30800,0x20020000,0x20030000,0x20020800,0x20030800);
      $pc8  = array (0,0x40000,0,0x40000,0x2,0x40002,0x2,0x40002,0x2000000,0x2040000,0x2000000,0x2040000,0x2000002,0x2040002,0x2000002,0x2040002);
      $pc9  = array (0,0x10000000,0x8,0x10000008,0,0x10000000,0x8,0x10000008,0x400,0x10000400,0x408,0x10000408,0x400,0x10000400,0x408,0x10000408);
      $pc10 = array (0,0x20,0,0x20,0x100000,0x100020,0x100000,0x100020,0x2000,0x2020,0x2000,0x2020,0x102000,0x102020,0x102000,0x102020);
      $pc11 = array (0,0x1000000,0x200,0x1000200,0x200000,0x1200000,0x200200,0x1200200,0x4000000,0x5000000,0x4000200,0x5000200,0x4200000,0x5200000,0x4200200,0x5200200);
      $pc12 = array (0,0x1000,0x8000000,0x8001000,0x80000,0x81000,0x8080000,0x8081000,0x10,0x1010,0x8000010,0x8001010,0x80010,0x81010,0x8080010,0x8081010);
      $pc13 = array (0,0x4,0x100,0x104,0,0x4,0x100,0x104,0x1,0x5,0x101,0x105,0x1,0x5,0x101,0x105);
      $seed = array (4294967295,2147483647,1073741823,536870911,268435455,134217727,67108863,33554431,16777215,8388607,4194303,2097151,1048575,524287,262143,131071,65535,32767,16383,8191,4095,2047,1023,511,255,127,63,31,15,7,3,1,0);

       
      $keySet = array (); 
      $sh = array (0, 0, 1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 0);
      $x=0;
      $y=0;

      $leftKey = (ord($key{$x++}) << 24) | (ord($key{$x++}) << 16) | (ord($key{$x++}) << 8) | ord($key{$x++});
      $rightKey = (ord($key{$x++}) << 24) | (ord($key{$x++}) << 16) | (ord($key{$x++}) << 8) | ord($key{$x++});

    $temp = (($leftKey >> 4 & $seed[4]) ^ $rightKey) & 0x0f0f0f0f; $rightKey ^= $temp; $leftKey ^= ($temp << 4);
    $temp = (($rightKey >> 16 & $seed[16]) ^ $leftKey) & 0x0000ffff; $leftKey ^= $temp; $rightKey ^= ($temp << 16);
    $temp = (($leftKey >> 2 & $seed[2]) ^ $rightKey) & 0x33333333; $rightKey ^= $temp; $leftKey ^= ($temp << 2);
    $temp = (($rightKey >> 16 & $seed[16]) ^ $leftKey) & 0x0000ffff; $leftKey ^= $temp; $rightKey ^= ($temp << 16);
    $temp = (($leftKey >> 1 & $seed[1]) ^ $rightKey) & 0x55555555; $rightKey ^= $temp; $leftKey ^= ($temp << 1);
    $temp = (($rightKey >> 8 & $seed[8]) ^ $leftKey) & 0x00ff00ff; $leftKey ^= $temp; $rightKey ^= ($temp << 8);
    $temp = (($leftKey >> 1 & $seed[1]) ^ $rightKey) & 0x55555555; $rightKey ^= $temp; $leftKey ^= ($temp << 1);

    $temp = ($leftKey << 8) | (($rightKey >> 20 & $seed[20]) & 0x000000f0);

    $leftKey = ($rightKey << 24) | (($rightKey << 8) & 0xff0000) | (($rightKey >> 8 & $seed[8]) & 0xff00) | (($rightKey >> 24 & $seed[24]) & 0xf0);
    $rightKey = $temp;

    for ($i=0; $i < count($sh); $i++) 
    {
        
        if ($sh[$i] > 0) {
            $leftKey = (($leftKey << 2) | ($leftKey >> 26 & $seed[26]));
            $rightKey = (($rightKey << 2) | ($rightKey >> 26 & $seed[26]));
        } else {
               $leftKey = (($leftKey << 1) | ($leftKey >> 27 & $seed[27]));
               $rightKey = (($rightKey << 1) | ($rightKey >> 27 & $seed[27]));
        }
        $leftKey = $leftKey & -0xf;
        $rightKey = $rightKey & -0xf;

        $leftKeytemp = $pc0[$leftKey >> 28 & $seed[28]] | $pc1[($leftKey >> 24 & $seed[24]) & 0xf] | $pc2[($leftKey >> 20 & $seed[20]) & 0xf] | $pc3[($leftKey >> 16 & $seed[16]) & 0xf] | $pc4[($leftKey >> 12 & $seed[12]) & 0xf] | $pc5[($leftKey >> 8 & $seed[8]) & 0xf] | $pc6[($leftKey >> 4 & $seed[4]) & 0xf];
        $rightKeytemp = $pc7[$rightKey >> 28 & $seed[28]] | $pc8[($rightKey >> 24 & $seed[24]) & 0xf] | $pc9[($rightKey >> 20 & $seed[20]) & 0xf] | $pc10[($rightKey >> 16 & $seed[16]) & 0xf] | $pc11[($rightKey >> 12 & $seed[12]) & 0xf] | $pc12[($rightKey >> 8 & $seed[8]) & 0xf] | $pc13[($rightKey >> 4 & $seed[4]) & 0xf];
        $temp = (($rightKeytemp >> 16 & $seed[16]) ^ $leftKeytemp) & 0x0000ffff; 
        $keySet[$y++] = $leftKeytemp ^ $temp; 
        $keySet[$y++] = $rightKeytemp ^ ($temp << 16);
      }
       

      return $keySet;
  } 

  function stringToHex ($string) {
      $answer = "";
      for ($i=0; $i<strlen($string); $i++) 
      {
        $o = ord($string[$i]);
        $hex = dechex($o);
        $answer .= substr('0'.$hex, -2);
      }
      return $answer;
  }

  function hexToString ($hexString) {
      $answer = "";
      for ($i=0; $i < strlen($hexString)-1; $i+=2){
          $answer .= chr(hexdec($hexString[$i].$hexString[$i+1]));
      }
      return $answer;
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
		if ($key == "") {
	        return "Please enter key <br>";
	    }else if (strlen($key) != 8) {
	        return "Key should be 8 characters long (64-bits)";
	    }
	    return "";
	}
	function validate_file($upfilename, $key){
		if ($key == "") {
	        return "Please enter key <br>";
	    }else if (strlen($key) != 8) {
	        return "Key should be 8 characters long (64-bits)";
	    }else if ($upfilename == "") {
	        return "Please upload file <br>";
	    }elseif ($_FILES['fileToUpload']['type'] != "text/plain"){
	        return "Upload text file only";
	    }
	    return "";
	}

?>
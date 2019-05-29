<?php
// Initialize the session
session_start();

// Include config file
require_once "config.php";

$filedetails=""; $input_err =""; $upfilename_err=""; $encrypttext = "";


echo <<<_END
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; margin: 40px; }   
        .radio-toolbar {
          margin: 10px;
        }
        .radio-toolbar input[type="radio"] {
            display:none; 
        }
        .radio-toolbar label {
            display:inline-block;
            background-color:#ddd;
            padding: 10px 20px;
            font-family:Arial;
            font-size:16px;
            border: 2px solid #444;
            border-radius: 4px;    
        }
        .radio-toolbar label:hover {
          background-color: #337ab7;
        }
        .radio-toolbar input[type="radio"]:checked + label { 
            background-color:#337ab7;
            border-color: #4c4;
        }
    </style>
   <script src='http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'></script>  
   <script>
   $(document).ready(function() {  // wait until the document has loaded

        $("input[name=radio1]").click(function(){
            var form = $('#my_form')[0];
            var data = new FormData(form);

            var cipher = document.getElementById("cipher").value;
            var URL = "";
            if(cipher == "Simple Substitution"){
                URL = "SimpleSubstitution1.php";
            }else if(cipher == "Double Transposition"){
                URL = "DoubleTransposition.php";
            }else if(cipher == "RC4"){
                URL = "RC4.php";
            }else if(cipher == "DES"){
                URL = "DES.php";
            }

            $( "#errordiv" ).empty();
            $.ajax({
            type: "POST",
            enctype: 'multipart/form-data',
            url: URL,
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            dataType: 'json',
            success: function (response) {
               
                if(response[0] == "false"){
                    $('#errordiv').html(response[1]);
                }else{
                    $('#result').html(response[1]);
                }
            }
              });
        });
    });
   </script>
     
</head>
<body>
    <div class="page-header">
        <h1>Encrypt and Decrypt Tool</h1>
_END;
        if(!isset($_SESSION["loggedin"])){
            echo "<p><a href='register.php'>Sign Up</a> / <a href='login.php'>Login</a></p>";
        }
echo <<<_END1
    </div>
    <p>
    <form action="" id="my_form" method="post" enctype="multipart/form-data">
    <span class="help-block" style="color: red;"font=18px sans-serif; id="errordiv"> $input_err </span>
        <div class="form-group">
            Select the Encryption/Decryption Type &nbsp;&nbsp;
            <select name="cipher" id="cipher" class="form-control" style="width: 550px;>
              <option value=""></option>
              <option value="Simple Substitution">Simple Substitution</option>
              <option value="Double Transposition">Double Transposition</option>
              <option value="RC4">RC4</option>
              <option value="DES">DES</option>
            </select>
        </div>
        <div class="form-group" >
            <input type="text" name="key" id="key" class="form-control" value="" align="center" style="width: 550px;" placeholder="Enter key">
        </div>
        <div class="form-group">
            <textarea class="form-control" placeholder="Enter the Plain or Cipher Text" id ="encrypttext" name ="encrypttext"  style="width: 559px;
            height: 255px; z-index: auto; position: relative; line-height: 20px; font-size: 14px; transition: none 0s ease 0s; background: transparent !important;"></textarea>
        </div>
        <br>
        <div class="form-group">
            OR
        </div>
        <br>
        <div class="form-group">
            <label>Upload the File (.txt only)</label>
            <input type="file" name="fileToUpload" id="fileToUpload" class="form-control" style="width: 250px; z-index: auto"/>
        </div>
        <br>
        <br>
       <div class="radio-toolbar">
            <input type="radio" id="encrypt" name="radio1" value="encrypt" class="myradio">
            <label for="encrypt">Encrypt</label>
            <input type="radio" id="decrypt" name="radio1" value="decrypt" class="myradio">
            <label for="decrypt">Decrypt</label>

        </div>
        <div class="form-group">
            <textarea class="form-control" placeholder="Result" id="result"  style="width: 559px;
            height: 255px; z-index: auto; position: relative; line-height: 20px; " readonly></textarea>
        </div> 
    </form>
    </p>
    </body>
    </html>
_END1;
?>
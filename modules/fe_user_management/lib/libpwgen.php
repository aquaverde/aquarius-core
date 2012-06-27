<?php

/*
Copyright (c) 2002, Kevin Gilbertson, Gilbertson Consulting
Copyright (c) 2002, Flinn Mueller, ActiveIntra.net, Inc.
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided 
that the following conditions are met:

Redistributions of source code must retain the above copyright notice, this list of conditions and the 
following disclaimer.
 
Redistributions in binary form must reproduce the above copyright notice, this list of conditions and 
the following disclaimer in the documentation and/or other materials provided with the distribution. 

Neither the name of the Gilbertson Consulting, ActiveIntra.net, Inc. nor the names of its contributors 
may be used to endorse or promote products derived from this software without specific prior written 
permission.

THIS SOFTWARE 
IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, 
INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR 
PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, 
PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT 
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
OF THE POSSIBILITY OF SUCH DAMAGE.
*/

/***
* This class is designed to create somewhat random passwords
* to set up initial user accounts where
* you email out the initial login information. 
* 
* Created By: Kevin Gilbertson (kevin AT gilbertsonconsulting DOT com)
* Last Updated: 4/22/02
* Updated By: Flinn Mueller (flinn AT activeintra DOT net)
* Bug Fix Unusable Character: Rolf Holtsmark <rolf AT holtsmark DOT no>
* Last Updated: 01/07/03
***/

class PWGen
{
 var $passwdchars;
 var $passwd = NULL;
 var $length;

 function PWGen($min=6, $max=8, $special=NULL, $chararray=NULL) {
    if ($chararray == NULL) {
       $this->passwdstr = "abcdefghijklmnopqrstuvwxyz";
       $this->passwdstr .= strtoupper($this->passwdstr);
       $this->passwdstr .= "1234567890";

       // add special chars to start
       if ($special) {
          $this->passwdstr .= "!@#$%";
       }
    } else {
        $this->passwdstr = $chararray;
    }

    for($i=0; $i<strlen($this->passwdstr); $i++) {
        $this->passwdchars[$i]=$this->passwdstr[$i];
    }
             
    // randomize the chars
    srand ((float)microtime()*1000000);
    shuffle($this->passwdchars);

    $this->length = rand($min, $max);

    for($i=0; $i<$this->length; $i++) {
       $charnum = rand(1, count($this->passwdchars));
       $this->passwd .= $this->passwdchars[$charnum-1];
    }    
 }

 function getPasswd() {
    return $this->passwd;
 }
 
 function getPasswdImg() {
    // create the image
    $png = ImageCreate(200,80);
    $bg = ImageColorAllocate($png,192,192,192);
    $tx = ImageColorAllocate($png,128,128,128);
    ImageFilledRectangle($png,0,0,200,80,$bg);
    srand ((float)microtime()*1000000);
    ImageString($png,5,rand(0,90),rand(0,50),$this->passwd,$tx);

    // send the image
    header("content-type: image/png");
    ImagePng($png);
    Imagedestroy($png);
 }

 function getPasswdHtml() {
    return htmlentities($this->passwd);
 }
 
}

?>

<?php

//sample ean's
//$ean = "500043101137"; //will return 3
$ean = "7622100996206"; //will return 0 
//$ean = "500043091"; // will return 8
//$ean = "1"; //will return 7
//$ean = "501437910982"; // will return 5
//$ean = "76221009962067977724"; // will return 0
//$ean= "76561198112017378";
 echo "entry is&nbsp;&nbsp;".$ean."<br>";
  
      $ean = cdv($ean);
 echo "return is ".$ean;
 
 
 
function cdv($pluNo,$result= false ,$type = false) 
{
/*
'---------------------------------------------------------------------------------------
' Procedure     : cdv
' Author        : Jim Richardson -NoIdeer Software
' Original Date : 12/01/1993
' Original Type : PDS7 (QuickBasic 7)
' Project       : CDV (other utilities)
' Purpose       : return the correct check digit from an EAN or full EAN
' Project Type  : PHP
' PHP Version   : 01/10/2014 - updated 01/08/2015
' Notes         : $pluNo  input must be digits as a string
' Notes         : PHP version pads the input to 12 or 7 digits dependent on length 
' Notes         : PHP version returns the full EAN code 
' Notes         : PHP version setting the $result variable to true returns the CDV digit only
' Notes         : PHP version setting the $type variable to true returns a string rather than a number    
' Notes         : $type is not fully functional .... or is it ?
' ToDo          : allow $pluNo to be chrs rather than numbers and either return letters, letter or numeric representations 
'---------------------------------------------------------------------------------------
*/ 
	
$plulen = intval(strlen($pluNo)); //get current length of the input

if ($plulen == 13 || $plulen == 8 ) // test length of input & strip off the cdv, this applies to input of 13 or 8 digit inputs
	{ 
		$pluNo = substr($pluNo, 0, -1); // chop off the last digit
		 
		}
else {
	// if the input length is less than 12 and does not equal 7 
	if ($plulen < 12 and $plulen <> 7 )
	{
		// pack to the front of the string with 0's to 12 digits or 7 digits dependent on length
		switch ($plulen) {
			case $plulen <= 6:
			    //pack to 7
			    $notoadd = intval(7 - $plulen);
				for ($i = 1; $i <= $notoadd; $i++)
			     {
						$pluNo = "0".$pluNo;
			     } 
				break;
			case $plulen >= 9:
				//pack to 12 
				$notoadd = intval(12 - $plulen);
				for ($i = 1; $i <= $notoadd; $i++)
			     {
						$pluNo = "0".$pluNo;
			     } 
				break;
			}	
		
		
	}	 
	elseif ($plulen > 12)
	{
		// take first 12 digits if the number is longer than 13 digits
		$pluNo = substr($pluNo,0,12);
		
	}	
}
	
	$plulen = intval(strlen($pluNo)); // re calculate the length if the plu has an altered length from original input

for ($i = 1; $i <= $plulen; $i++)
{
	      // split odd & even digits of the string apart and add them together as odd or even
	     switch ($i) {
			case $i % 2 == true: // odd digits using mod
				$pl = intval(substr($pluNo, $i-1, 1)); //turn the odd string value to numeric for processing
				$oddPlu = $oddPlu + $pl; // add the next odd one
				break;
          
			case $i % 2 == false: // even digits using mod 
				$pl = intval(substr($pluNo, $i-1, 1));  // turn the even string value to numeric for processing
				$evenPlu = $evenPlu + $pl; // add the next even one
				break;
            }
            
}

switch ($plulen) {
	//note the difference between odd & even length digit codes
	case 7: 
        $CdvX = $oddPlu * 3;  // multiply by 3
        $CdvX = $CdvX + $evenPlu; //add the even total
        break;
	case 12:
        $CdvX = $evenPlu * 3; // multiply by 3
        $CdvX = $CdvX + $oddPlu; // add the odd total
        break;
}

$CdvX = $CdvX % 10 ; // take the remainder after a division by 10 (mod)
$CdvX = 10 - $CdvX; // then take the remainder from 10, will always return a positive number or zero 

if ($CdvX >= 10) 
{
	$CdvX = $CdvX - 10; //the cdv has to be less than 10, but it can not be less than zero
	}

// return the required result 

if ($result === false)
{
	$pluNo .= $CdvX; // return string
		if ($type == false)
			{
				$pluNo = intval($pluNo); //return number
			}
}
elseif ($result === true) 
 {
	 // returns just the digit
	$pluNo = $CdvX; // return string
		if ($type == false)
			{
				$pluNo = intval($pluNo); //return number
			}
}
return $pluNo;
}
 
?>

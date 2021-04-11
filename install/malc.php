<?php
$num = 95555;

$find[]=0000;
$find[]=1111;
$find[]=2222;
$find[]=3333;
$find[]=4444;
$find[]=5555;
$find[]=6666;
$find[]=7777;
$find[]=8888;
$find[]=9999;
$x = 1;
while ($x > 0) {
$num++;
//echo $num.PHP_EOL;
$back = substr($num,-4);
echo $back.PHP_EOL;
if (in_array($back,$find)) {
//echo $num.PHP_EOL;
if (is_prime($num)) {
echo $num.PHP_EOL;
exit;
}
}
} 

function is_prime($number)
{
    // 1 is not prime
    if ( $number == 1 ) {
        return false;
    }
    // 2 is the only even prime number
    if ( $number == 2 ) {
        return true;
    }
    // square root algorithm speeds up testing of bigger prime numbers
    $x = sqrt($number);
    $x = floor($x);
    for ( $i = 2 ; $i <= $x ; ++$i ) {
        if ( $number % $i == 0 ) {
            break;
        }
    }
 
    if( $x == $i-1 ) {
        return true;
    } else {
        return false;
    }
}



<?php
$host = 'localhost';
$db   = 'chucknorris';
//echo php_uname('n'); exit;

if (php_uname('n') == 'Arthurs-Mac-mini.local') {
  $username = 'root';
  $password = '';

} elseif (php_uname('n') == 'alastair-osheas-macbook.local') {
  $username = 'root';
  $password = 'root';
  
} elseif (php_uname('n') == 'jane-kellys-macbook-pro.local') {
  $username = 'root';
  $password = 'root';
  
} elseif (php_uname('n') == 'Rikkis-MacBook-Air.local') {
  $username = 'root';
  $password = '';
  
} elseif (php_uname('n') == 'janesmbp.lan') { 
  $username = 'root';
  $password = 'root';

} elseif (php_uname('n') == 'art.local') {
  $username = 'root';
  $password = 'skoot';

} elseif (php_uname('n') == 'timinator.lan') {
  $username = 'root';
  $password = 'root';

} elseif (php_uname('n') == 'kien56.local') {
  $username = 'root';
  $password = 'n0tr00t';

} elseif (php_uname('n') == 'camel.lan') {
  $username = 'root';
  $password = 'DGKmjYr4drbt8LNVNR';

} elseif (php_uname('n') == 'Ben-no-MacBook-Air.local' && 'Bens-MacBook-Pro-5.local') {
  $username = 'root';
  $password = 'communist10';

} else { // Live server
  $host     = '';
  $db       = '';
  $username = '';
  $password = '';

}

R::setup('mysql:host=' . $host . ';dbname=' . $db, $username, $password);
//R::freeze( true );
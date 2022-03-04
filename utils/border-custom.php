//--TEST--
Border: new custom mode
--FILE--
<?php
error_reporting(E_ALL | E_NOTICE);
if (file_exists(dirname(__FILE__) . 'includes/class.table.php')) {
    require_once dirname(__FILE__) . '/../Table.php';
} else {
    require_once 'includes/class.table1.php';
}
require_once 'includes/color.php';

$cc = new Console_Color2();
$table = new table(
    CONSOLE_TABLE_ALIGN_LEFT,
    array('horizontal' => '─', 'vertical' => '│', 'intersection' => '┼','left' =>'├','right' => '┤','left_top' => '┌','right_top'=>'┐','left_bottom'=>'└','right_bottom'=>'┘','top_intersection'=>'┬'),3,null,true
);
$table->setHeaders(array($cc->convert("%mCity, I guess ?%n"), 'Mayor'));
$table->addRow(array('Leipzig', $cc->convert("%MMajor Tom%n")));
$table->addRow(array('New York', $cc->convert("%YTowerhouse%n")));
$table->addRow(array('Hereford', $cc->convert("%REm%n")));
$table->addRow(array('Malervn',$cc->convert("%GJim%n")));
$table->addRow(array('Worcester', $cc->convert("%bThe Old DEER%n")));
echo $table->getTable();
$table = new table(
    CONSOLE_TABLE_ALIGN_RIGHT,'',3,null,true
);
$table->setHeaders(array('City', 'Mayor'));
$table->addRow(array('Leipzig', 'Major Tom'));
$table->addRow(array('New York', 'Towerhouse'));
$table->addRow(array('Hereford', 'Emma'));
$table->addRow(array('Malvern', 'Jim'));
$table->addRow(array('Worcester', 'The Old Deer'));
echo $table->getTable();
?>
--EXPECT--
*==========*============*
: City     : Mayor      :
*==========*============*
: Leipzig  : Major Tom  :
: New York : Towerhouse :
*==========*============*

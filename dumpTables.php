<?
require_once("db.php");
$result=mysqli_query($mysqli, "SHOW TABLES FROM connect4");
$q="";
while ($res=mysqli_fetch_row($result))
{
	$q .= '`'.$res[0].'`, ';		
}
$q = substr($q,0,strlen($q)-2);
mysqli_query($mysqli, "DROP TABLE $q");
mysqli_close($mysqli);
?>
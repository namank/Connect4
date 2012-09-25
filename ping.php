<?
require_once("db.php");
$game = @$_REQUEST['id'];
$player = @$_REQUEST['player'];
$gameStatus = $game."status";
$q = "SELECT player FROM $gameStatus LIMIT 2";
$result = mysqli_query($mysqli,$q);
$row=mysqli_fetch_row($result);
$p1 = $row[0];
$row=mysqli_fetch_row($result);
$p2=$row[0];
$playerName = $player == "P1" ? $p1:$p2;

$time=time();
$q = "SELECT player FROM $gameStatus WHERE player='$playerName'";
$result = mysqli_query($mysqli,$q);
if ($result && mysqli_num_rows($result) == 1) // this player has not pinged yet
{	
	$q="INSERT INTO $gameStatus (player,time) VALUES('$playerName',$time)";	
	mysqli_query($mysqli,$q);	
}
else //update time value
{
	$q="UPDATE $gameStatus SET time=$time WHERE (count>2 AND player='$playerName')";	
	mysqli_query($mysqli,$q);	
}
$otherPlayer = $player == "P1" ? $p2 : $p1;
$time = $time - 15;
$q="SELECT * FROM $gameStatus WHERE (count>2 AND player='$otherPlayer' AND time>($time))";
$result = mysqli_query($mysqli,$q);
if ($result && mysqli_num_rows($result) == 0)
	echo "Offline :(";
else
	echo "Online :)";
mysqli_close($mysqli);
?>
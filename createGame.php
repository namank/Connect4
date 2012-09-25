<?
require_once("db.php");
$game = "";
for ($i = 0; $i <6; $i++)
{
	if (rand (1,100) %2 == 0)
		$game .= chr((rand(65,90))); 
	else
		$game .= (rand(1,9));
}
$p1=mysql_real_escape_string($_REQUEST['player1']);
$p2=mysql_real_escape_string($_REQUEST['player2']);
$toInvite = @$_REQUEST['creator'] == "P2" ? $p1 : $p2;
echo ("<p>To invite $toInvite, send this link: http://" . $_SERVER["SERVER_NAME"] . "/connect4/game.php?id=$game</p>");
$gameStatus = $game."status";
$gameStatus=mysql_real_escape_string($gameStatus);
$game=mysql_real_escape_string($game."g");

mysqli_query($mysqli, "CREATE TABLE $game(count int primary key auto_increment, move char(3))");
mysqli_query($mysqli, "CREATE TABLE $gameStatus(count int primary key auto_increment, player varchar(20), time int)");
$time = time();
mysqli_query($mysqli, "INSERT INTO $gameStatus (player,time) VALUES('$p1','$time')");
mysqli_query($mysqli, "INSERT INTO $gameStatus (player,time) VALUES('$p2','$time')");
?>
<form method="post" action="game.php">	
	<input type="submit" value="Start game!">	
	<input type="hidden" id="player1" name="player1" value="player1">	
	<input type="hidden" id="id" name="id" value="<?echo substr($game,0,-1);?>">
</form>
<?
//000000000000000000000000000000000000000000
$action = @$_REQUEST['action'];
$game=$_REQUEST['id'];
require_once("db.php");
$playersMove = "--";
//get position of a position on the physical board as a position in the board string
function getPos($i, $j)
{
	return ((ord($i)-48)* 7 + (ord($j)-48));
}

function playerMove($board,$player,$row,$col)
{		
	for ($i =ord('5'); $i>=ord('0'); $i--)
	{
		$index=getPos(chr($i),$col);		
		if ($board[$index] == '0') // first empty spot from bottom
		{		
			$GLOBALS['playersMove'] = chr($i) . '' . $col;
			
			$board[$index] = $player;
			return $board;
		}
	}
	//column full
	return $board;
}

function gameOver($board)
{		
	for ($i=ord('5'); $i>=ord('3'); $i--)
	{
		// /
		for ($j=ord('0'); $j<=ord('3');$j++)
		{
			$cur = $board[getPos(chr($i),chr($j))];			
			if (($cur != '0') && ($cur == $board[getPos(chr($i-1),chr($j+1))]) && ($cur == $board[getPos(chr($i-2),chr($j+2))]) && ($cur == $board[getPos(chr($i-3),chr($j+3))]))
			{
				return $cur;
			}			
		}
		
		// \
		for ($j=ord('6'); $j>=ord('3');$j--)
		{
			$cur = $board[getPos(chr($i),chr($j))];
			if (($cur != '0') && ($cur == $board[getPos(chr($i-1),chr($j-1))]) && ($cur == $board[getPos(chr($i-2),chr($j-2))]) && ($cur == $board[getPos(chr($i-3),chr($j-3))]))
			{
				return $cur;
			}		
		}
		
		// |
		for ($j=ord('0'); $j<=ord('6');$j++)
		{
			$cur = $board[getPos(chr($i),chr($j))];
			if (($cur != '0') && ($cur == $board[getPos(chr($i-1),chr($j))]) && ($cur == $board[getPos(chr($i-2),chr($j))]) && ($cur == $board[getPos(chr($i-3),chr($j))]))
			{
				return $cur;
			}			
		}
	}
	
	// - 
	for ($i=ord('5'); $i>=ord('0'); $i--)
	{
		for ($j=ord('0'); $j<=ord('3');$j++)
		{
			$cur = $board[getPos(chr($i),chr($j))];
			if (($cur != '0') && ($cur == $board[getPos(chr($i),chr($j+1))]) && ($cur == $board[getPos(chr($i),chr($j+2))]) && ($cur == $board[getPos(chr($i),chr($j+3))]))
			{
				return $cur;
			}
		}
	}
	
	return '0';
}

switch ($action)
{
	case 'getBoard':
	{
		$player=substr($_REQUEST['player'],1,1);
		$p1=@$_REQUEST['p1name'];
		$p2=@$_REQUEST['p2name'];
		$time = time();
		$oldBoard=@$_REQUEST['board'];
		$colors = array("1"=>'Y',"2"=>'R');
		$gameBoard = $game."Board";
		$game=$game."g";
		$q = "SELECT state FROM $gameBoard";
		while (time() - $time < 25)
		{
			if ($result = mysqli_query($mysqli, $q)) //table already exists
			{
				$row = mysqli_fetch_row($result);	
				$stat = gameOver($row[0]);
				
				if (strcmp($row[0],$oldBoard) != 0)
				{	
					
					$q = "SELECT * FROM $game ORDER BY count DESC LIMIT 1"; //get last row			
					$result=mysqli_query($mysqli, $q);					
					if ($result && mysqli_num_rows($result) > 0)
					{				
						$res = mysqli_fetch_assoc($result);			
						
						if (strcmp(substr($res['move'],0,1),$player) == 0) //waiting for other player if my move was last
						{								
							$waitFor = (strcmp($player,"1") == 0) ? $p2:$p1;							
							if ($stat != '0') 
							{
								echo $row[0].$colors[$player].substr($res['move'],1,2)."won".$stat; //won1 or won2
								mysqli_close($mysqli);
								exit();
							}
							echo $row[0].$colors[$player].substr($res['move'],1,2)."Waiting for ".$waitFor;
						}
						else
						{
							$c = (strcmp($player,"1") == 0) ? "2":"1";
							if ($stat != '0') 
							{
								echo $row[0].$colors[$c].substr($res['move'],1,2)."won".$stat; //won1 or won2
								mysqli_close($mysqli);
								exit();
							}							
							echo $row[0].$colors[$c].substr($res['move'],1,2)."Your turn";
						}
					}
					else
					{
						echo $row[0]."Your turn";
					}
					mysqli_close($mysqli);
					exit();
				}	
				else
				{
					
				}
			}
			else
			{				
				$q = "CREATE TABLE $gameBoard (state varchar(42))";
				mysqli_query($mysqli, $q);
				$empty = str_repeat("0",42);
				$q="INSERT INTO $gameBoard (state) VALUES('$empty')";
				mysqli_query($mysqli, $q);
				echo $empty."Your turn";
				mysqli_close($mysqli);
				exit();
			}
		}
		
		echo $oldBoard;
		break;
	}
	case 'attemptMove':
	{
		$player=substr($_REQUEST['player'],1,1); // 1 or 2
		$oppPlayer = ($player == "1") ? "2" : "1"; // 2 or 1
		$row=$_REQUEST['row'];
		$col=$_REQUEST['col'];
		
		$pmove=$player.$row.$col;
		$gameBoard = $game."Board";
		
		$q = "SELECT state FROM $gameBoard";
		$result = mysqli_query($mysqli,$q);			
		$rowN = mysqli_fetch_row($result);			
		$oldBoard = $rowN[0];
		
		$board = playerMove($oldBoard,$player,$row,$col);
		
		$game=$game."g";
		if (strcmp($board,$oldBoard) == 0) //invalid move, return false
		{			
			$q = "SELECT * FROM $game ORDER BY count DESC LIMIT 1"; //get last row			
			$result=mysqli_query($mysqli,$q);
			$res = mysqli_fetch_assoc($result);
			
			if (strcmp(substr($res['move'],0,1),$player) == 0) //waiting for other player if my move was last
			{
				echo $board."invalidwaiting";
			}
			else
			{
				echo $board."invalid";
			}
			mysqli_close($mysqli);
			exit();
		}	
		
		$q = "SELECT * FROM $game ORDER BY count DESC LIMIT 1"; //get last row				
		$result=mysqli_query($mysqli,$q);
		
		$m = $player.$playersMove; //actual new move, to insert into table
		if (!$result || mysqli_num_rows($result) == 0)
		{								
			$q="INSERT INTO $game (move) VALUES($m)"; //record move, waiting for other player			
			mysqli_query($mysqli,$q);
			//update board
			$q = "UPDATE $gameBoard SET state ='$board'";
			mysqli_query($mysqli,$q);
			if (gameOver($board,$player)) 
			{
				echo $playersMove.$board."won".$player; //won1 or won2
				exit();
			}
			echo $playersMove.$board."waiting";	
			mysqli_close($mysqli);			
			exit();
		}
		else //already exists
		{
			$res = mysqli_fetch_assoc($result);
			if (strcmp(substr($res['move'],0,1),$player) != 0) //other player made previous move $res[$playerCol] != "" && $res[$oppPlayerCol] != "")
			{
				$q="INSERT INTO $game (move) VALUES($m)"; //record move, waiting for other player
				mysqli_query($mysqli,$q);
				//update board
				$q = "UPDATE $gameBoard SET state ='$board'";
				mysqli_query($mysqli,$q);
				
				if (gameOver($board,$player)) 
				{
					echo $playersMove.$board."won".$player; //won1 or won2
					mysqli_close($mysqli);
					exit();
				}
				echo $playersMove.$board."waiting";	
				mysqli_close($mysqli);				
				exit();
			}
			echo $oldBoard."invalidwaiting";
			mysqli_close($mysqli);
			exit();			
		}
		break;
	}
}
?>
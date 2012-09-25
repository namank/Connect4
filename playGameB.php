<?
//000000000000000000000000000000000000000000
$action = @$_REQUEST['action'];
$game=$_REQUEST['id'];
require_once("db.php");
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
		$gameBoard = $game."Board";
		$game=$game."g";
		$q = "SELECT state FROM $gameBoard";
		if ($result = mysql_query($q)) //table already exists
		{
			$row = mysql_fetch_row($result);		
			$stat = gameOver($row[0]);
			if ($stat != '0') 
			{
				echo $row[0]."won".$stat; //won1 or won2
				exit();
			}			
			echo $row[0];
			$q = "SELECT * FROM $game ORDER BY count DESC LIMIT 1"; //get last row			
			$result=mysql_query($q);
			if ($result)
			{				
				$res = mysql_fetch_assoc($result);			
				
				if (strcmp(substr($res['move'],0,1),$player) == 0) //waiting for other player if my move was last
				{
					$waitFor = (strcmp($player,"1") == 0) ? $p2:$p1;
					echo "Waiting for ".$waitFor;
				}
				else
					echo "Your turn";
			}
			else
			{
				echo "Your turn";
			}
		}
		else
		{
			$q = "CREATE TABLE $gameBoard (state varchar(42))";
			mysql_query($q);
			$empty = str_repeat("0",42);
			$q="INSERT INTO $gameBoard (state) VALUES('$empty')";
			mysql_query($q);
			echo $empty."Your turn";
		}
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
		$result = mysql_query($q);			
		$rowN = mysql_fetch_row($result);			
		$oldBoard = $rowN[0];
		
		$board = playerMove($oldBoard,$player,$row,$col);
		
		$game=$game."g";
		if (strcmp($board,$oldBoard) == 0) //invalid move, return false
		{			
			$q = "SELECT * FROM $game ORDER BY count DESC LIMIT 1"; //get last row			
			$result=mysql_query($q);
			$res = mysql_fetch_assoc($result);
			
			if (strcmp(substr($res['move'],0,1),$player) == 0) //waiting for other player if my move was last
			{
				echo $board."invalidwaiting";
			}
			else
			{
				echo $board."invalid";
			}
			exit();
		}	
		
		$q = "SELECT * FROM $game ORDER BY count DESC LIMIT 1"; //get last row				
		$result=mysql_query($q);
		
		if (!$result || mysql_num_rows($result) == 0)
		{					
			$q="INSERT INTO $game (move) VALUES($pmove)"; //record move, waiting for other player			
			mysql_query($q);
			//update board
			$q = "UPDATE $gameBoard SET state ='$board'";
			mysql_query($q);
			if (gameOver($board,$player)) 
			{
				echo $board."won".$player; //won1 or won2
				exit();
			}
			echo $board."waiting";					
			exit();
		}
		else //already exists
		{
			$res = mysql_fetch_assoc($result);
			if (strcmp(substr($res['move'],0,1),$player) != 0) //other player made previous move $res[$playerCol] != "" && $res[$oppPlayerCol] != "")
			{
				$q="INSERT INTO $game (move) VALUES($pmove)"; //record move, waiting for other player
				mysql_query($q);
				//update board
				$q = "UPDATE $gameBoard SET state ='$board'";
				mysql_query($q);
				
				if (gameOver($board,$player)) 
				{
					echo $board."won".$player; //won1 or won2
					exit();
				}
				echo $board."waiting";					
				exit();
			}
			echo $oldBoard."invalidwaiting";
			exit();			
		}
		break;
	}
}
?>
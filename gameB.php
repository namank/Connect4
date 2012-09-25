<?
require_once("db.php");
$game = $_REQUEST['id'];
$player = @($_REQUEST['player1']) ? "P1" : "P2";
$gameStatus = $game."status";
$q = "SELECT player FROM $gameStatus LIMIT 2";
$result = mysql_query($q);
if (!$result)
{
	header('Location: http://localhost/connect4/');
}
$row=mysql_fetch_row($result);
$p1 = $row[0];
$row=mysql_fetch_row($result);
$p2=$row[0];

$gameStatus=mysql_real_escape_string($gameStatus);
$game=mysql_real_escape_string($game);
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="description" content="Online Multiplayer Connect4 Game">
	<meta name="author" content="Namank Shah">
	<title>Connect 4 Online!</title>
<style>
td
{
	text-align:center;
	background-color:white;
	color:rgba(0,0,0,0.0);
}
a
{
	color:blue;	
}
#table
{		
	background-color:blue;
	border-collapse:separate;
}
#p1name,#p2name
{
	min-height:30px; min-width:30px; font-size:40px;
}
#info > div
{
	float:left; 	
	display:inline; 
	text-align:center;
	font-size:20px;
}
#container
{	
	text-align:center;
}
#status
{
	margin-top:20px;
}
#board
{
	border:1px black solid;
}
</style>
<script src="jquery-1.7.1.js"></script>
<script>
$('document').ready(function(){
	var player = "<? echo $player; ?>"; //current player's name (p1/p2)
	var game = "<? echo $game; ?>";		//game id
	var oppPlayerName="";
	var p1name = "<? echo $p1; ?>"; //p1's actual name
	var p2name = "<? echo $p2; ?>"; //p2's actual name
	var board="";
	if (player == "P1")
	{
		p1name="You";
		oppPlayerName=p2name;
	}
	else
	{
		oppPlayerName=p1name;
		p2name="You";
	}
	
	$('#p1name').text(p1name);
	$('#p2name').text(p2name);
	
	ping();
	function ping()
	{
		$.ajax({
		url: 'ping.php',			
		dataType: "text",
		data: {'id': game, 'player':player},			
		success: function(data) { 
			if (player == "P1")
			{
				$('#p2status').text(data);				
				$('#p1status').text("Online :)");
			}
			else
			{
				$('#p1status').text(data);
				$('#p2status').text("Online :)");
			}			
		} // success: function(data)
		});			 
	}
	var pingTimer = setInterval(function() 
	{			
		ping();
	}, 5000);
	
	function adjustTable()
	{
		var w = $(window).width()*0.9; //leave 10% margin on both sides
		var h = $(window).height() - $('#p1').height()-50;
		var maxW = Math.round(w/7);
		var maxH = Math.round(h/6);
		var size = Math.min(maxW,maxH);
		$('#table').height(Math.round(size*6));
		$('#table').width(Math.round(size*7));
		//$('#board').height(Math.round(size*6));
		//$('#board').width(Math.round(size*7));
		//$('#board').css({'margin-left':($(window).width()-$('#board').width()) / 2-10, 'margin-right': ($(window).width()-$('#board').width()) / 2-10});
		$('#table').css({'margin-left':($(window).width()-$('#table').width()) / 2-10, 'margin-right': ($(window).width()-$('#table').width()) / 2-10});
		$('td').css({'-moz-border-radius': $('#00').width()/2, 'border-radius': $('#00').width()/2});
	}
	adjustTable();
	
	$(window).resize(function() {
		adjustTable();
	});
	
	//get position of a position on the physical board as a position in the board string
	function getPos(i, j)
	{
		return (i*7+j);
	}
	function show(data)
	{
		
	}
	//display the board according to current values
	function displayBoard()
	{		
		$.ajax({
		url: 'playGame.php',
		dataType: 'text',
		cache:'false',
		data: {'id':game,'action':'getBoard','player':player,'p1name':p1name,'p2name':p2name, 'board':board},
		success: function(data)
		{
			var status = data.substring(42);			
			for (i = 0; i <6; i++)
			{
				for (j=0;j<7;j++)
				{
					var em = $('#'+i+j);
					var newData = data.charAt(getPos(i,j));
					em.text(newData);												
					if (newData == "0")
					{					
						em.css({'background-color':'white'});
					}
					else if (newData == "1")
					{					
						em.css({'background-color':'yellow'});
					}
					else
					{						
						em.css({'background-color':'red'});
					}																	
				}
			}				
			
			if (status.substring(0,3) == "won")
			{
				if (status.substring(3) == player.substring(1))
					status = "Game over. You won! <a href=\"http://localhost/connect4\">New Game</a>";
				else
					status = "Game over. "+oppPlayerName+" won. Better luck next time! <a href=\"http://localhost/connect4\">New Game</a>";				
				
					
				clearInterval(displayTimer);
				
				$('td').each(function(index)
				{
					var j = index % 7; //column
					var i = (index-j)/7; //row					
					
					$('#'+i+j).unbind();
					
					$('#'+i+j).click(function(event)
					{							
						alert("Game over. Please create a new game to restart.");
					});	
				}
				);
	
			}
			$('#status').html(status);
		}		
		});
	}		
	displayBoard();
	var displayTimer = setInterval(function()
		{
			displayBoard();
		}, "3000");	
	
	function makeMove(i,j)
	{		
		$.ajax({
		url: 'playGame.php',
		dataType: 'text',
		cache:'false',
		data: {'id':game,'action':'attemptMove', 'row':i,'col':j,'player':player},
		success: function(data)
		{
			var status=data.substring(42);
			
			for (i = 0; i <6; i++)
			{
				for (j=0;j<7;j++)
				{
					var em = $('#'+i+j);
					var newData = data.charAt(getPos(i,j));
					em.text(newData);							
					if (newData == "0")
					{						
						em.css({'background-color':'white'});
					}
					else if (newData == "1")
					{						
						em.css({'background-color':'yellow'});
					}
					else
					{						
						em.css({'background-color':'red'});
					}
				}
			}
			
			if (status == "invalid")
			{
				alert("Invalid move. Please try again!");
				$('#status').text("Your turn");
			}
			else if (status == "waiting" )
			{
				$('#status').text("Waiting for "+oppPlayerName);			
			}
			else if (status == "done")
			{
				$('#status').text("Your turn");
			}
			else if (status == "invalidwaiting")
			{
				alert("It is not your turn!");
				$('#status').text("Waiting for "+oppPlayerName);
			}
			else if (status.substring(0,3) == "won")
			{
				if (status.substring(3) == player.substring(1))
					$('#status').text("Game over. You won!");
				else
					$('#status').text("Game over. "+oppPlayerName+" won. Better luck next time!");
					
				clearInterval(displayTimer);
				clearInterval(pingTimer);
				$('#p1status').remove();
				$('#p2status').remove();
				$('td').each(function(index)
				{
					var j = index % 7; //column
					var i = (index-j)/7; //row
					
					$('#'+i+j).unbind();
					
					$('#'+i+j).click(function(event)
					{							
						alert("Game over. Please create a new game to restart.");
					});	
				}
				);
	
			}
		}		
		});		
	}
	
	
	$('td').each(function(index)
	{
		var j = index % 7; //column
		var i = (index-j)/7; //row
		
		//works for all objects
		$('#'+i+j).click(function()
		{			
			makeMove(i,j);			
		});	
	}
	);	
});
</script>
</head>
<body>
<div id = "container">
<div id="info">
<div style="width:30%" id="p1">
<div id ="p1name" >Player 1</div>
<div id="p1status">Player 1 Status</div>
</div>
<div style="width:40%;" id = "status">Click to make your move</div>
<div style="width:30%" id="p2">
<div id ="p2name">Player 2</div>
<div id="p2status">Player 2 Status</div>
</div>
</div>
<div style="clear:left;" id="tableHolder">
<br>
<table id = "table" cellspacing="10px">
	<tr id='0'>
		<td id ='00'>0</td><td id ='01'>0</td><td id ='02'>0</td><td id ='03'>0</td><td id ='04'>0</td><td id ='05'>0</td><td id ='06'>0</td>
	</tr>
	<tr id='1'>
		<td id ='10'>0</td><td id ='11'>0</td><td id ='12'>0</td><td id ='13'>0</td><td id ='14'>0</td><td id ='15'>0</td><td id ='16'>0</td>
	</tr>
	<tr id='2'>
		<td id ='20'>0</td><td id ='21'>0</td><td id ='22'>0</td><td id ='23'>0</td><td id ='24'>0</td><td id ='25'>0</td><td id ='26'>0</td>
	</tr>
	<tr id='3'>
		<td id ='30'>0</td><td id ='31'>0</td><td id ='32'>0</td><td id ='33'>0</td><td id ='34'>0</td><td id ='35'>0</td><td id ='36'>0</td>
	</tr>
	<tr id='4'>
		<td id ='40'>0</td><td id ='41'>0</td><td id ='42'>0</td><td id ='43'>0</td><td id ='44'>0</td><td id ='45'>0</td><td id ='46'>0</td>
	</tr>
	<tr id='5'>
		<td id ='50'>0</td><td id ='51'>0</td><td id ='52'>0</td><td id ='53'>0</td><td id ='54'>0</td><td id ='55'>0</td><td id ='56'>0</td>
	</tr>
</table>
</div>
</div>
</body>
</html>
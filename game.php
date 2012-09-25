<?
require_once("db.php");
$game = $_REQUEST['id'];
$player = @($_REQUEST['player1']) ? "P1" : "P2";
$gameStatus = $game."status";
$q = "SELECT player FROM $gameStatus LIMIT 2";
$result = mysqli_query($mysqli,$q);
if (!$result)
{
	header('Location: http://localhost/connect4/');
}
$row=mysqli_fetch_row($result);
$p1 = $row[0];
$row=mysqli_fetch_row($result);
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
	-webkit-box-shadow: 0px 0px 5px 5px rgba(0,0,150,0.5);
	box-shadow: 0px 0px 5px 5px rgba(0,0,150,0.5);
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
@-moz-keyframes beepR /* Firefox */
{
0%, 100% {background: white;}
20%, 80% {background: red;}
}
@-webkit-keyframes beepR /* Webkit */
{
0%, 100% {background: white;}
20%, 80% {background: red;}
}
@keyframes beepR /* Else */
{
0%, 100% {background: white;}
20%, 80% {background: red;}
}
@-moz-keyframes beepY /* Firefox */
{
0%,100% {background: white;}
20%, 80% {background: yellow;}
}
@-webkit-keyframes beepY /* Webkit */
{
0%,100% {background: white;}
20%, 80% {background: yellow;}
}
@keyframes beepY /* Else */
{
0%,100% {background: white;}
20%, 80% {background: yellow;}
}
form
{
	text-align:center;
}
input
{
	background-color:rgba(255,0,0,0.1);
	color:rgb(0,0,255);
	margin:10px;
	font-size:2em;
	
}
body
{
text-align:center;
}
p
{
font-size:2em;
}
input#submit
{
	color:black;
	height:3em;
	background-color:teal;	
	width:7em;
	font-size:3em;
}
</style>
<script src="jquery-1.7.1.js"></script>
<script>
$('document').ready(function(){
	var player = "<? echo $player; ?>"; //current player's name (p1/p2)
	colors = {'P1':'Yellow', 'P2':'Red'};
	
	var game = "<? echo $game; ?>";		//game id
	var oppPlayerName="";
	var p1name = "<? echo $p1; ?>"; //p1's actual name
	var p2name = "<? echo $p2; ?>"; //p2's actual name
	var board="1", attempt = 0, over=0;
	var move = [-1,-1];
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
	
	function pulsate(col, start, end, color)
	{
		var prop = "";
		for (i = start; i <= end; i++)
		{
			prop = 'beep'+color+' 1s linear '+(i-start)+'s';
			$('#'+(i-start)+col).css({'-moz-animation':'','animation':'','-webkit-animation':''});
			$('#'+(i-start)+col).css({'-moz-animation':prop,'animation':prop,'-webkit-animation':prop});
		}		
		var c = (color == 'R') ? "red":"yellow";
		setTimeout(function () {
			$('#'+end+col).css('background-color',c);
		},(end-start+1)*1000);	
	}
	
	function show(data)
	{			
		newBoard = data.substring(0,42);
		if (board != newBoard)
		{				
			
			board = newBoard;
			
			var status = data.substring(42);	
			
			var curMove = status.substring(0,3); 
			if (!isNaN(curMove[1]))			
			{
				status = status.substring(3);
				move[0] = curMove[1];
				move[1] = curMove[2];
				pulsate(move[1],0,move[0],curMove[0]);
			}
			
			
			for (i = 0; i <6; i++)
			{
				for (j=0;j<7;j++)
				{
					var em = $('#'+i+j);						
					
					//if this is where move was made, then dont change color
					if (!(move[0] == i && move[1] == j))
					{						
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
						else if (newData == "2")
						{							
							em.css({'background-color':'red'});
						}																	
					}					
				}
			}
								
			
			if (status.substring(0,3) == "won")
			{				
				over=1;
				
				if (status.substring(3) == player.substring(1))
					status = "Game over. You won! <a id = \"again\" href=\"http://<? echo $_SERVER['SERVER_NAME']; ?>/connect4\">Play again</a>";
				else
					status = "Game over. "+oppPlayerName+" won. Better luck next time! <a id=\"again\" href=\"http://<? echo $_SERVER['SERVER_NAME']; ?>/connect4\">Play again</a>";									
				
				$('td').each(function(index)
				{
					var j = index % 7; //column
					var i = (index-j)/7; //row					
					
					$('#'+i+j).unbind();
					
					$('#'+i+j).click(function(event)
					{							
						alert("Game over. Please create a new game to restart.");
					});	
				});

			}
			$('#status').html(status);
			$('#again').click(function(event)
			{				
				event.preventDefault();
				$.ajax({
				url: 'createGame.php',
				dataType: 'text',
				data: {'player1':'<? echo $p1; ?>', 'player2':'<? echo $p2; ?>', 'creator':'<? echo $player; ?>'},
				success: function(data)
				{
					clearInterval(pingTimer);
					$('body').html(data);
				}
				});
			});
		}
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
			newBoard = data.substring(0,42);
			attempt++;			
			if (board != newBoard) //&& attempt != 1)
			{
				show(data);					
				displayBoard();
			}
			else if (over == 0)				
			{			
				displayBoard();				
			}
		}		
		});
	}		
	displayBoard();
	
	function makeMove(i,j)
	{		
		move[0]=-1;
		move[1]=-1;
		$.ajax({
		url: 'playGame.php',
		dataType: 'text',
		cache:'false',
		data: {'id':game,'action':'attemptMove', 'row':i,'col':j,'player':player},
		success: function(data)
		{
			var status=data.substring(42);			
			if (status == "invalid")
			{
				alert("Invalid move. Please try again!");				
			}
			else if (status == "invalidwaiting")
			{
				alert("It is not your turn!");			
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
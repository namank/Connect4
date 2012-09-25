<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="description" content="Online Multiplayer Connect4 Game">
	<meta name="author" content="Namank Shah">
	<title>Welcome to Connect 4 Online!	</title>
<script src="jquery-1.7.1.js"></script>
<style>
label
{
	font-size:4em;
	color:red;	
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
<script>
$('document').ready(function(){
	$('#newgame').submit(function(event) {
		event.preventDefault();	
		$.ajax({
			url: 'createGame.php',
			dataType: 'text',
			cache:'false',
			type: 'POST',
			data: {'player1':$('#player1').val(), 'player2':$('#player2').val(), 'creator':'P1'},
			success: function(data)
			{
				$('body').html(data);
			}
		});
	});
});
</script>

</head>
<body>
<form id = "newgame" method="post" action="createGame.php">
	<label for ="player1">Your name</label> <br>
	<input type="text" autofocus required name="player1" id = "player1"><br>
	<label for "player2">Opponent's name</label><br>
	<input type="text" required name="player2" id="player2"><br><br>
	<input type="submit" id="submit" value="Create game!">	
</form>
</body>
</html>
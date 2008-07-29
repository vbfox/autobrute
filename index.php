<html>
	<head>
		<title>Auto brute</title>
		<link href="/favicon.ico" type="image/x-icon" rel="shortcut icon"/>
		<link href="brute.css" type="text/css" rel="stylesheet"/>
	</head>
<body>

<h1>Auto brute</h1>

<?php

if ( (!array_key_exists('login', $_GET))
	|| (!array_key_exists('password', $_GET)) )
{
	?>
	<form method="get" action="">
		<label for="login">Login: </label><input type="text" name="login" id="login" /><br />
		<label for="password">Password: </label><input type="password" name="password" id="password" /></br />
		<input type="submit" value="Fight for me !"/>
	</form>
	<?php

	die();
}

require_once('libbrute.php');

play_brute($_GET['login'], $_GET['password']);

?>


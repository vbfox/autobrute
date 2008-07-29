<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
	<head>
		<title>Auto brute [multi]</title>
		<link href="/favicon.ico" type="image/x-icon" rel="shortcut icon"/>
		<link href="../brute.css" type="text/css" rel="stylesheet"/>
		<script type="text/javascript" src="../swfobject.js"></script>
		<script type="text/javascript" src="../jquery.js"></script>
	</head>
<body>

<h1>Auto brute [multi]</h1>

<?php
if ( (!array_key_exists('login', $_GET))
	|| (!array_key_exists('password', $_GET))
	|| (!is_array($_GET['login']))
	)
{
?>
<p>
	<strong>Auto brute [multi]</strong> is a script to play automatically multiple players of the game
	<a href="http://virtualblackfox.labrute.fr">labrute</a>. It is made to be used as an automated
	<a href="http://www.webcron.org/click.php?ref=blackfox">WebCron</a> task.
</p>
<h2>Syntax</h2>
<p>
	The syntax if you have 3 fighters called <strong>fa</strong>, <strong>fb</strong> and <strong>fc</strong>
	all with the password <strong>pwd</strong> is :
</p>
<pre>
	http://script/url/?login[]=fa&amp;login[]=fb&amp;login[]=fc&amp;password=pwd
</pre>
<?php
}
else
{
	require_once('../libbrute.php');

	$logins = $_GET['login'];
	$first = true;
	foreach($logins as $login)
	{
		if (!$first) print("<hr class='clear' />");
		$first = false;

		play_brute($login, $_GET['password']);
	}
}
?>
</body>
</html>

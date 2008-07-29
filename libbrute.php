<?php

function progress_fail($reason = NULL)
{
	print("<span class='error'>Failed");
	if ($reason != NULL)
	{
		print(": $reason");
	}
	print(".</span></li></ul>");
}

function progress_start($name)
{
	print("<li>$name... ");
}

function progress_success($reason = NULL)
{
	print("<span class='ok'>Ok");
	if ($reason != NULL)
	{
		print(": $reason");
	}
	print(".</span></li>");
}

function play_brute($login, $password)
{
	$login_html = htmlspecialchars($login);

	swf_placeholders($login);
	print("<div class='play'>");

	print("<h2>Playing for $login_html</h2>");
	print("<ul>");

	$ch = curl_init();

	play_brute_($login, $password, $ch);

	curl_close ($ch); 
	print("</ul>");
	print("</div>");
}

function play_brute_($login, $password, $ch)
{
	$login_html = htmlspecialchars($login);
	$url_base = "http://$login.labrute.fr";

	progress_start("Login as <a href='http://$login_html.labrute.fr/cellule'>$login_html</a>");

	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.1) Gecko/2008070208 Firefox/3.0.1");
	curl_setopt($ch, CURLOPT_URL, "$url_base/login");
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "pass=".$password);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_HEADER, true);

	$login_page = curl_exec ($ch);

	if (!preg_match("/Location: \/cellule/", $login_page) ||
		!preg_match("/Set-Cookie: (.*)/u", $login_page, $cookies))
	{
		progress_fail();
		return;
	}
	$cookies = $cookies[1];
	curl_setopt($ch, CURLOPT_COOKIE, $cookies);
	curl_setopt($ch, CURLOPT_POST, false);	
	progress_success();

	progress_start('Loading jail');
	curl_setopt($ch, CURLOPT_URL, "$url_base/cellule");
	$cellule = curl_exec ($ch);

	if (preg_match("/(path=http:\/\/data.labrute.fr\/swf\/[^\"]*)\"/", $cellule, $brute_flash_vars))
	{
		$brute_flash_vars = $brute_flash_vars[1];
		brute_swf($login, $brute_flash_vars);
	}

	if (preg_match("/(infos=[^\"]*)\"/", $cellule, $inventory_flash_vars))
	{
		$inventory_flash_vars = $inventory_flash_vars[1];
		inventory_swf($login, $inventory_flash_vars);
	}

	if (preg_match("/<span>Niveau (\d+)<\/span>/", $cellule, $level)
		&& preg_match("/Force : (\d+)/", $cellule, $force)
		&& preg_match("/Agilit.. : (\d+)/", $cellule, $agility)
		&& preg_match("/Rapidit.. : (\d+)/", $cellule, $speed)
		)
	{
		set_infos_js($login, $level[1], $force[1], $agility[1], $speed[1]);		
	}
	progress_success();

	progress_start('Loading arena');

	curl_setopt($ch, CURLOPT_URL, "$url_base/arene");
	$arene = curl_exec ($ch);

	if (preg_match("/Location: \/cellule/", $arene))
	{
		progress_fail('No more fights today');
		return;
	}

	$preg_result = preg_match("/reste <em>(\d)<\/em> combat/", $arene, $combats);
	if ($preg_result == 0)
	{
		progress_fail('Combat regex error');
		print("<pre>".htmlentities($arene)."</pre>");
		return;
	}
	$combats = $combats[1];
	progress_success("$combats fights to do");

	progress_start('Search adversary names');
	$preg_result = preg_match_all("/onclick=\"document\.location='\/vs\/([^']*)';\"/", $arene, $combatants, PREG_PATTERN_ORDER);
	if ($preg_result == 0)
	{
		progress_fail('Combatants regex error');
		print("<pre>".htmlentities($arene)."</pre>");
		return;
	}
	progress_success();

	print("<li>Fights : <ul>");
	$combatants = $combatants[1];
	for($i = 0; $i < $combats; $i++)
	{
		$combatant = $combatants[$i];
		print("<li>Fighting <strong>$combatant</strong>...");

		curl_setopt($ch, CURLOPT_URL, "$url_base/vs/$combatant");
		curl_exec ($ch);
		
		print(" Done.");
	}

	print("</ul></li>");
}

function swf_placeholders($login)
{
	$login_html = htmlentities($login);
?>
	<div class="swf_brute" id="swf_brute_<? echo $login_html; ?>"></div>
	<div class="more_info" id="more_info_<? echo $login_html; ?>">
		Level : <span id="level_<? echo $login_html; ?>">?</span><br />
		Force : <span id="force_<? echo $login_html; ?>">?</span><br />
		Agility : <span id="agility_<? echo $login_html; ?>">?</span><br />
		Speed : <span id="speed_<? echo $login_html; ?>">?</span><br />
		<a href="#" onmouseover="$('#swf_inventory_<? echo $login_html; ?>').show();"
			onmouseout="$('#swf_inventory_<? echo $login_html; ?>').hide();">Inventory</a>
	</div>
	<div class="swf_inventory" id="swf_inventory_<? echo $login_html; ?>"></div>
<?php	
}

function set_infos_js($login, $level, $force, $agility, $speed)
{
	$login_html = htmlentities($login);
?>
	<script type="text/javascript">
	//<![CDATA[
	$("#level_<? echo $login_html; ?>").text("<?php echo $level; ?>");
	$("#force_<? echo $login_html; ?>").text("<?php echo $force; ?>");
	$("#agility_<? echo $login_html; ?>").text("<?php echo $agility; ?>");
	$("#speed_<? echo $login_html; ?>").text("<?php echo $speed; ?>");
	$("#more_info_<? echo $login_html; ?>").show();
	//]]>
	</script>
<?php
}

function inventory_swf($login, $flash_vars)
{
	$login_html = htmlentities($login);
?>
	<script type="text/javascript">
	//<![CDATA[
	var so = new SWFObject("http://data.labrute.fr/swf/inventory.swf?v=2","inventory_<? echo $login_html; ?>",310,600,8,"#FAF8C3");
	so.addParam("menu","false");
	so.addParam("wmode","transparent");
	so.addParam("AllowScriptAccess","always");
	so.addParam("FlashVars","<? echo $flash_vars; ?>");
	so.addParam("scale","noscale");
	so.write("swf_inventory_<? echo $login_html; ?>");
	//]]>
	</script>
<?php
}

function brute_swf($login, $flash_vars)
{
	$login_html = htmlentities($login);
?>
	<script type="text/javascript">
	//<![CDATA[
	var so = new SWFObject("http://data.labrute.fr/swf/loader.swf?v=0","brute_<? echo $login_html; ?>",90,175,8,"#FAF8C3");
	so.addParam("menu","false");
	so.addParam("wmode","transparent");
	so.addParam("AllowScriptAccess","always");
	so.addParam("FlashVars","<? echo $flash_vars; ?>");
	so.addParam("scale","noscale");
	so.write("swf_brute_<? echo $login_html; ?>");
	//]]>
	</script>
<?php
}
?>

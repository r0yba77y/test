<!DOCTYPE html>
<html style="
	font-family: Abel;
	font-size: 15px;
	cursor: default;
	background-color: #444;
	text-align: center;
	-webkit-user-select: none;
	-moz-user-select: none;
	-ms-user-select: none;
	user-select: none"
	lang="en">
<head style="margin: 0;height: 100%;overflow: hidden">

	<?php
	$payoff = array(
		"instant gratification",
		"show me new tricks",
		"don't be shy",
		"pure new pleasure seeker",
		"turning point",
		"you know that you want",
		"i'm all you require",
		"come and live your desire",
		"this is it",
		"never do what they told ya",
		"all the stars may shine bright",
		"you have to be"
	);
	$payoff = $payoff[(int)(date('i') / 5)];
	?>

	<title>BACKTEST | <?php echo $payoff; ?></title>
	<meta charset="utf-8">
	<meta name="theme-color" content="#000">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	<meta http-equiv="Cache-Control" content="no-cache">
	<link rel="shortcut icon" href="/favicon.ico">
	<link href="https://fonts.googleapis.com/css?family=Abel" rel="stylesheet">

	<style>
		a {
			text-decoration: none;
		}
	</style>

</head>

<body style="overflow: hidden">
<img style="margin-top: 150px;height: 150px" src="/logo.png">
<div style="display: none;font-size: 70px;color: darkkhaki;letter-spacing: 5px;margin-top: 100px;line-height: 60px">BACKTEST</div>
<div style="font-size: 30px;color: #BBB;margin-top: 10px;letter-spacing: 1px">
<div style="font-size: 30px;color: #BBB;margin-top: 10px;letter-spacing: 1px"><?php echo $payoff; ?></div>
</body>

</html>
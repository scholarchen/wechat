<?php>
    setcookie("user", "chen", time()+3600);
	print date("Y-m-d") . "\n";
	print_r($_GET);
	print_r($_POST);
	print_r($_REQUEST);
	print_r($_FILES);
	print_r($_COOKIE);
	$file=fopen("http://scholar-accesstoken.stor.sinaapp.com/access_token.json","r") or exit("can't open file");
	while(!feof($file))
	{
    	echo fgets($file);
	}
	fclose($file);
?>
<!doctype html>
<html>
<head>

</head>
<body >

</body>
</html>


	

	
			
    

<?

	$q=array();
	$q[]="UPDATE `".$MyOpt["tbl"]."_cron` SET module='admin' WHERE scripts='echeances'";

  	foreach($q as $i=>$query)
	{
		$sql->Update(utf8_decode($query));
	}

?>
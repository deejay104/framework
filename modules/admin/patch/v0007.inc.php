<?

	$q=array();
	$q[]="DELETE FROM `".$MyOpt["tbl"]."_cron` WHERE module='comptabilite' AND scripts='echeances'";

	$query="SELECT id FROM ".$MyOpt["tbl"]."_cron WHERE module='admin' AND script='echeances'";
	$res=$sql->QueryRow($query);

	if ($res["id"]==0)
	{		
		$q[]="INSERT INTO `".$MyOpt["tbl"]."_cron` SET description='Notification des échéances', module='admin', script='echeances', schedule='10080', actif='non'";
	}
	
  	foreach($q as $i=>$query)
	{
		$sql->Update($query);
	}

?>
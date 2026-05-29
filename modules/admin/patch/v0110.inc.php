<?php

	$q="TRUNCATE ".$MyOpt["tbl"]."_token";
	$sql->Update($q);

	$q=array();
	$query="SELECT id FROM ".$MyOpt["tbl"]."_cron WHERE module='admin' AND script='expire'";
	$res=$sql->QueryRow($query);
	if (!isset($res["id"]))
	{		
		$q[]="INSERT INTO `".$MyOpt["tbl"]."_cron` SET description='Expire les éléments', module='admin', script='expire', schedule='1440', actif='non'";
	}
	
  	foreach($q as $i=>$query)
	{
		$sql->Update($query);
	}

?>
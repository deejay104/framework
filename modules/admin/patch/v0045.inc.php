<?

	$q=array();
	$q[]="DELETE FROM ".$MyOpt["tbl"]."_config WHERE param='version'";

  	foreach($q as $i=>$query)
	{
		$sql->Update($query);
	}


?>
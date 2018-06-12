<?

	$q=array();
	$q[]="UPDATE `".$MyOpt["tbl"]."_actualites` SET uid_maj=uid_modif,dte_maj=dte_modif";
	$q[]="ALTER TABLE `".$MyOpt["tbl"]."_actualites`  DROP `uid_modif`,  DROP `dte_modif`";
	

  	foreach($q as $i=>$query)
	{
		$sql->Update($query);
	}


?>
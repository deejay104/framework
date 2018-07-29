<?

	$q=array();
	$q[]="UPDATE `".$MyOpt["tbl"]."_echeance` SET uid_creat=uid_create,dte_creat=dte_create";
	$q[]="ALTER TABLE `".$MyOpt["tbl"]."_echeance`  DROP `uid_create`,  DROP `dte_create`";

  	foreach($q as $i=>$query)
	{
		$sql->Update($query);
	}


?>
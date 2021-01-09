<?

	$q="SELECT id,password FROM ".$MyOpt["tbl"]."_utilisateurs WHERE actif='oui' AND password<>'' AND creds IS NULL";
	$sql->Query($q);
	
	$tabUser=array();

	for($i=0; $i<$sql->rows; $i++)
	{ 
		$sql->GetRow($i);

		$tabUser[$sql->data["id"]]=$sql->data["password"];
	}

	foreach($tabUser as $id=>$pwd)
	{
		$q="UPDATE ".$MyOpt["tbl"]."_utilisateurs SET creds='".password_hash($pwd, PASSWORD_BCRYPT, array('cost' => 12))."' WHERE id='".$id."'";
		$sql->Update($q);
	}

?>
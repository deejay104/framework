<?
	require ("version.php");

	$q=array();
	
	$query="SELECT COUNT(*) AS nb FROM ".$MyOpt["tbl"]."_utilisateurs";
	$res=$sql->QueryRow($query);
	
	if ($res["nb"]==0)
	{
		$q[]="INSERT INTO `".$MyOpt["tbl"]."_utilisateurs` SET nom='admin', prenom='admin', initiales='adm', password='21232f297a57a5a743894a0e4a801fc3', notification='oui', droits='SYS', actif='oui', virtuel='non', uid_maj=1, dte_maj=NOW()";
		$q[]="INSERT INTO `".$MyOpt["tbl"]."_utilisateurs` SET nom='system', prenom='', initiales='', password='', notification='non', droits='SYS', actif='oui', virtuel='oui', uid_maj=1, dte_maj=NOW()";

		$q[]="INSERT INTO `".$MyOpt["tbl"]."_groupe` SET groupe='ALL', description='Tout le monde'";
		
		$q[]="INSERT INTO `".$MyOpt["tbl"]."_droits` SET groupe='SYS', uid=1, uid_creat=1, dte_creat=NOW()";
		$q[]="INSERT INTO `".$MyOpt["tbl"]."_droits` SET groupe='SYS', uid=2, uid_creat=1, dte_creat=NOW()";
	}

	$q[]="TRUNCATE `".$MyOpt["tbl"]."_cron`";

	$q[]="INSERT INTO `".$MyOpt["tbl"]."_cron` SET description='Mail d\'actualités', module='actualites', script='sendmail', schedule='5', actif='non'";
	$q[]="INSERT INTO `".$MyOpt["tbl"]."_cron` SET description='Notification des échéances', module='admin', script='echeances', schedule='10080', actif='non'";

  	foreach($q as $i=>$query)
	{
		$sql->Update($query);
	}

?>
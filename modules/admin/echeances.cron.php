<?
// ---------------------------------------------------------------------------------------------
//   Batch de notification 
// ---------------------------------------------------------------------------------------------
?>
<?
	if ($gl_mode!="batch")
	  { FatalError("Acces refuse","Ne peut etre execute qu'en arriere plan"); }

  	require_once ("class/echeance.inc.php");

// ---- Mail du président

	$mailpre=array();
	$mailpre["name"]=$MyOpt["site_title"];
	$mailpre["mail"]=$MyOpt["from_email"];
	if ($mailpre=="")
	{
		return;
	}

	$tabPre=array();
	$lst=ListActiveUsers($sql,"",array("NotifEcheance"),"non");
	foreach($lst as $i=>$id)
	{
		$usr = new user_core($id,$sql,false,true);
		if ($usr->data["mail"]!="")
		{
			$tabPre[]=$usr->data["mail"];
		}
	}

	myPrint("Notification Echeance : ".implode(",",$tabPre));

// ---- Liste les comptes actifs
	$query="SELECT * FROM ".$MyOpt["tbl"]."_echeancetype ORDER BY description";
	$sql->Query($query);

	$lsttype=array();
	for($i=0; $i<$sql->rows; $i++)
	{
		$sql->GetRow($i);
		$lsttype[$sql->data["id"]]=$sql->data;
	}
	
	$gl_res="OK";
	
	foreach($lsttype as $id=>$d)
	{
		myPrint("* ".$d["description"]);

		$delai=$d["delai"];
		
		if ($d["notif"]=="oui")
		{
			$lstdte=array();
			$lstdte=ListeEcheanceType($sql,$id);
			foreach($lstdte as $i=>$did)
			{
				$dte = new echeance_core($did,$sql,0);
				$usr = new user_core($dte->uid,$sql,false);
				$ret=true;

				if (date_diff_txt($dte->Val(),date("Y-m-d"))>0)
				{
					myPrint($usr->fullname." - ".$dte->description." echue");

					$tabvar=array();
					$tabvar["description"]=$dte->description;
					$tabvar["type"]="est échue depuis le";
					$tabvar["date"]=sql2date($dte->Val());
					
					SendMailFromFile($mailpre,$usr->data["mail"],$tabPre,"[".$MyOpt["site_title"]."] : ".$dte->description." échue",$tabvar,"echeances");
				}
				else if (date_diff_txt($dte->Val(),date("Y-m-d"))>-$delai*24*3600)
				{
					myPrint($usr->fullname." - ".$dte->description." expire dans moins de ".$delai." jours");

					$tabvar=array();
					$tabvar["description"]=$dte->description;
					$tabvar["type"]="est échue depuis le";
					$tabvar["date"]=sql2date($dte->Val());
					
					SendMailFromFile($mailpre,$usr->data["mail"],$tabPre,"[".$MyOpt["site_title"]."] : ".$dte->description." arrive à échéance le ".sql2date($dte->Val()),$tabvar,"echeances");
				}
				if (!$ret)
				{
					$gl_res="ERREUR";
				}
			}
		}
	}
	
	
	myPrint($gl_res);
?>
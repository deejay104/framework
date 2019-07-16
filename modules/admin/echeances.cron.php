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

// ---- Liste les échéances actives
	$query="SELECT * FROM ".$MyOpt["tbl"]."_echeancetype WHERE actif='oui' AND notif='oui' ORDER BY description";
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
		
		$lstdte=array();
		
		$ech=new echeancetype_core($id,$sql);
		if ($ech->val("recipient")=="")
		{
			$lstdte=$ech->ListeEcheance();
			foreach($lstdte as $i=>$did)
			{
				$dte = new echeance_core($did,$sql,0);
				$usr = new user_core($dte->uid,$sql,false);
				
				if ($usr->actif=="oui")
				{
					$ret=true;

					if (date_diff_txt($dte->Val(),date("Y-m-d"))>0)
					{
						myPrint($usr->fullname." - ".$dte->description." echue");

						$tabvar=array();
						$tabvar["description"]=$dte->description;
						$tabvar["type"]="est échue depuis le";
						$tabvar["date"]=sql2date($dte->Val());
						
						SendMailFromFile($mailpre,$usr->data["mail"],$tabPre,"[".$MyOpt["site_title"]."] : ".$dte->description." échue",$tabvar,"echeance_nok");
					}
					else if (date_diff_txt($dte->Val(),date("Y-m-d"))>-$delai*24*3600)
					{
						myPrint($usr->fullname." - ".$dte->description." expire dans moins de ".$delai." jours");

						$tabvar=array();
						$tabvar["description"]=$dte->description;
						$tabvar["type"]="expire le";
						$tabvar["date"]=sql2date($dte->Val());
						
						SendMailFromFile($mailpre,$usr->data["mail"],$tabPre,"[".$MyOpt["site_title"]."] : ".$dte->description." arrive à échéance le ".sql2date($dte->Val()),$tabvar,"echeance_ok");
					}
					if (!$ret)
					{
						$gl_res="ERREUR";
					}
				}
			}
		}
		else
		{
			// Need to add group notification
			// List all group users and send the email to them
			// Maybe need to create a new email template ?
			$lstdte=$ech->ListeEcheance();
			foreach($lstdte as $i=>$did)
			{
				$dte = new echeance_core($did,$sql,0);
				$ret=true;
				if (date_diff_txt($dte->Val(),date("Y-m-d"))>0)
				{
					myPrint($ech->val("recipient")." - ".$dte->description." echue");

					$tabvar=array();
					$tabvar["description"]=$dte->description;
					$tabvar["type"]="est échue depuis le";
					$tabvar["date"]=sql2date($dte->Val());

					$grp=new groupe_core(0,$sql,$ech->val("recipient"));
					$tabdst=$grp->ListUsers();
					foreach ($tabdst as $i=>$uid)
					{					
						$usr = new user_core($uid,$sql,false);
						myPrint($usr->fullname." - ".$dte->description." echue");
						SendMailFromFile($mailpre,$usr->data["mail"],array(),"[".$MyOpt["site_title"]."] : ".$dte->description." échue",$tabvar,"echeance_nok");
					}
				}
				else if (date_diff_txt($dte->Val(),date("Y-m-d"))>-$delai*24*3600)
				{
					myPrint($ech->val("recipient")." - ".$dte->description." expire dans moins de ".$delai." jours");

					$tabvar=array();
					$tabvar["description"]=$dte->description;
					$tabvar["type"]="expire le";
					$tabvar["date"]=sql2date($dte->Val());

					$grp=new groupe_core(0,$sql,$ech->val("recipient"));
					$tabdst=$grp->ListUsers();
					foreach ($tabdst as $i=>$uid)
					{					
						$usr = new user_core($uid,$sql,false);
						myPrint($usr->fullname." - ".$dte->description." expire dans moins de ".$delai." jours");
				
						SendMailFromFile($mailpre,$usr->data["mail"],array(),"[".$MyOpt["site_title"]."] : ".$dte->description." arrive à échéance le ".sql2date($dte->Val()),$tabvar,"echeance_ok");
					}
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
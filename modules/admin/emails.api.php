<?php
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

 // ---- Vérifie les paramètres
	$fonc=checkVar("fonc","varchar");

// ---- 
	$ret=array();
	$ret["result"]="OK";
	$ret["data"]="";

	$sql->show=true;

	$ret["result"]="OK";
	$ret["data"]="";

	$tabMails=array();

	if (file_exists("modules/admin/conf/emails.tmpl.php"))
	{
		require_once("modules/admin/conf/emails.tmpl.php");
	}
	if (file_exists($appfolder."/modules/admin/conf/emails.tmpl.php"))
	{
		require_once($appfolder."/modules/admin/conf/emails.tmpl.php");
	}

// *****************************************************************

	foreach($tabMails as $name=>$d)
	{
		$query="SELECT id FROM ".$MyOpt["tbl"]."_mailtmpl WHERE nom='".$name."'";
		$res=$sql->QueryRow($query);

		if ($res["id"]>0)
		{
			if ($fonc=="init")
			{
				$ret["data"].=AjoutLog("- Reset: ".$name);
				$q="UPDATE ".$MyOpt["tbl"]."_mailtmpl SET titre='".addslashes($d["titre"])."', corps='".addslashes($d["mail"])."', balise='".$d["balise"]."',uid_maj='".$gl_uid."', dte_maj='".now()."' WHERE id=".$res["id"];
				$sql->Update($q);
			}
			else
			{
				$ret["data"].=AjoutLog("- Update: ".$name);
				$q="UPDATE ".$MyOpt["tbl"]."_mailtmpl SET balise='".$d["balise"]."',uid_maj='".$gl_uid."', dte_maj='".now()."' WHERE id=".$res["id"];
				$sql->Update($q);
			}
		}
		else
		{
			$ret["data"].=AjoutLog("- Insert: ".$name);
			$q="INSERT INTO ".$MyOpt["tbl"]."_mailtmpl SET nom='".$name."',titre='".addslashes($d["titre"])."', corps='".addslashes($d["mail"])."', balise='".$d["balise"]."', uid_creat='".$gl_uid."', dte_creat='".now()."',uid_maj='".$gl_uid."', dte_maj='".now()."'";
			$sql->Insert($q);
		}
	}


// ---- Renvoie le log
	echo json_encode($ret);
  
  
function AjoutLog($txt)
{
	return htmlentities($txt,ENT_HTML5,"ISO-8859-1")."<br />";
}
?>
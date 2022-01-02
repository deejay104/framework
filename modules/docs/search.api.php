<?php

// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

	if (!GetDroit("AccesDocuments"))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- List all papers from a folder
	require_once ("class/folder.inc.php");
	require_once ("class/document.inc.php");

	$id=checkVar("id","numeric");
	$crit=checkVar("crit","varchar",50);
	$filter=checkVar("filter","varchar",10);
	$date=checkVar("date","date");

	if (($date=="0000-00-00") && ($crit==""))
	{
		$date=date("Y-m-d");
	}

	
	$lst=ListActivePapers($id,$sql);

	$result=array();
	$result["crit"]=$crit;
	$result["date"]=$date;
	$result["filter"]=$filter;

	$k=0;
	foreach($lst as $i=>$d)
	{
		$ok=false;
		$line=array();

		$line=$d;

		if ($crit!="")
		{
			if (preg_match("/".$crit."/i",$d["title"]))
			{
				$ok=true;
			}
		}

		if ($date!="0000-00-00")
		{
			if ( ((strtotime($d["dte_start"])<=strtotime($date)) && (strtotime($d["dte_end"])>=strtotime($date))) || ($d["dte_end"]=="0000-00-00") )
			{
				$ok=true;
			}
		}
		else if ($filter=="all")
		{
			$ok=true;
		}
		
		$usr = new user_core($d["uid_creat"],$sql,false);
		$line["author"]=$usr->fullname;
		$line["created"]=DisplayDate($d["dte_creat"]);

		// Affiche les pièces jointes au message
		$lstdoc=ListDocument($sql,$d["id"],"paper");

		if ((is_array($lstdoc)) && (count($lstdoc)>0))
		{
			foreach($lstdoc as $ii=>$did)
			{
				$doc = new document_core($did,$sql);
				$doc->editmode="regular";
				$line["doc"][$did]=$doc->Affiche();

				if (preg_match("/".$crit."/",$doc->name))
				{
					$ok=true;
				}
			}
		}
		
		if ($ok)
		{
			$result["data"][$k]=$line;
			$k++;
		}
	}
	
	echo json_encode($result);	
?>
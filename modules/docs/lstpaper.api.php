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
	$filter=checkVar("filter","varchar",10,"active");
	$sort=checkVar("sort","varchar",10,"dte_creat");
	$limit=checkVar("limit","numeric");
	$date=checkVar("date","date");


	if ($date=="0000-00-00")
	{
		$date=date("Y-m-d");
	}

	
	$lst=ListActivePapers($id,$sql,$sort);

	$result=array();
	$result["crit"]=$crit;
	$result["date"]=$date;
	$result["filter"]=$filter;

	$k=0;
	foreach($lst as $i=>$d)
	{
		if (($limit>0) && ($k>=$limit))
		{
			break;
		}

		$okcrit=false;
		$okfilter=false;
		$line=array();

		$line=$d;

		if ($crit!="")
		{
			if (preg_match("/".$crit."/i",$d["title"]))
			{
				$okcrit=true;
			}
		}
		else
		{
			$okcrit=true;
		}

		if (($date!="0000-00-00") && ($filter!="all"))
		{
			if ((strtotime($d["dte_start"])<=strtotime($date)) && (strtotime($d["dte_end"])>=strtotime($date)))
			{
				$okfilter=true;
			}
			else if ((strtotime($d["dte_start"])<=strtotime($date)) && ($d["dte_end"]=="0000-00-00"))
			{
				$okfilter=true;
			}
			else if (($d["dte_start"]=="0000-00-00") && (strtotime($d["dte_end"])>=strtotime($date)))
			{
				$okfilter=true;
			}
			else if ((($d["dte_start"]=="0000-00-00")) && ($d["dte_end"]=="0000-00-00") )
			{
				$okfilter=true;
			}
		}
		else if ($filter=="all")
		{
			$okfilter=true;
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

				if (preg_match("/".$crit."/i",$doc->name))
				{
					$okcrit=true;
				}
			}
		}
// echo "crit=".$crit." filter=".$filter." okcrit:".$okcrit." okfilter:".$okfilter." - ";		
		if (($okcrit) && ($okfilter))
		{
			$result["data"][$k]=$line;
			$k++;
		}
	}
	
	echo json_encode($result);	
?>
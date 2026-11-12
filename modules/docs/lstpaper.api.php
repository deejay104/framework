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

	
//	$lst=ListActivePapers($id,$sql,$sort);

	$result=array();
	$result["crit"]=$crit;
	$result["date"]=$date;
	$result["filter"]=$filter;
	$result["data"]=array();


	$q ="SELECT doc.*,r.dte_read FROM ".PREFIX_TBL."_doc_paper AS doc ";
	$q.="LEFT JOIN ".PREFIX_TBL."_doc_read AS r ON doc.id=r.id_paper ";
	$q.="WHERE doc.actif='oui' ".(($id>0) ? "AND doc.id_folder=".$id : "")." ORDER BY doc.".$sort;

	$sql->Query($q);

	$nb=0;
	$lst=array();
	for($i=0; $i<$sql->rows; $i++)
	{ 
		$sql->GetRow($i);
		$lst[$sql->data["id"]]=array();
		foreach($sql->data as $k=>$v)
		{
			if (!is_numeric($k))
			{
				$lst[$sql->data["id"]][$k]=$v;
			}
		}
	}

	foreach($lst as $i=>$line)
	{
		if (($limit>0) && ($nb>$limit))
		{
			break;
		}

		$okcrit=false;
		$okfilter=false;

		if ($crit!="")
		{
			if (preg_match("/".$crit."/i",$line["title"]))
			{
				$okcrit=true;
			}

			$ref=$line["nature"]."-".CompleteTxt($line["refid"],3,"0");
			if (preg_match("/".$crit."/i",$ref))
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
			if ((strtotime($line["dte_start"])<=strtotime($date)) && (strtotime($line["dte_end"])>strtotime($date)))
			{
				$okfilter=true;
			}
			else if ((strtotime($line["dte_start"])<=strtotime($date)) && ($line["dte_end"]=="0000-00-00"))
			{
				$okfilter=true;
			}
			else if (($line["dte_start"]=="0000-00-00") && (strtotime($line["dte_end"])>strtotime($date)))
			{
				$okfilter=true;
			}
			else if ((($line["dte_start"]=="0000-00-00")) && ($line["dte_end"]=="0000-00-00") )
			{
				$okfilter=true;
			}
		}
		else if ($filter=="all")
		{
			$okfilter=true;
		}

		$line["reference"]=$line["nature"]."-".CompleteTxt($line["refid"],3,"0");

		$line["begin"]=sql2date($line["dte_start"],"jour");
		$line["expire"]=sql2date($line["dte_end"],"jour");

		$usr = new user_core($line["uid_creat"],$sql,false);
		$line["author"]=$usr->fullname;
		$line["created"]=sql2date($line["dte_creat"],"jour");

		$usr = new user_core($line["uid_checked"],$sql,false);
		$line["reviewer"]=$usr->fullname;
		$line["checked"]=sql2date($line["dte_creat"],"jour");

		$usr = new user_core($line["uid_approved"],$sql,false);
		$line["approver"]=$usr->fullname;
		$line["approved"]=sql2date($line["dte_creat"],"jour");

		$folder = new folder_core($line["id_folder"],$sql,false);
		$line["folder"]=$folder->export();

		// Si on a le droit de modifier le dossier, on peut voir les fiches en draft ou submitted
		if ((!GetDroit($folder->val("group_write"))) && ($line["status"]!="open") && ($line["status"]!="approved"))
		{
			$okfilter=false;
		}

		// Affiche les pièces jointes au message
		$lstdoc=ListDocsPaper($sql,$line["id"]);

		if ((is_array($lstdoc)) && (count($lstdoc)>0))
		{
			foreach($lstdoc as $ii=>$did)
			{
				$doc = new paper_docs_core($did,$sql);
				$attr=$doc->getAttributes();
				$line["doc"][$did]["id"]=$did;
				$line["doc"][$did]["name"]=$doc->val("name");
				$line["doc"][$did]["read"]=$doc->read($gl_uid);
				$line["doc"][$did]["icon"]=$attr["icon"];
				$line["doc"][$did]["color"]=$attr["color"];

				if (preg_match("/".$crit."/i",$doc->val("name")))
				{
					$okcrit=true;
				}
			}
		}


		// Nombre de documents lus
		$line["nbread"]=null;
		$line["nbtotalread"]=null;
		if ($line["mandatory"]=="oui")
		{
			$query="SELECT COUNT(*) AS nb FROM ".PREFIX_TBL."_doc_read WHERE id_paper=".$line["id"];
			$res=$sql->QueryRow($query);
			$line["nbread"]=$res["nb"];

			$query="SELECT COUNT(*) AS nb FROM ".PREFIX_TBL."_droits WHERE groupe='".$folder->val("group_read")."'";
			$res=$sql->QueryRow($query);
			$line["nbtotalread"]=$res["nb"];
		}

		// Ranvoie la ligne
		if (($okcrit) && ($okfilter))
		{
			$result["data"][$nb]=$line;
			$nb++;
		}
	}
	
	echo json_encode($result);	
?>
<?php

// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

	if (!GetDroit("AccesDocuments"))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- List all papers from a folder
	require_once ("class/document.inc.php");
	require_once ("class/folder.inc.php");

	$id=checkVar("id","numeric");

	$paper=new paper_core($id,$sql);
	$folder=new folder_core($paper->val("id_folder"),$sql);
	if (!GetDroit($folder->val("group_read")))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

	
	$lst=ListActiveComments($id,$sql);

	$result=array();
	foreach($lst as $i=>$d)
	{
		$result["data"][$i]["id"]=$d["id"];
		$result["data"][$i]["id_paper"]=$d["id_paper"];
		$result["data"][$i]["description"]=nl2br($d["description"]);

		$usr = new user_core($d["uid_creat"],$sql,false);

		$result["data"][$i]["author"]=$usr->fullname;
		$result["data"][$i]["created"]=DisplayDate($d["dte_creat"]);

		$lstdoc=ListDocument($sql,$d["uid_creat"],"avatar");

		if (count($lstdoc)>0)
		{
			$img=new document_core($lstdoc[0],$sql);
			$result["data"][$i]["avatar"]=$img->GenerePath(64,64);
		}
		else
		{
			$result["data"][$i]["avatar"]="static/images/icn64_membre.png";
		}
	}
	
	echo json_encode($result);	
?>
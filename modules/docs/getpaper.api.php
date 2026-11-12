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

	$folder=new folder_core($id,$sql);
	if (!GetDroit($folder->val("group_read")))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

	
	$paper = new paper_core($id,$sql);

	$result=array();
	$result=$paper->export();

	$result["data"]["reference"]=$paper->ref();


	$usr = new user_core($paper->uid_creat,$sql,false);
	$result["data"]["author"]=$usr->fullname;
	$result["data"]["dte_create"]=sql2date($paper->dte_creat,"jour");
	$result["data"]["created"]=$paper->dte_creat;

	$usr = new user_core($paper->val("uid_checked"),$sql,false);
	$result["data"]["usr_checked"]=$usr->fullname;
	$usr = new user_core($paper->val("uid_approved"),$sql,false);
	$result["data"]["usr_approved"]=$usr->fullname;

	$read = new paper_read_core($id,$sql);
	$read->loadFromField(array("id_paper"=>$id,"uid"=>$gl_uid));
	$result["data"]["read"]=($read->val("dte_read")!="0000-00-00 00:00:00") ? true : false;
	$result["data"]["dte_read"]=$read->val("dte_read");
	

	// Affiche les pièces jointes au message
	$lstdoc=ListDocsPaper($sql,$id);

	if ((is_array($lstdoc)) && (count($lstdoc)>0))
	{
		foreach($lstdoc as $ii=>$did)
		{
			$doc = new paper_docs_core($did,$sql);
			$attr=$doc->getAttributes();
			$result["docs"][$did]["id"]=$did;
			$result["docs"][$did]["name"]=$doc->val("name");
			$result["docs"][$did]["read"]=$doc->read($gl_uid);
			$result["docs"][$did]["icon"]=$attr["icon"];
			$result["docs"][$did]["color"]=$attr["color"];
		}
	}

	// Affiche les documents lus
	$result["read"]=$paper->getReads();


	echo json_encode($result);	
?>
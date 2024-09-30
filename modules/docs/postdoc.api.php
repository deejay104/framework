<?php
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- 
	$sql->show=false;
	
	$id=checkVar("id","numeric");
	$folder=checkVar("folder","numeric");

	require_once ("class/folder.inc.php");
	require_once ("class/document.inc.php");

// ----
	$result=array();

	$paper=new paper_core($id,$sql);

	if ($id==0)
	{
		$paper->Create();
		$id=$paper->id;
		$paper->valid("title",$_FILES["file"]["name"]);
		$paper->valid("id_folder",$folder);
		$paper->valid("dte_start",date("Y-m-d"));
		$paper->Save();
	}

	$folder=new folder_core($paper->val("id_folder"),$sql);

	if (!GetDroit($folder->val("group_write")))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

	$doc = new document_core(0,$sql,"paper");
	$doc->droit=$folder->val("group_read");
	$r=$doc->Save($id,$_FILES["file"]["name"],$_FILES["file"]["tmp_name"]);

	$result["id"]=$id;
	$result["status"]="ok";

	if ($r!="")
	{
		$result["error"]=$r;
		$result["status"]="500";
	}
	$doc->editmode="regular";

	$result["type"]=$doc->type;
	$result["title"]=$paper->val("title");
	$result["docid"]=$doc->id;
	$result["link"]=$doc->Affiche();
	$result["author"]=$myuser->fullname;
	$result["created"]=now();

	echo json_encode($result);	
?>
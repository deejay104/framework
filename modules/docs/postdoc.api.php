<?php
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- 
	$sql->show=false;
	
	$id=checkVar("id","numeric");
	$idfolder=checkVar("folder","numeric");
	$fonc=checkVar("fonc","varchar");

	require_once ("class/folder.inc.php");
	require_once ("class/document.inc.php");

	if ((GetDroit("ModifUserAll")) || (GetDroit("ModifUserDocument")))
	{
		$uid=checkVar("uid","numeric");
	}
	else
	{
		$uid=$gl_uid;
	}

// ----
	$result=array();

	if ($idfolder>0)
	{
		$paper=new paper_core($id,$sql);

		if ($id==0)
		{
			$paper->Create();
			$id=$paper->id;
			$paper->valid("title",$_FILES["file"]["name"]);
			$paper->valid("id_folder",$idfolder);
			$paper->valid("dte_start",date("Y-m-d"));
			$paper->Save();
		}

		$folder=new folder_core($paper->val("id_folder"),$sql);

		if (!GetDroit($folder->val("group_write")))
		{
			header("HTTP/1.0 401 Unauthorized");
			exit; 
		}

		$doc = new document_core(0,$sql,"paper");
		$doc->droit=$folder->val("group_read");
		$r=$doc->Save($id,$_FILES["file"]["name"],$_FILES["file"]["tmp_name"]);

		$result["id"]=$id;
		$result["status"]=200;

		if ($r!="")
		{
			$result["error"]=$r;
			$result["status"]="500";
		}
		$doc->editmode="regular";

		$result["command"]="new";
		$result["type"]=$doc->type;
		$result["title"]=$paper->val("title");
		$result["docid"]=$doc->id;
		$result["link"]=$doc->Affiche();
		$result["author"]=$myuser->fullname;
		$result["created"]=now();
	}
	else if ($fonc=="delete")
	{
	
		if ($id>0)
		{
			$result["command"]="delete";
			$doc = new document_core($id,$sql);
			if (($doc->uid==$gl_uid) || (GetDroit("ModifUserAll")) || (GetDroit("ModifUserDocument")))
			{
				$result["status"]=200;
				$doc->Delete();
			}
			else
			{
				$result["status"]=401;
			}
				
		}
		else
		{
			$result["status"]=500;
		}
		$result["id"]=$doc->id;
	}
	else
	{
		$name=checkVar("name","varchar");

		$doc = new document_core($id,$sql);

		if ($id>0)
		{
			$result["command"]="update";
			$doc->name=$name;
			$doc->Update();
		}
		else
		{
			$result["command"]="add";
			$doc->droit="ALL";
			$r=$doc->Save($uid,$_FILES["file"]["name"],$_FILES["file"]["tmp_name"]);
		}

		$doc->editmode="regular";

		$result["id"]=$doc->id;
		$result["status"]=200;
		$result["type"]=$doc->type;
		$result["title"]=$doc->name;
		$result["docid"]=$doc->id;
		$result["link"]=$doc->Affiche();
		$result["author"]=$myuser->fullname;
		$result["created"]=now();
	}

	echo json_encode($result);	
?>
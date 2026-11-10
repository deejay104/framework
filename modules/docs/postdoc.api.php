<?php
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- 
	$sql->show=false;
	
	$id=checkVar("id","numeric");
	$idfolder=checkVar("folder","numeric");
	$dest=checkVar("dest","varchar",10,"doc");
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

	if (($idfolder>0) && ($dest="paper"))
	{
		$paper=new paper_core($id,$sql);
		if ($id==0)
		{
			$paper->valid("title",$_FILES["file"]["name"]);
			$paper->valid("id_folder",$idfolder);
			$paper->valid("dte_start",date("Y-m-d"));
		}

		$folder=new folder_core($paper->val("id_folder"),$sql);

		if (!GetDroit($folder->val("group_write")))
		{
			apiError(401,"Not allowed to upload to this folder",true,"",array());
		}

		if ($id==0)
		{
			$paper->Save();
			$id=$paper->id;
		}
		$result["id"]=$id;
		$result["status"]=200;

		$doc = new document_core(0,$sql,"paper");
		$doc->droit=$folder->val("group_read");
		$r=$doc->Save($id,$_FILES["file"]["name"],$_FILES["file"]["tmp_name"],$_FILES["file"]["size"]);

		if ($r!="")
		{
			$result["error"]=$r;
			$result["status"]="500";
		}

		$refdoc = new paper_docs_core(0,$sql);
		$refdoc->valid("id_paper",$id);
		$refdoc->valid("id_doc",$doc->id);
		$refdoc->valid("name",$doc->originalname);
		$refdoc->Save();

		$doc->editmode="regular";
		$attr=$doc->getAttributes();

		$result["command"]="new";
		$result["data"]["title"]=$paper->val("title");

		$result["data"]["id"]=$refdoc->id;
		$result["data"]["name"]=$refdoc->val("name");
		$result["data"]["read"]=$refdoc->read($gl_uid);
		$result["data"]["icon"]=$attr["icon"];
		$result["data"]["color"]=$attr["color"];


		/*
		$result["name"]=$doc->originalname;
		$result["docid"]=$refdoc->id;
		$result["link"]=$doc->Affiche();
		$result["author"]=$myuser->fullname;
		$result["created"]=now();
		$result["icon"]="mdi-file-".$attr["icon"];
		$result["color"]=$attr["color"];
		*/
	}
	else if ($fonc=="delete")
	{
		if ($id>0)
		{
			if ($dest=="doc")
			{
				$result["command"]="remove doc";
				$doc = new document_core($id,$sql);
				if (($doc->uid==$gl_uid) || (GetDroit("ModifUserAll")) || (GetDroit("ModifUserDocument")))
				{
					$result["status"]=200;
					$doc->Delete();
				}
				else
				{
					apiError(401,"Not allowed to upload to this folder",true,"",array());
				}	
			}
			else
			{
				$result["command"]="remove paper doc";
				$doc = new paper_docs_core($id,$sql);
				$paper=new paper_core($doc->val("id_paper"),$sql);		
				$folder=new folder_core($paper->val("id_folder"),$sql);
		
				if (!GetDroit($folder->val("group_write")))
				{
					apiError(401,"Not allowed to upload to this folder",true,"",array());
				}
				$doc->Delete();
			}				
		}
		else
		{
			apiError(402,"Document ID not set","bad_parameter");
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
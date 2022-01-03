<?php
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

	if (!GetDroit("CreeDossier"))
	  { header("HTTP/1.0 401 Unauthorized (CreeDossier)"); exit; }

// ---- 
	$sql->show=false;
	
	$id=checkVar("id","numeric");
	$title=checkVar("title","varchar",100);
	$description=checkVar("description","text");
	$group_read=checkVar("group_read","varchar",3);
	$group_write=checkVar("group_write","varchar",3);

	require_once ("class/folder.inc.php");
	require_once ("class/document.inc.php");

// ----
	$result=array();

	$folder=new folder_core($id,$sql);

	if ($title!="") { $folder->valid("title",$title); }
	if ($description!="") { $folder->valid("description",$description); }
	if ($group_read!="") 
	{
		if (($id>0) && ($group_read!=$folder->val("group_read")))
		{
			$result["group_read"]=$group_read;

			$lst=ListActivePapers($id,$sql);

			foreach($lst as $i=>$d)
			{
				$lstdoc=ListDocument($sql,$d["id"],"paper");

				if ((is_array($lstdoc)) && (count($lstdoc)>0))
				{
					foreach($lstdoc as $ii=>$did)
					{
						$doc = new document_core($did,$sql);
						$doc->droit=$group_read;
						$doc->Update();
					}
				}
			}

		}
		$folder->valid("group_read",$group_read);
	}
	if ($group_write!="") { $folder->valid("group_write",$group_write); }

	$folder->Save();

	$result["id"]=$folder->id;
	$result["status"]="ok";

	echo json_encode($result);	
?>
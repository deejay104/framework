<?php

// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

	if (!GetDroit("AccesDocuments"))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- List all papers from a folder
	require_once ("class/folder.inc.php");
	require_once ("class/document.inc.php");

	if ((GetDroit("ModifUserAll")) || (GetDroit("ModifUserDocument")))
	{
		$uid=checkVar("id","numeric");
	}
	else
	{
		$uid=$gl_uid;
	}

    $lstdoc=ListDocument($sql,$uid,"document");
			
	$result=array();

	$k=0;
	foreach($lstdoc as $i=>$d)
	{
        $line=array();

        $doc = new document_core($d,$sql);


		$usr = new user_core($doc->uid_creat,$sql,false);
		$line["id"]=$doc->id;
		$line["title"]=$doc->name;
		$line["author"]=$usr->fullname;
		$line["created"]=DisplayDate($doc->dte_creat);

//				$doc = new document_core($did,$sql);
				$doc->editmode="read";
				$line["doc"][$d]["url"]=$doc->Affiche();
				$line["doc"][$d]["name"]=$doc->originalname;
                $result["data"][$k]=$line;
                $k++;
	}
	
	echo json_encode($result);	
?>
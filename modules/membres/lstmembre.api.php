<?php

// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }


// ---- List all papers from a folder

	$id=checkVar("id","numeric");
	$crit=checkVar("crit","varchar",50);
	$limit=checkVar("limit","numeric");

	
	$lstusr=ListActiveUsers($sql,"std","","non");

	$result=array();
	$result["status"]=200;
	$result["crit"]=$crit;
	$result["data"]=array();

	$k=0;
	foreach($lstusr as $i=>$d)
	{
		if (($limit>0) && ($k>=$limit))
		{
			break;
		}

		$usr = new user_core($d,$sql,false);

		if ($crit!="")
		{
			$okcrit=false;
			if (preg_match("/".$crit."/i",$usr->fullname))
			{
				$okcrit=true;
			}
		}
		else
		{
			$okcrit=true;
		}

	
		if ($okcrit)
		{
			$result["data"][$k]["id"]=$usr->id;
			$result["data"][$k]["name"]=$usr->fullname;
			$k++;
		}
	}
	
	echo json_encode($result);	
?>
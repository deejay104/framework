<?
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- 
	$sql->show=false;
	
	$id=checkVar("id","numeric");

	require_once ("class/amelioration.inc.php");
	
	$res=array();

	if ($id>0)
	{
		$pb = new amelioration_core($id,$sql);
		$lst=$pb->ListeCommentaire();

		foreach($lst as $i=>$d)
		{
			foreach($d as $k=>$v)
			{
				if (($k!="usr_creat") && (!is_numeric($k)))

				{
					$res["lst"][$i][$k]=utf8_encode($v);
				}
			}
			$res["lst"][$i]["uid_creat"]=$res["lst"][$i]["uid_dist"];
		}
	}

	// Send JSON to the client.
	echo json_encode($res);

?>
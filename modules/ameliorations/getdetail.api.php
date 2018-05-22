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
		$pb = new amelioration_class($id,$sql);

		foreach($pb->data as $k=>$v)
		{
			$res["data"][$k]=utf8_encode($v);
		}
	}

	// Send JSON to the client.
	echo json_encode($res);

?>
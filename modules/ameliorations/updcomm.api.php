<?
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- 
	$sql->show=false;
	
	// $id=checkVar("id","numeric");

	require_once ("class/amelioration.inc.php");
	
	$res=array();

	$data=json_decode(file_get_contents('php://input'), true);
error_log(print_r($data,true));
	if (count($data)>0)
	{
		$id=$data["fid"];
		$txt=utf8_decode($data["description"]);
		$uid=$data["uid_dist"];

		$pb = new amelioration_class($id,$sql);
		$pb->AddCommentaire($txt,$uid);
		$res["result"]="OK";
	}
	else
	{
		$res["result"]="NOK";
	}
	// Send JSON to the client.
	echo json_encode($res);

?>
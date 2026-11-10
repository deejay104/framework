<?php


class tags_core extends objet_core {
	protected $table="tags";
	protected $mod="document";
	protected $rub="index";

	protected $droit=array();

	protected $fields=array(
		"tag_id"=>Array("type" => "numeric"),
		"obj_id"=>Array("type" => "numeric"),
		"obj_type"=>Array("type" => "varchar", "len"=>10),
	);

	protected $tabList=array(

	);
}

class tags_conf_core extends objet_core {
	protected $table="tags_conf";
	protected $mod="document";
	protected $rub="index";

	protected $droit=array();

	protected $fields=array(
		"scope"=>Array("type" => "enum", "default"=>"audience"),
		"name"=>Array("type" => "varchar", "len"=>10),
	);
	
	protected $tabList=array(
		"scope"=>array(
			"fr"=>array('audience'=>'Public concerné','topic'=>'Thème','type'=>'Nature document','aircraft'=>'Avion'),
			"en"=>array('audience'=>'Audience','topic'=>'Topic','type'=>'Doc type','aircraft'=>'Avion'),
			"ca"=>array('audience'=>'Public concerné','topic'=>'Thème','type'=>'Approuvé','aircraft'=>'Avion'),
		),
	);


}

?>
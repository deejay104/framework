<?php


class tags_core extends objet_core {
	protected $table="tags";
	protected $mod="document";
	protected $rub="index";

	protected $droit=array();

	protected $fields=array(
		"id_tag"=>Array("type" => "numeric"),
		"id_obj"=>Array("type" => "numeric"),
		"type_obj"=>Array("type" => "varchar", "len"=>10),
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
		"name"=>Array("type" => "varchar", "len"=>15),
	);
	
	protected $tabList=array(
		"scope"=>array(
			"fr"=>array('audience'=>'Public concerné','topic'=>'Thème','type'=>'Nature document','aircraft'=>'Avion'),
			"en"=>array('audience'=>'Audience','topic'=>'Topic','type'=>'Doc type','aircraft'=>'Avion'),
			"ca"=>array('audience'=>'Public concerné','topic'=>'Thème','type'=>'Approuvé','aircraft'=>'Avion'),
		),
	);

	protected $tabScope=array(
		"audience"=>array("label"=>'Public', "color"=>"#2f7fd1"),
		"topic"=>array("label"=>'Thème', "color"=>"#2e9e5b"),
		"type"=>array("label"=>'Nature', "color"=>"#8e5bd1"),
		"aircraft"=>array("label"=>'Avion', "color"=>"#c27c0e"),
	);

	function val($key)
	{
		if ($key=="color")
		{
			return $this->tabScope[$this->data["scope"]]["color"];
		}
		else if ($key=="label")
		{
			return $this->tabScope[$this->data["scope"]]["label"];
		}

		return parent::val($key);
	}
}

function ListTagsConf($sql,$status="",$order=array())
{
	$crit=array("actif"=>"oui");
	if ($status!="")
	{
		$crit["status"]=$status;
	}
	return ListeObjets($sql,"tags_conf",array("id"),$crit,$order);
}

?>
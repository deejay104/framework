<?
/*
    MnMs Framework
    Copyright (C) 2018 Matthieu Isorez

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


class amelioration_class extends objet_core
{
	protected $table="ameliorations";
	protected $mod="ameliorations";
	protected $rub="detail";

	protected $droit=array("status"=>"ModifAmeliorationStatus");
	protected $type=array("description"=>"text","status"=>"enum","module"=>"enum");
	
	protected $tabList=array(
			"status"=>array('1new'=>'Nouveau','2sched'=>'Prochaine version','3inprg'=>'En cours','4test'=>'En test','5close'=>'Publié'),
			"module"=>array("core"=>"Framework","user"=>"Utilisateur","admin"=>"Administration","docs"=>"Documents","custom"=>"Autre")
			);

	# Constructor
	function __construct($id=0,$sql)
	{
		$this->data["titre"]="";
		$this->data["description"]="";
		$this->data["version"]="";
		$this->data["status"]="";
		$this->data["module"]="";



		parent::__construct($id,$sql);
		
		// print_r($this);
	}

	function AddCommentaire($txt)
	{
		global $gl_uid;
		$sql=$this->sql;

		$td=array();
		$td["fid"]=$this->id;
		$td["description"]=addslashes($txt);
		$td["uid_creat"]=$gl_uid;
		$td["dte_creat"]=now();
		$td["uid_maj"]=$gl_uid;
		$td["dte_maj"]=$td["dte_creat"];
		$sql->Edit("ameliorations",$this->tbl."_ameliore_com",0,$td);
	}
	
	function ListeCommentaire()
	{
		$sql=$this->sql;

		$q="SELECT * FROM ".$this->tbl."_ameliore_com WHERE fid='".$this->id."' ORDER BY dte_creat";
		$sql->Query($q);

		$lst=array();
		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			$lst[$i]=$sql->data;
		}
		foreach($lst as $i=>$d)
		{
			$lst[$i]["usr_creat"]=new user_core($sql->data["uid_maj"],$sql,false,false);
		}
		return $lst;
	}
}

function ListActiveAmeliorations($sql)
{
	return ListeObjets($sql,"ameliorations",array("id"),array("actif"=>"oui"));
}
?>
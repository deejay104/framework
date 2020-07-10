<?php
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


class amelioration_core extends objet_core
{
	protected $table="ameliorations";
	protected $mod="ameliorations";
	protected $rub="detail";

	protected $droit=array("status"=>"ModifAmeliorationStatus");

	protected $fields=array
	(
		"titre"=>Array("type"=>"varchar","len"=>100,"formlen"=>500),
		"description"=>Array("type"=>"text"),
		"version"=>Array("type"=>"varchar","len"=>10),
		"status"=>Array("type"=>"enum","index" => "1","default"=>"1new"),
		"module"=>Array("type"=>"enum","index" => "1"),
		"actif"=>Array("type"=>"bool", "default" => "oui", "index" => "1",),
		"uid_dist" => Array("type" => "number"),
		"mail_dist" => Array("type" => "varchar","len"=>104),
	);

	
	protected $tabList=array(
		"status"=>array(
			"fr"=>array('1new'=>'Nouveau','2sched'=>'Prochaine version','3inprg'=>'En cours','4test'=>'En test','5close'=>'Publié','6duplicate'=>'Doublon','7cancel'=>'Annulé'),
			"en"=>array('1new'=>'New','2sched'=>'Next release','3inprg'=>'In progress','4test'=>'Testing','5close'=>'Released','6duplicate'=>'Duplicate','7cancel'=>'Canceled'),
		),
		"module"=>array(
			"fr"=>array("core"=>"Framework","user"=>"Utilisateur","admin"=>"Administration","docs"=>"Documents","custom"=>"Autre"),
			"en"=>array("core"=>"Framework","user"=>"User","admin"=>"Administration","docs"=>"Documents","custom"=>"Other")
		)
	);

	# Constructor
	function __construct($id=0,$sql)
	{
		global $gl_uid;

		parent::__construct($id,$sql);		

		if ($this->data["uid_dist"]==0)
		{
			$this->data["uid_dist"]=$gl_uid;
		}
		if ($this->data["mail_dist"]=="")
		{
			$tmpusr = new user_core($gl_uid,$sql,false,false);
			$this->data["mail_dist"]=$tmpusr->data["mail"];
		}
		
		$this->usr_maj = new user_core($this->uid_maj,$sql,false,false);
	}

	function load($id)
	{
		global $MyOpt;

		if ((isset($MyOpt["amelioration"]["url"])) && ($MyOpt["amelioration"]["url"]!=""))
		{

			$url=$MyOpt["amelioration"]["url"]."/api.php?mod=ameliorations&rub=getdetail&id=".$id;

			$options = array(
				CURLOPT_RETURNTRANSFER => true,     // return web page
				CURLOPT_HEADER         => false,    // don't return headers
				CURLOPT_FOLLOWLOCATION => true,     // follow redirects
				CURLOPT_ENCODING       => "",       // handle all encodings
				CURLOPT_USERAGENT      => "MNMS", // who am i
				CURLOPT_AUTOREFERER    => true,     // set referer on redirect
				CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
				CURLOPT_TIMEOUT        => 120,      // timeout on response
				CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
				CURLOPT_SSL_VERIFYPEER => false,     // Disabled SSL Cert checks
				CURLOPT_USERPWD        => $MyOpt["amelioration"]["login"].':'.md5($MyOpt["amelioration"]["pwd"])
			);

			$ch = curl_init($url); 
			curl_setopt_array( $ch, $options );
			$data = curl_exec($ch); 

			curl_close($ch); 
			$tabList=json_decode($data,true);

			$this->id=$id;
			$this->uid_creat=$tabList["uid_creat"];
			$this->dte_creat=$tabList["dte_creat"];
			$this->uid_maj=$tabList["uid_maj"];
			$this->dte_maj=$tabList["dte_maj"];

			foreach($this->data as $k=>$v)
			{
				if (isset($tabList["data"][$k]))
				{
					$this->data[$k]=$tabList["data"][$k];
				}
				else
				{
					$this->data[$k]="";
				}
			}
		}
		else
		{
			parent::load($id);
		}
	}

	function Save()
	{
		global $MyOpt;

		if ((isset($MyOpt["amelioration"]["url"])) && ($MyOpt["amelioration"]["url"]!=""))
		{
			$post=array();
			// $post[1]['titre']=utf8_encode("hello");
			// $post['description']=utf8_encode("Description détaillée de l'amélioration");
			foreach($this->data as $k=>$v)
			{
				$post["data"][$k]=$v;
			}

			$url=$MyOpt["amelioration"]["url"]."/api.php?mod=ameliorations&rub=upddetail&id=".$this->id;
			$options = array(
				CURLOPT_RETURNTRANSFER => true,     // return web page
				CURLOPT_HEADER         => false,    // don't return headers
				CURLOPT_FOLLOWLOCATION => true,     // follow redirects
				CURLOPT_ENCODING       => "",       // handle all encodings
				CURLOPT_USERAGENT      => "MNMS", // who am i
				CURLOPT_AUTOREFERER    => true,     // set referer on redirect
				CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
				CURLOPT_TIMEOUT        => 120,      // timeout on response
				CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
				CURLOPT_SSL_VERIFYPEER => false,     // Disabled SSL Cert checks
				CURLOPT_USERPWD        => $MyOpt["amelioration"]["login"].':'.md5($MyOpt["amelioration"]["pwd"]),
				CURLOPT_HTTPHEADER     => array('Content-Type: application/json'),
				CURLOPT_CUSTOMREQUEST  => "PUT",
				CURLOPT_POSTFIELDS     => json_encode($post)
			);

			$ch = curl_init($url); 
			curl_setopt_array( $ch, $options );
			$data = curl_exec($ch); 
			curl_close($ch); 

			$tabList=json_decode($data,true);
			if ($this->id==0)
			{
				$this->id=$tabList["id"];
			}
		}
		else
		{
			parent::save();

			$lst=ListActiveUsers($this->sql,"",array("NotifAmelioration"),"non");

			foreach($lst as $i=>$id)
			{
				$usr = new user_core($id,$this->sql,false,true);
				if ($usr->data["mail"]!="")
				{
					// MyMail($MyOpt["from_email"],$usr->data["mail"],array(),"[Amélioration] ".$this->data["titre"],);
					// $this->data["description"]."<br><br><a href='".$MyOpt["host"]."/index.php?mod=ameliorations&rub=detail&id=".$this->id."'>-Détail-</a>"

					$tabvar=array();
					$tabvar["description"]=$this->val("description");
					$tabvar["status"]=$this->aff("status");
					$tabvar["num"]="#".CompleteTxt($this->id,4,"0");
					$tabvar["id"]=$this->id;
				
					SendMailFromFile($MyOpt["from_email"],$usr->data["mail"],array(),"[Amélioration] ".$this->data["titre"],$tabvar,"amelioration");
				}
			}
		}
	}
	
	function aff($key,$typeaff="html",$formname="form_data",&$render="",$formid="")
	{
		global $MyOpt;
		$ret=parent::aff($key,$typeaff,$formname,$render,$formid);

		if ($key=="id")
		{
			$ret="<a href='".geturl("ameliorations","detail","id=".$this->id)."'>#".CompleteTxt($this->id,4,"0")."</a>";
		}
		else if (($key=="description") && ($render!="form"))
		{
			$ret=preg_replace("/&num;([0-9]{4})/","<a href='".$MyOpt["host"]."/index.php?mod=ameliorations&rub=detail&id=$1'>#$1</a>",$ret);
		}
		else if ($key=="uid_creat")
		{
			$usr=new user_core($this->uid_creat,$this->sql);
			$ret=$usr->aff("fullname");
		}
		return $ret;
	}
	
	
	function AddCommentaire($txt,$uid=0)
	{
		global $MyOpt,$gl_uid;


		if ((isset($MyOpt["amelioration"]["url"])) && ($MyOpt["amelioration"]["url"]!=""))
		{
			$td=array();
			$td["fid"]=$this->id;
			$td["description"]=utf8_encode($txt);
			$td["uid_dist"]=$gl_uid;
			$td["uid_creat"]=$gl_uid;
			$td["dte_creat"]=now();
			$td["uid_maj"]=$gl_uid;
			$td["dte_maj"]=$td["dte_creat"];

			$url=$MyOpt["amelioration"]["url"]."/api.php?mod=ameliorations&rub=updcomm";
			$options = array(
				CURLOPT_RETURNTRANSFER => true,     // return web page
				CURLOPT_HEADER         => true,    // don't return headers
				CURLOPT_FOLLOWLOCATION => true,     // follow redirects
				CURLOPT_ENCODING       => "",       // handle all encodings
				CURLOPT_USERAGENT      => "MNMS", // who am i
				CURLOPT_AUTOREFERER    => true,     // set referer on redirect
				CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
				CURLOPT_TIMEOUT        => 120,      // timeout on response
				CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
				CURLOPT_SSL_VERIFYPEER => false,     // Disabled SSL Cert checks
				CURLOPT_USERPWD        => $MyOpt["amelioration"]["login"].':'.md5($MyOpt["amelioration"]["pwd"]),
				CURLOPT_HTTPHEADER     => array('Content-Type: application/json'),
				CURLOPT_CUSTOMREQUEST  => "PUT",
				CURLOPT_POSTFIELDS     => json_encode($td)
			);

			$ch = curl_init($url); 
			curl_setopt_array( $ch, $options );
			$data = curl_exec($ch); 
			curl_close($ch); 
		}
		else
		{
			$td=array();
			$td["fid"]=$this->id;
			$td["description"]=addslashes($txt);
			$td["uid_dist"]=$uid;
			$td["uid_creat"]=$gl_uid;
			$td["dte_creat"]=now();
			$td["uid_maj"]=$gl_uid;
			$td["dte_maj"]=$td["dte_creat"];
			$sql=$this->sql;
			$sql->Edit("ameliorations",$this->tbl."_ameliore_com",0,$td);

			if ($this->data["mail_dist"]!="")
			{
				MyMail($MyOpt["from_email"],$this->data["mail_dist"],array(),"[Amélioration] ".$this->data["titre"],$txt."<br><br><a href='".$MyOpt["host"]."/index.php?mod=ameliorations&rub=detail&id=".$this->id."'>-Détail-</a>");
			}

			$lst=ListActiveUsers($this->sql,"",array("NotifAmelioration"),"non");

			foreach($lst as $i=>$id)
			{
				$usr = new user_core($id,$this->sql,false,true);
				if ($usr->data["mail"]!="")
				{
					MyMail($MyOpt["from_email"],$usr->data["mail"],array(),"[Amélioration] ".$this->data["titre"],$txt."<br><br><a href='".$MyOpt["host"]."/index.php?mod=ameliorations&rub=detail&id=".$this->id."'>-Détail-</a>");
				}
			}
			
		}
	}
	
	function ListeCommentaire()
	{
		global $MyOpt;
		$sql=$this->sql;

		if ((isset($MyOpt["amelioration"]["url"])) && ($MyOpt["amelioration"]["url"]!=""))
		{

			$url=$MyOpt["amelioration"]["url"]."/api.php?mod=ameliorations&rub=getcomm&id=".$this->id;

			$options = array(
				CURLOPT_RETURNTRANSFER => true,     // return web page
				CURLOPT_HEADER         => false,    // don't return headers
				CURLOPT_FOLLOWLOCATION => true,     // follow redirects
				CURLOPT_ENCODING       => "",       // handle all encodings
				CURLOPT_USERAGENT      => "MNMS", // who am i
				CURLOPT_AUTOREFERER    => true,     // set referer on redirect
				CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
				CURLOPT_TIMEOUT        => 120,      // timeout on response
				CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
				CURLOPT_SSL_VERIFYPEER => false,     // Disabled SSL Cert checks
				CURLOPT_USERPWD        => $MyOpt["amelioration"]["login"].':'.md5($MyOpt["amelioration"]["pwd"])
			);

			$ch = curl_init($url); 
			curl_setopt_array( $ch, $options );
			$data = curl_exec($ch); 
			curl_close($ch); 
			$tabList=json_decode($data,true);

			$lst=array();
			if ((isset($tabList["lst"])) && (count($tabList["lst"])>0))
			{
				foreach($tabList["lst"] as $k=>$v)
				{
					foreach($v as $kk=>$vv)
					{
						$lst[$k][$kk]=$vv;
					}
				}
			}
		}
		else
		{
			$q="SELECT * FROM ".$this->tbl."_ameliore_com WHERE fid='".$this->id."' ORDER BY dte_creat";
			$sql->Query($q);

			$lst=array();
			for($i=0; $i<$sql->rows; $i++)
			{ 
				$sql->GetRow($i);
				$lst[$i]=$sql->data;
			}
		}
		foreach($lst as $i=>$d)
		{
			$lst[$i]["description"]=preg_replace("/#([0-9]{4})/","<a href='index.php?mod=ameliorations&rub=detail&id=$1'>#$1</a>",$lst[$i]["description"]);
			$lst[$i]["usr_creat"]=new user_core($d["uid_creat"],$sql,false,false);
			if ($d["uid_creat"]==0)
			{
				$lst[$i]["usr_creat"]->fullname="Développeur";
			}
		}
		return $lst;
	}
}

function ListActiveAmeliorations($sql)
{
	global $MyOpt;

	if ((isset($MyOpt["amelioration"]["url"])) && ($MyOpt["amelioration"]["url"]!=""))
	{
		$url=$MyOpt["amelioration"]["url"]."/api.php?mod=ameliorations&rub=getlist";

		$options = array(
			CURLOPT_RETURNTRANSFER => true,     // return web page
			CURLOPT_HEADER         => false,    // don't return headers
			CURLOPT_FOLLOWLOCATION => true,     // follow redirects
			CURLOPT_ENCODING       => "",       // handle all encodings
			CURLOPT_USERAGENT      => "MNMS", // who am i
			CURLOPT_AUTOREFERER    => true,     // set referer on redirect
			CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
			CURLOPT_TIMEOUT        => 120,      // timeout on response
			CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
			CURLOPT_SSL_VERIFYPEER => false,     // Disabled SSL Cert checks
			CURLOPT_USERPWD        => $MyOpt["amelioration"]["login"].':'.md5($MyOpt["amelioration"]["pwd"])
		);

		$ch = curl_init($url); 
		curl_setopt_array( $ch, $options );
		$data = curl_exec($ch); 
		curl_close($ch); 
		$tabList=json_decode($data,true);

		$lst=array();
		if ( (is_array($tabList["lst"])) && (count($tabList["lst"])>0) )
		{
			foreach($tabList["lst"] as $k=>$v)
			{
				$lst[$k]=array();
			}
		}

		return $lst;
	}
	else
	{
		return ListeObjets($sql,"ameliorations",array("id"),array("actif"=>"oui"));
	}
}
?>
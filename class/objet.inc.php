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

/*
Type:
	number: Nombre
	date
	datetime
	duration: Temps
	ucword
	uppercase
	lowercase
	email
	tel
	text
	bool
	enum
	price
*/

// Class Utilisateur
class objet_core
{
	# Constructor	
	function __construct($id=0,$sql)
	{
		global $MyOpt;
		global $gl_uid;

		$this->tbl=$MyOpt["tbl"];

		$this->sql=$sql;
		// $this->table="";

		$this->id=0;
		$this->uid_creat=$gl_uid;
		$this->dte_creat=date("Y-m-d H:i:s");
		$this->uid_maj=$gl_uid;
		$this->dte_maj=date("Y-m-d H:i:s");

		if ($id>0)
		{
			$this->load($id);
		}

	}


	# Load object informations
	function load($id)
	{
		$sql=$this->sql;
		$this->id=$id;
		$query = "SELECT * FROM ".$this->tbl."_".$this->table." WHERE id='$id'";
		$res = $sql->QueryRow($query);
		if (!is_array($res))
		{
			return "";
		}

		$this->uid_creat=$res["uid_creat"];
		$this->dte_creat=$res["dte_creat"];
		$this->uid_maj=$res["uid_maj"];
		$this->dte_maj=$res["dte_maj"];
		
		// Charge les variables
		foreach($this->data as $k=>$v)
		{
			$this->data[$k]=$res[$k];
		}

	}
	
	# Show object informations
	function aff($key,$typeaff="html",$formname="form_data",&$render="")
	{
		global $MyOpt;

		if ($render=="")
		{
			$render=$typeaff;
		}

		$ret="";
		$len=0;

		$txt=$this->val($key);
		$mycond=$this->GetDroit($key);

		$type=(isset($this->type[$key]))? $this->type[$key] : "";

		if (!$mycond)
		{
			$render="html";
		}
		if (($this->nomodif[$key]=="yes") && ($this->id>0))
		{
			$render="html";
		}
 	
		if ($render=="form")
		{
			if ($type=="text")
		  	{
				$ret="<textarea id='".$key."'  name=\"".$formname."[$key]\" rows=5>".$txt."</textarea>";
			}
			else if ($type=="bool")
		  	{
				$ret ="<input id='".$key."' type='radio' name=\"".$formname."[$key]\" value='oui' ".(($txt=="oui") ? "checked='checked'" : "")."> Oui";
				$ret.="<input id='".$key."' type='radio' name=\"".$formname."[$key]\" value='non' ".(($txt=="non") ? "checked='checked'" : "")."> Non";
			}
			else if (($type=="enum") && (is_array($this->tabList[$key])))
			{
		  	  	$ret ="<select id='".$key."'  name=\"".$formname."[$key]\">";
				foreach($this->tabList[$key] as $k=>$v)
				{
					$ret.="<option value=\"".$k."\" ".(($txt==$k) ? "selected" : "").">".$this->tabList[$key][$k]."</option>";
				}
		  	  	$ret.="</select>";

			}
			else if (($type=="multi") && (is_array($this->tabList[$key])))
			{
				$t=explode(",",$txt);
				$tt=array();
				foreach($t as $i=>$v)
				{
					$tt[$v]="on";
				}

				$ret="<span>";
				foreach($this->tabList[$key] as $k=>$v)
				{
					$ret.="<input type='checkbox' name='".$formname."[".$key."][".$k."]' value='".$k."' ".(($tt[$k]=="on") ? "checked" : "")."> ".$this->tabList[$key][$k]."<br />";
				}
				$ret.="</span>";
			}
			else if ($type=="datetime")
			{
				// $ret=sql2date($ret);
				$type="date";
				$ret="<INPUT id='".$key."'  name=\"".$formname."[$key][date]\" value=\"".date2sql(sql2date($txt,"jour"))."\" type=\"date\"> ";
				$ret.="<INPUT id='".$key."'  name=\"".$formname."[$key][time]\" value=\"".sql2time($txt)."\" type=\"time\" style=\"width:110px!important;\">";
			}
			else if ($type=="date")
			{
				$ret="<INPUT id='".$key."'  name=\"".$formname."[$key]\" value=\"".date2sql(sql2date($txt,"jour"))."\" type=\"date\">";
			}
			else if (is_array($txt))
			{
				$ret="";
			}
			else
			{
				$type=(isset($type)) ? $type : "text";
				$ret="<INPUT id='".$key."'  name=\"".$formname."[$key]\" value=\"".$txt."\" ".(($type!="") ? "type=\"".$type."\"" : "")." ".(($len>0) ? "style='width:".$len."px!important;'" : "").">";
			}
		}
		else if ($render=="val")
		{

		}
		else
		{
			$link=true;

			if ($type=="text")
			{
				$ret=nl2br(htmlentities($txt,ENT_HTML5,"ISO-8859-1"));
				$ret="<p class='formulaire'>".$ret."</p>";
				$link=false;
			}
			else if ($type=="date")
			{
				if ($ret=="0000-00-00")
				{
					$ret="-";
				}
				else
				{
					$ret=sql2date($txt,"jour");
				}
			}
			else if ($type=="datetime")
			{
				if ($txt=="0000-00-00 00:00:00")
				{
					$ret="-";
				}
				$ret=sql2date($txt);
			}
			else if ($type=="email")
			{
				$ret="<A href=\"mailto:".strtolower($txt)."\">".strtolower($txt)."</A>";
				$link=false;
			}
			else if ($type=="tel")
			{
				$ret=AffTelephone($txt);
				$link=false;
			}
			else if ($type=="price")
			{
				$ret=AffMontant($txt);
			}
			else if (($type=="enum") && (is_array($this->tabList[$key])))
			{
				$ret=$this->tabList[$key][$txt];
			}
			else if ($type=="bool")
		  	{
				$ret=($txt=='oui') ? "Oui" : "Non";
			}
			else if (($type=="multi") && (is_array($this->tabList[$key])))
			{
				$t=explode(",",$txt);
				$tt=array();
				foreach($t as $i=>$v)
				{
					$tt[]=$this->tabList[$key][$v];
				}
				$ret=implode(",",$tt);
			}
			else if (is_array($txt))
			{
				$ret="";
			}
			else
			{
				$ret=$txt;
			}
			
			// A voir si on met tous les champs en clicable ou pas
			if (($this->mod!="") && ($this->rub!="") && ($link))
			{
				$ret="<A href=\"index.php?mod=".$this->mod."&rub=".$this->rub."&id=".$this->id."\">".$ret."</A>";
			}

		}
	
		return $ret;
	}

	function val($key)
	{
		global $MyOpt;

		$txt="";
		if (isset($this->data[$key]))
		{
			$txt=$this->data[$key];
		}

		$type="";
		if (isset($this->type[$key]))
		{
			$type=$this->type[$key];
		}

		if (is_numeric($key))
		  { $ret="******"; }
		else if ($key=="uid_maj")
		  { $ret="******"; }
		else if ($type=="ucword")
		{
			$ret=ucwords($txt);
		}
		else if ($type=="uppercase")
		{
			$ret=strtoupper($txt);
		}
		else if ($type=="lowercase")
		{
			$ret=strtolower($txt);
		}
		else if ($type=="email")
		{
			$ret=strtolower($txt);
		}
		else if ($type=="tel")
		{
			$ret=$txt;
		}
		else if ($type=="duration")
		{
			$ret=AffTemps($txt,"no");
			$len=80;
			$type="";
		}
		else if ($type=="number")
		{
			if (!is_numeric($txt))
			{
				$ret=0;
			}
			else
			{
				$ret=$txt;
			}
			$type="number";
		}
		else if ($type=="price")
		{
			if (!is_numeric($txt))
			{
				$ret=0;
			}
			else
			{
				$ret=$txt;
			}
		}
		// else if ((isset($this->type[$key])) && ($this->type[$key]=="text"))
		// {
			// $ret=nl2br(htmlentities($txt,ENT_HTML5,"ISO-8859-1"));
		// }
		else if ($type=="text")
		{
			$ret=$txt;
		}
		else if ($type=="varchar")
		{
			$ret=$txt;
		}
		else if ($type=="enum")
		{
			$ret=$txt;
		}
		else if (is_array($txt))
		{
			$ret=$txt;
		}
		else
		{
			$ret=strtolower($txt);
		}

	  return $ret;
	}

	function url()
	{
		global $MyOpt;
		return $MyOpt["host"]."/index.php?mod=".$this->mod."&rub=".$this->rub."&id=".$this->id;
	}
	
	function Create()
	{
		global $gl_uid;
		$sql=$this->sql;

		$this->id=$sql->Edit($this->table,$this->tbl."_".$this->table,$this->id,array("uid_creat"=>$gl_uid, "dte_creat"=>now(),"uid_maj"=>$gl_uid, "dte_maj"=>now()));		
		
		return $this->id;
	}
	
	function Valid($key,$v,$ret=false)
	{
		$vv="**none**";

		if (!isset($this->type[$key]))
		{
			if (!is_array($v))
			{
				$vv=strtolower($v);
			}
			else
			{
				$vv="";
			}
		}
		else if ($this->type[$key]=="duration")
		{
			$vv=CalcTemps($v,false);
		}
		else if ($this->type[$key]=="text")
		{
			$vv=$v;
		}
	  	else if ($this->type[$key]=="date")
		{
	  	  	if (date2sql($v)!="nok")
	  	  	  { $vv=date2sql($v); }
	  	  	else if (preg_match("/^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})([0-9: ]*)$/",$v))
	  	  	  { $vv=$v; }
		}
	  	else if ($this->type[$key]=="datetime")
		{
	  	  	if (is_array($v))
	  	  	{
				$vv=$v["date"]." ".$v["time"];
			}
	  	  	else if (preg_match("/^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})([0-9: ]*)$/",$v))
			{
				$vv=$v;
			}
	  	  	else if (date2sql($v)!="nok")
	  	  	{
				$vv=date2sql($v); 
			}
			else
			{
				$vv="0000-00-00 00:00:00";
			}
		}
		else if ($this->type[$key]=="multi")
		{
			if (is_array($v))
			{
				$vv=implode(",",$v);
			}
			else
			{
				$vv=$v;
			}
		}
		else if ($this->type[$key]=="varchar")
		{
			$vv=$v;
		}
		else if ($this->type[$key]=="enum")
		{
			$vv=$v;
		}
		else
		{
			$vv=strtolower($v);
		}

		if ( (!is_numeric($key)) && ("($vv)"!="(**none**)") && ($ret==false))
		{
			if ($this->GetDroit($key))
			{
				$this->data[$key]=$vv;
			}
			return "";
		}
		else if ($ret==true)
		{
			return addslashes($vv);
		}
	}

	function Save()
	{
		global $gl_uid;
		$sql=$this->sql;

		if ($this->id==0)
		{
			$this->Create();
		}
		
		$td=array();
		foreach($this->data as $k=>$v)
		{
			// A réactiver après test
			// if ( ((isset($this->droit[$k])) && (GetDroit($this->droit[$k]))) || (!isset($this->droit[$k])) || ($this->droit[$k]=="") )
			// {
				$vv=$this->Valid($k,$v,true);
				$td[$k]=$vv;
			// }
		}
		$td["uid_maj"]=$gl_uid;
		$td["dte_maj"]=now();

		$sql->Edit($this->table,$this->tbl."_".$this->table,$this->id,$td);
		return $sql->a_rows;
	}

	function GetDroit($key)
	{
		if (!isset($this->droit[$key]))
		{
			return true;
		}
		if ((!is_array($this->droit[$key])) && ($this->droit[$key]==""))
		{
			return true;
		}

		$mycond=false;
		if (is_array($this->droit[$key]))
		{
			foreach($this->droit[$key] as $i=>$role)
			{
				if ($this->CheckDroit($role))
				{
					$mycond=true;
				}
			}
		}
		else
		{
			$mycond=$this->CheckDroit($this->droit[$key]);
		}

		// Champs en lecture seule
		if (($key=="id") || ($key=="uid_creat") || ($key=="dte_creat") || ($key=="uid_maj") || ($key=="dte_maj"))
		{
			$mycond=false;
		}
	
		return $mycond;
	}

	function CheckDroit($role)
	{
		$mycond=false;
		// On test si on a le role autorisé
		if (GetDroit($role))
		{
			$mycond=true;
		} 

		// On teste si l'id de l'objet est le notre
		if (($role=="ownerid") && (GetMyId($this->id)))
		{
			$mycond=true;
		} 
		// On teste si l'id de creation est le notre
		if (($role=="creatid") && (GetMyId($this->uid_creat)))
		{
			$mycond=true;
		} 
		
		// Si l'utilisateur a le droit de tout modifier alors on force
		// Pas dit que cette partie soit nécessaire
		if (GetDroit("SYS"))
		  { $mycond=true; }
	  
		return $mycond;
	}
	
	function Delete()
	{
		global $gl_uid;
		$sql=$this->sql;
		$this->actif="non";

		$sql->Edit($this->table,$this->tbl."_".$this->table,$this->id,array("actif"=>'non', "uid_maj"=>$gl_uid, "dte_maj"=>now()));
	}

	function Render($form,$render)
	{
		global $tmpl_x;

		$tmpl_x->assign($form."_id",$this->id);
		if (isset($this->usr_maj))
		{
			$tmpl_x->assign($form."_usr_maj",$this->usr_maj->aff("fullname"));
		}
		foreach($this->data as $k=>$v)
		{
			$tmpl_x->assign($form."_".$k,$this->aff($k,$render));
		}
	}

	function AffTableLine($tab)
	{
		$tabLine=array();
		foreach($tab as $i=>$v)
		{
			if (isset($this->data[$v]))
			{
				$tabLine[$v]["val"]=$this->val($v);
				$tabLine[$v]["aff"]=$this->aff($v);
			}
		}
		return $tabLine;
	}
	
	function LastUpdate()
	{
		return DisplayDate($this->dte_maj);
	}
} # End of class

function ListeObjets($sql,$table,$champs=array(),$crit=array())
{
	global $MyOpt;
	
	$s="";
	if (count($champs)>0)
	{
		$s=",";
	}

	$w="";
	if ((is_array($crit)) && (count($crit)>0))
	{
		$w="WHERE 1=1";
		foreach ($crit as $c=>$v)
		{
			$w.=" AND ".$c."='".$v."'";
		}
	}
		
	$q="SELECT id".$s.implode(",",$champs)." FROM ".$MyOpt["tbl"]."_".$table." ".$w;
	$sql->Query($q);

	$lst=array();
	for($i=0; $i<$sql->rows; $i++)
	{ 
		$sql->GetRow($i);
		$lst[$sql->data["id"]]=$sql->data;
	}
	return $lst;
}

?>
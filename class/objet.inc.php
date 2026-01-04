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

/*
Type:
	number : Nombre
	date
	datetime
	duration: Temps
	birthday: date + age
	ucword
	uppercase
	lowercase
	email
	tel
	text
	bool
	enum
	radio
	price
	varchar
	multi
*/

// Class Utilisateur
class objet_core
{
	public $id=0;
	public $actif="oui";
	public $settime=true;

	public $data=array();

	public $uid_creat=0;
	public $dte_creat ="";
	public $uid_maj=0;
	public $dte_maj="";

	protected $tbl="";
	protected $sql="";
	protected $type=array();
	protected $tabLang=array();
	protected $tabLangObject=array(
		"yes"=>array(
			"fr"=>"Oui",
			"en"=>"Yes",
			"ca"=>"Oui",
		),
		"no"=>array(
			"fr"=>"Non",
			"en"=>"No",
			"ca"=>"Non",
		)
	);

	
	# Constructor	
	function __construct($id=0,$sql="")
	{
		global $MyOpt;
		global $gl_uid;

		$this->tbl=$MyOpt["tbl"];

		$this->sql=$sql;
		// $this->table="";

		$this->id=0;
		$this->actif="oui";
		// $this->settime=true;
		$this->uid_creat=$gl_uid;
		$this->dte_creat=date("Y-m-d H:i:s");
		$this->uid_maj=$gl_uid;
		$this->dte_maj=date("Y-m-d H:i:s");

		$this->tabLang=array_merge($this->tabLang,$this->tabLangObject);

		if ((isset($this->fields)) && (is_array($this->fields)))
		{
			foreach($this->fields as $key=>$field)
			{
				$this->type[$key]=$field["type"];
				if (($field["type"]=="date") || ($field["type"]=="birthday"))
				{
					if ((isset($field["default"])) && ($field["default"]=="now"))
					{
						$this->data[$key]=date("Y-m-d");
					}
					else if (isset($field["default"]))
					{
						$this->data[$key]=date("Y-m-d");
					}
					else
					{
						$this->data[$key]="0000-00-00";
					}
				}
				else if ($field["type"]=="datetime")
				{
					if ((isset($field["default"])) && ($field["default"]=="now"))
					{
						$this->data[$key]=now();
					}
					else
					{
						$this->data[$key]="0000-00-00 00:00:00";
					}
				}
				else if (($field["type"]=="number") || ($field["type"]=="numeric"))
				{
					if ((isset($field["default"])) && (is_numeric($field["default"])))
					{
						$this->data[$key]=$field["default"];
					}
					else
					{
						$this->data[$key]=0;
					}
					if (!(isset($field["default"])))
					{
						$this->fields[$key]["default"]=0;
					}
				}
				else if (isset($field["default"]))
				{
					$this->data[$key]=$field["default"];
				}
				else
				{
					$this->data[$key]="";
				}
			}
		}
		else
		{
			$this->fields=array();
			foreach($this->type as $k=>$v)
			{
				$this->fields[$k]["type"]=$v;
			}
		}

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
			return 0;
		}

		$this->actif=$res["actif"];
		$this->uid_creat=$res["uid_creat"];
		$this->dte_creat=$res["dte_creat"];
		$this->uid_maj=$res["uid_maj"];
		$this->dte_maj=$res["dte_maj"];
		
		// Charge les variables
		
		foreach($this->fields as $k=>$v)
		{
			$this->data[$k]=$res[$k];
		}
	}
	
	# Show object informations
	function aff($key,$typeaff="html",$formname="form_data",&$render="",$formid="")
	{
		global $MyOpt,$lang,$theme;

		if ($render=="")
		{
			$render=$typeaff;
		}

		$ret="";
		$len=((isset($this->fields[$key]["formlen"])) && ($this->fields[$key]["formlen"]>0)) ? $this->fields[$key]["formlen"] : 0;

		$txt=$this->val($key);
		$mycond=$this->GetDroit($key);

		$type=(isset($this->type[$key])) ? $this->type[$key] : "";

		if (!$mycond)
		{
			if ($render=="form")
			{
				$render="read";
			}
			else
			{
				$render="html";
			}
		}
		
		if ((isset($this->droit[$key])) && ($this->droit[$key]=="[readonly]"))
		{
			$render="html";
		}
		if ((isset($this->nomodif[$key])) && ($this->nomodif[$key]=="yes") && ($this->id>0))
		{
				$render="html";
		}
		if (isset($this->fields[$key]["readonly"]))
		{
			if ($render=="form")
			{
				$render="read";
			}
			else
			{
				$render="html";
			}
		}
		if ((isset($this->fields[$key]["nomodif"])) && ($this->id>0))
		{
			if ($render=="form")
			{
				$render="read";
			}
			else
			{
				$render="html";
			}
		}

		if ($key=="dte_creat")
		{
			// return "<A href=\"".geturl($this->mod,$this->rub,"id=".$this->id)."\">".sql2date($this->dte_creat,"jour")."</A>";
			$txt=sql2date($this->dte_creat,"jour");
			$render="read";
		}
		
		if ($render=="form")
		{
			$placeholder="";
			if ((isset($this->fields[$key]["placeholder"])) && ($this->fields[$key]["placeholder"]))
			{
				$defaultaff=(isset($this->fields[$key]["default"])) ? $this->fields[$key]["default"] : "";

				if (($type=="number") && ($txt=="0"))
				{
					$txt="";
				}
				else if (($type=="duration") && ($txt=="0h 00"))
				{
					$txt="";
				}

				$placeholder="placeholder='".$defaultaff."'";
			}



			if ($type=="text")
		  	{
				$ret="<textarea id='".(($formid!="") ? $formid : "").$key."' class='form-control form-input' name=\"".$formname."[$key]\" rows=8>".$txt."</textarea>";
			}
			else if ($type=="bool")
		  	{
				$ret="<label class='toggle-switch toggle-switch-success'>";
				$ret.="<input type='hidden' name=\"".$formname."[$key]\" value='non'>";
				$ret.="<input id='".$key."' type='checkbox' name=\"".$formname."[$key]\" ".(($txt=="oui") ? "checked" : "")." value='oui'>";
				$ret.="<span class='toggle-slider round'></span></label>";

			}
			else if (($type=="enum") && (isset($this->tabList[$key][$lang])) && (is_array($this->tabList[$key][$lang])))
			{
		  	  	$ret ="<select id='".(($formid!="") ? $formid : "").$key."' class='form-control form-input' name=\"".$formname."[$key]\">";
				foreach($this->tabList[$key][$lang] as $k=>$v)
				{
					$ret.="<option value=\"".$k."\" ".(($txt==$k) ? "selected" : "").">".$this->tabList[$key][$lang][$k]."</option>";
				}
		  	  	$ret.="</select>";

			}
			else if (($type=="enum") && (isset($this->tabList[$key])) && (is_array($this->tabList[$key])))
			{
		  	  	$ret ="<select id='".(($formid!="") ? $formid : "").$key."' class='form-control form-input' name=\"".$formname."[$key]\">";
				foreach($this->tabList[$key] as $k=>$v)
				{
					$ret.="<option value=\"".$k."\" ".(($txt==$k) ? "selected" : "").">".$this->tabList[$key][$k]."</option>";
				}
		  	  	$ret.="</select>";

			}
			else if (($type=="radio") && (is_array($this->tabList[$key][$lang])))
			{
		  	  	$ret ="<div class='form-inline'>";
				foreach($this->tabList[$key][$lang] as $k=>$v)
				{
					// $ret.="<input id='".(($formid!="") ? $formid : "").$key."_".$k."' type='radio' class='form-control form-input'  name=\"".$formname."[$key]\" value=\"".$k."\" ".(($txt==$k) ? "checked" : "").">".$this->tabList[$key][$lang][$k]." ";


					$ret.="<div class='form-check'>";
					$ret.='<label class="form-check-label"><input id="'.(($formid!="") ? $formid : "").$key."_".$k.'" type="radio" class="form-check-input" name="'.$formname.'['.$key.']" value="'.$k.'" '.(($txt==$k) ? "checked" : "").'>'.$this->tabList[$key][$lang][$k].'<i class="input-helper"></i></label>&nbsp;';
					$ret.="</div>";
				}
				$ret.="</div>";
			}
			else if (($type=="multi") && (isset($this->tabList[$key][$lang])) && (is_array($this->tabList[$key][$lang])))
			{
				$t=explode(",",$txt);
				$tt=array();
				foreach($t as $i=>$v)
				{
					$tt[$v]="on";
				}

				$ret="<span>";
				foreach($this->tabList[$key][$lang] as $k=>$v)
				{
					// $ret.="<input type='checkbox' class='form-check-input'  name='".$formname."[".$key."][".$k."]' value='".$k."' ".(((isset($tt[$k])) && ($tt[$k]=="on")) ? "checked" : "")."> ".$this->tabList[$key][$lang][$k]."<br />";
					$ret.="<div class='form-check form-check-primary'><label class='form-check-label'><input type='checkbox' class='form-control form-input' name='".$formname."[".$key."][".$k."]' ".(((isset($tt[$k])) && ($tt[$k]=="on")) ? "checked" : "")." value='".$k."' /> ".$this->tabList[$key][$lang][$k]."<i class='input-helper'></i></label></div>";
				}
				$ret.="</span>";
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
					$ret.="<input type='checkbox' class='form-control form-input' name='".$formname."[".$key."][".$k."]' value='".$k."' ".(((isset($tt[$k])) && ($tt[$k]=="on")) ? "checked" : "")."> ".$v."<br />";
				}
				$ret.="</span>";
			}
			else if ($type=="datetime")
			{
				// $ret=sql2date($ret);
				$type="date";
				$ret="<INPUT id='".(($formid!="") ? $formid : "").$key."_jour' class='form-control form-input'  name=\"".$formname."[$key][date]\" value=\"".date2sql(sql2date($txt,"jour"))."\" type=\"date\" style=\"width:160px!important;\"> ";
				$ret.="<INPUT id='".(($formid!="") ? $formid : "").$key."_heure' class='form-control form-input'  name=\"".$formname."[$key][time]\" value=\"".sql2time($txt)."\" type=\"time\" style=\"width:140px!important;\">";
			}
			else if (($type=="date") || ($type=="birthday"))
			{
				$type="date";
				$ret="<INPUT id='".(($formid!="") ? $formid : "").$key."' class='form-control form-input'  name=\"".$formname."[$key]\" value=\"".date2sql(sql2date($txt,"jour"))."\" type=\"date\" style=\"width:160px!important;\">";
			}
			else if ($type=="price")
			{
				$ret="<INPUT id='".(($formid!="") ? $formid : "").$key."' class='form-control form-input'  name=\"".$formname."[$key]\" value=\"".$txt."\" type=\"number\" step=\"0.01\" style='width:150px!important;' placeholder='0'>";
			}
			else if ($type=="duration")
			{
				$len=80;
				$ret="<INPUT id='".(($formid!="") ? $formid : "").$key."' class='form-control form-input'  name=\"".$formname."[$key]\" value=\"".$txt."\" style='width:80px!important;' placeholder='0h 00'>";
			}
			else if (is_array($txt))
			{
				$ret="";
			}
			else
			{
				$type=(isset($type)) ? $type : "text";
				$ret="<INPUT id='".(($formid!="") ? $formid : "").$key."' name=\"".$formname."[$key]\" value=\"".$txt."\" ".(($type!="") ? "type=\"".$type."\"" : "").$placeholder." class='form-control form-input' ".((isset($this->fields[$key]["len"]) && ($this->fields[$key]["len"]>0)) ? "maxlength='".$this->fields[$key]["len"]."'" : "").">";
			}
		}
		else
		{
			$link=true;

			if ($type=="text")
			{
				$ret=nl2br(htmlentities($txt,ENT_HTML5,"UTF-8"));
				$ret="<p>".$ret."</p>";
				$link=false;
			}
			else if ($type=="date")
			{
				if ($txt=="0000-00-00")
				{
					$ret="-";
				}
				else
				{
					$ret=sql2date($txt,"jour");
				}
			}
			else if ($type=="birthday")
			{
				if ($txt=="0000-00-00")
				{
					$ret="-";
				}
				else
				{
					$ret=sql2date($txt,"jour")." (".floor((time()-strtotime($txt))/31536000)." ans)";
				}
			}
			else if ($type=="datetime")
			{
				if ($txt=="0000-00-00 00:00:00")
				{
					$ret="-";
				}
				else
				{
					$ret=sql2date($txt);
				}
			}
			else if ($type=="email")
			{
				$ret=strtolower($txt);
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
			else if (($type=="enum") && (isset($this->tabList[$key][$lang])) && (is_array($this->tabList[$key][$lang])))
			{
				$ret=$this->tabList[$key][$lang][$txt];
			}
			// Pour compatibilité ascendente
			else if (($type=="enum") && (isset($this->tabList[$key])) && (is_array($this->tabList[$key])))
			{
				$ret=$this->tabList[$key][$txt];
			}
			else if (($type=="radio") && (is_array($this->tabList[$key][$lang])))
			{
				$ret=$this->tabList[$key][$lang][$txt];
			}
			else if ($type=="bool")
		  	{
				$ret=($txt=='oui') ? $this->tabLang["yes"][$lang] : $this->tabLang["no"][$lang];

				$tabIcon=array("oui"=>array("icon"=>"mdi-checkbox-marked-outline","color"=>"green"),"non"=>array("icon"=>"mdi-checkbox-blank-outline","color"=>"#cccccc"));
				$ret="<i class='mdi ".$tabIcon[$txt]["icon"]."' style='font-size:20px; color:".$tabIcon[$txt]["color"].";'></i>";

			}
			else if (($type=="multi") && (is_array($this->tabList[$key][$lang])))
			{
				$t=explode(",",$txt);
				$tt=array();
				foreach($t as $i=>$v)
				{
					$tt[]=$this->tabList[$key][$lang][$v];
				}
				if ((isset($this->fields[$key]["show"])) && ($this->fields[$key]["show"]=="tag"))
				{
					$ret="";
					foreach($tt as $t)
					{
						$ret.="<div class='tagsinput'><span class='tag'>".$t."</span></div>";
					}
				}
				else
				{
					$ret=implode(",",$tt);
				}
			}
			else if (is_array($txt))
			{
				$ret="";
			}
			else
			{
				$ret=$txt;
			}

			if ($render=="read")
			{
				$link=false;
				$type=(isset($type)) ? $type : "text";
				$ret="<INPUT class='form-control form-input' value=\"".$ret."\" ".(($type!="") ? "type=\"".$type."\"" : "")." ".(($len>0) ? "style='width:".$len."px!important;'" : "")." readonly style='background-color:#eeeeee;'>";
			}

			
			if (($type!="multi") && (isset($this->fields[$key]["show"])) && ($this->fields[$key]["show"]=="tag"))
			{
				$ret="<div class='tagsinput'><span class='tag ".((isset($this->color[$key][$txt])) ? "bg-".$this->color[$key][$txt] : "")."'>".$ret."</span></div>";
			}

			// A voir si on met tous les champs en clicable ou pas
			if (($this->mod!="") && ($this->rub!="") && ($link))
			{
				if ($type=="email")
				{
					$ret="<A href=\"mailto:".strtolower($txt)."\">".strtolower($txt)."</A>";
				}
				else
				{
					$ret="<A href=\"".geturl($this->mod,$this->rub,"id=".$this->id)."\">".$ret."</A>";
				}
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
			$type="";
		}
		else if (($type=="number") || ($type=="numeric"))
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
		else if ($type=="bool")
		{
			$ret=$txt;
		}
		else if ($type=="enum")
		{
			$ret=$txt;
		}
		else if ($type=="radio")
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

		if (!isset($this->fields[$key]))
		{
			return "";
		}

		if (!isset($v))
		{
			$v="";
		}

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
		else if ($this->type[$key]=="number")
		{
			$vv=intval($v);
		}		
		else if ($this->type[$key]=="duration")
		{
			$vv=CalcTemps($v,false);
		}
		else if ($this->type[$key]=="text")
		{
			$vv=$v;
		}
		else if ($this->type[$key]=="bool")
		{
			if (($v=="on") || ($v=="oui"))
			{
				$vv="oui";
			}
			else
			{
				$vv="non";
			}
		}
	  	else if (($this->type[$key]=="date") || ($this->type[$key]=="birthday"))
		{
	  	  	if (date2sql($v)!="nok")
	  	  	  { $vv=date2sql($v); }
	  	  	else if (preg_match("/^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})([0-9: ]*)$/",$v))
	  	  	  { $vv=$v; }
			if ($v=="")
			{
				$vv="0000-00-00";
			}
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
			if ((isset($this->fields[$key]["len"])) && ($this->fields[$key]["len"]>0))
			{
				$vv=substr($v,0,$this->fields[$key]["len"]);
			}
			else
			{
				$vv=$v;
			}
		}
		else if ($this->type[$key]=="enum")
		{
			$vv=$v;
		}
		else if ($this->type[$key]=="radio")
		{
			$vv=$v;
		}
		else
		{
			$vv=strtolower($v);
		}

		if (!is_array($vv))
		{
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
		else
		{
			return "";
		}
	}

	function Save()
	{
		global $gl_uid;
		$sql=$this->sql;
		
		$td=array();
		// foreach($this->data as $k=>$v)
		foreach($this->fields as $k=>$v)
		{
			// A réactiver après test
			// if ( ((isset($this->droit[$k])) && (GetDroit($this->droit[$k]))) || (!isset($this->droit[$k])) || ($this->droit[$k]=="") )
			// {
				//$vv=$this->Valid($k,$v,true);
				$vv=$this->Valid($k,$this->data[$k],true);
				$td[$k]=$vv;
			// }
		}
		$td["actif"]=$this->actif;
		
		$td["uid_maj"]=$gl_uid;
		$td["dte_maj"]=now();
		$this->uid_maj=$gl_uid;
		$this->dte_maj=now();

		if ($this->id==0)
		{
			if ($this->settime)			// Can be removed once migration from forum to document is done
			{
				$this->uid_creat=$gl_uid;
				$this->dte_creat=now();
			}
			$td["uid_creat"]=$this->uid_creat;
			$td["dte_creat"]=$this->dte_creat;
		}

		$this->id=$sql->Edit($this->table,$this->tbl."_".$this->table,$this->id,$td);
		return $sql->a_rows;
	}

	function getFields()
	{
		return $this->fields;
	}

	function isFields($k)
	{
		return isset($this->fields[$k]);
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
		$this->data["actif"]="non";

		$sql->Edit($this->table,$this->tbl."_".$this->table,$this->id,array("actif"=>'non', "uid_maj"=>$gl_uid, "dte_maj"=>now()));
	}

	function Render($form,$typeaff,$formname="form_data",$formid="")
	{
		global $tmpl_x;

		$tmpl_x->assign($form."_id",$this->id);
		if (isset($this->usr_maj))
		{
			$tmpl_x->assign($form."_usr_maj",$this->usr_maj->aff("fullname"));
		}
		else
		{
			$usrmaj = new user_core($this->uid_maj,$this->sql);
			$tmpl_x->assign($form."_usr_maj",$usrmaj->aff("fullname"));
		}
		$tmpl_x->assign($form."_dte_maj",DisplayDate($this->dte_maj));


		foreach($this->data as $k=>$v)
		{
			$render=$typeaff;
			$tmpl_x->assign($form."_".$k,$this->aff($k,$typeaff,$formname,$render,$formid));
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
	
	function sign($t)
	{
		$s="";
		foreach($t as $i=>$k)
		{		
			$s.="_";
			
			if (isset($this->data[$k]))
			{
				$s.=$this->data[$k];
			}
		}
		return md5($s);
	}

	# Export as JSON
	function export()
	{
		$ret=array();
		
		$ret["id"]=$this->id;
		$ret["type"]=$this->table;
		$ret["data"]=array();
		foreach($this->data as $k=>$v)
		{
			$ret["data"][$k]=$this->val($k);
		}
		
		return $ret;
	}

	
	function genSqlTab(&$tab)
	{
	// "navpoints" => Array
	// (
		// "nom" => Array("Type" => "varchar(20)", "Index" => "1", ),
		// "description" => Array("Type" => "varchar(200)", ),
		// "lat" => Array("Type" => "varchar(10)", ),
		// "lon" => Array("Type" => "varchar(10)", ),
		// "icone" => Array("Type" => "varchar(20)", ),
	// ),

		$tabobj=array();
		if ((isset($this->fields)) && (is_array($this->fields)))
		{
			foreach($this->fields as $key=>$field)
			{
				$tabobj[$key]=array();
				if (($field["type"]=="number") || ($field["type"]=="numeric"))
				{
					$tabobj[$key]["Type"]="int(10) unsigned";
					$tabobj[$key]["Default"]="0";
					if (isset($field["default"]))
					{
						$tabobj[$key]["Default"]=$field["default"];
					}
				}
				else if (($field["type"]=="date") || ($field["type"]=="birthday"))
				{
					$tabobj[$key]["Type"]="date";
					$tabobj[$key]["Default"]="0000-00-00";
				}
				else if ($field["type"]=="datetime")
				{
					$tabobj[$key]["Type"]="datetime";
					$tabobj[$key]["Default"]="0000-00-00 00:00:00";
				}
				else if ($field["type"]=="duration")
				{
					$tabobj[$key]["Type"]="int(10) unsigned";
					$tabobj[$key]["Default"]="0";
					if (isset($field["default"]))
					{
						$tabobj[$key]["Default"]=$field["default"];
					}
				}
				// else if ($field["type"]=="ucword")
				// {
				// }
				// else if ($field["type"]=="uppercase")
				// {
				// }
				// else if ($field["type"]=="lowercase")
				// {
				// }
				else if ($field["type"]=="email")
				{
					$tabobj[$key]["Type"]="varchar(104)";
				}
				else if ($field["type"]=="tel")
				{
					$tabobj[$key]["Type"]="varchar(20)";
				}
				else if ($field["type"]=="text")
				{
					$tabobj[$key]["Type"]="text";
				}
				else if ($field["type"]=="bool")
				{
					$tabobj[$key]["Type"]="enum('oui','non')";
					if (isset($field["default"]))
					{
						$tabobj[$key]["Default"]=$field["default"];
					}
					else
					{
						$tabobj[$key]["Default"]="non";
					}
				}
				else if (($field["type"]=="enum") || ($field["type"]=="radio"))
				{
					if (isset($this->tabList[$key]))
					{
						if ((isset($this->tabList[$key]["fr"])) && (is_array($this->tabList[$key]["fr"])))
						{
							$t=$this->tabList[$key]["fr"];
						}
						else
						{
							$t=$this->tabList[$key];
						}

						$l="";
						$s="";
						foreach($t as $k=>$v)
						{
							$l.=$s."'".$k."'";
							$s=",";
						}
						
						if (isset($field["default"]))
						{
							$tabobj[$key]["Type"]="enum(".$l.")";
						}
						else
						{
							$tabobj[$key]["Type"]="enum('',".$l.")";
							$tabobj[$key]["Default"]="";
						}
					}
					if (isset($field["default"]))
					{
						$tabobj[$key]["Default"]=$field["default"];
					}
				}
				// else if ($field["type"]=="multi")
				// {
				// }
				else if ($field["type"]=="price")
				{
					$tabobj[$key]["Type"]="decimal(10,2)";
					$tabobj[$key]["Default"]="0.00";
				}
				else
				{
					$tabobj[$key]["Type"]="varchar(".( ( (isset($field["len"])) && ($field["len"]>0) ) ? $field["len"] : "50").")";
					if (isset($field["default"]))
					{
						$tabobj[$key]["Default"]=$field["default"];
					}
				}

				if (isset($field["index"]))
				{
					$tabobj[$key]["Index"]=1;
				}
			}
		}
		
		if (!isset($tabobj["actif"]))
		{
			$tabobj["actif"]=Array("Type" => "enum('oui','non')", "Default" => "oui", "Index" => "1");
		}
		if (!isset($tabobj["uid_creat"]))
		{
			$tabobj["uid_creat"]=Array("Type" => "int(10) unsigned", "Default" => "0");
		}
		if (!isset($tabobj["dte_creat"]))
		{
			$tabobj["dte_creat"]=Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00");
		}
		if (!isset($tabobj["uid_maj"]))
		{
			$tabobj["uid_maj"]=Array("Type" => "int(10) unsigned", "Default" => "0");
		}
		if (!isset($tabobj["dte_maj"]))
		{
			$tabobj["dte_maj"]=Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00");
		}
		// $tabobj["uid_maj"]=Array("Type" => "int(10) UNSIGNED");
		// $tabobj["dte_maj"]=Array("Type" => "datetime", "Default" => "0000-00-00 00:00:00");
		
		$tab[$this->table]=$tabobj;
	}
	
} # End of class

function ListeObjets($sql,$table,$champs=array(),$crit=array(),$order=array())
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
	if (is_array($order))
	{
		if (count($order)>0)
		{
			$q.=" ORDER BY ".implode(",",$order);
		}
	}
	$sql->Query($q);

	$lst=array();
	for($i=0; $i<$sql->rows; $i++)
	{ 
		$sql->GetRow($i);
		foreach($sql->data as $k=>$v)
		{
			if (!is_numeric($k))
			{
				$lst[$sql->data["id"]][$k]=$v;
			}
		}
	}
	return $lst;
}

?>

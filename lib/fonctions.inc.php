<?php
// ---------------------------------------------------------------------------------------------
//   Fonctions
// ---------------------------------------------------------------------------------------------

function GetModule($mod)
  { global $MyOpt;
	if ( (isset($MyOpt["module"][$mod])) && ($MyOpt["module"][$mod]=="on") )
	  { return true; }
	else
	  { return false; }
  }

function MyRep($file,$mymod="",$custom=true)
{
	global $mod,$theme,$appfolder;
	if ($mymod=="")
	{
		$mymod=$mod;
	}

  	$myfile=substr($file,0,strrpos($file,"."));
	$myext=substr($file,strrpos($file,".")+1,strlen($file)-strrpos($file,".")-1);

	if ($custom)
	{
		if ((file_exists($appfolder."/modules/$mymod/tmpl/$myfile.$theme.$myext")) && ($mymod!=""))
		  { return $appfolder."/modules/$mymod/tmpl/$myfile.$theme.$myext"; }
		else if ((file_exists($appfolder."/modules/$mymod/tmpl/$file")) && ($mymod!=""))
		  { return $appfolder."/modules/$mymod/tmpl/$file"; }
		else if ((file_exists($appfolder."/modules/$mymod/$file")) && ($mymod!=""))
		  { return $appfolder."/modules/$mymod/$file"; }
		else if ((file_exists($appfolder."/modules/$mymod/lang/$file")) && ($mymod!=""))
		  { return $appfolder."/modules/$mymod/lang/$file"; }
	}

	if ((file_exists("modules/$mymod/tmpl/$myfile.$theme.$myext")) && ($mymod!=""))
  	  { return "modules/$mymod/tmpl/$myfile.$theme.$myext"; }
	else if ((file_exists("modules/$mymod/tmpl/$file")) && ($mymod!=""))
  	  { return "modules/$mymod/tmpl/$file"; }
	else if ((file_exists("modules/$mymod/$file")) && ($mymod!=""))
  	  { return "modules/$mymod/$file"; }
	else if ((file_exists("modules/$mymod/lang/$file")) && ($mymod!=""))
  	  { return "modules/$mymod/lang/$file"; }

	else
  	  { return ""; }
  }

function geturl($mod,$rub,$param="")
{
	global $MyOpt;
	$url="";
	if ($MyOpt["shorturl"]=="on")
	{
		$url=$MyOpt["host"]."/".$mod.(($rub!="") ? "/".$rub : "").(($param!="") ? "?".$param : "");
	}
	else
	{
		$url="index.php?mod=".$mod.(($rub!="") ? "&rub=".$rub : "").(($param!="") ? "&".$param : "");
	}
	return $url;
}

function geturlapi($mod,$rub,$fonc,$param="")
{
	global $MyOpt;
	$url="";
	if ($MyOpt["shorturl"]=="on")
	{
		$url=$MyOpt["host"]."/api/v1/".$mod."/".$rub.(($fonc!="") ? "/".$fonc : "").(($param!="") ? "?".$param : "");
	}
	else
	{
		$url="api.php?mod=".$mod."&rub=".$rub.(($fonc!="") ? "&fonc=".$fonc : "").(($param!="") ? "&".$param : "");
	}
	return $url;
}


function addPageMenu($path,$mod,$title,$url,$img,$selected=false,$confirm="")
{
	global $tmpl_prg,$module,$MyOpt;
	
	
	if ($confirm!="")
	{
		$tmpl_prg->assign("pagemenu_url","#");
		$tmpl_prg->assign("pagemenu_confirm","OnClick=\"ConfirmeClick('".$url."','".$confirm."')\"");
	}
	else
	{
		$tmpl_prg->assign("pagemenu_url",$url);
		$tmpl_prg->assign("pagemenu_confirm","");
	}
	
	
	$tmpl_prg->assign("pagemenu_name",$title);
	$tmpl_prg->assign("pagemenu_image",$MyOpt["host"]."/".(($path!="") ? $path."/" : "").$module."/".$mod."/img/".$img);

	if ($selected)
	{
		$tmpl_prg->assign("pagemenu_class","class='pageTitleSelected'");
	}
	else
	{
		$tmpl_prg->assign("pagemenu_class","");
	}

	$tmpl_prg->parse("main.lst_pagemenu");
}

function addSubMenu($path,$title,$url,$img="",$selected=false,$confirm="",$onclick="")
{
	global $tmpl_prg,$module,$mod,$MyOpt,$corefolder;
	
	if ($confirm!="")
	{
		$tmpl_prg->assign("submenu_url","#");
		$tmpl_prg->assign("submenu_confirm","OnClick=\"ConfirmeClick('".$url."','".$confirm."')\"");
	}
	else if ($onclick!="")
	{
		$tmpl_prg->assign("submenu_url","#");
		$tmpl_prg->assign("submenu_confirm","OnClick=\"".$onclick."\"");
	}
	else
	{
		$tmpl_prg->assign("submenu_url",$url);
		$tmpl_prg->assign("submenu_confirm","");
	}
	
	$tmpl_prg->assign("submenu_name",$title);

	if ($selected)
	{
		$tmpl_prg->assign("submenu_class","class='pageTitleSelected'");
	}
	else
	{
		$tmpl_prg->assign("submenu_class","");
	}

	if ($img!="")
	{
		$tmpl_prg->assign("submenu_image",$MyOpt["host"]."/".(($path!="") ? $path."/" : "").$module."/".$mod."/img/".$img);
	}
	else
	{
		$tmpl_prg->assign("submenu_image",$MyOpt["host"]."/".$corefolder."/static/images/icn32_extend.png");
	}

	$tmpl_prg->parse("main.aff_submenu.lst_submenu");
}
function affSubMenu()
{
	global $tmpl_prg;
	$tmpl_prg->parse("main.aff_submenu");
}


function checkVar($var,$type,$len=256,$default="")
{
	global $_REQUEST,$tabPost;

	$tabPost[$var]="ok";

	if (!is_numeric($len))
	{
		$len=256;
	}
	
	if (!isset($_REQUEST[$var]))
	{
		$v=$default;
	}
	else
	{
		$v=$_REQUEST[$var];
	}

	if ($type=="numeric")
	{
		if (is_numeric($v))
		{
			return $v;
		}
		else if (is_numeric($default))
		{
			return $default;
		}
		else
		{
			return 0;
		}
	}
	else if ($type=="varchar")
	{
		return substr($v,0,$len);
	}
	else if ($type=="token")
	{
		$v=preg_replace("/[^a-z0-9]*/","",$v);
		return substr($v,0,$len);
	}
	else if ($type=="date")
	{
		if (preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/",$v))
		{
			return $v;
		}
		else if (preg_match("/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/",$v))
		{
			return $v;
		}
		else
		{
			return "0000-00-00";
		}
	}
	else if ($type=="array")
	{
		if (is_array($v))
		{
			return $v;
		}
		else
		{
			return array();
		}
	}
	else if ($type=="text")
	{
		return strip_tags($v);
	}
}
  
function GetDroit($droit)
{
	global $myuser;

	if (trim($droit)=="")
	  { return true; }
	else if (trim($droit)=="ALL")
	  { return true; }
	else if ((isset($myuser->role[$droit])) && ($myuser->role[$droit]))
	  { return true; }
	else if ((isset($myuser->groupe["SYS"])) && ($myuser->groupe["SYS"]))
	  { return true; }
	elseif ((isset($myuser->groupe[$droit])) && ($myuser->groupe[$droit]))
	  { return true; }
	else
	  { return false; }
}

// Test si un ID correspond à l'utilisateur ou un de ses enfants
function GetMyId($id)
{
	global $myuser;

  	if ($id==$myuser->id)
  	  { return true; }

  	return false;
}

function myPrint($txt)
{ global $gl_mode,$gl_myprint_txt;
	if ($gl_mode=="batch")
	{
		$gl_myprint_txt.=utf8_encode($txt)."\n";
	}
	else
	{
		$gl_myprint_txt.=$txt."<br />";
	}
}


// Charge un template
function LoadTemplate($tmpl,$mymod="",$custom=true)
{ global $tabLang;
	$t=MyRep($tmpl.".htm",$mymod,$custom);
	if (!file_exists($t))
	{
		$t=MyRep("empty.htm","default");
	}

	$tmpl = new XTemplate ($t);

	foreach ($tabLang as $key=>$val)
	{
		$tmpl->assign($key, $val);
	}

	return $tmpl;
}

// Affiche un temps en minute en heures/minutes
function AffTemps($tps,$short="yes") {
	$th=floor(abs($tps)/60);
	$tm=abs($tps)-$th*60;
	$tm=substr("00",0,2-strlen($tm)).$tm;

	if (($th>0) && ($short=="full"))
	{
		if ($th>24)
		{
			$td=floor(abs($tps)/(24*60));
			$th=floor((abs($tps)-$td*24*60)/60);
			$tm=$tps-$td*24*60-$th*60;

			$th=substr("00",0,2-strlen($th)).$th;
			$tm=substr("00",0,2-strlen($tm)).$tm;

			return (($tps<0) ? "-" : "").$td."j ".$th."h ".$tm;
		}
		else
		{
			return (($tps<0) ? "-" : "").$th."h ".$tm;
		}
	}
	else if (($th>0) || ($short=="no"))
	  { return (($tps<0) ? "-" : "").$th."h ".$tm; }
	else
	  { return (($tps<0) ? "-" : "").$tm."min"; }
}

// Transforme un temps en minute
function CalcTemps($tps,$short=true)
{
	if ( (preg_match("/^([0-9][0-9])([0-9][0-9])$/",$tps,$m)) && ($short) )
	{
		$t=$m[1]*60+$m[2];
	}
	else if (preg_match("/^([0-9]?[0-9]):([0-9][0-9])$/",$tps,$m))
	{
		$t=$m[1]*60+$m[2];
	}
	else if (preg_match("/^([0-9]*?[0-9])h[ ]?([0-9]?[0-9])$/",$tps,$m))
	{
		$t=$m[1]*60+$m[2];
	}
	else if (preg_match("/^([0-9]?[0-9])j[ ]?([0-9]?[0-9]?)h?[ ]?([0-9]?[0-9]?)$/",$tps,$m))
	{
		$t=$m[1]*60*24+$m[2]*60+$m[3];
	}
	else if (preg_match("/^([0-9]*?[0-9])h$/",$tps,$m))
	{
		$t=$m[1]*60;
	}
	else if (is_numeric($tps))
	{
		$t=$tps;
	}
	else
	{
		$t=0;
	}
	
	return $t;
}


// Transforme une date en format SQL
function date2sql($date) {
	if (preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/",$date))
	  { return $date; }

  $d = preg_replace('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{2,4})$/','\\3-\\2-\\1', $date);
  if ($d == $date) { $d = preg_replace('/^([0-9]{1,2})-([0-9]{1,2})-([0-9]{2,4})$/','\\3-\\2-\\1', $date); }
  if ($d == $date) { $d = preg_replace('/^([0-9]{1,2}).([0-9]{1,2}).([0-9]{2,4})$/','\\3-\\2-\\1', $date); }
  if ($d == $date) { $d = preg_replace('/^([0-9]{2,4})\/([0-9]{1,2})\/([0-9]{1,2})$/','\\1-\\2-\\3', $date); }
  if ($d == $date) { $d = preg_replace('/^([0-9]{2,4}).([0-9]{1,2}).([0-9]{1,2})$/','\\1-\\2-\\3', $date); }
  if ($d == $date) { $d = preg_replace('/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ?([0-9:]*)?$/','\\1-\\2-\\3', $date); }
  if ($d == $date) { $d = preg_replace('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{2,4}) ?([0-9:]*)?$/','\\3-\\2-\\1', $date); }
  if (($d == $date) && ($date != '')) { $d = "nok"; }
  return $d;
}

// Transforme une date SQL en date jj/mm/aaaa
function sql2date($date,$aff="") {
	if ($aff=="jour")
	  { return preg_replace('/^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2}) ?[^$]*$/','\\3/\\2/\\1', $date); }
	else if ($aff=="nosec")
	  { return preg_replace('/^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]*):([0-9]*):([0-9 ]*)$/','\\3/\\2/\\1 \\4:\\5', $date); }
	else if ($aff=="heure")
	  {
			$h=preg_replace('/^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):?([0-9]{1,2})?:?([0-9]{1,2})?$/','\\4', $date);
			$m=preg_replace('/^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):?([0-9]{1,2})?:?([0-9]{1,2})?$/','\\5', $date);
	  	return $h.(($m!="") ? ":$m" : ":00");
	  }
	else
	  { return preg_replace('/^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})([0-9: ]*)$/','\\3/\\2/\\1\\4', $date); }
}

// Transforme une date SQL en heure hh:mm
function sql2time($date,$aff="") {
	if ($aff=="nosec")
	  { return preg_replace('/^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]*):([0-9]*):([0-9]*)$/','\\4:\\5', $date); }
	else
	  { return preg_replace('/^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]*):([0-9]*):([0-9]*)$/','\\4:\\5:\\6', $date); }
}


// Calcul le nombre de secondes entre deux dates
function date_diff_txt($date1, $date2)
{
  $s = strtotime($date2)-strtotime($date1);
  return $s;
}



// Ajoute un nombre de jour à une date
function CalcDate($dte, $n)
{
		return date("Y-m-d",mktime(0, 0, 0, date("n",strtotime($dte)), date("j",strtotime($dte))+$n, date("Y",strtotime($dte))));
}	



  
function SendMailFromFile($from,$to,$tabcc,$subject="",$tabvar,$name,$files="",$dest="mail")
{ global $sql,$mod,$appfolder,$MyOpt,$gl_uid;
	$q="SELECT * FROM ".$MyOpt["tbl"]."_mailtmpl WHERE nom='".$name."'";
	$res=$sql->QueryRow($q);

	if ($res["id"]>0)
	{
	}
	else
	{
		error_log("No template found");
		return false;
	}
	
	if ($res["titre"]!="")
	{
		$subject=$res["titre"];
	}

	if ($subject=="")
	{
		$subject="Notification";
	}

	$mail=$res["corps"];
	foreach($tabvar as $p=>$d)
	{
		$mail=str_replace("{".$p."}",$d,$mail);
	}
	
	if ($dest=="actualite")
	{
		$t=array(
			"titre"=>addslashes($subject),
			"message"=>addslashes($mail),
			"mail" =>"non",
			"uid_creat"=>$gl_uid,
			"dte_creat"=>now(),
			"uid_maj"=>$gl_uid,
			"dte_maj"=>now(),
		);

		$sql->Edit("actualites",$MyOpt["tbl"]."_actualites",0,$t);

		return true;
	}
	else
	{
		$mail.="\n\n-Email envoyé à partir du site ".$MyOpt["site_title"]."-";
		$mail=nl2br($mail);

		return MyMail($from,$to,$tabcc,$subject,$mail,"",$files);
	}
}

function MyMail($from,$to,$tabcc,$subject,$message,$headers="",$files="")
{ global $MyOpt;

	if (is_array($from))
	{
		$me=$from["name"];
		$fromadd=$from["mail"];
		$txtfrom=$from["mail"];
	}
	else
	{
		if ($from=="") { $from = $MyOpt["from_email"]; }

		preg_match("/^([^@]*)@([^$]*)$/",$from,$t);
		$me=$t[0];
		$fromadd=$from;
		$txtfrom=$from;
	}

	$txtcc="";
	if ((is_array($tabcc)) && (count($tabcc)>0))
	{
		$txtcc=implode(",",$tabcc);
	}

	if ($MyOpt["sendmail"]!="on") { myPrint("From:".$txtfrom." - To:".$to." - Cc:".$txtcc." - Subject:".$subject); return false; }

	require_once 'external/PHPMailer/PHPMailerAutoload.php';
	
	//Create a new PHPMailer instance
	$mail = new PHPMailer;

	if ($MyOpt["mail"]["smtp"]=="on")
	{
		// Set PHPMailer to use SMTP transport
		$mail->isSMTP();

		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);

		//Set the hostname of the mail server
		$mail->Host = $MyOpt["mail"]["host"];
		//Set the SMTP port number - likely to be 25, 465 or 587
		$mail->Port = $MyOpt["mail"]["port"];

		// Do not close connection to SMTP
		$mail->SMTPKeepAlive = true;
		//Whether to use SMTP authentication
		$mail->SMTPAuth = false;
		if ($MyOpt["mail"]["username"]!="")
		{
			$mail->SMTPAuth = true;
			//Username to use for SMTP authentication
			$mail->Username = $MyOpt["mail"]["username"];
			//Password to use for SMTP authentication
			$mail->Password = $MyOpt["mail"]["password"];
		}

	}
	else
	{
		$mail->isSendmail();
	}

	//Set who the message is to be sent from
	$mail->setFrom($MyOpt["from_email"], $me);
	//Set an alternative reply-to address
	$mail->addReplyTo($fromadd, "");
	//Set who the message is to be sent to

	$mail->addAddress($to);
//$mail->addAddress("matthieu@les-mnms.net");

	if ((is_array($tabcc)) && (count($tabcc)>0))
	{
		foreach($tabcc as $i=>$m)
		{
			$mail->addCC($m);
		}
	}
	
	//Set the subject line
	$mail->CharSet = 'UTF-8';
	$mail->Subject = $subject;

	$mail->msgHTML($message);
	$mail->AltBody = strip_tags($message);

	if (is_array($files))
	{
		foreach($files as $i=>$d)
		{
			if ($d["type"]=="text")
			{
				$mail->AddStringAttachment($d["data"],$d["nom"]);
			}
			else if ($d["type"]=="file")
			{
				$mail->AddAttachment($d["data"],$d["nom"]);
			}
		}
	}

	//send the message, check for errors
	return $mail->send();
}


function SendMail($From,$To,$Cc,$Subject,$Text,$Html,$AttmFiles)
{
	affInformation("*Fonction SendMail supprimée*","warning");
}


/* **** Fonction d'affichage d'un tableau ****

	$tab	Tableau contenant l'ensemble des entrées à afficher, chaque ligne est constituée d'un tableau
		par ex $tab[0]["nom"]="Produit 1"; $tab[0]["statut"]="OK"; $tab[0]["id"]="104"; $tab[0]["type"]="P";
		       $tab[1]["nom"]="Produit 2"; $tab[1]["statut"]="NOK"; $tab[1]["id"]="97"; $tab[1]["type"]="P";
		       ...
	$varaff	Tableau ou liste (séparé par des virgules) des champs à afficher
		par ex $varaff="nom,statut";
	
	$varlar	Tableau ou liste (séparé par des virgules) de la largeur de chaque colonne
		par ex $varlar="350,50";
	
	$order	Nom du champs sur lequel va être trié la sortie (default sélectionne le résultat de la fonction AffProduit)
		par ex $order="nom";
	
	$vartitre Tableau ou liste (séparé par des virgules) indiquant le nom à afficher en haut de chaque colonne
		par ex $vartitre="Produit,Statut";

	$valign	Alignement du texte dans les cellules (top, middle, bottom)
	
	$sens	Sens pour le trie des colonnes 'i' -> normal, 'd' -> inversé

	$skey	Si 'yes' test l'appuye de touche pour le raccourcis clavier

	Les exemples donnerait la sortie suivante :
		Produit		Statut

		Produit1	OK
		Produit2	NOK
*/


function AfficheTableau($tabValeur,$tabTitre=array(),$order="",$trie="",$url="",$start=0,$limit=-1,$nbline=0,$showicon="")
  {global $mod,$rub,$corefolder,$MyOpt;
	// $ret ="\n<table id='mytable' class='tableauAff' width'100%'>\n";
	$idtbl=uniqid("tbl_");
	$ret ="\n<table id='".$idtbl."' class='tableauAff' width='100%'>\n";

	$ret.="<thead><tr>";
	$nb=1;

	if (is_array($order))
	{
		$taborder=$order;
	}
	else
	{
		$taborder=array();
		$taborder[$order]=(($trie=="d") ? "asc" : "desc");
	}
	
	$page=$_SERVER["SCRIPT_NAME"]."?mod=$mod&rub=$rub";

	if (!is_array($tabTitre))
	{
	  	$tabTitre=array();
	  	foreach($tabValeur[0] as $name=>$t)
	  	{
	  	  	$tabTitre[$name]["aff"]=$name;
	  	}
	}
	$affsub=0;
	$sub="<tr>";
	$subb="<tr>";
	$affsubb=0;
  	foreach($tabTitre as $name=>$v)
	{
		if (!isset($v["align"]))
		{
			$tabTitre[$name]["align"]="center";
			$v["align"]="center";
		}
		if (!isset($v["mobile"]))
		{
			$tabTitre[$name]["mobile"]="";
			$v["mobile"]="";
		}
		if (!isset($v["width"]))
		{
			$v["width"]=0;
		}

		if ($v["aff"]=="<line>")
		{
			if ((!isset($v["width"])) || (!is_numeric($v["width"])))
			{
				$v["width"]=1;
			}
			$ret.="<th style='width:".$v["width"]."px; border-left: 1px solid black;'".(($v["mobile"]=="no") ? " class='noMobile'" :"").">";
			$sub.="<th style='border-left: 1px solid black;'".(($v["mobile"]=="no") ? " class='noMobile'" :"")."></th>";
			$subb.="<th style='border-left: 1px solid black;'".(($v["mobile"]=="no") ? " class='noMobile'" :"")."></th>";
		}
		else
		{
			$ret.="<th ".(($v["width"]>0) ? "width='".$v["width"]."'" : "")." ".(($v["align"]!="") ? " align='".$v["align"]."'" : "").(($v["mobile"]=="no") ? " class='noMobile'" :"").">";
			// $ret.="<b><a href='$page&order=$name&trie=d".(($url!="") ? "&$url" : "")."&ts=0'>".$v["aff"]."</a></b>";
			$ret.=$v["aff"];
			$sub.="<th align='".$v["align"].(($v["mobile"]=="no") ? " class='noMobile'" :"")."'>".((isset($v["sub"])) ? $v["sub"] : "")."</th>";
			$subb.="<th align='".$v["align"].(($v["mobile"]=="no") ? " class='noMobile'" :"")."'>".((isset($v["bottom"])) ? $v["bottom"] : "")."</th>";
		}
		if (isset($v["sub"]))
		{
			$affsub=1;
		}
		if (isset($v["bottom"]))
		{
			$affsubb=1;
		}
		
		$ret.="</th>";
		$nb++;
	}
	$ret.="</tr></thead>\n";
	$sub.="</tr>";
	$subb.="</tr>";

	if ($affsub==1)
	{
		$ret.=$sub;
	}

	$ret.="<tbody>\n";
	
	if (is_array($tabValeur))
	  {
		$ii=0;

		foreach($tabValeur as $i=>$val)
		{ 
			$ret.="<tr";
			if ($showicon!="")
			{
				$ret.=" OnMouseOver=\"document.getElementById('".$showicon."_".$val["id"]["val"]."').style.display='block';\" OnMouseOut=\"document.getElementById('".$showicon."_".$val["id"]["val"]."').style.display='none';\"";
			}
			$ret.=">";
	
			foreach($tabTitre as $name=>$v)
			{
				if (!isset($val[$name]["val"]))
				{
					$val[$name]["val"]="";
				}
				if (!isset($val[$name]["color"]))
				{
					$val[$name]["color"]="";
				}
				if ((!isset($val[$name]["aff"])) || ($val[$name]["aff"]==""))
				{
					$val[$name]["aff"]=$val[$name]["val"];
				}
				if (!isset($val[$name]["align"]))
				{
					$val[$name]["align"]="left";
				}
				if ("*".$val[$name]["val"]=="*<line>")
				{
					// echo "'".$val[$name]["val"]."'";
					$ret.="<td style='border-left: 1px solid black;'".(($v["mobile"]=="no") ? " class='noMobile'" :"")."></td>";
				}
				else
				{
					$ret.="<td data-sort='".str_replace("'","_",$val[$name]["val"])."' ".(($val[$name]["align"]!="") ? "align='".$val[$name]["align"]."'" : "").(($v["mobile"]=="no") ? " class='noMobile'" :"").(($val[$name]["color"]!="") ? " style='background-color:#".$val[$name]["color"].";'" : "").">".$val[$name]["aff"]."</td>";
				}
			}
			$ret.="</tr>\n";
			$ii=$ii+1;
		}
	}
	
	if ($affsubb==1)
	{
		$ret.="<tfoot>";
		$ret.=$subb;
		$ret.="</tfoot>";
	}
	$ret.="</tbody>\n";
	$ret.="</table>\n";

	$ret.="<link rel='stylesheet' type='text/css' href='".$MyOpt["host"]."/core/external/jquery/css/dataTables.min.css' />";
	$ret.="<script type='text/javascript' src='".$MyOpt["host"]."/core/external/jquery/jquery.dataTables.min.js'></script>";

	$ret.="<script>";
	$ret.="$(document).ready(function() {";
	$ret.="$('#".$idtbl."').DataTable({";

	if ($limit<0)
	{
		$limit=count($tabValeur);
	}

// $limit.',10, 25, 50, 75, 100
	$lstLimit="";
	$tabLimit=array(10,25,50,75,100);
	$s="";
	$ok=0;
	foreach($tabLimit as $i=>$l)
	{
		if (($limit<$l) && ($ok==0))
		{
			$lstLimit.=$s.$limit;
			$s=",";
			$ok=1;
		}
		$lstLimit.=$s.$l;
		$s=",";
	}
	if ($limit>100)
	{
		$lstLimit.=$s.$limit;
	}

	$ret.='    "language": { ';
	$ret.='       "paginate": { "first":"Début","last":"Fin","next":"Suivant","previous":"Précédent" },';
	$ret.='       "search": "Rechercher:",';
	$ret.='       "lengthMenu":     "Affiche _MENU_ ligne(s)",';
	$ret.='   	  "loadingRecords": "Chargement...",';
	$ret.='       "processing":     "Mise en page...",';
	$ret.='       "emptyTable":     "Pas de donnée disponible",';
	$ret.='       "info":           "Affiche les lignes de _START_ à _END_ sur _TOTAL_",';
 	$ret.='       "infoEmpty":      "Affiche 0 ligne",';
	$ret.='       "infoFiltered":   "(filtered from _MAX_ total entries)",';
	$ret.='    },';
	$ret.='    "pageLength": '.$limit.',';
	$ret.='    "lengthMenu": [ '.$lstLimit.' ],';

	$ret.='    "columns": [';
	$o="";
	$i=0;
	$o='   "order": [';
	foreach($tabTitre as $name=>$t)
	{
		$ret.='{ "name": "'.$name.'"},';
		if (isset($taborder[$name]))
		{
			$o.='["'.$i.'","'.$taborder[$name].'"],';
			// ["'.$i.'", "'.(($trie=="d") ? "asc" : "desc").'" ]]';
		}
		$i=$i+1;
	}
	$o.="]";
	$ret.='],';
	$ret.=$o;

	$ret.="}) });";
	$ret.="</script>";

	return $ret;
  }

function TrieVal ($a, $b)
{
	global $order;
	if (!isset($a[$order]["val"]))
	{
		return 1;
	}
	if (!isset($b[$order]["val"]))
	{
		return 1;
	}

	if (strtolower($a[$order]["val"]) == strtolower($b[$order]["val"]))
	  { return 0; }
	else if (strtolower($a[$order]["val"]) < strtolower($b[$order]["val"]))
	  { return -1; }
	else
	  { return 1; }
//	return (strtolower($a[$order]["val"]) < strtolower($b[$order]["val"])) ? -1 : 1;
}
function TrieValInv ($a, $b)
  { global $order;
//	return (strtolower($a[$order]["val"]) < strtolower($b[$order]["val"])) ? 1 : -1;
	if (strtolower($a[$order]["val"]) == strtolower($b[$order]["val"]))
	  { return ""; }
	else if (strtolower($a[$order]["val"]) < strtolower($b[$order]["val"]))
	  { return 1; }
	else
	  { return -1; }
  }


/* **** Fonction d'affichage d'un tableau avec filtre ****

*/

function AfficheTableauRemote($tabTitre="",$url,$order="",$trie="d",$search,$nbline=25)
{
	global $mod,$rub,$corefolder,$MyOpt;

	$idtbl=uniqid("tbl_");
	$ret ="\n<table id='".$idtbl."' class='tableauAff'>\n";

	$ret.="<thead><tr>";

	if (!is_array($tabTitre))
	{
	  	$tabTitre=array();
	  	foreach($tabValeur[0] as $name=>$t)
	  	{
	  	  	$tabTitre[$name]["aff"]=$name;
	  	}
	}

  	foreach($tabTitre as $name=>$v)
	{
		if (!isset($v["align"]))
		{
			$tabTitre[$name]["align"]="center";
			$v["align"]="center";
		}
		if (!isset($v["mobile"]))
		{
			$tabTitre[$name]["mobile"]="";
			$v["mobile"]="";
		}

		if ($name==$order)
		{
			$ret.="<th width='".$v["width"]."'".(($v["align"]!="") ? " align='".$v["align"]."'" : "").(($v["mobile"]=="no") ? " class='noMobile'" :"").">";
			// $ret.="<b><a href='$page&order=$name&trie=".(($trie=="d") ? "i" : "d").(($url!="") ? "&$url" : "")."&ts=0'>".$v["aff"]."</a></b>";
		  	// $ret.=" <img src='".$MyOpt["host"]."/".$corefolder."/static/images/sens_$trie.gif' border=0>";
			$ret.=$v["aff"];
		}
		else if ($v["aff"]=="<line>")
		{
			if ((!isset($v["width"])) || (!is_numeric($v["width"])))
			{
				$v["width"]=1;
			}
			$ret.="<th style='width:".$v["width"]."px; border-left: 1px solid black;'".(($v["mobile"]=="no") ? " class='noMobile'" :"").">";
		}
		else
		{
			$ret.="<th width='".$v["width"]."'".(($v["align"]!="") ? " align='".$v["align"]."'" : "").(($v["mobile"]=="no") ? " class='noMobile'" :"").">";
			// $ret.="<b><a href='$page&order=$name&trie=d".(($url!="") ? "&$url" : "")."&ts=0'>".$v["aff"]."</a></b>";
			$ret.=$v["aff"];
		}
		if (isset($v["sub"]))
		{
			$affsub=1;
		}
		if (isset($v["bottom"]))
		{
			$affsubb=1;
		}
		
		$ret.="</th>";
	}
	$ret.="</tr></thead>\n";


	$ret.="<tbody>\n";

	$ret.="</tbody>\n";
	// if ($affsubb==1)
	// {
		// $ret.="<tfoot>";
		// $ret.=$subb;
		// $ret.="</tfoot>";
	// }
	$ret.="</table>\n";

	$ret.="<link rel='stylesheet' type='text/css' href='".$MyOpt["host"]."/core/external/jquery/css/dataTables.min.css' />";
	$ret.="<script type='text/javascript' src='".$MyOpt["host"]."/core/external/jquery/jquery.dataTables.min.js'></script>";

	$ret.="<script>";
	$ret.='$(document).ready(function() {';
	$ret.='  $("#'.$idtbl.'").DataTable({';
	if (!$search)
	{
		$ret.='"searching": false,';
	}
	$ret.='    "language": { ';
	$ret.='       "paginate": { "first":"Début","last":"Fin","next":"Suivant","previous":"Précédent" },';
	$ret.='       "search": "Rechercher:",';
	$ret.='       "lengthMenu":     "Affiche _MENU_ ligne(s)",';
	$ret.='   	  "loadingRecords": "Chargement...",';
	$ret.='       "processing":     "Mise en page...",';
	$ret.='       "emptyTable":     "Pas de donnée disponible",';
	$ret.='       "info":           "Lignes de _START_ à _END_ sur un total de _TOTAL_",';
 	$ret.='       "infoEmpty":      "Affiche 0 ligne",';
	$ret.='       "infoFiltered":   "(filtered from _MAX_ total entries)",';
	$ret.='    },';
	$ret.='    "pageLength": '.$nbline.',';
	$ret.='    "processing": true,';
    $ret.='    "serverSide": true,';
    $ret.='    "ajax": "'.$url.'",';
	$ret.='    "columns": [';
	
	$o="";
	$i=0;
	foreach($tabTitre as $name=>$t)
	{
		$ret.='{ "name": "'.$name.'"},';
		if ($name==$order)
		{
			$o='   "order": [[ '.$i.', "'.(($trie=="d") ? "asc" : "desc").'" ]]';
		}
		$i=$i+1;
	}
	$ret.=' ],';
	$ret.=$o;
	$ret.='   });';
	$ret.='});';
	$ret.="</script>";

	return $ret;
}

function AfficheTableauFiltre($tabValeur,$tabTitre="",$order="",$trie="",$url="",$start=0,$limit=0,$nbline=0,$affsearch=false,$sort=true,$showicon="")
{
	global $mod,$rub,$tabsearch,$corefolder,$MyOpt;
	$ls="";
	if (is_array($tabsearch))
	{
		foreach ($tabsearch as $v=>$d)
		{
			if ($d!="")
			{
				$ls.="&tabsearch[".$v."]=".$d;
			}
		}
	}

	$ret ="\n<table class='tableauAff'>\n";

	$ret.="<tr>";
	$ret.="<th width=20>&nbsp;</th>";
	$nb=1;
	
	$page=$_SERVER["SCRIPT_NAME"]."?mod=$mod&rub=$rub";

	if (!is_array($tabTitre))
	{
	  	$tabTitre=array();
	  	foreach($tabValeur[0] as $name=>$t)
		{
	  	  	$tabTitre[$name]["aff"]=$name;
	  	}
	}

	$sub="<tr><th></th>";
	$affsub=0;
	$subb="<tr><th></th>";
	$affsubb=0;
	$search="<tr><th></th>";

  	foreach($tabTitre as $name=>$v)
	{
		if (!isset($v["align"]))
		{
			$tabTitre[$name]["align"]="center";
			$v["align"]="center";
		}
		if (!isset($v["width"]))
		{
			$tabTitre[$name]["width"]=1;
			$v["width"]=1;
		}
		if (!isset($v["mobile"]))
		{
			$tabTitre[$name]["mobile"]="";
			$v["mobile"]="";
		}
		
		if ($name==$order)
		{
			$ret.="<th width='".$v["width"]."'".(($v["align"]!="") ? " align='".$v["align"]."'" : "").(($v["mobile"]=="no") ? " class='noMobile'" :"").">";
			if ($sort)
			{
				$ret.="<b><a href='$page&order=$name&trie=".(($trie=="d") ? "i" : "d").(($url!="") ? "&$url" : "")."&ts=0".$ls."'>".$v["aff"]."</a></b>";
			}
			else
			{
				$ret.="<b>".$v["aff"]."</b>";
			}
		  	$ret.=" <img src='".$MyOpt["host"]."/".$corefolder."/static/images/sens_$trie.gif' border=0><input type='hidden' name='trie' value='".$trie."'><input type='hidden' name='order' value='".$order."'>";
			$sub.="<th align='".$v["align"]."'>".((isset($v["sub"])) ? $v["sub"] : "").(($v["mobile"]=="no") ? " class='noMobile'" :"")."</th>";
			$subb.="<th align='".$v["align"]."'>".((isset($v["bottom"])) ? $v["bottom"] : "").(($v["mobile"]=="no") ? " class='noMobile'" :"")."</th>";
			$search.="<th><input type='text' style='width:".$v["width"]."px;' name='tabsearch[".$name."]' value='".((isset($tabsearch[$name])) ? $tabsearch[$name] : '').(($v["mobile"]=="no") ? " class='noMobile'" :"")."' OnChange='document.getElementById(\"form_tableau\").submit();'></th>";
		}
		else if ($v["aff"]=="<line>")
		{
			$ret.="<th style='width:".$v["width"]."px; border-right: ".$v["width"]."px solid black; '>";
			$sub.="<th style='border-right: 1px solid black;'></th>";
			$subb.="<th style='border-right: 1px solid black;'></th>";
			$search.="<th style='border-right: 1px solid black;'></th>";
		}
		else
		{
			$ret.="<th width='".$v["width"]."'".(($v["align"]!="") ? " align='".$v["align"]."'" : "").(($v["mobile"]=="no") ? " class='noMobile'" :"").">";
			if ($sort)
			{
				$ret.="<b><a href='$page&order=$name&trie=d".(($url!="") ? "&$url" : "")."&ts=0".$ls."'>".$v["aff"]."</a></b>";
			}
			else
			{
				$ret.="<b>".$v["aff"]."</b>";
			}
			$sub.="<th align='".$v["align"]."'>".((isset($v["sub"])) ? $v["sub"] : "").(($v["mobile"]=="no") ? " class='noMobile'" :"")."</th>";
			$subb.="<th align='".$v["align"]."'>".((isset($v["bottom"])) ? $v["bottom"] : "").(($v["mobile"]=="no") ? " class='noMobile'" :"")."</th>";
			$search.="<th><input type='text' style='width:".($v["width"]-5)."px;' name='tabsearch[".$name."]' value='".((isset($tabsearch[$name])) ? $tabsearch[$name] : '').(($v["mobile"]=="no") ? " class='noMobile'" :"")."' OnChange='document.getElementById(\"form_tableau\").submit();'></th>";
		}
		if (isset($v["sub"]))
		{
			$affsub=1;
		}
		if (isset($v["subb"]))
		{
			$affsubb=1;
		}
		$ret.="</th>";
		$nb++;
	  }
	
	$ret.="</tr>\n";
	$sub.="</tr>";
	$subb.="</tr>";
	$search.="</tr>";

	if ($affsub==1)
	{
		$ret.=$sub;
	}
	
	if ($affsearch)
	{
		$ret.=$search;
	}

	if (is_array($tabValeur))
	{
		$ii=0;
	
		if ($limit=="")
		  { $limit=count($tabValeur); }

		foreach($tabValeur as $i=>$val)
		  { 
//			if (($ii>=$start) && ($ii<$start+$limit))
//			  {
				// $col = abs($col-110);
				// $ret.="<tr onmouseover=\"setPointer(this, 'over', '#".$myColor[$col]."', '#".$myColor[$col+5]."', '#FF0000')\" onmouseout=\"setPointer(this, 'out', '#".$myColor[$col]."', '#".$myColor[$col+5]."', '#FF0000')\">";
				// $ret.="<tr>";
				// $ret.="<td bgcolor=\"#".$myColor[$col]."\">&nbsp;</td>";
				$ret.="<tr";
				if ($showicon!="")
				{
					$ret.=" OnMouseOver=\"document.getElementById('".$showicon."_".$val["id"]["val"]."').style.display='block';\" OnMouseOut=\"document.getElementById('".$showicon."_".$val["id"]["val"]."').style.display='none';\"";
				}
				$ret.=">";

				$ret.="<td>&nbsp;</td>";

				foreach($tabTitre as $name=>$v)
				  {
					if ($val[$name]["val"]=="<line>")
					  {
						$ret.="<td style='border-right: ".$v["width"]."px solid black;'></td>";
					  }
					else
					  {
						$ret.="<td ".(((isset($val[$name]["align"])) && ($val[$name]["align"]!="")) ? " align='".$val[$name]["align"]."'" : "").(($v["mobile"]=="no") ? " class='noMobile'" :"").">".(((!isset($val[$name]["aff"])) || ($val[$name]["aff"]=="")) ? $val[$name]["val"] : $val[$name]["aff"])."</td>";
					  }
				  }
				$ret.="</tr>\n";
			  }
			$ii=$ii+1;
//		  }
	  }

	if ($affsubb==1)
	{
		$ret.=$subb;
	}
	$ret.="</table>\n";

	// Affiche la liste des pages
	$nbtot=($nbline>0) ? $nbline : count($tabValeur);
	if ($nbtot>$limit)
	  {
		$lstpage="";
		$ii=1;
  	  	$t=0;
		$nbp=20;
		
		for($i=0; $i<$nbtot; $i=$i+$limit)
		  {
		  	if (($i<=$start) && ($i>$start-$limit))
		  	  {
		  	  	$lstpage.="<a href='$page&order=$order".(($trie!="") ? "&trie=$trie" : "").(($url!="") ? "&$url" : "")."&ts=$i".$ls."'>[$ii]</a> ";
		  	  	$t=0;
		  	  }
			else if ( (($i>$start-$nbp*$limit/2) && ($i<$start+$nbp*$limit/2)) || ($i>$nbtot-$limit) || ($i==0))
		  	  {
		  	  	$lstpage.="<a href='$page&order=$order".(($trie!="") ? "&trie=$trie" : "").(($url!="") ? "&$url" : "")."&ts=$i".$ls."'>$ii</a> ";
		  	  	$t=0;
		  	  }
		  	else if ($t==0)
		  	  {
		  	  	$lstpage.=" ... ";
				$t=1;
		  	  }
		  	$ii=$ii+1;
		  }

		$ret.="Pages : $lstpage<br />\n";
	  }


	return $ret;
  }	
  
  

function TrieProduit ($a, $b)
  {
	$a["nom_produit"]=preg_replace("/<[^>]*>/i","",$a["nom_produit"]);
	$b["nom_produit"]=preg_replace("/<[^>]*>/i","",$b["nom_produit"]);

	if (strtolower($a["nom_produit"]) == strtolower($b["nom_produit"]))
	  {
		$a["nom_produit2"]=preg_replace("/<[^>]*>/i","",$a["nom_produit2"]);
		$b["nom_produit2"]=preg_replace("/<[^>]*>/i","",$b["nom_produit2"]);
		if (strtolower($a["nom_produit2"]) == strtolower($b["nom_produit2"])) { return 0; }
		return (strtolower($a["nom_produit2"]) < strtolower($b["nom_produit2"])) ? -1 : 1;
	  }
	return (strtolower($a["nom_produit"]) < strtolower($b["nom_produit"])) ? -1 : 1;
  }

function TrieProduit2 ($a, $b)
  {
	$a["nom_produit"]=preg_replace("/<[^>]*>/i","",$a["nom_produit"]);
	$b["nom_produit"]=preg_replace("/<[^>]*>/i","",$b["nom_produit"]);

	if (strtolower($a["nom_produit"]) == strtolower($b["nom_produit"]))
	  {
		$a["nom_produit2"]=preg_replace("/<[^>]*>/i","",$a["nom_produit2"]);
		$b["nom_produit2"]=preg_replace("/<[^>]*>/i","",$b["nom_produit2"]);
		if (strtolower($a["nom_produit2"]) == strtolower($b["nom_produit2"])) { return 0; }
		return (strtolower($a["nom_produit2"]) < strtolower($b["nom_produit2"])) ? 1 : -1;
	  }
	return (strtolower($a["nom_produit"]) < strtolower($b["nom_produit"])) ? 1 : -1;
  }


// Calcul un dégradé de couleur
function CalcColor($color,$pour,$fcolor="FFFFFF")
  {
	$color2=str_replace('#','',$color);

	$rr=hexdec(substr($color2, 0, 2))*((100-$pour)/100)+hexdec(substr($fcolor, 0, 2))*($pour/100);
	if ($rr>254) { $rr=255; }
	$rr=strtoupper(dechex($rr));
	$rr=substr("00",0,2-strlen($rr)).$rr;

	$vv=hexdec(substr($color2, 2, 2))*((100-$pour)/100)+hexdec(substr($fcolor, 2, 2))*($pour/100);
	if ($vv>254) { $vv=255; }
	$vv=strtoupper(dechex($vv));
	$vv=substr("00",0,2-strlen($vv)).$vv;

	$bb=hexdec(substr($color2, 4, 2))*((100-$pour)/100)+hexdec(substr($fcolor, 4, 2))*($pour/100);
	if ($bb>254) { $bb=255; }
	$bb=strtoupper(dechex($bb));
	$bb=substr("00",0,2-strlen($bb)).$bb;

	$color2=$rr.$vv.$bb;
	return $color2;
  }


/* **** Complète une chaine de caractères ****

	$txt	Chaine à compléter
	$nb	Nb de caractères que doit comporter la chaine
	$car	Caractère de remplissage  
*/
function CompleteTxt($txt,$nb,$car)
  {
	$n=$nb-strlen($txt);
	if ($n<0) { $n=0; }
	$ret="";
	for ($i=0;$i<$nb;$i++) { $ret.=$car; }
	return substr($ret,0,$n).$txt;
  }

function InvCompleteTxt($txt,$car)
  {
	$n=-1;
	for ($i=0;$i<strlen($txt);$i++) { if ((substr($txt,$i,1)!=$car) && ($n==-1)) { $n=$i; } }
	return substr($txt,$n,strlen($txt)-$n);
  }


/* **** Return a chain with the first letter in uppercase ****
	$txt	Chain
*/

function UpperFirstLetter($txt)
  {
  	$t=strtoupper(substr($txt,0,1)).substr($txt,1,strlen($txt)-1);
  	return $t;
  }

function FatalError($txt,$msg="")
  { global $MyOpt,$tmpl_prg,$corefolder,$theme;
  	if (isset($tmpl_prg))
  	{
		$tmpl_prg->assign("style_url", $MyOpt["host"]."/".GenereStyle(($theme=="phone") ? "phone" : "default"));
		$tmpl_prg->assign("icone","<IMG src=\"".$corefolder."/static/images/icn48_erreur.png\">");
		$tmpl_prg->assign("infos","$txt");
		$tmpl_prg->assign("corps","$msg");
		$tmpl_prg->parse("main");
		echo $tmpl_prg->text("main");
	}
	else
	{
		echo $txt."\"n";
		echo $msg."\"n";
	}
	exit;			 
  }

// Affiche une valeur au format xxx,yy
function AffMontant($val)
  {
  	global $MyOpt;
  	preg_match("/([\-0-9]*)\.?([0-9]*)/i",round($val,2),$m);
	$ret=$m[1].",".$m[2].substr("00",0,2-strlen($m[2]));
	
	$ret=$ret." ".$MyOpt["devise"];
	
	return $ret;
  }

// Duplique une chaine de caractères
function Duplique($txt,$nb)
  {
	$ret="";
	for($i=0;$i<$nb;$i++)
	  { $ret.=$txt; }
	return $ret;
  }

// Affiche la taille d'un fichier en human reading
function CalcSize($s)
{
	if ($s<1024)
	{
		return $s." octets";
	}
	else if ($s<1024*1024)
	{
		return floor($s/1024)." ko";
	}
	else if ($s<1024*1024*1024)
	{
		return floor($s/1024/1024)." Mo";
	}
	else if ($s<1024*1024*1024*1024)
	{
		return floor($s/1024/1024/1024)." Go";
	}
}

// Affiche les 4 premières lignes d'un texte

/*
Truc mavchin<BR>chose et companie<BR>1<BR>
2<BR>3<BR>4<BR>
5&gt;<BR>6<BR>
7<BR>8<BR>
9<BR>

**

Truc mavchin<BR>chose et companie<BR>1<BR>
2<BR>3<BR>4<BR>
5&gt;<BR>6<BR>
7<BR>8<BR>
9<BR>


*/

function GetFirstLine($txt,$nb=4)
  {
  	$p=0;
  	$i=0;

	$txt=preg_replace("/<br ?\/?>/i","<br/>",$txt);
	$txt=preg_replace("/<br\/><br\/>/","<br/>",$txt);
	$txt=preg_replace("/<br\/><br\/>/","<br/>",$txt);
	$txt=preg_replace("/\r|\n/i","",$txt);

	while($i<$nb)
	  {
		$p0=strpos($txt,"<br/>",$p);
		if ($p0>0)
		  {
			$p=$p0+1;
		  }
		else
		  {
			$p=strlen($txt);
		  	$i=$nb+1;
		  }
		$i=$i+1;
	  }
	if ($p==strlen($txt))
	  { return $txt; }
	else
	  { return substr($txt,0,$p-1)."<br/>..."; }
}


// Convertie une couleur en RGB
function ConvertColor2RGB($col,$add=0)
{
  	$r=hexdec(substr($col,0,2));
  	$r=($r+$add>255) ? 255 : $r+$add;
  	$g=hexdec(substr($col,2,2));
  	$g=($g+$add>255) ? 255 : $g+$add;
  	$b=hexdec(substr($col,4,2));
  	$b=($b+$add>255) ? 255 : $b+$add;
  	return "rgb($r, $g, $b)";
}



// Affiche une date
function DisplayDate($dte)
{ global $tabLang;
	$d=time()-strtotime($dte);
	$mid=time()-strtotime(date("Y-m-d 23:59:59",time()-3600*24));

	$h=floor($d/3600);
	$m=floor(($d-$h*3600)/60);
	$s=$d-$h*3600-$m*60;

	if (($s<2) && ($m==0) && ($h==0))
	  {
			return $tabLang["core_since"].$s." ".$tabLang["core_second"].$tabLang["core_ago"];
	  }
	else if (($s<60) && ($m==0) && ($h==0))
	  {
			return $tabLang["core_since"].$s." ".$tabLang["core_seconds"].$tabLang["core_ago"];
	  }
	else if (($m<2) && ($h==0))
	  {
			return $tabLang["core_since"]."1 ".$tabLang["core_minute"].$tabLang["core_ago"];
	  }
	else if (($m<60) && ($h==0))
	  {
			return $tabLang["core_since"].$m." ".$tabLang["core_minutes"].$tabLang["core_ago"];
	  }
	else if (($h<2) && ($m==0))
	  {
			return $tabLang["core_since"]."1 ".$tabLang["core_hour"].$tabLang["core_ago"];
	  }
	else if (($h<2) && ($m<2))
	  {
			return $tabLang["core_since"]."1 ".$tabLang["core_hour"]." et 1 ".$tabLang["core_minute"].$tabLang["core_ago"];
	  }
	else if ($h<2)
	  {
			return $tabLang["core_since"]."1 ".$tabLang["core_hour"]." et ".$m." ".$tabLang["core_minutes"].$tabLang["core_ago"];
	  }
	else if (($d<$mid) && ($h<2))
	  {
			return $tabLang["core_since"]."1 ".$tabLang["core_hour"]." et ".$m." ".$tabLang["core_minutes"].$tabLang["core_ago"];
	  }
	else if ($d<$mid)
	  {
			return $tabLang["core_since"].$h." ".$tabLang["core_hours"]." ".$tabLang["core_and"]." ".$m." ".$tabLang["core_minutes"].$tabLang["core_ago"];
	  }
	else if (($d<$mid+3600*34) && ($d>$mid))
	  {
			return $tabLang["core_yesterday"]." ".sql2time($dte,"no");
	  }
	else
	  {
			return $tabLang["core_the"]." ".sql2date($dte,"jour")." ".$tabLang["core_at"]." ".sql2time($dte,"no");
	  }	


}

// Affiche une date SQL avec une couleur
function AffDate($dte)
{
	if ($dte!="0000-00-00")
	{
		$ret=sql2date($dte);
		if (date_diff_txt($dte,date("Y-m-d"))>0)
		{
			$ret="<B><font color=\"red\">$ret</font></B>";
		}
		else if (date_diff_txt($dte,date("Y-m-d"))>-30*24*3600)
		{
			$ret="<B><font color=\"orange\">$ret</font></B>";
		}
	}
	else
	{	$ret="-"; }
	return $ret;
}

function TestDate($dte)
{
	// Ex EcheanceDate
	$ret="ok";
	if (date_diff_txt($dte,date("Y-m-d"))>0)
	{
		$ret="nok";
	}
	else if (date_diff_txt($dte,date("Y-m-d"))>-30*24*3600)
	{
		$ret="ok";
	}
	return $ret;
}


// Affiche un temps en minute en heure:minute
function AffHeures($min){
	$t=$min;
	$h=floor($t/60);
	$m=$t-$h*60;
	$m=substr("00",0,2-strlen($m)).$m;

	$ret=$h."h $m";
	return $ret;
}

// Affiche un téléphone
function AffTelephone($txt)
  {
  	$rtxt=$txt;
		$rtxt=preg_replace("/^0([1-9])([0-9]*)$/","+33\\1\\2",$txt);
		return $rtxt;
  }


// Génère le fichier des variables
function GenereVariables($tab)
{
	global $sql,$gl_tbl;

	$ret="";
	$conffile="../config/variables.inc.php";
	if (!file_exists($conffile))
	{
		$ret.="Création du fichier";
	}

	$tab["version"]["valeur"]=time();

	if(is_writable($conffile))
	{
		$fd=fopen($conffile,"w");
		fwrite($fd,"<?php\n");
		foreach($tab as $nom=>$d)
		{
			if (is_array($d))
			{
				foreach($d as $var=>$dd)
				{
					if ($var=="valeur")
					{
						fwrite($fd,"\$MyOpt[\"".$nom."\"]=\"".$dd."\";\n");
						$name1=$nom;
						$name2="";
					}
					else
					{
						fwrite($fd,"\$MyOpt[\"".$nom."\"][\"".$var."\"]=\"".$dd."\";\n");
						$name1=$nom;
						$name2=$var;
					}
					
					$query="SELECT id FROM ".$gl_tbl."_config WHERE param='variable' AND name1='".$name1."' AND name2='".$name2."'";
					$res=$sql->QueryRow($query);

					if ($res["id"]>0)
					{
						$query="UPDATE ".$gl_tbl."_config SET value='".addslashes($dd)."', dte_creat='".now()."' WHERE id='".$res["id"]."'";
						$sql->Update($query);
					}
					else
					{
						$query="INSERT INTO ".$gl_tbl."_config SET param='variable', name1='".$name1."', name2='".$name2."', value='".addslashes($dd)."', dte_creat='".now()."'";
						$ret=$sql->Insert($query);
					}
				}
			}
		}
	
		fwrite($fd,"?>\n");
		fclose($fd);
		$ret.="Enregistrement effectué";
	}
	else
	{
		$ret.="Accès refusé. Fichier : ".$conffile;
	}
	return $ret;
}

function GenereFichierVariables($tab)
{
	$ret="";
	$conffile="../config/variables.inc.php";
	if (!file_exists($conffile))
		{ $ret.="Création du fichier";}

	$tab["version"]=time();
	if(is_writable($conffile))
	{
		$fd=fopen($conffile,"w");
		fwrite($fd,"<?\n");
		foreach($tab as $nom=>$d)
		{
			if (is_array($d))
			{
				foreach($d as $var=>$dd)
				{
					fwrite($fd,"\$MyOpt[\"".$nom."\"][\"".$var."\"]=\"".$dd."\";\n");
				}
			}
			else
			{
				fwrite($fd,"\$MyOpt[\"".$nom."\"]=\"".$d."\";\n");
			}
		}
		
		fwrite($fd,"?>\n");
		fclose($fd);
		$ret.="Enregistrement effectué";
	}
	else
	{
		$ret.="Accès refusé. Fichier : ".$conffile;
	}
	return $ret;
}

function UpdateVariables($tab)
{
	global $gl_tbl;
	
	$MyOpt=array();
	$MyOpt["tbl"]=$gl_tbl;
	foreach($tab as $nom=>$d)
	{
		if (is_array($d))
		{
			foreach($d as $var=>$dd)
			{
				if ($var=="valeur")
				{
					$MyOpt[$nom]=$dd;
				}
				else
				{
					$MyOpt[$nom][$var]=$dd;
				}
			}
		}
	}
	return $MyOpt;
}

function now()
{
	return date("Y-m-d H:i:s");
}


// Calcul de la distance entre 2 points
function getDistance($lat1, $lon1, $lat2, $lon2, $unit="K") {

  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  $unit = strtoupper($unit);

  if ($unit == "K") {
    return ($miles * 1.609344);
  } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
        return $miles;
      }
}

// Calcul du cap pour aller d'un point à un autre
function getBearing($lat1, $lon1, $lat2, $lon2) {
  //difference in longitudinal coordinates
  $dLon = deg2rad($lon2) - deg2rad($lon1);

  //difference in the phi of latitudinal coordinates
  $dPhi = log(tan(deg2rad($lat2) / 2 + pi() / 4) / tan(deg2rad($lat1) / 2 + pi() / 4));

  //we need to recalculate $dLon if it is greater than pi
  if(abs($dLon) > pi()) {
    if($dLon > 0) {
      $dLon = (2 * pi() - $dLon) * -1;
    }
    else {
      $dLon = 2 * pi() + $dLon;
    }
  }
  //return the angle, normalized
  return (rad2deg(atan2($dLon, $dPhi)) + 360) % 360;
}

function getCompassDirection( $bearing )
{
  static $cardinals = array( 'N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW', 'N' );
  return $cardinals[round( $bearing / 45 )];
}


function affInformation($txt,$res)
{
	global $tmpl_prg;

	if ($txt!="")
	{
		$tmpl_prg->assign("msg_infos", $txt);
		$tmpl_prg->assign("msg_class", $res);
		$tmpl_prg->parse("main.aff_infos");
	}
}

function GenereStyle($name)
{
	global $MyOpt,$core_version,$myrev,$corefolder;
	
	if (!is_numeric($MyOpt["version"]))
	{
		$MyOpt["version"]=0;
	}

	$sfile="static/cache/style/".$name.".".$MyOpt["version"].".".$myrev.".".$core_version.".css";
	if (file_exists("../".$sfile))
	{
		return $sfile;
	}
	
	if (!is_dir("../static/cache/style"))
	{
		mkdir("../static/cache/style");
	}

	// Feuille de style framework
	$tmpl_style = new XTemplate ("modules/default/tmpl/".$name.".css");
	foreach($MyOpt["styleColor"] as $n=>$c)
	{
		$tmpl_style->assign($n,$c);
	}
	$tmpl_style->parse("main");
	$s=$tmpl_style->text("main");

	// Feuille de style custom
	if (file_exists("../modules/default/tmpl/".$name.".css"))
	{
		$tmpl_style = new XTemplate ("../modules/default/tmpl/".$name.".css");
		foreach($MyOpt["styleColor"] as $n=>$c)
		{
			$tmpl_style->assign($n,$c);
		}
		$tmpl_style->parse("main");
		$s.="\n\n".$tmpl_style->text("main");
	}
	
	$s=Purge($s);
	
	$fd=fopen("../".$sfile,"w");
	fwrite($fd,$s);
	fclose($fd);
	return $sfile;
}

// Compresse un fichier
function Purge($txt)
{
	$p=array();
	$r=array();
	$p[]="/ [ ]*/";	$r[]=" ";
	$p[]="/:[ ]*/";	$r[]=":";
	$p[]="/;[ ]*/";	$r[]=";";
	$p[]="/{[ ]*/";	$r[]="{";
	$p[]="/\t/";	$r[]="";
	$p[]="/\r/";	$r[]="";
	$p[]="/\n/";	$r[]="";
	
	$txt=preg_replace($p,$r,$txt);

	return $txt;
}
?>

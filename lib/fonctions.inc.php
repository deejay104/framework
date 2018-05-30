<?
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

function MyRep($file,$mymod="")
{
	global $mod, $theme,$appfolder;
	
	if ($mymod=="")
	{
		$mymod=$mod;
	}

  	$myfile=substr($file,0,strrpos($file,"."));
	$myext=substr($file,strrpos($file,".")+1,strlen($file)-strrpos($file,".")-1);

  	if ((file_exists($appfolder."/modules/$mymod/tmpl/$myfile.$theme.$myext")) && ($mymod!=""))
  	  { return $appfolder."/modules/$mymod/tmpl/$myfile.$theme.$myext"; }
	else if ((file_exists($appfolder."/modules/$mymod/tmpl/$file")) && ($mymod!=""))
  	  { return $appfolder."/modules/$mymod/tmpl/$file"; }
	else if ((file_exists($appfolder."/modules/$mymod/$file")) && ($mymod!=""))
  	  { return $appfolder."/modules/$mymod/$file"; }
  	else if ((file_exists("modules/$mymod/tmpl/$myfile.$theme.$myext")) && ($mymod!=""))
  	  { return "modules/$mymod/tmpl/$myfile.$theme.$myext"; }
	else if ((file_exists("modules/$mymod/tmpl/$file")) && ($mymod!=""))
  	  { return "modules/$mymod/tmpl/$file"; }
	else if ((file_exists("modules/$mymod/$file")) && ($mymod!=""))
  	  { return "modules/$mymod/$file"; }
  	else
  	  { return ""; }
  }

function checkVar($var,$type)
{
	global $_REQUEST;
	
	if (!isset($_REQUEST[$var]))
	{
		$v="";
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
		else
		{
			return 0;
		}
	}
}
  
function GetDroit($droit)
{
	global $myuser;

	if (trim($droit)=="")
	  { return true; }
	else if ((isset($myuser->role[$droit])) && ($myuser->role[$droit]))
	  { return true; }
	else if ((isset($myuser->groupe["SYS"])) && ($myuser->groupe["SYS"]))
	  { return true; }
	// elseif ((isset($myuser->groupe[$droit])) && ($myuser->groupe[$droit]))
	  // { return true; }
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



// Affiche un temps en minute en heures/minutes
function AffTemps($tps,$short="yes") {
	$th=floor($tps/60);
	$tm=$tps-$th*60;
	$tm=substr("00",0,2-strlen($tm)).$tm;

	if (($th>0) && ($short=="full"))
	{
		if ($th>24)
		{
			$td=floor($tps/(24*60));
			$th=floor(($tps-$td*24*60)/60);
			$tm=$tps-$td*24*60-$th*60;

			$th=substr("00",0,2-strlen($th)).$th;
			$tm=substr("00",0,2-strlen($tm)).$tm;

			return $td."j ".$th."h ".$tm;
		}
		else
		{
			return $th."h ".$tm;
		}
	}
	else if (($th>0) || ($short=="no"))
	  { return $th."h ".$tm; }
	else
	  { return $tm."min"; }
}

// Transforme un temps en minute
function CalcTemps($tps,$short=true)
{
	if ( (preg_match("/^([0-9][0-9])([0-9][0-9])$/",$tps,$m)) && ($short) )
	{
		$t=$m[1]*60+$m[2];
	}
	else if (preg_match("/^([0-9][0-9]):([0-9][0-9])$/",$tps,$m))
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



  
function SendMailFromFile($from,$to,$tabcc,$subject,$tabvar,$file)
{ global $mod,$appfolder,$MyOpt;

	if (file_exists($appfolder."/custom/".$file.".mail.txt"))
	{
		$tmail=file($appfolder."/custom/".$file.".mail.txt");
	}
	else if (file_exists($appfolder."/modules/".$mod."/".$file.".mail.txt"))
	{
		$tmail=file($appfolder."/modules/".$mod."/".$file.".mail.txt");
	}
	else if (file_exists("modules/".$mod."/".$file.".mail.txt"))
	{
		$tmail=file("modules/".$mod."/".$file.".mail.txt");
	}
	else
	{
		echo "file not found";
		return false;
	}

	$mail = '';
	foreach($tmail as $ligne)
	{
		$mail.=$ligne;
	}

	$mail=nl2br($mail);
	foreach($tabvar as $p=>$d)
	{
		$mail=str_replace("{".$p."}",$d,$mail);
	}

	// $mail=str_replace("{url}",substr($_SERVER["HTTP_REFERER"],0,strrpos($_SERVER["HTTP_REFERER"],"/")),$mail);
	// $mail=str_replace("{url}",$MyOpt["host"],$mail);
	
	MyMail($from,$to,$tabcc,$subject,$mail,"","");

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
		if ($from=="") { $from = ini_get("sendmail_from"); }

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


function AfficheTableau($tabValeur,$tabTitre=array(),$order="",$trie="",$url="",$start=0,$limit="",$nbline=0)
  {global $mod,$rub,$corefolder;
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
  	foreach($tabTitre as $name=>$v)
	{
		if (!isset($v["align"]))
		{
			$v["align"]="center";
		}
		if ($name==$order)
		{
			$ret.="<th width='".$v["width"]."'".(($v["align"]!="") ? " align='".$v["align"]."'" : "").">";
			$ret.="<b><a href='$page&order=$name&trie=".(($trie=="d") ? "i" : "d").(($url!="") ? "&$url" : "")."&ts=0'>".$v["aff"]."</a></b>";
		  	$ret.=" <img src='".$corefolder."/static/images/sens_$trie.gif' border=0>";
			$sub.="<th align='".$v["align"]."'>".((isset($v["sub"])) ? $v["sub"] : "")."</th>";
			$subb.="<th align='".$v["align"]."'>".((isset($v["bottom"])) ? $v["bottom"] : "")."</th>";
		}
		else if ($v["aff"]=="<line>")
		{
			if ((!isset($v["width"])) || (!is_numeric($v["width"])))
			{
				$v["width"]=1;
			}
			$ret.="<th style='width:".$v["width"]."px; border-left: 1px solid black;'>";
			$sub.="<th style='border-left: 1px solid black;'></th>";
			$subb.="<th style='border-left: 1px solid black;'></th>";
		}
		else
		{
			$ret.="<th width='".$v["width"]."'".(($v["align"]!="") ? " align='".$v["align"]."'" : "").">";
			$ret.="<b><a href='$page&order=$name&trie=d".(($url!="") ? "&$url" : "")."&ts=0'>".$v["aff"]."</a></b>";
			$sub.="<th align='".$v["align"]."'>".((isset($v["sub"])) ? $v["sub"] : "")."</th>";
			$subb.="<th align='".$v["align"]."'>".((isset($v["bottom"])) ? $v["bottom"] : "")."</th>";
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
	$ret.="</tr>\n";
	$sub.="</tr>";
	$subb.="</tr>";

	if ($affsub==1)
	{
		$ret.=$sub;
	}
	
	if (is_array($tabValeur))
	  {
		if ($trie=="d")
		  { usort($tabValeur,"TrieVal"); }
		else if ($trie=="i")
		  { usort($tabValeur,"TrieValInv"); }
		$ii=0;
	
		if ($limit=="")
		  { $limit=count($tabValeur); }

		foreach($tabValeur as $i=>$val)
		{ 
			if (($ii>=$start) && ($ii<$start+$limit))
			{
				$ret.="<tr >";
				// $ret.="<tr onmouseover=\"setPointer(this, 'over', '#".$myColor[$col]."', '#".$myColor[$col+5]."', '#FF0000')\" onmouseout=\"setPointer(this, 'out', '#".$myColor[$col]."', '#".$myColor[$col+5]."', '#FF0000')\">";
				// $ret.="<td bgcolor=\"#".$myColor[$col]."\">&nbsp;</td>";
				$ret.="<td>&nbsp;</td>";
		
				foreach($tabTitre as $name=>$v)
				  {
					if ((!isset($val[$name]["aff"])) || ($val[$name]["aff"]==""))
					{
						$val[$name]["aff"]=$val[$name]["val"];
					}
					if (!isset($val[$name]["align"]))
					{
						$val[$name]["align"]="left";
					}
					if ($val[$name]["val"]=="<line>")
					{
						$ret.="<td style='border-left: 1px solid black;'></td>";
					}
					else
					{
						$ret.="<td ".(($val[$name]["align"]!="") ? "align='".$val[$name]["align"]."'" : "").">".$val[$name]["aff"]."</td>";
					}
				}
				$ret.="</tr>\n";
			}
			$ii=$ii+1;
		}
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
		$nbp=10;

		for($i=0; $i<$nbtot; $i=$i+$limit)
		  {
		  	if (($i<=$start) && ($i>$start-$limit))
		  	  {
		  	  	$lstpage.="<a href='$page&order=$order".(($trie!="") ? "&trie=$trie" : "").(($url!="") ? "&$url" : "")."&ts=$i'>[$ii]</a> ";
		  	  	$t=0;
		  	  }
			else if ( (($i>$start-$nbp*$limit/2) && ($i<$start+$nbp*$limit/2)) || ($i>$nbtot-$limit) || ($i==0))
		  	  {
		  	  	$lstpage.="<a href='$page&order=$order".(($trie!="") ? "&trie=$trie" : "").(($url!="") ? "&$url" : "")."&ts=$i'>$ii</a> ";
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

function AfficheTableauFiltre($tabValeur,$tabTitre="",$order="",$trie="",$url="",$start=0,$limit=0,$nbline=0,$affsearch=false)
{
	global $mod,$rub,$tabsearch,$corefolder;

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
		if ($name==$order)
		{
			$ret.="<th width='".$v["width"]."'".(((isset($v["align"])) && ($v["align"]!="")) ? " align='".$v["align"]."'" : "").">";
			$ret.="<b><a href='$page&order=$name&trie=".(($trie=="d") ? "i" : "d").(($url!="") ? "&$url" : "")."&ts=0".$ls."'>".$v["aff"]."</a></b>";
		  	$ret.=" <img src='".$corefolder."/static/images/sens_$trie.gif' border=0>";
			$sub.="<th align='".$v["align"]."'>".((isset($v["sub"])) ? $v["sub"] : "")."</th>";
			$subb.="<th align='".$v["align"]."'>".((isset($v["bottom"])) ? $v["bottom"] : "")."</th>";
			$search.="<th><input type='text' style='width:".$v["width"]."px;' name='tabsearch[".$name."]' value='".((isset($tabsearch[$name])) ? $tabsearch[$name] : '')."'></th>";
		}
		else if ($v["aff"]=="<line>")
		{
			$ret.="<th style='width:".$v["width"]."px; border-right: ".$v["width"]."px solid black; '>";
			$sub.="<th style='border-left: 1px solid black;'></th>";
			$subb.="<th style='border-left: 1px solid black;'></th>";
			$search.="<th style='border-left: 1px solid black;'></th>";
		}
		else
		{
			$ret.="<th width='".$v["width"]."'".(((isset($v["align"])) && ($v["align"]!="")) ? " align='".$v["align"]."'" : "").">";
			$ret.="<b><a href='$page&order=$name&trie=d".(($url!="") ? "&$url" : "")."&ts=0".$ls."'>".$v["aff"]."</a></b>";
			$sub.="<th align='".$v["align"]."'>".((isset($v["sub"])) ? $v["sub"] : "")."</th>";
			$subb.="<th align='".$v["align"]."'>".((isset($v["bottom"])) ? $v["bottom"] : "")."</th>";
			$search.="<th><input type='text' style='width:".$v["width"]."px;' name='tabsearch[".$name."]' value='".((isset($tabsearch[$name])) ? $tabsearch[$name] : '')."'></th>";
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
				$col = abs($col-110);
				// $ret.="<tr onmouseover=\"setPointer(this, 'over', '#".$myColor[$col]."', '#".$myColor[$col+5]."', '#FF0000')\" onmouseout=\"setPointer(this, 'out', '#".$myColor[$col]."', '#".$myColor[$col+5]."', '#FF0000')\">";
				$ret.="<tr>";
				// $ret.="<td bgcolor=\"#".$myColor[$col]."\">&nbsp;</td>";
				$ret.="<td>&nbsp;</td>";
		
				foreach($tabTitre as $name=>$v)
				  {
					if ($val[$name]["val"]=="<line>")
					  {
						$ret.="<td style='border-right: ".$v["width"]."px solid black;'></td>";
					  }
					else
					  {
						$ret.="<td ".(((isset($val[$name]["align"])) && ($val[$name]["align"]!="")) ? " align='".$val[$name]["align"]."'" : "").">".(((!isset($val[$name]["aff"])) || ($val[$name]["aff"]=="")) ? $val[$name]["val"] : $val[$name]["aff"])."</td>";
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
		$nbp=10;
		
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
  { global $tmpl_prg,$corefolder;
  	if (isset($tmpl_prg))
  	{
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
{
	$d=time()-strtotime($dte);
	$mid=time()-strtotime(date("Y-m-d 23:59:59",time()-3600*24));

	$h=floor($d/3600);
	$m=floor(($d-$h*3600)/60);
	$s=$d-$h*3600-$m*60;

	if (($s<60) && ($m==0) && ($h==0))
	  {
			return "il y a ".$s." secondes";
	  }
	else if (($m<2) && ($h==0))
	  {
			return "il y a 1 minute";
	  }
	else if (($m<60) && ($h==0))
	  {
			return "il y a ".$m." minutes";
	  }
	else if (($h<2) && ($m==0))
	  {
			return "il y a  1 heure";
	  }
	else if (($h<2) && ($m<2))
	  {
			return "il y a  1 heure"." et 1 minute";
	  }
	else if ($h<2)
	  {
			return "il y a  1 heure"." et ".$m." minutes";
	  }
	else if (($d<$mid) && ($h<2))
	  {
			return "il y a  1 heure et ".$m." minutes";
	  }
	else if ($d<$mid)
	  {
			return "il y a ".$h." heures et ".$m." minutes";
	  }
	else if (($d<$mid+3600*34) && ($d>$mid))
	  {
			return "hier à ".sql2time($dte,"no");
	  }
	else
	  {
			return "le ".sql2date($dte,"jour")." à ".sql2time($dte,"no");
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
	$ret="";
	$conffile="../config/variables.inc.php";
	if (!file_exists($conffile))
		{ $ret.="Création du fichier";}

	$tab["styletime"]["valeur"]=time();
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
					if ($var=="valeur")
					{
						fwrite($fd,"\$MyOpt[\"".$nom."\"]=\"".$dd."\";\n");
					}
					else
					{
						fwrite($fd,"\$MyOpt[\"".$nom."\"][\"".$var."\"]=\"".$dd."\";\n");
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

function UpdateVariables($tab)
{
	$MyOpt=array();
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
	
	$tmpl_prg->assign("msg_infos", $txt);
	$tmpl_prg->assign("msg_class", $res);
	$tmpl_prg->parse("main.aff_infos");
}

function GenereStyle($name)
{
	global $MyOpt,$core_version,$myrev,$corefolder;
	
	if (!is_numeric($MyOpt["styletime"]))
	{
		$MyOpt["styletime"]=0;
	}

	$sfile="static/cache/style/".$name.".".$MyOpt["styletime"].".".$myrev.".".$core_version.".css";
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

<?php
// ---------------------------------------------------------------------------------------------
//   Crontab - Variables
//     ($Author: miniroot $)
// ---------------------------------------------------------------------------------------------
//   Variables  : 
// ---------------------------------------------------------------------------------------------
/*

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
?>

<?php

// ---- Vérifie le droit d'accès
	if (!GetDroit("AccesConfigCrontab")) { FatalError($tabLang["lang_accessdenied"]." (AccesConfigCrontab)"); }

	$id=checkVar("id","numeric");
	
// ---- Affiche le menu
	$aff_menu="";
	require_once("modules/".$mod."/menu.inc.php");
	$tmpl_x->assign("aff_menu",$aff_menu);


// ---- Execute les scripts
	if (($fonc=="start") && ($id>0))
	{
		$query="SELECT * FROM ".$MyOpt["tbl"]."_cron WHERE id='".$id."'";
		$res=$sql->QueryRow($query);
		
		if ($res["id"]>0)
		{
			$gl_mode="batch";
			$gl_myprint_txt="";
			$gl_id=$id;
			$gl_res="";
			$mod=$sql->data["module"];

			$f="modules/".$sql->data["module"]."/".$sql->data["script"].".cron.php";
			if (file_exists($appfolder."/".$f))
			{
				require($appfolder."/".$f);
			}
			else if (file_exists($f))
			{
				require($f);
			}
			else
			{
				$gl_res="NOK";
				$gl_myprint_txt="Script non trouvé : ".$sql->data["script"];
			}

			$q="UPDATE ".$MyOpt["tbl"]."_cron SET lastrun='".now()."', nextrun='".date("Y-m-d H:i:s",time()+$res["schedule"]*60)."', txtretour='".$gl_res."', txtlog='".addslashes($gl_myprint_txt)."' WHERE id='".$gl_id."'";
			$sql->Update($q);

			
			$tmpl_x->assign("aff_resultat",nl2br($gl_myprint_txt));
			$tmpl_x->parse("corps.resultat");
			$mod="admin";
		}
	}

// ---- Entete du tableau

	$tabTitre=array();
	$tabTitre["description"]["aff"]=$tabLang["lang_description"];
	$tabTitre["description"]["width"]=400;
	$tabTitre["schedule"]["aff"]=$tabLang["lang_schedule"];
	$tabTitre["schedule"]["width"]=100;
	$tabTitre["lastrun"]["aff"]=$tabLang["lang_lastexec"];
	$tabTitre["lastrun"]["width"]=200;
	$tabTitre["nextrun"]["aff"]=$tabLang["lang_nextexec"];
	$tabTitre["nextrun"]["width"]=200;
	$tabTitre["resultat"]["aff"]=$tabLang["lang_result"];
	$tabTitre["resultat"]["width"]=100;
	$tabTitre["actif"]["aff"]=$tabLang["lang_active"];
	$tabTitre["actif"]["width"]=100;
	$tabTitre["action"]["aff"]=$tabLang["lang_action"];
	$tabTitre["action"]["width"]=100;


// ---- Charge la liste des taches planifiées
	$query="SELECT * FROM ".$MyOpt["tbl"]."_cron";
	$sql->Query($query);

	$tabValeur=array();

	for($i=0; $i<$sql->rows; $i++)
	{
		$sql->GetRow($i);

		$tabValeur[$i]["description"]["val"]=$sql->data["description"];
		$tabValeur[$i]["schedule"]["val"]=$sql->data["schedule"];
		$tabValeur[$i]["schedule"]["aff"]="<div id='schedule_".$sql->data["id"]."' class='fieldAdmin'><a id='schedule_".$sql->data["id"]."_a' onClick='SwitchEdit(\"schedule\",".$sql->data["id"].")'>".AffTemps($sql->data["schedule"],"full")."</a></div>";
		$tabValeur[$i]["lastrun"]["val"]=sql2date($sql->data["lastrun"]);
		$tabValeur[$i]["nextrun"]["val"]=sql2date($sql->data["nextrun"]);
		$tabValeur[$i]["resultat"]["val"]=$sql->data["txtretour"];
		$tabValeur[$i]["actif"]["val"]=$sql->data["actif"];
		$tabValeur[$i]["actif"]["aff"]="<div id='actif_".$sql->data["id"]."' class='fieldAdmin'><a id='actif_".$sql->data["id"]."_val' onClick='SwitchOn(\"actif\",".$sql->data["id"].")'>".$sql->data["actif"]."</a></div>";
		$tabValeur[$i]["action"]["val"]="Démarrer";
		$tabValeur[$i]["action"]["aff"]="<div class='fieldAdmin'><a href='index.php?mod=admin&rub=crontab&id=".$sql->data["id"]."&fonc=start'>Démarrer</a></div>";
	}
	
	if ((!isset($order)) || ($order=="")) { $order="groupe"; }
	if ((!isset($trie)) || ($trie=="")) { $trie="d"; }

	$tmpl_x->assign("aff_tableau",AfficheTableau($tabValeur,$tabTitre,$order,$trie));

// ---- Affecte les variables d'affichage
	$tmpl_x->parse("icone");
	$icone=$tmpl_x->text("icone");
	$tmpl_x->parse("infos");
	$infos=$tmpl_x->text("infos");
	$tmpl_x->parse("corps");
	$corps=$tmpl_x->text("corps");

?>

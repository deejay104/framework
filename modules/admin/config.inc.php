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
?>

<?php

// ---- Charge les variables par défault
	require_once("modules/admin/conf/variables.tmpl.php");
	if (file_exists($appfolder."/modules/admin/conf/variables.tmpl.php"))
	{
		require_once($appfolder."/modules/admin/conf/variables.tmpl.php");
	}

// ---- Vérifie le droit d'accès
	if (!GetDroit("AccesConfigVar")) { FatalError("Accès non autorisé (AccesConfiguration)"); }

// ---- Affiche le menu
	$aff_menu="";
	require_once("modules/".$mod."/menu.inc.php");
	$tmpl_x->assign("aff_menu",$aff_menu);

// ---- Enregistre le fichier des variables
	if ($fonc==$tabLang["lang_save"])
	{
		$MyOptTab=checkVar("MyOptTab","array");
		
		$ret=GenereVariables($MyOptTab);
		// $tmpl_x->assign("msg_ret", $ret);
		// $tmpl_x->parse("corps.msgok");
		affInformation($ret,"ok");
		$MyOpt=UpdateVariables($MyOptTab);
		$MyOpt["tbl"]=$gl_tbl;
	}

// ---- Charge les variables
	$q="SELECT * FROM ".$MyOpt["tbl"]."_config WHERE param='variable'";
	$sql->Query($q);
	for($i=0; $i<$sql->rows; $i++)
	{
		$sql->GetRow($i);
		if ($sql->data["name2"]=="")
		{
			$MyOpt[$sql->data["name1"]]=$sql->data["value"];
		}
		else
		{
			$MyOpt[$sql->data["name1"]][$sql->data["name2"]]=$sql->data["value"];
		}
	}

// ---- Affiche la liste
	foreach ($MyOptTmpl as $nom=>$d)
	{
		$tmpl_x->assign("param_nom", $nom);

		if (is_array($d))
		{
			$tmpl_x->assign("param_class", "formulaireSep");
			foreach($d as $var=>$dd)
			{

				$tmpl_x->assign("param_var", $var);
				$tmpl_x->assign("param_var1", $nom);
				$tmpl_x->assign("param_var2", $var);
				$tmpl_x->assign("param_txt", (isset($MyOpt[$nom][$var])) ? $MyOpt[$nom][$var] : $dd);
				$tmpl_x->assign("param_default", ($dd=="") ? "<i>-vide-</i>" : $dd);
				$tmpl_x->assign("param_help", (isset($MyOptHelp[$nom][$var])) ? $MyOptHelp[$nom][$var] : "");

				$tmpl_x->parse("corps.lst_param");

				$tmpl_x->assign("param_class", "");
				$tmpl_x->assign("param_nom", "");
			}
		}
		else
		{
			$tmpl_x->assign("param_class", "formulaireSep");

			$tmpl_x->assign("param_var", "valeur");
			$tmpl_x->assign("param_var1", $nom);
			$tmpl_x->assign("param_var2", "valeur");
			$tmpl_x->assign("param_txt", (isset($MyOpt[$nom])) ? $MyOpt[$nom] : $d);
			$tmpl_x->assign("param_default", ($d=="") ? "<i>-vide-</i>" : $d);
				$tmpl_x->assign("param_help", $MyOptHelp[$nom]);

			$tmpl_x->parse("corps.lst_param");
		}

	}


// ---- Affecte les variables d'affichage
	$tmpl_x->parse("icone");
	$icone=$tmpl_x->text("icone");
	$tmpl_x->parse("infos");
	$infos=$tmpl_x->text("infos");
	$tmpl_x->parse("corps");
	$corps=$tmpl_x->text("corps");



?>

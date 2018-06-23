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
?>

<?
	require_once ("class/amelioration.inc.php");
	if (!GetDroit("AccesAmeliorations")) { FatalError("Accès non autorisé (AccesAmeliorations)"); }

// ---- Vérifie les variables
	$order=checkVar("order","varchar");
	$trie=checkVar("trie","varchar");
	
// ---- Charge le template
	$tmpl_x = new XTemplate (MyRep("index.htm"));
	$tmpl_x->assign("path_module",$corefolder."/".$module."/".$mod);

// ---- Affiche le menu
	$aff_menu="";
	require("modules/".$mod."/menu.inc.php");
	$tmpl_x->assign("aff_menu",$aff_menu);

// ---- Liste des ameliorations
	$lst=ListActiveAmeliorations($sql);

	$tabTitre=array();
	$tabTitre["id"]["aff"]="Num";
	$tabTitre["id"]["width"]=100;
	$tabTitre["titre"]["aff"]="Titre";
	$tabTitre["titre"]["width"]=300;
	
	$tabTitre["status"]["aff"]="Status";
	$tabTitre["status"]["width"]=150;
	
	if ($theme!="phone")
	{
		$tabTitre["version"]["aff"]="Version";
		$tabTitre["version"]["width"]=100;
		$tabTitre["module"]["aff"]="Module";
		$tabTitre["module"]["width"]=100;
		$tabTitre["creat"]["aff"]="Demandeur";
		$tabTitre["creat"]["width"]=180;
	}

	$tabValeur=array();
	foreach($lst as $i=>$d)
	{
		$pb = new amelioration_core($i,$sql);

		$tabValeur[$i]["id"]["val"]=$pb->id;
		$tabValeur[$i]["id"]["aff"]=$pb->aff("id");
		$tabValeur[$i]["titre"]["val"]=$pb->val("titre");
		$tabValeur[$i]["titre"]["aff"]=$pb->aff("titre");
		$tabValeur[$i]["status"]["val"]=$pb->val("status");
		$tabValeur[$i]["status"]["aff"]=$pb->aff("status");
		$tabValeur[$i]["version"]["val"]=$pb->val("version");
		$tabValeur[$i]["version"]["aff"]=$pb->aff("version");
		$tabValeur[$i]["module"]["val"]=$pb->val("module");
		$tabValeur[$i]["module"]["aff"]=$pb->aff("module");
		$tabValeur[$i]["creat"]["val"]=$pb->uid_creat;
		$tabValeur[$i]["creat"]["aff"]=$pb->aff("uid_creat");
	}

	if ((!isset($order)) || ($order=="")) { $order="status"; }
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
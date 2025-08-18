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
	require_once ("class/amelioration.inc.php");
	if (!GetDroit("AccesAmeliorations")) { FatalError("Accès non autorisé (AccesAmeliorations)"); }

// ---- Vérifie les variables
	$order=checkVar("order","varchar");
	$trie=checkVar("trie","varchar");
	$id=checkVar("id","numeric");
	
	
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
		$tabTitre["dte_creat"]["aff"]="Date";
		$tabTitre["dte_creat"]["width"]=100;
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
		// $pb = new amelioration_core($i,$sql);

		$tabValeur[$i]["id"]["val"]=$d["id"];
		$tabValeur[$i]["id"]["aff"]="<a href='".geturl("ameliorations","detail","id=".$d["id"])."'>#".CompleteTxt($d["id"],4,"0")."</a>";;
		$tabValeur[$i]["titre"]["val"]=$d["titre"];
		$tabValeur[$i]["titre"]["aff"]="<a href='".geturl("ameliorations","detail","id=".$d["id"])."'>".$d["titre"]."</a>";
		$tabValeur[$i]["status"]["val"]=$d["status"];
		$tabValeur[$i]["status"]["aff"]=$d["affstatus"];
		$tabValeur[$i]["dte_creat"]["val"]=$d["dte_creat"];
		$tabValeur[$i]["dte_creat"]["aff"]=sql2date($d["dte_creat"],"jour");
		$tabValeur[$i]["version"]["val"]=$d["version"];
		$tabValeur[$i]["version"]["aff"]=$d["version"];
		$tabValeur[$i]["module"]["val"]=$d["module"];
		$tabValeur[$i]["module"]["aff"]=$d["module"];
		$tabValeur[$i]["creat"]["val"]=$d["uid_creat"];

		$usr=new user_core($d["uid_creat"],$sql);
		$tabValeur[$i]["creat"]["aff"]=$usr->aff("fullname");
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
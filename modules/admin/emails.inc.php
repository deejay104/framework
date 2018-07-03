<?
/*
    Easy-Aero
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
// ---- Vérifie le droit d'accès
	if (!GetDroit("AccesConfigEmails")) { FatalError("Accès non autorisé (AccesConfigEmails)"); }

// ---- Charge le template
	$tmpl_x = new XTemplate (MyRep("emails.htm"));
	$tmpl_x->assign("path_module",$corefolder."/".$module."/".$mod);

// ---- Vérifie les variables
	$order=checkVar("order","varchar");
	$trie=checkVar("trie","varchar");

// ---- Affiche le menu
	$aff_menu="";
	require_once("modules/".$mod."/menu.inc.php");
	$tmpl_x->assign("aff_menu",$aff_menu);

// ---- Liste les emails
	$tabTitre=array(
		"nom"=>array(
			"aff"=>"Nom",
			"width"=>150
		),
		"titre"=>array(
			"aff"=>"Titre",
			"width"=>300
		),
		"action"=>array(
			"aff"=>"&nbsp;",
			"width"=>20
		)
	);

	$query="SELECT id,nom,titre FROM ".$MyOpt["tbl"]."_mailtmpl ORDER BY nom";
	$sql->Query($query);

	$tabValeur=array();

	for($i=0; $i<$sql->rows; $i++)
	{
		$sql->GetRow($i);
		$tabValeur[$i]["nom"]["val"]=$sql->data["nom"];
		$tabValeur[$i]["nom"]["aff"]=$sql->data["nom"];
		$tabValeur[$i]["titre"]["val"]=$sql->data["titre"];
		$tabValeur[$i]["titre"]["aff"]=$sql->data["titre"];
		$tabValeur[$i]["id"]["val"]=$sql->data["id"];
		$tabValeur[$i]["action"]["val"]=$sql->data["id"];
		$tabValeur[$i]["action"]["aff"]="<div id='action_".$sql->data["id"]."' style='display:none;'><a id='edit_".$sql->data["id"]."' class='imgDelete' ><img src='".$corefolder."/".$module."/".$mod."/img/icn16_editer.png'></a></div>";
		
		$tmpl_x->assign("lst_id",$sql->data["id"]);
		$tmpl_x->parse("corps.lst_edit");
	}

	if ($order=="") { $order="groupe"; }
	if ($trie=="") { $trie="d"; }

	$tmpl_x->assign("aff_tableau",AfficheTableau($tabValeur,$tabTitre,$order,$trie,"",0,"",0,"action"));
	                           // AfficheTableau($tabValeur,$tabTitre,$order,$trie,$url="",$start=0,$limit="",$nbline=0,$showicon="")
	
// ---- Affecte les variables d'affichage
	$tmpl_x->parse("icone");
	$icone=$tmpl_x->text("icone");
	$tmpl_x->parse("infos");
	$infos=$tmpl_x->text("infos");
	$tmpl_x->parse("corps");
	$corps=$tmpl_x->text("corps");
	
?>
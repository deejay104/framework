<?php
// ---------------------------------------------------------------------------------------------
//   Gestion des groupes
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
	if (!GetDroit("AccesConfigGroupes")) { FatalError($tabLang["lang_accessdenied"]." (AccesConfigGroupes)"); }

// ---- Vérifie les variables
	$order=checkVar("order","varchar");
	$trie=checkVar("trie","varchar");

// ---- Affiche le menu
	$aff_menu="";
	require_once("modules/".$mod."/menu.inc.php");
	$tmpl_x->assign("aff_menu",$aff_menu);

	addPageMenu("","admin",$tabLang["lang_new"],geturl("admin","groupes/gdetail",""),"");


// ---- Liste les groupes
	$tabTitre=array();
	$tabTitre["groupe"]["aff"]=$tabLang["lang_group"];
	$tabTitre["groupe"]["width"]=250;
	$tabTitre["description"]["aff"]=$tabLang["lang_description"];
	$tabTitre["description"]["width"]=400;

	$query="SELECT groupe,description FROM ".$MyOpt["tbl"]."_groupe ORDER BY groupe";
	$sql->Query($query);

	$tabValeur=array();

	for($i=0; $i<$sql->rows; $i++)
		{
			$sql->GetRow($i);
			$tabValeur[$i]["groupe"]["val"]=$sql->data["groupe"];
			$tabValeur[$i]["groupe"]["aff"]="<a href='/admin/groupes/gdetail?grp=".$sql->data["groupe"]."'>".$sql->data["groupe"]."</a>";
			$tabValeur[$i]["description"]["val"]=$sql->data["description"];
			$tabValeur[$i]["description"]["aff"]="<a href='/admin/groupes/gdetail?grp=".$sql->data["groupe"]."'>".$sql->data["description"]."</a>";
	  }

	if ($order=="") { $order="groupe"; }
	if ($trie=="") { $trie="d"; }

	$tmpl_x->assign("aff_tableau",AfficheTableau($tabValeur,$tabTitre,$order,$trie));

// ---- Affecte les variables d'affichage
	$tmpl_x->parse("icone");
	$icone=$tmpl_x->text("icone");
	$tmpl_x->parse("infos");
	$infos=$tmpl_x->text("infos");
	$tmpl_x->parse("corps");
	$corps=$tmpl_x->text("corps");

?>

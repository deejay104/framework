<?php
// ---- Refuse l'accès en direct
        if ((!isset($token)) || ($token==""))
          { header("HTTP/1.0 401 Unauthorized"); exit; }

// ----
        $ret=array();
        $ret["result"]="OK";
        $ret["data"]="";

        $sql->show=false;



function AjoutLog($txt)
{
        return htmlentities($txt,ENT_HTML5,"UTF-8")."<br />";
}


// ---- Charge la structure des tables de la version_compare
        $MyOpt["tbl"]=$gl_tbl;

        $ret["data"].=AjoutLog($tabLang["lang_checkdb"]);
        require ("modules/admin/conf/structure.tmpl.php");

        if (file_exists("../modules/admin/conf/structure.tmpl.php"))
        {
                require("../modules/admin/conf/structure.tmpl.php");

                foreach($tabCustom as $tab=>$fields)
                {
                        foreach($fields as $field=>$desc)
                        {
                                $tabTmpl[$tab][$field]=$desc;
                        }
                }
        }

// ---- Vérifie le character set de la base
        $q="SELECT @@character_set_database AS charset, @@collation_database AS col";
        $res=$sql->QueryRow($q);
        if ($res["charset"]!="utf8mb4")
        {
                $ret["data"].=AjoutLog("Convert characterset from ".$res["charset"]." to UTF-8 for ".$db);
                $q="ALTER DATABASE ".$db." CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
                $res=$sql->Update($q);
        }

// ---- Charge la structure des tables en base
        $tabProd=array();
        $q="SHOW TABLES;";
        $sql->Query($q);
        for($i=0; $i<$sql->rows; $i++)
        {
                $sql->GetRow($i);
                $tabProd[$sql->data["Tables_in_".$db]]=array();
        }

        foreach($tabProd as $tab=>$t)
        {
                // Get Characterset
                $q ="SELECT CCSA.character_set_name AS charset FROM information_schema.`TABLES` T, information_schema.`COLLATION_CHARACTER_SET_APPLICABILITY` CCSA ";
                $q.="WHERE CCSA.collation_name = T.table_collation ";
                $q.="AND T.table_schema = '".$db."' ";
                $q.="AND T.table_name = '".$tab."'; ";
                $res=$sql->QueryRow($q);
                if ($res["charset"]!="utf8mb4")
                {
                        $ret["data"].=AjoutLog("Convert characterset from ".$res["charset"]." to UTF-8 for ".$db.".".$tab);
                        $q="ALTER TABLE ".$tab." CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
                        $res=$sql->Update($q);
                }

                // Get Structure
                $q="DESCRIBE ".$tab.";";
                $sql->Query($q);
                for($i=0; $i<$sql->rows; $i++)
                {
                        $sql->GetRow($i);
                        $tabProd[$tab][$sql->data["Field"]]["Type"]=$sql->data["Type"];
                        $tabProd[$tab][$sql->data["Field"]]["Null"]=$sql->data["Null"];
                        $tabProd[$tab][$sql->data["Field"]]["Extra"]=$sql->data["Extra"];
                        $tabProd[$tab][$sql->data["Field"]]["Default"] = $sql->data["Default"];
                }

                // Get Index
                $q="SHOW INDEX FROM ".$tab.";";
                $sql->Query($q);
                for($i=0; $i<$sql->rows; $i++)
                {
                        $sql->GetRow($i);
                        $tabProd[$tab][$sql->data["Column_name"]]["Index"]=($sql->data["Key_name"]=="PRIMARY") ? "PRIMARY" : 1;
                }

        }

// ---- Exporte la structure existante
/*
        //[Non_unique] => 0
        echo "Array\n(\n";
        foreach($tabProd as $tab=>$t)
        {
                echo "\t\"".$tab."\" => Array (\n";

                foreach($t as $f=>$d)
                {
                        echo "\t\t\"".$f."\" => Array(";
                        foreach($d as $ff=>$dd)
                        {
                                echo "\"".$ff."\" => \"".$dd."\", ";
                        }
                        echo "),\n";
                }
                echo "\t),\n";
        }
        echo ");";
*/


// ---- Compare les structures
    // [ae_abo_ligne] => Array
        // (
            // [id] => Array
                // (
                    // [Type] => int(10) unsigned
                    // [Index] => PRIMARY
                // )

            // [abonum] => Array
                // (
                    // [Type] => varchar(8)
                    // [Index] => 1
                // )
        // )

        foreach($tabTmpl as $tab=>$fields)
        {
                // Tester si la table n'existe pas
                if (!isset($tabProd[$MyOpt["tbl"]."_".$tab]))
                {
                        $q="CREATE TABLE `".$MyOpt["tbl"]."_".$tab."` (`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
                        $res=$sql->Update($q);
                        if ($res==-1)
                        {
                                $ret["result"]="NOK";
                                $ret["data"].=AjoutLog(" ! ".$tabLang["lang_errorcreate"]." ".$MyOpt["tbl"]."_".$tab);
                        }
                        else
                        {
                                $ret["data"].=AjoutLog(" - ".$tabLang["lang_createtable"]." ".$MyOpt["tbl"]."_".$tab);
                                $tabProd[$MyOpt["tbl"]."_".$tab]["id"]["Type"]=(isset($fields["id"]["Type"])) ? $fields["id"]["Type"] : "int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
                                $tabProd[$MyOpt["tbl"]."_".$tab]["id"]["Index"]="PRIMARY";
                        }
                }
                // Vérifie si le champs obligatoire existent
                // if (!isset($tabTmpl[$tab]["uid_creat"]))
                // {
                        // $tabTmpl[$tab]["uid_creat"]["Type"]="int(10) UNSIGNED";
                // }
                // if (!isset($tabTmpl[$tab]["dte_creat"]))
                // {
                        // $tabTmpl[$tab]["uid_creat"]["Type"]="datetime";
                // }
                // if (!isset($tabTmpl[$tab]["uid_maj"]))
                // {
                        // $tabTmpl[$tab]["uid_maj"]["Type"]="int(10) UNSIGNED";
                // }
                // if (!isset($tabTmpl[$tab]["dte_maj"]))
                // {
                        // $tabTmpl[$tab]["dte_maj"]["Type"]="datetime";
                // }

                // Si la table existe ou qu'elle a pu être créée
                if (isset($tabProd[$MyOpt["tbl"]."_".$tab]))
                {
                        foreach($tabTmpl[$tab] as $field=>$d)
                        {
// echo $tab.":".$field."=".$tabTmpl[$tab][$field]["Type"]." ".$tabProd[$MyOpt["tbl"]."_".$tab][$field]["Type"]."<br>\n";
                                if (!isset($tabTmpl[$tab][$field]["Index"]))
                                {
                                        $tabTmpl[$tab][$field]["Index"]=0;
                                }

                                // Le champ n'existe pas
                                if (!isset($tabProd[$MyOpt["tbl"]."_".$tab][$field]))
                                {
                                        $q="ALTER TABLE `".$MyOpt["tbl"]."_".$tab."` ADD `".$field."` ".$tabTmpl[$tab][$field]["Type"]." DEFAULT ".(isset($tabTmpl[$tab][$field]["Default"]) ? " '".$tabTmpl[$tab][$field]["Default"]."' NOT NULL" : "NULL");
                                        $res=$sql->Update($q);
                                        if ($res==-1)
                                        {
                                                $ret["result"]="NOK";
                                                $ret["data"].=AjoutLog(" ! ".$tabLang["lang_errorcreate"]." ".$MyOpt["tbl"]."_".$tab.":".$field);
                                        }
                                        else
                                        {
                                                $ret["data"].=AjoutLog(" - ".$tabLang["lang_create"]." ".$MyOpt["tbl"]."_".$tab.":".$field." -> ".$tabTmpl[$tab][$field]["Type"]);
                                        }
                                }
                                // Le champ n'a pas le bon type
                                else if ( ($tabTmpl[$tab][$field]["Type"]!=$tabProd[$MyOpt["tbl"]."_".$tab][$field]["Type"]) && ($tabTmpl[$tab][$field]["Index"]!="PRIMARY") )
                                {
                                        $q="ALTER TABLE `".$MyOpt["tbl"]."_".$tab."` MODIFY `".$field."` ".$tabTmpl[$tab][$field]["Type"]." DEFAULT ".(isset($tabTmpl[$tab][$field]["Default"]) ? " '".$tabTmpl[$tab][$field]["Default"]."' NOT NULL" : "NULL");
                                        $res=$sql->Update($q);
                                        if ($res==-1)
                                        {
                                                $ret["result"]="NOK";
                                                $ret["data"].=AjoutLog(" ! ".$tabLang["lang_errormodify"]." ".$MyOpt["tbl"]."_".$tab.":".$field." (".$q.")");
                                        }
                                        else
                                        {
                                                $ret["data"].=AjoutLog(" - ".$tabLang["lang_modify"]." ".$MyOpt["tbl"]."_".$tab.":".$field." -> ".$tabTmpl[$tab][$field]["Type"]);
                                        }
                                }
                                // Le champ n'a pas de valeur par défaut et n'est pas à NULL
                                else if ( ($field!="id") && ($tabProd[$MyOpt["tbl"]."_".$tab][$field]["Null"]=="NO") && (!isset($tabTmpl[$tab][$field]["Default"])) )
                                {
                                        $q="ALTER TABLE `".$MyOpt["tbl"]."_".$tab."` MODIFY `".$field."` ".$tabTmpl[$tab][$field]["Type"]." NULL";
                                        $res=$sql->Update($q);
                                        if ($res==-1)
                                        {
                                                $ret["result"]="NOK";
                                                $ret["data"].=AjoutLog(" ! ".$tabLang["lang_errormodify"]." ".$MyOpt["tbl"]."_".$tab.":".$field." -> NULL");
                                        }
                                        else
                                        {
                                                $ret["data"].=AjoutLog(" - ".$tabLang["lang_changetonull"]." ".$MyOpt["tbl"]."_".$tab.":".$field." -> NULL");
                                        }
                                }
                                // Le champs est à NULL et à une valeur par défaut
                                else if ( ($field!="id") && ($tabProd[$MyOpt["tbl"]."_".$tab][$field]["Null"]=="YES") && (isset($tabTmpl[$tab][$field]["Default"])) )
                                {
                                        $q="UPDATE `".$MyOpt["tbl"]."_".$tab."` SET `".$field."`='".$tabTmpl[$tab][$field]["Default"]."' WHERE ".$field." IS NULL";
                                        $res=$sql->Update($q);

                                        $q="ALTER TABLE `".$MyOpt["tbl"]."_".$tab."` MODIFY `".$field."` ".$tabTmpl[$tab][$field]["Type"]." NOT NULL";
                                        $res=$sql->Update($q);
                                        if ($res==-1)
                                        {
                                                $ret["result"]="NOK";
                                                $ret["data"].=AjoutLog(" ! ".$tabLang["lang_errormodify"]." ".$MyOpt["tbl"]."_".$tab.":".$field." -> NOT NULL (".$q.")");
                                        }
                                        else
                                        {
                                                $ret["data"].=AjoutLog(" - ".$tabLang["lang_changetonull"]." ".$MyOpt["tbl"]."_".$tab.":".$field." -> NOT NULL");
                                        }
                                }
                                //
                                else if ( (isset($tabTmpl[$tab][$field]["Default"])) && (isset($tabProd[$MyOpt["tbl"]."_".$tab][$field]["Default"])) && ($tabTmpl[$tab][$field]["Default"]!=$tabProd[$MyOpt["tbl"]."_".$tab][$field]["Default"]) )
                                {
// ALTER TABLE `core_roles` CHANGE `dte_maj` `dte_maj` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
                                        $q="ALTER TABLE `".$MyOpt["tbl"]."_".$tab."` CHANGE `".$field."` `".$field."` ".$tabTmpl[$tab][$field]["Type"]." DEFAULT ".(isset($tabTmpl[$tab][$field]["Default"]) ? " '".$tabTmpl[$tab][$field]["Default"]."' NOT NULL" : "NULL");
                                        $res=$sql->Update($q);
                                        if ($res==-1)
                                        {
                                                $ret["result"]="NOK";
                                                $ret["data"].=AjoutLog(" ! ".$tabLang["lang_errormodify"]." ".$MyOpt["tbl"]."_".$tab.":".$field." (".$q.")");
                                        }
                                        else
                                        {
                                                $ret["data"].=AjoutLog(" - ".$tabLang["lang_modify"]." ".$MyOpt["tbl"]."_".$tab.":".$field." -> ".$tabTmpl[$tab][$field]["Type"]." '".$tabTmpl[$tab][$field]["Default"]."'!='".(isset($tabProd[$MyOpt["tbl"]."_".$tab][$field]["Default"]) ? $tabProd[$MyOpt["tbl"]."_".$tab][$field]["Default"] : "")."'");
                                        }
                                }
                                else if ( ($field=="id") && ($tabProd[$MyOpt["tbl"]."_".$tab][$field]["Extra"]!="auto_increment") )
                                {
                                        // ALTER TABLE `core_tarifs` CHANGE `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
                                        $q="ALTER TABLE `".$MyOpt["tbl"]."_".$tab."` CHANGE `".$field."` `".$field."` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
                                        $res=$sql->Update($q);
                                        if ($res==-1)
                                        {
                                                $ret["result"]="NOK";
                                                $ret["data"].=AjoutLog(" ! ".$tabLang["lang_errormodify"]." ".$MyOpt["tbl"]."_".$tab.":".$field." AUTO INCR (".$q.")");
                                        }
                                        else
                                        {
                                                $ret["data"].=AjoutLog(" - ".$tabLang["lang_addautoinc"]." ".$MyOpt["tbl"]."_".$tab.":".$field." AUTO INCR");
                                        }
                                }

                                // Index
                                if (($tabTmpl[$tab][$field]["Index"]!="0") && (!isset($tabProd[$MyOpt["tbl"]."_".$tab][$field]["Index"])))
                                {
                                        $q="ALTER TABLE `".$MyOpt["tbl"]."_".$tab."` ADD INDEX (`".$field."`)";
                                        $res=$sql->Update($q);
                                        if ($res==-1)
                                        {
                                                $ret["result"]="NOK";
                                                $ret["data"].=AjoutLog(" ! ".$tabLang["lang_errorcreateindex"]." ".$MyOpt["tbl"]."_".$tab.":".$field);
                                        }
                                        else
                                        {
                                                $ret["data"].=AjoutLog(" - ".$tabLang["lang_createindex"]." ".$MyOpt["tbl"]."_".$tab.":".$field);
                                        }
                                }
                        }
                }
        }

// ---- Vérification des variables
        require ("modules/$mod/conf/variables.tmpl.php");
        if (file_exists("../modules/admin/conf/variables.tmpl.php"))
        {
                require("../modules/admin/conf/variables.tmpl.php");
        }

        $ret["data"].=AjoutLog($tabLang["lang_checkvar"]);

        $nb=0;
        $MyOptTab=array();

        foreach ($MyOptTmpl as $nom=>$d)
        {
                if (is_array($d))
                {
                        foreach($d as $var=>$dd)
                        {
                                if(!isset($MyOpt[$nom][$var]))
                                {
                                        $MyOptTab[$nom][$var]=$dd;
                                        $nb=$nb+1;
                                        // echo "Ajout : \$MyOpt[\"".$nom."\"][\"".$var."\"]='".$dd."'<br>";
                                        $ret["data"].=AjoutLog(" - Ajout : ".$nom.":".$var."='".$dd."'");
                                }
                                else
                                {
                                        $MyOptTab[$nom][$var]=$MyOpt[$nom][$var];
                                }
                        }
                }
                else
                {
                        if(!isset($MyOpt[$nom]))
                        {
                                $MyOptTab[$nom]["valeur"]=$d;
                                $nb=$nb+1;
                                $ret["data"].=AjoutLog(" - Ajout : ".$nom."='".preg_replace("/\//","-",$d)."'");
                        }
                        else
                        {
                                $MyOptTab[$nom]["valeur"]=$MyOpt[$nom];
                        }
                }
        }

        if ($nb>0)
        {
                // echo $nb." variables ajoutées<br>";
                $ret["data"].=AjoutLog($nb." variables ajoutées");

                $res=GenereVariables($MyOptTab);
                $ret["data"].=AjoutLog($res);
                $MyOpt=UpdateVariables($MyOptTab);
        }

        if (!file_exists("../static/cache/config/variables.inc.php"))
        {
                error_log("easy-aero variable file does not exist");
                $ret["result"]="NOK";
                $ret["data"]=AjoutLog("La création du fichier variables a échouée");
                echo json_encode($ret);
                exit;
        }


// ---- Applique les patchs
        $MyOpt["tbl"]=$gl_tbl;
        $ret["data"].=AjoutLog($tabLang["lang_patchcore"]);

        $tabPatch=array();
        $q="SELECT * FROM ".$MyOpt["tbl"]."_config WHERE param='core'";
        $sql->Query($q);
        for($i=0; $i<$sql->rows; $i++)
        {
                $sql->GetRow($i);
                $tabPatch[$sql->data["value"]]=$sql->data["dte_creat"];
        }

        $dir = "modules/admin/patch";
        $tdir = array_diff(scandir($dir), array('..', '.'));

        foreach ($tdir as $ii=>$d)
        {
                preg_match("/v([0-9]*)\.inc\.php/",$d,$p);
                $num=$p[1];

                if (!isset($tabPatch[$num]))
                {
                        $ok=0;
                        require($dir."/".$d);
                        if ($ok==0)
                        {
                                $ret["data"].=AjoutLog(" - Patch ".$p[1]);
                                $q="INSERT INTO ".$MyOpt["tbl"]."_config SET param='core',value='".$num."',dte_creat='".now()."'";
                                $sql->Insert($q);
                        }
                        else
                        {
                                $ret["data"].=AjoutLog(" ! ".$tabLang["lang_errorpatch"]." ".$p[1]);
                        }
                }
        }

// ---- Applique les patchs custom
        $ret["data"].=AjoutLog($tabLang["lang_patch"]);

        $tabPatch=array();
        $q="SELECT * FROM ".$MyOpt["tbl"]."_config WHERE param='patch'";
        $sql->Query($q);
        for($i=0; $i<$sql->rows; $i++)
        {
                $sql->GetRow($i);
                $tabPatch[$sql->data["value"]]=$sql->data["dte_creat"];
        }

        $dir = $appfolder."/modules/admin/patch";
        $tdir = array_diff(scandir($dir), array('..', '.'));

        foreach ($tdir as $ii=>$d)
        {
                preg_match("/v([0-9]*)\.inc\.php/",$d,$p);
                $num=$p[1];

                if (!isset($tabPatch[$num]))
                {
                        $ok=0;
                        require($dir."/".$d);
                        if ($ok==0)
                        {
                                $ret["data"].=AjoutLog(" - Patch ".$p[1]);
                                $q="INSERT INTO ".$MyOpt["tbl"]."_config SET param='patch',value='".$num."',dte_creat='".now()."'";
                                $sql->Insert($q);
                        }
                        else
                        {
                                $ret["data"].=AjoutLog(" ! ".$tabLang["lang_errorpatch"]." ".$p[1]);
                        }
                }
        }


// ---- Mise à jour de la base
        require("version.php");
        require($appfolder."/version.php");
        $query="SELECT id FROM ".$MyOpt["tbl"]."_config WHERE param='version'";
        $res=$sql->QueryRow($query);
        $id=$res["id"];

        if ($id>0)
        {
                $query="UPDATE ".$MyOpt["tbl"]."_config SET value='".$myrev."-".$core_version."',dte_creat='".now()."' WHERE id='".$id."'";
                $sql->Update($query);
        }
        else
        {
                $query="INSERT INTO ".$MyOpt["tbl"]."_config SET param='version',value='".$myrev."-".$core_version."',dte_creat='".now()."'";
                $sql->Insert($query);
        }

// ---- Renvoie le log
        echo json_encode($ret);
?>
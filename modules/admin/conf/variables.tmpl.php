<?php
// ---------------------------------------------------------------------------------------------
//   Variables
// ---------------------------------------------------------------------------------------------

$MyOptTmpl=array();
$MyOptHelp=array();

$MyOptHelp[""]="";

// Prefixe des tables
// $MyOptTmpl["tbl"]="core";
// $MyOptHelp["tbl"]="Prefixe des tables dans la base de données";

// Timestamp pour le cache de la feuille de style
// $MyOptHelp["styletime"]="Timestamp pour le cache de la feuille de style. Cette valeur sera re-écrite lors de l'enregistrement";
// $MyOptTmpl["styletime"]=time();

// Timestamp pour la version du fichier de variable
$MyOptHelp["version"]="Timestamp pour la version du fichier de variables. Cette valeur sera re-écrite lors de l'enregistrement";
$MyOptTmpl["version"]=time();

// Site en maintenance
$MyOptTmpl["maintenance"]="off";
$MyOptHelp["maintenance"]="Mettre le site en maintenance (on=site en maintenance, off=site accessible)";

// Debug
$MyOptTmpl["debug"]="off";
$MyOptHelp["debug"]="Affiche des informations de debuggage, notament les requetes MySQL";

// Debug
$MyOptTmpl["debugtime"]="off";
$MyOptHelp["debugtime"]="Affiche des informations détaillées des temps d'exécution";

// path
$MyOptTmpl["mydir"]=htmlentities(preg_replace("/[a-z]*\.php/","",$_SERVER["SCRIPT_FILENAME"]));
$MyOptHelp["mydir"]="Chemin de l'installation. Utilisé pour l'exécution des scripts";

// Language
$MyOptTmpl["DefaultLanguage"]="fr";
$MyOptHelp["DefaultLanguage"]="Langue par défault du site";

// Timezone
$MyOptTmpl["timezone"]=date_default_timezone_get();
$MyOptHelp["timezone"]="Sélectionner la timezone locale (Europe/Paris)";


// URL
$MyOptTmpl["host"]=htmlentities($_SERVER["REQUEST_SCHEME"]."://".$_SERVER["SERVER_NAME"].preg_replace("/\/[a-z]*\.php/","",$_SERVER["SCRIPT_NAME"]));
$MyOptHelp["host"]="Chemin complet du site. Utilisé pour générer les url statiques.";

// Short url
$MyOptTmpl["shorturl"]="off";
$MyOptHelp["shorturl"]="Activer les url courtes (on=Activé)";

// Titre du site
$MyOptTmpl["site_title"]="MnMs";
$MyOptHelp["site_title"]="Titre du site web";

// Temps de validité des tokens
$MyOptTmpl["tokenexpire"]="0";
$MyOptHelp["tokenexpire"]="Nombre de jours de validité des sessions par token (0=désactivé)";


// email par défault d'envoie des mails
$MyOptTmpl["from_email"]="noreply@les-mnms.net";
$MyOptHelp["from_email"]="Email par défault d'envoie des mails";

// Logo du site dans le dossier images
$MyOptTmpl["site_logo"]="logo.png";
$MyOptHelp["site_logo"]="Nom du fichier pour le logo. Il doit se trouver dans le dossier custom.";

// Active l'envoi de mail (0=ok, 1=nok)
$MyOptTmpl["sendmail"]="off";
$MyOptHelp["sendmail"]="Active l'envoi de mail (on=Activé)";

$MyOptTmpl["mail"]["smtp"]="on";
$MyOptHelp["mail"]["smtp"]="Envoie des mails par SMTP (on=SMTP sinon sendmail)";

$MyOptTmpl["mail"]["host"]="localhost";
$MyOptHelp["mail"]["host"]="FQDN du serveur SMTP";

$MyOptTmpl["mail"]["port"]="25";
$MyOptHelp["mail"]["port"]="SMTP port";

$MyOptTmpl["mail"]["username"]="";
$MyOptHelp["mail"]["username"]="SMTP username";

$MyOptTmpl["mail"]["password"]="";
$MyOptHelp["mail"]["password"]="SMTP user password";

// Uid Système
$MyOptTmpl["uid_system"]=2;
$MyOptHelp["uid_system"]="ID du compte système";

// Trie par Nom ou par Prénom
$MyOptTmpl["globalTrie"]="prenom";
$MyOptHelp["globalTrie"]="Ordre de trie par défault des listes (prenom, nom,...). Mettre le nom du champs pour le trie";

// Active la visualisation des membres supprimés
$MyOptTmpl["showDesactive"]="";
$MyOptHelp["showDesactive"]="on : Affiche les membres supprimés";

$MyOptTmpl["showSupprime"]="";
$MyOptHelp["showSupprime"]="on : Affiche les membres supprimés";

// Documents
$MyOptTmpl["expireCache"]="0";
$MyOptHelp["expireCache"]="Si supérieur à 0, nombre d'heure durant lesquelles on garde les fichiers en cache. Si 0, on garde indéfiniment.";

// API améliorations
$MyOptTmpl["amelioration"]["url"]="https://admin.les-mnms.net";
$MyOptHelp["amelioration"]["url"]="URL de l'API pour la centralisation des améliorations";
$MyOptTmpl["amelioration"]["login"]="";
$MyOptHelp["amelioration"]["login"]="login de l'API pour la centralisation des améliorations";
$MyOptTmpl["amelioration"]["pwd"]="";
$MyOptHelp["amelioration"]["pwd"]="Mot de passe de l'API pour la centralisation des améliorations";


// Modules
$MyOptTmpl["module"]["actualites"]="on";
$MyOptHelp["module"]["actualites"]="Active le module d'actualités (on=Activé)";
$MyOptTmpl["module"]["documents"]="on";
$MyOptHelp["module"]["documents"]="Active le module de gestion des documents (on=Activé)";
$MyOptTmpl["module"]["echeances"]="on";
$MyOptHelp["module"]["echeances"]="Active le module des échéances (on=Activé)";
$MyOptTmpl["module"]["ameliorations"]="on";
$MyOptHelp["module"]["ameliorations"]="Active le module de gestion des améliorations (on=Activé)";


// Unité
$MyOptTmpl["devise"]="€";
$MyOptHelp["devise"]="Devise";

// Couleurs pour les feuilles de style
$MyOptTmpl["styleColor"]["MenuBackgroundNormal"]="888e91";
$MyOptTmpl["styleColor"]["MenuBackgroundHover"]="585e61";
$MyOptTmpl["styleColor"]["TitleBackgroundNormal"]="38a9e3";
$MyOptTmpl["styleColor"]["TitleBackgroundHover"]="1799c3";
$MyOptTmpl["styleColor"]["TitleBackgroundButton"]="38a9e3";
$MyOptTmpl["styleColor"]["FormulaireBackgroundNormal"]="e8e8e8";
$MyOptTmpl["styleColor"]["FormulaireBackgroundLight"]="f5f5f5";
$MyOptTmpl["styleColor"]["FormulaireBackgroundDark"]="888e91";
$MyOptTmpl["styleColor"]["msgboxBackgroundOk"]="bbffaa";
$MyOptTmpl["styleColor"]["msgboxBackgroundWarning"]="ffe49c";
$MyOptTmpl["styleColor"]["msgboxBackgroundError"]="ffbbaa";
$MyOptTmpl["styleColor"]["TextBackgroundHover"]="ff6600";
$MyOptTmpl["styleColor"]["LineBackgroundHover"]="ffe1e1";
$MyOptTmpl["styleColor"]["BorderBlack"]="013366";

?>

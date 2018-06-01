<?
// ---------------------------------------------------------------------------------------------
//   Variables
// ---------------------------------------------------------------------------------------------

$MyOptTmpl=array();
$MyOptHelp=array();

$MyOptHelp[""]="";

// Prefixe des tables
$MyOptTmpl["tbl"]="core";
$MyOptHelp["tbl"]="Prefixe des tables dans la base de donn�es";

// Site en maintenance
$MyOptTmpl["maintenance"]="off";
$MyOptHelp["maintenance"]="Mettre le site en maintenance (on=site en maintenance, off=site accessible)";

// path
$MyOptTmpl["mydir"]=htmlentities(preg_replace("/[a-z]*\.php/","",$_SERVER["SCRIPT_FILENAME"]));
$MyOptHelp["mydir"]="Chemin de l'installation. Utilis� pour l'ex�cution des scripts";

// Timezone
$MyOptTmpl["timezone"]=date_default_timezone_get();
$MyOptHelp["timezone"]="S�lectionner la timezone locale (Europe/Paris)";


// URL
$MyOptTmpl["host"]=htmlentities($_SERVER["REQUEST_SCHEME"]."://".$_SERVER["SERVER_NAME"].preg_replace("/\/[a-z]*\.php/","",$_SERVER["SCRIPT_NAME"]));
$MyOptHelp["host"]="Chemin complet du site. Utilis� pour g�n�rer les url statiques.";

// Titre du site
$MyOptTmpl["site_title"]="MnMs";
$MyOptHelp["site_title"]="Titre du site web";

// email par d�fault d'envoie des mails
$MyOptTmpl["from_email"]="noreply@les-mnms.net";
$MyOptHelp["from_email"]="Email par d�fault d'envoie des mails";

// Logo du site dans le dossier images
$MyOptTmpl["site_logo"]="logo.png";
$MyOptHelp["site_logo"]="Nom du fichier pour le logo. Il doit se trouver dans le dossier custom.";

// Active l'envoi de mail (0=ok, 1=nok)
$MyOptTmpl["sendmail"]="off";
$MyOptHelp["sendmail"]="Active l'envoi de mail (on=Activ�)";

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

// Uid Syst�me
$MyOptTmpl["uid_system"]=2;
$MyOptHelp["uid_system"]="ID du compte syst�me";

// Trie par Nom ou par Pr�nom
$MyOptTmpl["globalTrie"]="prenom";
$MyOptHelp["globalTrie"]="Ordre de trie par d�fault des listes (prenom, nom,...). Mettre le nom du champs pour le trie";

// Active la visualisation des membres supprim�s
$MyOptTmpl["showDesactive"]="";
$MyOptHelp["showDesactive"]="on : Affiche les membres supprim�s";

$MyOptTmpl["showSupprime"]="";
$MyOptHelp["showSupprime"]="on : Affiche les membres supprim�s";

// Documents
$MyOptTmpl["expireCache"]="0";
$MyOptHelp["expireCache"]="Si sup�rieur � 0, nombre d'heure durant lesquelles on garde les fichiers en cache. Si 0, on garde ind�finiment.";

// API am�liorations
$MyOptTmpl["amelioration"]["url"]="https://admin.les-mnms.net";
$MyOptHelp["amelioration"]["url"]="URL de l'API pour la centralisation des am�liorations";
$MyOptTmpl["amelioration"]["login"]="";
$MyOptHelp["amelioration"]["login"]="login de l'API pour la centralisation des am�liorations";
$MyOptTmpl["amelioration"]["pwd"]="";
$MyOptHelp["amelioration"]["pwd"]="Mot de passe de l'API pour la centralisation des am�liorations";


// Modules
$MyOptTmpl["module"]["actualites"]="on";
$MyOptHelp["module"]["actualites"]="Active le module d'actualit�s (on=Activ�)";

// Unit�
$MyOptTmpl["devise"]="�";
$MyOptHelp["devise"]="Devise";


// Timestamp pour le cache de la feuille de style
$MyOptHelp["styletime"]="Timestamp pour le cache de la feuille de style. Cette valeur sera re-�crite lors de l'enregistrement";
$MyOptTmpl["styletime"]=time();

// Couleurs pour les feuilles de style
$MyOptTmpl["styleColor"]["MenuBackgroundNormal"]="888e91";
$MyOptTmpl["styleColor"]["MenuBackgroundHover"]="585e61";
$MyOptTmpl["styleColor"]["TitleBackgroundNormal"]="38a9e3";
$MyOptTmpl["styleColor"]["TitleBackgroundHover"]="1799c3";
$MyOptTmpl["styleColor"]["FormulaireBackgroundNormal"]="e8e8e8";
$MyOptTmpl["styleColor"]["FormulaireBackgroundLight"]="f5f5f5";
$MyOptTmpl["styleColor"]["msgboxBackgroundOk"]="bbffaa";
$MyOptTmpl["styleColor"]["msgboxBackgroundWarning"]="ffe49c";
$MyOptTmpl["styleColor"]["msgboxBackgroundError"]="ffbbaa";
$MyOptTmpl["styleColor"]["TextBackgroundHover"]="ff6600";
$MyOptTmpl["styleColor"]["LineBackgroundHover"]="ffe1e1";
$MyOptTmpl["styleColor"]["BorderBlack"]="013366";

?>

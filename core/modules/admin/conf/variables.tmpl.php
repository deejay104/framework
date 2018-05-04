<?
// ---------------------------------------------------------------------------------------------
//   Variables
// ---------------------------------------------------------------------------------------------

$MyOptTmpl=array();
$MyOptHelp=array();

$MyOptHelp[""]="";

// Prefixe des tables
$MyOptTmpl["tbl"]="log";
$MyOptHelp["tbl"]="Prefixe des tables dans la base de données";

// Site en maintenance
$MyOptTmpl["maintenance"]="off";
$MyOptHelp["maintenance"]="Mettre le site en maintenance (on=site en maintenance, off=site accessible)";

// path
$MyOptTmpl["mydir"]=htmlentities(preg_replace("/[a-z]*\.php/","",$_SERVER["SCRIPT_FILENAME"]));
$MyOptHelp["mydir"]="Chemin de l'installation. Utilisé pour l'exécution des scripts";

// Timezone
$MyOptTmpl["timezone"]=date_default_timezone_get();
$MyOptHelp["timezone"]="Sélectionner la timezone locale (Europe/Paris)";


// URL
$MyOptTmpl["host"]=htmlentities($_SERVER["REQUEST_SCHEME"]."://".$_SERVER["SERVER_NAME"].preg_replace("/\/[a-z]*\.php/","",$_SERVER["SCRIPT_NAME"]));
$MyOptHelp["host"]="Chemin complet du site. Utilisé pour générer les url statiques.";

// Titre du site
$MyOptTmpl["site_title"]="LogBook";
$MyOptHelp["site_title"]="Titre du site web";

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


// Modules

// Dénini les droits d'accès aux rubriques
// [vide] : Visible par tous
// x : Visible pour tous, y compris invité
// - : Masqué
// [Role] : Affiché que pour le role
$MyOptHelp["menu"]["accueil"]="Affichage des menus du site. [vide]: Visible par tous, x : visible pour tous y compris les invités, - : Masqué, [Role] : Affiché uniquement pour le role correspondant";

$MyOptTmpl["menu"]["accueil"]="";
$MyOptTmpl["menu"]["membres"]="x";
$MyOptTmpl["menu"]["forums"]="";
$MyOptTmpl["menu"]["mesinfos"]="x";
$MyOptTmpl["menu"]["logs"]="";
$MyOptTmpl["menu"]["rapports"]="";
$MyOptTmpl["menu"]["configuration"]="AccesConfiguration";


?>

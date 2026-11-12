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


// ---- Fonctions communes  
   	require ("lib/fonctions.inc.php");

	$id=checkVar("id","numeric");
	$docid=checkVar("docid","numeric");

// ---- Gestion des droits
	$gl_uid=0;
	if (isset($_COOKIE['t_session']))
	{
		$gl_auth=verifyJWT($_COOKIE['t_session']);
		$gl_uid=$gl_auth["uid"];
	}

	if ($gl_uid==0)
	{
		header("HTTP/1.0 302 Unauthorized"); 
		header("Location: ".$MyOpt["host"]."?redirect=/doc.php?id=".$id);
		exit;
	}


// ---- Variables
	$MyOpt["tbl"]=PREFIX_TBL;

	if (($id==0) && ($docid==0))
	{
		$ret=array();
		$ret["result"]="Incorrect id";
		$ret["status"]=406;
		echo json_encode($ret);
		header("HTTP/1.0 406 Not acceptable");
		exit;
	}

	if ($mysqluser=="")
	{
		$ret=array();
		$ret["result"]="Fichier de configuration introuvable";
		$ret["status"]=406;
		echo json_encode($ret);
		header("HTTP/1.0 406 Not acceptable");
		exit;
	}

	$mode=checkVar("mode","varchar");
	

// ---- Se connecte à la base MySQL
	require ("class/mysql.inc.php");
	$sql = new mysql_core($mysqluser, $mysqlpassword, $hostname, $db,$port);

// ---- Charge les informations de l'utilisateur connecté
	require ("class/objet.inc.php");
	require ("class/user.inc.php");
	$myuser = new user_core($gl_uid,$sql,true);

// ---- Charge le document
	require ("class/document.inc.php");
	require ("class/folder.inc.php");

	$stamp=false;
	if ($docid>0)
	{
		$paperdoc = new paper_docs_core($docid,$sql);
		$id=$paperdoc->val("id_doc");
		$paper = new paper_core($paperdoc->val("id_paper"),$sql);

		if ($paper->val("controlled")=="oui")
		{
			$stamp=true;
		}
	}

	$doc = new document_core($id,$sql);


// ---- Force la timezone
	if ($MyOpt["timezone"]!="")
	  { date_default_timezone_set($MyOpt["timezone"]); }

// ---- Delete document
	$fonc=checkVar("fonc","varchar");
	if ($fonc=="delete")
	{
		$ret=array();
		$ret["result"]=$doc->delete();
		echo json_encode($ret);
		exit;		
	}

// ---- Renvoie le contenu du fichier
	$type=checkVar("type","varchar");

	if ( $type=="image")
	{
		//$w=checkVar("width","numeric");
		//$h=checkVar("height","numeric");
		//$doc->ShowImage($w,$h);
		$doc->ShowImage(0,0);
	}
	else
	{
		$source="";
		if ($stamp)
		{
			$ext=GetExtension($doc->originalname);
			if ($ext=="pdf")
			{
				$source = tempnam("/tmp", "ea_source_").".pdf";
			}
			else
			{
				$stamp=false;
			}
		}

		$doc->Download($mode,$source);

		if ($stamp)
		{
			require_once('external/tcpdf/tcpdf.php');

			define('QPDF_BIN',     '/usr/bin/qpdf');
			define('TMP_DIR',      '/tmp');
            define('EA_PDF_FONT', 'helvetica');

            $usr_create = new user_core($paper->uid_creat,$sql,false);
            $usr_submit = new user_core($paper->val("uid_checked"),$sql,false);
            $usr_approve = new user_core($paper->val("uid_approved"),$sql,false);

            $ctx=array(
				"author"=>$usr_create->fullname,
				"title"=>$paper->val("title"),
				"file_name"=>$doc->originalname,
				"status"=>$paper->val("status"),
				"reference"=>$paper->ref(),
				"version"=>$paper->val("version"),
				"dte_effective"=>sql2date($paper->val("dte_start")),
				"dte_expiry"=>sql2date($paper->val("dte_end")),
                "signatures"=> array(
                    array('role' => 'Rédigé par',   'name' => $usr_create->fullname, 'date' => $paper->dte_creat),
                    array('role' => 'Vérifié par',  'name' => $usr_submit->fullname, 'date' => $paper->val("dte_checked")),
                    array('role' => 'Approuvé par', 'name' => $usr_approve->fullname, 'date' => $paper->val("dte_approved")),  
                ),
                "changes"=>$paper->val("history"),
			);

//            ['version' => '4', 'dte_effective' => '2026-09-01', 'author' => '…', 'checked_by' => '…',
  //          *      'approved_by' => '…', 'changes' => '…']
           
            $history=[
                array('version' => '4', 'dte_effective' => '2026-09-01', 'author' => '…', 'checked_by' => '…', 'approved_by' => 'Matthieu ISOREZ', 'changes' => 'Mise à jour de la liste des instructeurs'),
                array('dte_review' => '2026-03-12', 'par' => 'Paul MARTIN'),
            ];
            $history=$paper->history();
            
            pdfServeControlled($source,$ctx, $history);

		}
	}


// ---- PDF FUNCTIONS ------------------------------------------------

/* ======================================================================
 * Mise en page
 * ====================================================================== */

/**
 * Positions en mm, relevées dans les commandes de dessin du PDF SOP-004. Les distances « Right »
 * et « footerLine » se comptent depuis le bord droit et le bord bas, pour s'adapter aux pages en paysage.
 *
 * Sur les pages du document, logo et ligne du bas viennent du modèle : footerLine ne sert qu'à placer
 * le texte sous la ligne. Sur les pages d'approbation, deux possibilités :
 *   - 'background' : un PDF d'une page vide exporté depuis le modèle OnlyOffice (logo et ligne seulement),
 *     posé en fond par qpdf. Rendu identique au modèle, aucune mesure à tenir à jour. Recommandé.
 *   - sinon, logo ('logo') et ligne sont dessinés par TCPDF aux positions ci-dessous.
 */
function pdfLayout(array $overrides = []): array
{
    global $MyOpt,$appfolder;

    return $overrides + [
        'background'      => null,   // chemin du fond de page (PDF d'une page vide du modèle)
        'marginLeft'      => 19.5,   // début et fin de la ligne du bas = marges du texte du modèle
        'marginRight'     => 17.0,
        'logo'            => $appfolder."/custom/".$MyOpt["site_logo"],   // chemin du logo (PNG ou JPG), si pas de fond de page
        'logoX'           => 20.6,   // boîte de l'image du logo dans le modèle
        'logoY'           => 10.9,
        'logoWidth'       => 39.0,
        'footerLine'      => 16.4,   // ligne du bas du modèle, depuis le bord bas
        'footerLineWidth' => 0.18,   // 0,5 pt, comme dans le modèle
        'boxLeft'         => 68,     // cartouche : à droite du logo, jusqu'à la marge droite
        'boxTop'          => 11.4,   // aligné sur le dessin du logo
        'boxHeight'       => 24,
        'idWidth'         => 30,     // colonne référence / statut
        'statusHeight'    => 7.6,    // ligne du bas du cartouche (statut)
        'bodyTop'         => 42,     // début du texte sur la page d'approbation, sous le logo
    ];
}


/* ======================================================================
 * Utilisation dans la route
 * ====================================================================== */

/**
 * Envoie au navigateur $source avec cartouche et pied de page sur chaque page, suivi de la page d'approbation.
 * En cas d'échec, une exception est levée avant tout envoi d'en-tête : la route répond alors 500
 * plutôt que de servir le PDF sans cartouche.
 *
 * $doc = [
 *     'reference'     => 'SOP-001',
 *     'version'       => '4',
 *     'title'         => 'Politique de sécurité',          // titre de la fiche
 *     'file_name'     => 'Politique de sécurité.pdf',      // nom du fichier servi
 *     'status'        => 'current',     // current | upcoming | checked | draft | expired (calculé au téléchargement)
 *     'dte_effective' => '2026-09-01',
 *     'dte_expiry'    => null,          // date de péremption, pour 'expired' (nom de champ à adapter)
 *     'superseded_by' => null,          // version qui remplace celle-ci, pour 'expired'
 *     'signatures'    => [              // dans l'ordre d'affichage
 *         ['role' => 'Rédigé par',   'name' => 'Matthieu ISOREZ', 'date' => '2026-08-20 18:02:11'],
 *         ['role' => 'Vérifié par',  'name' => 'Paul MARTIN',     'date' => '2026-08-25 09:14:03'],
 *         ['role' => 'Approuvé par', 'name' => 'Julie BERNARD',   'date' => '2026-08-28 20:41:37'],
 *     ],
 *     'changes'       => 'Ajout du § 4 sur les vols de nuit.',   // facultatif
 *     'sha256'        => 'a3f1…',                                // facultatif : empreinte du fichier approuvé
 * ];
 *
 * $history : de la version la plus récente à la plus ancienne, versions et revues mêlées
 *     ['version' => '4', 'dte_effective' => '2026-09-01', 'author' => '…', 'checked_by' => '…',
 *      'approved_by' => '…', 'changes' => '…']
 *     ['dte_review' => '2026-03-12', 'reviewed_by' => 'Paul MARTIN']    // revue sans modification
 *
 * $layout : valeurs à remplacer dans pdfLayout(), au minimum ['logo' => '/chemin/vers/logo.png'].
 */
function pdfServeControlled(string $source, array $doc, array $history, array $layout = []): void
{
    if (!is_file($source) || !is_readable($source)) {
        throw new RuntimeException("PDF introuvable ou illisible : $source");
    }
    $layout   = pdfLayout($layout);
    $approval = tempnam(sys_get_temp_dir(), 'apr');
    $header   = tempnam(sys_get_temp_dir(), 'hdr');
    $output   = tempnam(sys_get_temp_dir(), 'pdf');
    try {
        $approvalPages = pdfBuildApprovalPage($doc, $history, $approval, $layout);
        $sourceSizes   = pdfGetPageSizes($source);
        $pageSizes     = array_merge($sourceSizes, array_fill(0, $approvalPages, [210, 297]));
        pdfBuildHeaderOverlay($doc, $pageSizes, $header, $layout);

        // $source reste le fichier principal (« . ») : titre, signets et liens conservés.
        // qpdf ajoute d'abord les pages d'approbation, pose le fond de page sous celles-ci,
        // puis superpose la page N du cartouche à la page N.
        $args = [$source, '--pages', '.', $approval, '--'];
        if (!empty($layout['background'])) {
            $args = array_merge($args, ['--underlay', $layout['background'],
                '--to=' . (count($sourceSizes) + 1) . '-z', '--from=', '--repeat=1', '--']);
        }
        pdfRunQpdf(array_merge($args, ['--overlay', $header, '--', $output]));

        $fileName = pdfCleanText($doc['file_name'] ?? basename($source));
        $ascii = preg_replace('/[^A-Za-z0-9._ -]/', '_', (string) iconv('UTF-8', 'ASCII//TRANSLIT', $fileName));
        header('Content-Type: application/pdf');
        header('Content-Length: ' . filesize($output));
        header('Content-Disposition: inline; filename="' . $ascii . '"; filename*=UTF-8\'\'' . rawurlencode($fileName));
        header('Cache-Control: private, no-store');   // le PDF contient la date de téléchargement et le statut du jour
        readfile($output);
    } finally {
        @unlink($approval);
        @unlink($header);
        @unlink($output);
    }
}


/* ======================================================================
 * Cartouche et pied de page
 * ====================================================================== */

/**
 * Génère dans $dest un PDF d'autant de pages que $pageSizes, chacune ne contenant que le cartouche
 * et le pied de page (fond transparent), aux dimensions de la page correspondante.
 * qpdf le superpose ensuite au document (--overlay).
 *
 *   [logo du modèle]   +-----------+------------------------------------------+
 *                      |  SOP-004  | Gestion des procédures                   |
 *                      | Version 1 | SOP-004 Gestion procédures.pdf           |
 *                      +-----------+------------------------------------------+
 *                      | BROUILLON | Non applicable                           |
 *                      +-----------+------------------------------------------+
 *   ____________________________________________________________ (ligne du modèle)
 *   Téléchargé le … – copie non maîtrisée une fois imprimée                Page 1 / 5
 *
 * Brouillon, version à approuver et version périmée reçoivent en plus un filigrane en diagonale.
 * $pageSizes : [[largeur, hauteur], …] en mm, telles qu'affichées (voir pdfGetPageSizes()).
 */
function pdfBuildHeaderOverlay(array $doc, array $pageSizes, string $dest, array $layout = []): void
{
    $L          = pdfLayout($layout);
    $doc        = pdfCleanArray($doc);
    $status     = pdfStatusInfo($doc);
    $downloaded = 'Téléchargé le ' . date('d/m/Y à H:i') . ' – copie non maîtrisée une fois imprimée';
    $fileName   = (string) ($doc['file_name'] ?? '');
    $keepEnd    = mb_strlen(pathinfo($fileName, PATHINFO_EXTENSION)) + 6;
    $total      = count($pageSizes);
    $pad        = 2;
    $dark       = [31, 41, 51];
    $grey       = [90, 99, 110];
    // Teinte claire de la couleur du statut, pour le fond de la case
    $tint       = array_map(function ($c) { return (int) round(255 - (255 - $c) * 0.14); }, $status['color']);

    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Easy-Aero');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(0, 0, 0);
    $pdf->SetAutoPageBreak(false, 0);
    $pdf->setCellPaddings(0, 0, 0, 0);

    foreach (array_values($pageSizes) as $i => [$pageWidth, $pageHeight]) {
        $pdf->AddPage($pageWidth > $pageHeight ? 'L' : 'P', [$pageWidth, $pageHeight]);

        if ($status['watermark'] !== null) {
            pdfWatermark($pdf, $status['watermark'], $status['color'], $pageWidth, $pageHeight);
        }

        // Géométrie du cartouche : deux colonnes, deux lignes
        $x  = $L['boxLeft'];
        $y  = $L['boxTop'];
        $w  = $pageWidth - $L['marginRight'] - $x;
        $h  = $L['boxHeight'];
        $w1 = $L['idWidth'];
        $w2 = $w - $w1;
        $h2 = $L['statusHeight'];
        $h1 = $h - $h2;

        // Fond de la case statut, puis cadre (sans fond ailleurs : si le document empiète, ça se voit)
        $pdf->SetFillColor(255, 255, 255);   // ajouté
        $pdf->Rect($x, $y, $w, $h, 'F');     // ajouté
        $pdf->SetFillColor(...$tint);
        $pdf->Rect($x, $y + $h1, $w1, $h2, 'F');
        $pdf->SetDrawColor(120, 128, 138);
        $pdf->SetLineWidth(0.25);
        $pdf->Rect($x, $y, $w, $h, 'D');
        $pdf->Line($x + $w1, $y, $x + $w1, $y + $h);
        $pdf->Line($x, $y + $h1, $x + $w, $y + $h1);

        // Référence et version, centrées dans leur case
        pdfHeaderText($pdf, $x + $pad, $y + $h1 / 2 - 5.2, $w1 - 2 * $pad, 6, $doc['reference'] ?? '', 'B', 12, $dark, 'C');
        pdfHeaderText($pdf, $x + $pad, $y + $h1 / 2 + 1.0, $w1 - 2 * $pad, 4, 'Version ' . ($doc['version'] ?? ''), '', 8, $grey, 'C');

        // Titre de la fiche (2 lignes au plus) et nom du fichier, centrés verticalement
        $tx = $x + $w1 + $pad;
        $tw = $w2 - 2 * $pad;
        $pdf->SetFont(EA_PDF_FONT, 'B', 9.5);
        $titleLines = pdfWrapLines($pdf, (string) ($doc['title'] ?? ''), $tw, 2);
        $lineHeight = 4.3;
        $ty = $y + ($h1 - (count($titleLines) * $lineHeight + 0.6 + 3.4)) / 2;
        foreach ($titleLines as $k => $line) {
            pdfHeaderText($pdf, $tx, $ty + $k * $lineHeight, $tw, $lineHeight, $line, 'B', 9.5, $dark);
        }
        pdfHeaderText($pdf, $tx, $ty + count($titleLines) * $lineHeight + 0.6, $tw, 3.4, $fileName, '', 7, $grey, 'L', $keepEnd);

        // Statut et détail
        pdfHeaderText($pdf, $x + 1, $y + $h1, $w1 - 2, $h2, mb_strtoupper($status['label']), 'B', 8.5, $status['color'], 'C');
        pdfHeaderText($pdf, $tx, $y + $h1, $tw, $h2, $status['detail'], '', 8.5, $dark);

        // Pied de page, sous la ligne du modèle
        $fy = $pageHeight - $L['footerLine'] + 1.2;
        $fw = $pageWidth - $L['marginLeft'] - $L['marginRight'];
        $pdf->SetFillColor(255, 255, 255);   // ajouté
        $pdf->Rect($x, $fy-1.05, $w, 10, 'F');     // ajouté

        pdfHeaderText($pdf, $L['marginLeft'], $fy, $fw - 30, 4, $downloaded, 'I', 7, $grey);
        pdfHeaderText($pdf, $pageWidth - $L['marginRight'] - 30, $fy, 30, 4, 'Page ' . ($i + 1) . ' / ' . $total, '', 8, $dark, 'R');
    }

    if (file_put_contents($dest, $pdf->Output('', 'S')) === false) {
        throw new RuntimeException("Impossible d'écrire le cartouche : $dest");
    }
}

/**
 * Libellé, détail, couleur et filigrane selon le statut affiché.
 * Utilisé par le cartouche et par la page d'approbation.
 */
function pdfStatusInfo(array $doc): array
{
    $effective = pdfDate($doc['dte_effective'] ?? null);
    switch ($doc['status'] ?? '') {
        case 'approved':
            return ['label' => 'En vigueur', 'detail' => "Applicable depuis le $effective",
                    'color' => [46, 158, 91], 'watermark' => null];
        case 'upcoming':
            return ['label' => 'À venir', 'detail' => "Applicable à partir du $effective",
                    'color' => [47, 127, 209], 'watermark' => null];
        case 'submitted':
            return ['label' => 'À approuver', 'detail' => 'Non applicable – en attente d\'approbation',
                    'color' => [142, 91, 209], 'watermark' => 'NON APPROUVÉ'];
        case 'draft':
            return ['label' => 'Brouillon', 'detail' => 'Non applicable – document en cours de rédaction',
                    'color' => [107, 114, 128], 'watermark' => 'BROUILLON'];
        case 'expired':
            $by   = empty($doc['superseded_by']) ? '' : ' par la version ' . $doc['superseded_by'];
            $date = pdfDate($doc['dte_expiry'] ?? null);
            return ['label' => 'Périmé', 'detail' => 'Non applicable – remplacé' . $by . ($date === '' ? '' : " le $date"),
                    'color' => [192, 57, 43], 'watermark' => 'PÉRIMÉ'];
        default:
            throw new InvalidArgumentException('Statut inconnu : ' . ($doc['status'] ?? '(vide)'));
    }
}

/** Une ligne de texte du cartouche, raccourcie avec « … » si elle dépasse la case. */
function pdfHeaderText(TCPDF $pdf, float $x, float $y, float $width, float $height, string $text,
                       string $style, float $size, array $color, string $align = 'L', int $keepEnd = 0): void
{
    $pdf->SetFont(EA_PDF_FONT, $style, $size);
    $pdf->SetTextColor(...$color);
    $pdf->SetXY($x, $y);
    $pdf->Cell($width, $height, pdfFitText($pdf, $text, $width, $keepEnd), 0, 0, $align, false, '', 0, false, 'T', 'M');
}

/** Découpe $text en $maxLines lignes au plus ; la dernière est raccourcie avec « … » si besoin. */
function pdfWrapLines(TCPDF $pdf, string $text, float $width, int $maxLines): array
{
    $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);
    $lines = [];
    $line  = '';
    foreach ($words as $i => $word) {
        $candidate = $line === '' ? $word : "$line $word";
        if ($line === '' || $pdf->GetStringWidth($candidate) <= $width) {
            $line = $candidate;
            continue;
        }
        if (count($lines) === $maxLines - 1) {          // dernière ligne : tout le reste, raccourci
            $line = implode(' ', array_merge([$line], array_slice($words, $i)));
            break;
        }
        $lines[] = pdfFitText($pdf, $line, $width);
        $line = $word;
    }
    $lines[] = pdfFitText($pdf, $line, $width);
    return $lines;
}

/**
 * Raccourcit $text pour qu'il tienne dans $width avec la police courante.
 * $keepEnd > 0 garde les derniers caractères (l'extension d'un nom de fichier) : « Politique de s….pdf ».
 */
function pdfFitText(TCPDF $pdf, string $text, float $width, int $keepEnd = 0): string
{
    if ($pdf->GetStringWidth($text) <= $width) {
        return $text;
    }
    $end   = $keepEnd > 0 ? mb_substr($text, -$keepEnd) : '';
    $start = mb_substr($text, 0, mb_strlen($text) - mb_strlen($end));
    while ($start !== '' && $pdf->GetStringWidth(rtrim($start) . '…' . $end) > $width) {
        $start = mb_substr($start, 0, -1);
    }
    return rtrim($start) . '…' . $end;
}

/** Filigrane en diagonale, centré, couleur du statut à 12 % d'opacité. */
function pdfWatermark(TCPDF $pdf, string $text, array $color, float $pageWidth, float $pageHeight): void
{
    $pdf->SetFont(EA_PDF_FONT, 'B', 100);
    $size = min(110, 100 * 0.55 * hypot($pageWidth, $pageHeight) / $pdf->GetStringWidth($text));
    $pdf->SetFont(EA_PDF_FONT, 'B', $size);
    $textWidth  = $pdf->GetStringWidth($text);
    $textHeight = $size * 25.4 / 72;

    $pdf->StartTransform();
    $pdf->Rotate(rad2deg(atan2($pageHeight, $pageWidth)), $pageWidth / 2, $pageHeight / 2);
    $pdf->setAlpha(0.12);
    $pdf->SetTextColor(...$color);
    $pdf->SetXY(($pageWidth - $textWidth) / 2, ($pageHeight - $textHeight) / 2);
    $pdf->Cell($textWidth, $textHeight, $text, 0, 0, 'C', false, '', 0, false, 'T', 'M');
    $pdf->StopTransform();
    $pdf->setAlpha(1);
}


/* ======================================================================
 * Nettoyage des textes
 * ====================================================================== */

/**
 * Recompose les accents décomposés (« e » + accent combinant, fréquent dans les noms de fichiers
 * venant d'un Mac ou copiés depuis un navigateur). Les polices standard de TCPDF ne savent pas
 * les afficher et écrivent « proce?dures ». Utilise l'extension intl si elle est là.
 */
function pdfCleanText(string $text): string
{
    static $map = null;
    if (class_exists('Normalizer')) {
        $normalized = Normalizer::normalize($text, Normalizer::FORM_C);
        $text = $normalized === false ? $text : $normalized;
        $map  = [];
    } elseif ($map === null) {
        $map = [];
        $marks = [
            "\u{0301}" => ['aeiouyAEIOUY', 'áéíóúýÁÉÍÓÚÝ'],
            "\u{0300}" => ['aeiouAEIOU',   'àèìòùÀÈÌÒÙ'],
            "\u{0302}" => ['aeiouAEIOU',   'âêîôûÂÊÎÔÛ'],
            "\u{0308}" => ['aeiouyAEIOU',  'äëïöüÿÄËÏÖÜ'],
            "\u{0327}" => ['cC',           'çÇ'],
            "\u{0303}" => ['anoANO',       'ãñõÃÑÕ'],
        ];
        foreach ($marks as $mark => [$bases, $composed]) {
            foreach (mb_str_split($bases) as $k => $base) {
                $map[$base . $mark] = mb_str_split($composed)[$k];
            }
        }
    }
    // Les accents combinants restants sont retirés : une lettre sans accent vaut mieux qu'un « ? »
    return preg_replace('/\p{Mn}/u', '', strtr($text, $map));
}

/** pdfCleanText() sur toutes les chaînes d'un tableau. */
function pdfCleanArray(array $data): array
{
    array_walk_recursive($data, function (&$value) {
        if (is_string($value)) {
            $value = pdfCleanText($value);
        }
    });
    return $data;
}


/* ======================================================================
 * Dimensions des pages du PDF source (qpdf)
 * ====================================================================== */

/**
 * Renvoie [[largeur, hauteur], …] en mm pour chaque page, telles qu'affichées :
 * même boîte que celle utilisée par qpdf pour --overlay (TrimBox, sinon CropBox, sinon MediaBox),
 * largeur et hauteur inversées pour les pages tournées de 90° ou 270°.
 */
function pdfGetPageSizes(string $file): array
{
    $json = json_decode(pdfRunQpdf(['--json=2', '--json-key=pages', '--json-key=qpdf', $file]), true);
    if (!isset($json['pages'], $json['qpdf'][1]) || !is_array($json['pages'])) {
        throw new RuntimeException("Structure du PDF illisible : $file");
    }
    $objects = $json['qpdf'][1];

    // Dictionnaire d'un objet indirect ("12 0 R"), ou valeur directe
    $resolve = function ($value) use ($objects) {
        if (is_string($value) && preg_match('/^\d+ \d+ R$/', $value)) {
            $object = $objects['obj:' . $value] ?? [];
            return $object['value'] ?? ($object['stream']['dict'] ?? null);
        }
        return $value;
    };

    $sizes = [];
    foreach ($json['pages'] as $page) {
        $dict = $resolve($page['object'] ?? null);
        $mediaBox = $cropBox = $rotate = null;
        // MediaBox, CropBox et Rotate peuvent être hérités de l'arbre des pages
        for ($node = $dict, $depth = 0; is_array($node) && $depth < 64; $depth++) {
            $mediaBox = $mediaBox ?? $resolve($node['/MediaBox'] ?? null);
            $cropBox  = $cropBox  ?? $resolve($node['/CropBox'] ?? null);
            $rotate   = $rotate   ?? $resolve($node['/Rotate'] ?? null);
            $node = isset($node['/Parent']) ? $resolve($node['/Parent']) : null;
        }
        $box = $resolve($dict['/TrimBox'] ?? null) ?? $cropBox ?? $mediaBox;
        if (!is_array($box) || count($box) !== 4) {
            throw new RuntimeException('Dimensions introuvables pour la page ' . (count($sizes) + 1) . " de $file");
        }
        $box    = array_map(function ($v) use ($resolve) { return (float) $resolve($v); }, array_values($box));
        $width  = abs($box[2] - $box[0]) * 25.4 / 72;
        $height = abs($box[3] - $box[1]) * 25.4 / 72;
        if (((((int) $rotate) % 360 + 360) % 180) === 90) {
            [$width, $height] = [$height, $width];
        }
        $sizes[] = [$width, $height];
    }
    if (!$sizes) {
        throw new RuntimeException("Aucune page dans $file");
    }
    return $sizes;
}

/** Lance qpdf sans passer par le shell et renvoie sa sortie standard. */
function pdfRunQpdf(array $args): string
{
    $proc = proc_open(array_merge([QPDF_BIN], $args), [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($proc)) {
        throw new RuntimeException('Impossible de lancer qpdf (proc_open désactivé ou qpdf absent ?).');
    }
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $code = proc_close($proc);

    // 0 : succès. 3 : succès avec avertissements (PDF légèrement abîmé, réparé par qpdf). 2 : échec.
    if ($code === 3) {
        error_log('qpdf, avertissements : ' . trim($stderr));
    } elseif ($code !== 0) {
        throw new RuntimeException("qpdf a échoué (code $code) : " . trim($stderr !== '' ? $stderr : $stdout));
    }
    return $stdout;
}


/* ======================================================================
 * Page « Approbation et historique »
 * ====================================================================== */

/**
 * Génère la page d'approbation dans $dest et renvoie son nombre de pages
 * (plus d'une si l'historique est long). Cartouche, numéro de page et mention de téléchargement
 * sont ajoutés ensuite par pdfBuildHeaderOverlay(), comme sur les pages du document.
 */
function pdfBuildApprovalPage(array $doc, array $history, string $dest, array $layout = []): int
{
    $L       = pdfLayout($layout);
    $doc     = pdfCleanArray($doc);
    $history = pdfCleanArray($history);

    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Easy-Aero');
    $pdf->SetTitle('Approbation et historique – ' . ($doc['reference'] ?? ''));
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins($L['marginLeft'], $L['bodyTop'], $L['marginRight']);
    $pdf->SetAutoPageBreak(true, $L['footerLine'] + 4);
    $pdf->AddPage();
    $width = $pdf->getPageWidth() - $L['marginLeft'] - $L['marginRight'];

    // Titre
    pdfParagraph($pdf, 'Approbation et historique', 'B', 14);
    pdfParagraph($pdf, ($doc['reference'] ?? '') . ' – ' . ($doc['title'] ?? ''), '', 10.5);
    $pdf->Ln(4);

    // Version courante
    $widths = pdfScaleWidths([30, 75, 75], $width);
    pdfTableRow($pdf, $widths, ['Version', 'Statut', "Date d'application"], 'header');
    pdfTableRow($pdf, $widths, ['v' . ($doc['version'] ?? ''), pdfStatusInfo($doc)['label'], pdfDate($doc['dte_effective'] ?? null)]);
    $pdf->Ln(5);

    // Validations
    pdfSectionTitle($pdf, 'Validation');
    $widths = pdfScaleWidths([35, 85, 60], $width);
    pdfTableRow($pdf, $widths, ['Rôle', 'Nom', 'Date et heure'], 'header');
    foreach ($doc['signatures'] ?? [] as $signature) {
        pdfTableRow($pdf, $widths, [$signature['role'] ?? '', $signature['name'] ?? '', pdfDate($signature['date'] ?? null, true)]);
    }
    $pdf->Ln(1.5);
    pdfParagraph($pdf, 'Validations électroniques enregistrées dans Easy-Aero par des comptes authentifiés, '
        . 'avec horodatage du serveur. Elles tiennent lieu de signatures.', 'I', 7.5, [110, 117, 125]);
    if (!empty($doc['sha256'])) {
        $pdf->SetFont('courier', '', 7);
        $pdf->MultiCell(0, 0, 'Empreinte SHA-256 du fichier approuvé : ' . $doc['sha256'], 0, 'L', false, 1);
    }
    $pdf->Ln(4);

    // Modifications de cette version
    if (!empty($doc['changes'])) {
        pdfSectionTitle($pdf, 'Modifications de cette version');
        pdfParagraph($pdf, $doc['changes'], '', 9);
        $pdf->Ln(4);
    }

    // Historique
    if ($history) {
        pdfSectionTitle($pdf, 'Historique des versions');
        $widths = pdfScaleWidths([16, 22, 30, 30, 30, 52], $width);
        $tableHeader = function () use ($pdf, $widths) {
            pdfTableRow($pdf, $widths, ['Version', 'Application', 'Rédigé par', 'Vérifié par', 'Approuvé par', 'Modifications'], 'header');
        };
        $tableHeader();
        foreach ($history as $entry) {
            if (isset($entry['dte_review'])) {
                $text = 'Revue du ' . pdfDate($entry['dte_review']) . ' sans modification'
                      . (empty($entry['reviewed_by']) ? '' : ' – ' . $entry['reviewed_by']);
                pdfTableRow($pdf, [array_sum($widths)], [$text], 'review', $tableHeader);
            } else {
                $style = (string) ($entry['version'] ?? '') === (string) ($doc['version'] ?? '') ? 'current' : 'normal';
                pdfTableRow($pdf, $widths, [
                    'v' . ($entry['version'] ?? ''),
                    pdfDate($entry['dte_effective'] ?? null),
                    $entry['author'] ?? '',
                    $entry['checked_by'] ?? '',
                    $entry['approved_by'] ?? '',
                    $entry['changes'] ?? '',
                ], $style, $tableHeader);
            }
        }
    }

    // À lire avant Output() : en fermant le document, TCPDF vide les propriétés de l'objet
    $pageCount = $pdf->getNumPages();

    // Sans fond de page : logo et ligne du bas dessinés aux places du modèle, une fois tout le contenu écrit.
    // On revient sur chaque page, quel que soit l'endroit où TCPDF a fait ses sauts de page.
    if (empty($L['background'])) {
        $pdf->SetAutoPageBreak(false, 0);
        for ($page = 1; $page <= $pageCount; $page++) {
            $pdf->setPage($page);
            if (!empty($L['logo']) && is_readable($L['logo'])) {
                $pdf->Image($L['logo'], $L['logoX'], $L['logoY'], $L['logoWidth']);
            }
            $lineY = $pdf->getPageHeight() - $L['footerLine'];
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetLineWidth($L['footerLineWidth']);
            $pdf->Line($L['marginLeft'], $lineY, $pdf->getPageWidth() - $L['marginRight'], $lineY);
        }
        $pdf->lastPage();
    }

    // 'S' renvoie le PDF sous forme de chaîne : évite les contrôles de TCPDF sur les chemins en mode 'F'
    if (file_put_contents($dest, $pdf->Output('', 'S')) === false) {
        throw new RuntimeException("Impossible d'écrire la page d'approbation : $dest");
    }
    return $pageCount;
}

/** Largeurs de colonnes proportionnelles, ramenées à la largeur disponible. */
function pdfScaleWidths(array $ratios, float $total): array
{
    $sum = array_sum($ratios);
    return array_map(function ($r) use ($sum, $total) { return $r * $total / $sum; }, $ratios);
}

/** Texte sur toute la largeur, aligné sur le bord des tableaux. */
function pdfParagraph(TCPDF $pdf, string $text, string $style = '', float $size = 9, array $color = [31, 41, 51]): void
{
    $pdf->setCellPaddings(0, 0, 0, 0);
    $pdf->SetFont(EA_PDF_FONT, $style, $size);
    $pdf->SetTextColor(...$color);
    $pdf->MultiCell(0, 0, $text, 0, 'L', false, 1);   // hauteur 0 : calculée d'après le texte
}

/** Titre de section, jamais seul en bas de page. */
function pdfSectionTitle(TCPDF $pdf, string $title): void
{
    if ($pdf->GetY() + 25 > $pdf->getPageHeight() - $pdf->getBreakMargin()) {
        $pdf->AddPage();
    }
    pdfParagraph($pdf, $title, 'B', 10);
    $pdf->Ln(1.5);
}

/**
 * Une ligne de tableau dont toutes les cellules prennent la hauteur de la plus haute.
 * $style : normal | header | current (version affichée, fond vert pâle) | review (italique gris)
 * $onNewPage : appelée après un saut de page, pour répéter l'en-tête du tableau.
 */
function pdfTableRow(TCPDF $pdf, array $widths, array $cells, string $style = 'normal', ?callable $onNewPage = null): void
{
    $fontStyle = $style === 'header' ? 'B' : ($style === 'review' ? 'I' : '');
    $pdf->SetFont(EA_PDF_FONT, $fontStyle, 8.5);
    $pdf->setCellPaddings(1.5, 1.2, 1.5, 1.2);

    $widths = array_values($widths);
    $cells  = array_map('strval', array_values($cells));
    $rowHeight = 0;
    foreach ($cells as $i => $text) {
        $rowHeight = max($rowHeight, $pdf->getStringHeight($widths[$i], $text, false, true, '', 1));
    }

    // Saut de page géré ici : sinon TCPDF couperait la ligne au milieu, cellule par cellule
    if ($pdf->GetY() + $rowHeight > $pdf->getPageHeight() - $pdf->getBreakMargin()) {
        $pdf->AddPage();
        if ($onNewPage) {
            $onNewPage();
            $pdf->SetFont(EA_PDF_FONT, $fontStyle, 8.5);
            $pdf->setCellPaddings(1.5, 1.2, 1.5, 1.2);
        }
    }

    // Couleurs redéfinies à chaque ligne : un saut de page de TCPDF peut les réinitialiser
    $pdf->SetDrawColor(190, 196, 204);
    $pdf->SetLineWidth(0.2);
    if ($style === 'header') {
        $pdf->SetFillColor(238, 241, 244);
    } elseif ($style === 'current') {
        $pdf->SetFillColor(234, 246, 238);
    }
    $fill = in_array($style, ['header', 'current'], true);
    if ($style === 'review') {
        $pdf->SetTextColor(110, 117, 125);
    } else {
        $pdf->SetTextColor(31, 41, 51);
    }

    $x0 = $pdf->getMargins()['left'];
    $y0 = $pdf->GetY();
    $x  = $x0;
    foreach ($cells as $i => $text) {
        // $rowHeight est une hauteur minimale : toutes les cellules de la ligne ont la même bordure
        $pdf->MultiCell($widths[$i], $rowHeight, $text, 1, 'L', $fill, 0, $x, $y0, true, 0, false, true, 0, 'T', false);
        $x += $widths[$i];
    }
    $pdf->SetXY($x0, $y0 + $rowHeight);
}

/** 2026-09-01 → 01/09/2026 ; avec $withTime : 01/09/2026 à 14:32. */
function pdfDate(?string $date, bool $withTime = false): string
{
    if ($date === null || $date === '' || substr($date, 0, 4) === '0000') {
        return '';
    }
    $timestamp = strtotime($date);
    return $timestamp === false ? $date : date($withTime ? 'd/m/Y à H:i' : 'd/m/Y', $timestamp);
}

<?php

/**
 * @author Pierre-Gilles Levallois <pgl@erasme.org>
 * 
 * ****************************************************************************************************
 * Intrégration de SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * dans Laclasse.com version 3 <http://www.laclasse.com/v3/>
 * Provisionning des établissements et des comptes en mode api.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 */
/*
 * ****************************************************************************************************
 *  Point d'entrée du mode api
 * 
 * La démarche est la suivante :
 *  1. Création d'un établissement
 *  2. Paramétrage automatique du sso cas, en fonction de la configuration
 *  3. Nomination des administrateurs SACoche (les administrateurs d'établissement de l'ENT et les Superadmins, s'il y en a)
 *  4. Création des élèves
 *  5. création des Personnels de l'Education Nationale
 *  6. Création des parents
 * 
 * ***************************************************************************************************
 */
// Variables globales
$uai = strtoupper($_REQUEST['uai']);

// Montrer les erreurs : a commenter si on n'est pas en developpement
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

/*
 * Fonctions diverses et variées des API
 */

function p($s) {
  echo htmlentities($s) . "<br/>";
}

function generer_appel($url, $params) {
  $canonical_string.= $url."?";
  // 1. trier les paramètres
  ksort($params);
  // 2. construction de la canonical string
  foreach ($params as $k => $v) $query_string .= $k."=".urlencode ($v)."&";
  $query_string = trim($query_string, "&");
  $canonical_string.= $query_string;
  // 3. ajout du timestamp
  $timestamp = date("Y-m-d\TH:i:s");
  $canonical_string .= ";".$timestamp;
  //4. Ajout de l'identifiant d'application (connu de l'annuaire, et qui lu permet de comprendre la signature)
  $app_id = ANNUAIRE_ENT_APP_ID;
  $canonical_string .= ";".$app_id; 
  // 5. Calcul de la signature : sha1 et Encodage Base64
  $signature = "signature=".urlencode(base64_encode(hash_hmac('sha1', $canonical_string, ANNUAIRE_ENT_API_KEY, true)));
  // Renvoie de la requete constituée
  $req = $url . "?" .  $query_string . ";app_id=" . $app_id . ";timestamp=" . urlencode($timestamp) . ";" . $signature;
  p($req);
  return $req;
}

/*
 * Fonction d'envoi d'un GET http vers l'annuaire ENT.
 */
function interroger_annuaire_ENT($url_api, $params) {
    $url = generer_appel($url_api, $params);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_ENCODING ,"");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($ch);
	if (curl_errno($ch)) {
		return curl_error($ch);
	}
	curl_close($ch);
	return $data;
}


/* * *************************************************************************************************** */
// Charger la configuration
require('config.api.inc.php');
// Constantes / Configuration serveur / Autoload classes / Fonction de sortie
require('../sacoche/_inc/_loader.php');
// Fichier d'informations sur l'hébergement (requis avant la gestion de la session).
require(CHEMIN_FICHIER_CONFIG_INSTALL);

// Ouverture de la session et gestion des droits d'accès
// Dire à SACoche qu'on est sur la page 'webservices' : ses droits sont publics, 
// et le fichier des droits n'est pas surchargeable car il est requis juste avant la vérif.
// 
// Je laisse à Thomas Crespin la charge de modifier les matrices de droits pour ajouter "api", 
// s'il juge utile d'intégrer ce module d'API dans le core de SACoche.
if (!Session::verif_droit_acces('webservices')) {
  exit_json(400, 'Droits de la page "' . SACoche . '" manquants.<br />Les droits de cette page n\'ont pas été attribués dans le fichier "' . FileSystem::fin_chemin(CHEMIN_DOSSIER_INCLUDE . 'tableau_droits.php'));
}
Session::execute();

//Fonctions diverses de SACoche
require(CHEMIN_DOSSIER_INCLUDE . 'fonction_divers.php');

/* * *************************************************************************************************** */
// MAIN LOOP
/* * *************************************************************************************************** */
if (non_nul($uai)) {
  if (tester_UAI($uai)) {
    p("Création établissement...");
    
    $r = interroger_annuaire_ENT(ANNUAIRE_ENT_API_ETAB.$uai, array("expand" => "true") );
    print_r($r);
    //include(CHEMIN_DOSSIER_PAGES . 'webmestre_structure_gestion.ajax.php');
    
    exit_json(200, "OK");
  } else {
    exit_json(400, "La valeur du paramètre uai est incorrecte.");
  }
} else {
  exit_json(400, "Le paramètre uai est manquant.");
}


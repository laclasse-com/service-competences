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
// Montrer les erreurs 
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

// Charger la configuration
require('config.api.inc.php');
// Constantes / Configuration serveur / Autoload classes / Fonction de sortie
require('./sacoche/_inc/_loader.php');
// Fichier d'informations sur l'hébergement (requis avant la gestion de la session).
require(CHEMIN_FICHIER_CONFIG_INSTALL);

// Ouverture de la session et gestion des droits d'accès
// Dire à SACoche qu'on est sur la page 'webservices' : ses droits sont publics, 
// et le fichier des droits n'est pas patchable car il est requis juste avant la vérif.
if (!Session::verif_droit_acces('webservices')) {
  exit_error('Droits manquants' /* titre */, 'Droits de la page "' . SACoche . '" manquants.<br />Les droits de cette page n\'ont pas été attribués dans le fichier "' . FileSystem::fin_chemin(CHEMIN_DOSSIER_INCLUDE . 'tableau_droits.php') . '".' /* contenu */, '' /* lien */);
}
Session::execute();

/*
 *  Fonctions diverses de SACoche
 */
require(CHEMIN_DOSSIER_INCLUDE . 'fonction_divers.php');

/*
 * Fonctions diverses et variées des API
 */

function p($s) {
  echo $s . "<br/>";
}

function setEtape($n) {
  $etape = $n;
}
/******************************************************************************************************/

setEtape(0);

if ($etape == '0' ) {
  if (non_nul($_REQUEST['uai']) && tester_UAI($_REQUEST['uai'])) {
    p("Création établissement...");
  }
}


p("Fin.");

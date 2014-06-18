service-competences
===================

Intégration de SACoche en tant que service distant, dans l'ENT du Département du Rhône

### Installation
le fichier provisionning_laclasse.php s'installe dans le répertoire "webservices" de SACcoche.
Copier le config.api.inc.php.sample dans le répertoire "webservices" de SACcoche sous le nom de config.api.inc.php.

### Run
l'appel à ce webservice de provisionning se fait par l'url 
http://domaine_serveur/rep_install_sacoche/webservices.php?qui=provisionning-Laclasse&uai=[codee uai de l'établissement à provisionner]

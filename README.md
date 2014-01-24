DefiVelib
=========

A website to compare travel times in Velib at Paris.

DefiVelib est un script PHP permettant de noter et de partager des durées de
trajet entre des stations de vélib. C'est un script tenant en un seul fichier,
que vous pouvez donc héberger facilement sur votre serveur pour avoir une
instance perso.

Il récupère la liste des stations de Vélib sur l'API REST disponible et permet
de sélectionner une station de départ et d'arrivée avec une durée de trajet
associée. Ceci permet ainsi de prévoir a priori la durée estimée du parcours,
mais également de montrer qu'on est bien souvent plus rapide à vélo qu'on ne le
pense.

Une instance de démonstration pleinement fonctionnelle est disponible à
l'adresse http://defivelib.phyks.me.

## Disclaimer

Les temps disponibles sont rentrés par les usagers du service et ne reflètent
pas forcément les temps réels de parcours. De plus, les conditions de
circulation sont variables au cours de la journée.

Le code de la route s'applique également aux vélos, et l'obtention d'un
meilleur temps ne doit pas se faire au détriment du respect du code de la
route.

## Données personnelles

Sur l'instance de démonstration, les logs de connexion au service sont
conservés. Aucune autre donnée personnelle n'est conservée et vous êtes libres
de spécifier un pseudo vous identifiant si vous le souhaitez.

## Installation

Pour l'installer sur son serveur, il suffit de cloner le dépôt Git dans un dossier accessible par votre serveur web. Au premier lancement, l'application récupère la liste des stations de vélib disponibles. Comme pour le script [BikeInParis](https://github.com/phyks/bikeinparis), la mise à jour se fait par une tâche cron à lancer sur votre serveur, en appelant le script avec un bon code de synchronisation.

Pour mettre à jour automatiquement la liste des stations, vous pouvez utiliser une tâche cron comme suit :
<code>sudo crontab -e</code>
puis insérer la ligne
<code>* * * * * wget -q -O adresse_de_base_de_DefiVelib/index.php?update=1&code=code_synchro #Commande de mise a jour des stations de velib</code>

en remplaçant code_synchro par votre code de synchronisation et en définissant * conformément à la fréquence de mises à jour souhaitée.

Toutes les données sont sauvegardées dans des fichiers, dans le dossier `data/`. Ce dossier est automatiquement créé au premier lancement de l'application, aussi assurez-vous que le serveur web ait des permissions suffisantes pour écrire des fichiers (chmod…).

Le code de synchronisation vous permet également d'accéder à l'administration, à l'adresse `adresse_de_base_de_DefiVelib/?code=code_synchro`. Cette interface vous permet notamment de retirer des temps.

## TODO

* Gestion des tickets de Velib ? => scan comme preuve + OCR ?

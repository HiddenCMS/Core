# Statistiques administratives

## Fréquentation

Le compteur local mesure les pages vues et les sessions de navigateur distinctes
par jour. Une session qui ouvre plusieurs pages le même jour compte une visite ;
les vues sont comptées à chaque chargement. Ce ne sont pas des personnes uniques.
Les requêtes trop rapprochées (moins de deux secondes) sont ignorées.

La collecte démarre uniquement avec l'accord au service « Statistiques du site ».
Les administrateurs, robots identifiés, espaces utilisateurs, fichiers et URL
privées sont exclus. Les paramètres et fragments des URL ne sont pas transmis.
Aucun historique ne peut être reconstruit pour les dates antérieures.

La table `statistics_daily` conserve seulement des compteurs par jour et chemin.
La table `statistics_seen` déduplique les sessions avec un HMAC journalier, sans
adresse IP, user-agent ou identifiant de compte. Les clés de déduplication de
plus de deux jours et les agrégats de plus de treize mois sont purgés lors de la
collecte. Le compteur ne remplace pas un outil de protection contre les robots.

La collecte peut être désactivée dans Paramètres > Confidentialité. La politique
de confidentialité du site doit décrire ce traitement. Les logs du serveur et
la gestion des sessions du CMS restent des traitements distincts.

## Activité et contenus

Les courbes utilisent les inscriptions, l'historique des connexions,
commentaires et fichiers déjà présents. Les connexions sont dédupliquées par
membre sur la période et par jour sur la courbe. Les purges de données peuvent
réduire l'historique disponible. L'inventaire des contenus est l'état actuel,
indépendamment de la période choisie ; seuls les modules actifs apparaissent.

L'export CSV inclut les séries quotidiennes d'activité et de fréquentation.
Les périodes proposées sont 7, 30, 90 et 365 jours, en incluant aujourd'hui.

La migration `0.7.2` crée les tables pour les mises à jour. Le schéma de
l'installation contient aussi ces tables.

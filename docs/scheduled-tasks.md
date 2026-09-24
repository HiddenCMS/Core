# Tâches planifiées

Les contrôles distants et les opérations d’entretien ne doivent pas ralentir
les requêtes web. HiddenCMS fournit des commandes CLI destinées au cron de
l’hébergement.

## Vérification des mises à jour

La commande suivante recherche les nouvelles versions du Core et des addons,
puis écrit le résultat dans `cache/updates/status.json` :

```sh
php tools/core.php updates-check
```

L’administration lit uniquement ce fichier. L’ouverture d’une page admin ne
lance donc aucun appel réseau ni commande Composer. Le bouton **Rechercher** de
la page **Mises à jour** permet toujours une vérification manuelle immédiate.

La commande utilise un verrou non bloquant : si une vérification précédente est
encore active, la nouvelle exécution se termine sans lancer un second processus.
Elle retourne un code différent de zéro si GitHub ou Composer signale une erreur.

Exemple de cron toutes les 30 minutes, à adapter au chemin du site et à celui de
PHP chez l’hébergeur :

```cron
*/30 * * * * cd /home/example/public_html && /usr/bin/php tools/core.php updates-check >> logs/update-check.log 2>&1
```

Le fichier de cache peut être plus ancien que l’ancien délai interne de quinze
minutes sans déclencher de recalcul lors de l’affichage. Sa fraîcheur dépend
désormais uniquement de la fréquence du cron ou d’une recherche manuelle.

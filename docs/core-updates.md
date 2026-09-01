# Versionnement et mises à jour du core

## Version de référence

La version du core est déclarée dans `hiddencms.json`. Elle respecte SemVer et
doit rester identique au champ `version` de `composer.json` et au tag de release
GitHub, sans le préfixe facultatif `v`.

Une release contient impérativement `hiddencms.json`. Le bloc `update` permet de
protéger les répertoires propres à l'installation et de déclarer les anciens
fichiers à supprimer :

```json
{
    "version": "0.4.0",
    "schema": "0.4.0",
    "php": ">=8.1",
    "update": {
        "protected": ["config", "uploads", "overrides"],
        "remove": ["ancien/fichier.php"]
    }
}
```

## Migrations

Les migrations du core sont placées dans `hiddencms/migrations` et portent le
nom de leur version, par exemple `0.4.0.php`. Chaque fichier retourne un objet
implémentant `HB\HiddenCMS\Addons\Migration`. Les migrations appliquées sont
journalisées dans `core_migrations` et exécutées dans une transaction.

```bash
php tools/core.php migrate
```

## Cycle de mise à jour

La page `Administration > Paramètres > Mises à jour` interroge la dernière
release GitHub et Composer. Une mise à jour du core suit cet ordre :

1. téléchargement et validation de la release ;
2. sauvegarde SQL et archive des fichiers gérés ;
3. activation du mode maintenance ;
4. remplacement des fichiers et suppressions déclarées ;
5. `composer install`, migrations du core et synchronisation des addons ;
6. réouverture du site.

Si une étape échoue après la sauvegarde, HiddenCMS tente immédiatement de
restaurer les fichiers et la base. Les points de retour restent disponibles dans
`backups/updates` et depuis l'administration.

Les mêmes opérations sont accessibles en CLI :

```bash
php tools/core.php status --refresh
php tools/core.php backup
php tools/core.php update
php tools/core.php rollback IDENTIFIANT
```

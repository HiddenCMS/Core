# Addons Composer

HiddenCMS découvre les paquets Composer dont le type est `hiddencms-addon` ou
`hiddencms-addon-{type}`. Les types disponibles sont `module`, `widget`, `theme`,
`language` et `authenticator`.

Exemple de manifeste :

```json
{
    "name": "vendor/news-extension",
    "type": "hiddencms-addon",
    "autoload": {
        "psr-4": {
            "Vendor\\NewsExtension\\": "src/"
        }
    },
    "extra": {
        "hiddencms": {
            "addons": [
                {
                    "type": "module",
                    "name": "news_extension",
                    "class": "Vendor\\NewsExtension\\NewsExtension",
                    "path": "modules/news_extension"
                },
                {
                    "type": "widget",
                    "name": "news_extension",
                    "class": "Vendor\\NewsExtension\\Widget",
                    "path": "widgets/news_extension"
                }
            ],
            "migrations": [
                "Vendor\\NewsExtension\\Migrations\\CreateTables"
            ],
            "seeders": [
                "Vendor\\NewsExtension\\Seeders\\DefaultContent"
            ]
        }
    }
}
```

Une migration implémente `HB\HiddenCMS\Addons\Migration` et fournit `up($db)`
et `down($db)`. Un seeder implémente `HB\HiddenCMS\Addons\Seeder` et fournit
`run($db)`. HiddenCMS exécute chaque classe une seule fois et l'enregistre dans
`addon_migrations`.

Un paquet peut déclarer un ou plusieurs addons. Le chemin de chaque addon est
relatif à la racine du paquet. Il est installé avec Composer ou avec les commandes
HiddenCMS suivantes :

```console
php tools/addons.php require vendor/package:^1.0
php tools/addons.php update vendor/package
php tools/addons.php remove vendor/package
```

Les scripts Composer lancent ensuite `php tools/addons.php sync`, qui enregistre
les addons, applique leurs migrations et insère leur contenu initial. Les nouveaux
addons restent désactivés jusqu'à leur activation explicite dans l'administration.

La suppression conserve les tables et les contenus afin de permettre une
réinstallation sans perte. `php tools/addons.php remove vendor/package --purge`
exécute aussi les méthodes `down()` des migrations et supprime définitivement les
données appartenant au paquet.

Si Composer n'est pas disponible dans le `PATH` du serveur web, la constante
`HIDDENCMS_COMPOSER_BINARY` peut contenir le chemin absolu de l'exécutable.

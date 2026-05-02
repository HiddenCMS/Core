# HiddenCMS

HiddenCMS est un CMS PHP/MySQL oriente contenus, modules et personnalisation de pages.

Ce projet est issu d'un fork de NeoFrag, mais son developpement reprend desormais avec une direction propre : compatibilite PHP moderne, installation plus simple, routage centre sur les pages, meilleure separation entre contenus statiques et instances de modules.

## Prerequis

- PHP 8.3 ou superieur recommande
- Extensions PHP : curl, gd, intl, mbstring, mysqli, zip
- MySQL ou MariaDB
- Apache avec `rewrite_module` active

## Installation CLI

L'installation peut etre lancee en ligne de commande :

```powershell
php install/cli.php `
  --db-host=localhost `
  --db-name=hiddencms `
  --db-user=root `
  --db-pass= `
  --create-db `
  --admin-user=admin `
  --admin-pass=admin123 `
  --admin-email=admin@example.test `
  --base=/hHiddenCMS/ `
  --yes
```

Le mode interactif est aussi disponible :

```powershell
php install/cli.php
```

Quand le dossier `install/` est conserve, l'installation CLI ecrit `install/installed.txt` pour desactiver l'installateur web.

## Installation web

L'installateur web historique reste disponible en ouvrant le projet dans un navigateur tant que le dossier `install/` est present.

## Heritage

HiddenCMS est base sur NeoFrag Alpha 0.2.3. Les credits et licences d'origine sont conserves dans les fichiers sources et les fichiers de licence.

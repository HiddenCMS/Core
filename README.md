# HiddenCMS

HiddenCMS est un CMS PHP/MySQL orienté contenus, modules et personnalisation de pages.

Ce projet est issu d'un fork de NeoFrag, mais son développement reprend désormais avec une direction propre : compatibilité PHP moderne, installation plus simple, routage centré sur les pages, meilleure séparation entre contenus statiques et instances de modules.

## Prérequis

- PHP 8.3 ou supérieur recommandé
- Extensions PHP : curl, gd, intl, mbstring, mysqli, zip
- MySQL ou MariaDB
- Apache avec `rewrite_module` activé

## Installation CLI

L'installation peut être lancée en ligne de commande :

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
  --base=/hNeoFrag/ `
  --yes
```

Le mode interactif est aussi disponible :

```powershell
php install/cli.php
```

## Installation web

L'installateur web historique reste disponible en ouvrant le projet dans un navigateur tant que le dossier `install/` est présent.

## Héritage

HiddenCMS est basé sur NeoFrag Alpha 0.2.3. Les crédits et licences d'origine sont conservés dans les fichiers sources et les fichiers de licence.

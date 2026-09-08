# Publier une version stable de HiddenCMS

Ce document est la procédure de référence pour publier le Core, News, Horizon et les futurs addons. Une version publiée ne doit jamais être remplacée ou retaguée. Toute correction ultérieure reçoit un nouveau numéro de version.

## Versions conseillées pour la première publication

- Core : `0.3.4`, release stable courante.
- News : `0.1.0`.
- Horizon : `0.1.0`.

Une version sans suffixe, comme `0.1.0`, est considérée comme stable par Composer. Le fait de rester sous `1.0.0` indique simplement que les API et les formats internes peuvent encore évoluer.

## Choisir le prochain numéro

HiddenCMS utilise SemVer sous la forme `MAJEURE.MINEURE.CORRECTIF`.

- Incrémenter le correctif pour une correction compatible : `0.3.1` vers `0.3.2`.
- Incrémenter la mineure pour une fonctionnalité ou une évolution notable : `0.3.2` vers `0.4.0`.
- Utiliser une version majeure à partir du moment où les contrats publics sont stabilisés, puis pour une rupture de compatibilité : `1.4.2` vers `2.0.0`.
- Utiliser `-beta.1` ou `-rc.1` pour une préversion : `0.4.0-rc.1`. Elle ne doit pas être marquée comme release stable dans GitHub.

Les tags Git utilisent le préfixe `v`, par exemple `v0.3.1`. Les manifestes utilisent uniquement `0.3.1`.

## Avant toute publication

1. Travailler sur `main` et récupérer les derniers changements.
2. Vérifier que `git status --short` est vide.
3. Choisir le numéro avec les règles ci-dessus.
4. Mettre à jour les versions et contraintes de compatibilité.
5. Ajouter toute migration nécessaire sans modifier une migration déjà publiée.
6. Exécuter les validations et les tests.
7. Tester une installation neuve et une mise à jour depuis la dernière version stable.
8. Préparer des notes de version compréhensibles par un administrateur du CMS.

Ne pas publier si un de ces contrôles échoue.

## Publier le Core

### 1. Préparer la version

Dans `hiddencms.json` :

- `version` correspond toujours à la version publiée ;
- `schema` correspond à la dernière version ayant modifié la base de données ;
- `channel` vaut `stable` pour une release stable ;
- `php` indique la contrainte PHP réellement testée.

Le champ `version` de `composer.json`, la valeur `version` de `hiddencms.json` et le tag Git doivent être identiques, sans le `v` du tag.

Exemple pour `v0.3.2` sans nouvelle migration :

```json
{
    "version": "0.3.2",
    "schema": "0.3.1",
    "channel": "stable"
}
```

Exemple pour `v0.4.0` avec une migration `hiddencms/migrations/0.4.0.php` :

```json
{
    "version": "0.4.0",
    "schema": "0.4.0",
    "channel": "stable"
}
```

Le bloc `update.protected` conserve les données propres au site. Le bloc `update.remove` doit lister les fichiers du Core devenus obsolètes et qui doivent être supprimés pendant la mise à jour.

### 2. Valider

Depuis la racine du Core :

```powershell
composer validate --strict --no-check-publish
php tools/test-user-fields.php --isolated-database
php tools/test-privacy.php
node tools/test-form-calendar.cjs
node tools/test-privacy.cjs
php tools/core.php migrate
php tools/addons.php sync
```

L’avertissement Composer concernant le champ `version` est actuellement accepté, car HiddenCMS garde cette valeur explicite synchronisée avec son manifeste de release. Toute autre erreur doit être corrigée.

Tester ensuite l’installation CLI dans un dossier et une base vierges, puis tester la mise à jour d’une copie de la dernière version stable. Vérifier au minimum la connexion admin, les pages, les fichiers, les menus, les outlines, les addons et le thème actif.

### 3. Commit et tag

Remplacer `X.Y.Z` par la version préparée :

```powershell
git add .
git commit -m "Release X.Y.Z"
git tag -a vX.Y.Z -m "HiddenCMS X.Y.Z"
git push origin main
git push origin vX.Y.Z
```

### 4. Créer la GitHub Release

Dans GitHub, ouvrir **Releases**, choisir le tag `vX.Y.Z`, puis créer une release publiée, ni brouillon ni préversion. Son titre recommandé est `HiddenCMS X.Y.Z`.

Cette étape est obligatoire : la page Mises à jour interroge la dernière GitHub Release publiée. Un tag seul n’est pas suffisant.

Les notes doivent contenir :

- les nouveautés visibles ;
- les corrections importantes ;
- les éventuelles migrations ;
- les changements de compatibilité PHP ou addon ;
- les actions manuelles éventuelles.

### 5. Vérifier la publication

Sur une installation de test encore à la version précédente :

```powershell
php tools/core.php status --refresh
php tools/core.php backup
php tools/core.php update
php tools/addons.php sync
```

Vérifier ensuite que la version affichée est correcte, que le mode maintenance est désactivé et que les addons sont toujours présents et actifs.

## Publier News, Horizon ou un autre addon

La version d’un addon provient de son tag Git. Ne pas ajouter de champ `version` dans son `composer.json`.

### 1. Vérifier le manifeste

Le paquet doit déclarer une contrainte compatible avec le Core testé :

```json
"require": {
    "php": ">=8.1",
    "hiddencms/core": "^0.3"
}
```

`^0.3` accepte les versions `0.3.x`, mais pas `0.4.0`. Lorsqu’un addon fonctionne avec plusieurs branches du Core, utiliser par exemple `^0.3 || ^0.4` après les avoir réellement testées.

Valider le paquet :

```powershell
composer validate --strict --no-check-publish
composer dump-autoload --no-scripts --no-plugins
```

Pour un addon contenant des migrations, ne jamais modifier une classe de migration déjà publiée : HiddenCMS ne l’exécuterait pas une seconde fois. Ajouter une nouvelle classe, l’ajouter à `extra.hiddencms.migrations`, fournir `up()` et `down()`, puis tester installation, mise à jour et purge. La même règle s’applique aux seeders déjà exécutés.

### 2. Publier le tag

Pour la première version stable de News ou Horizon :

```powershell
git add .
git commit -m "Release 0.1.0"
git tag -a v0.1.0 -m "Version 0.1.0"
git push origin main
git push origin v0.1.0
```

Créer ensuite une GitHub Release à partir du tag. Pour les addons, elle est recommandée pour conserver les notes de version, mais la détection des mises à jour repose sur Composer.

Si le paquet est publié sur Packagist, vérifier que le nouveau tag y apparaît. Si le webhook GitHub n’est pas configuré, ouvrir le paquet dans Packagist et lancer manuellement **Update**.

### 3. Vérifier depuis HiddenCMS

Sur une installation utilisant la version précédente :

```powershell
composer outdated hiddencms/* --direct
php tools/addons.php update hiddencms/news
php tools/addons.php sync
```

Pour Horizon, remplacer `hiddencms/news` par `hiddencms/horizon`. Vérifier la page Mises à jour, la page Addons, l’activation, la configuration et les routes admin et front.

## Ordre d’une publication coordonnée

- Si l’addon reste compatible avec l’ancien et le nouveau Core, publier d’abord l’addon avec une contrainte couvrant les deux versions, puis publier le Core.
- Si l’addon exige une fonction uniquement disponible dans le nouveau Core, publier d’abord le Core, puis l’addon.
- Ne jamais annoncer une compatibilité qui n’a pas été testée par une installation et une mise à jour réelles.

## Corriger une release publiée

Ne jamais supprimer puis recréer le même tag, car Composer et les installations peuvent avoir mis la première archive en cache.

1. Corriger le problème sur `main`.
2. Incrémenter le correctif, par exemple `0.3.1` vers `0.3.2`.
3. Refaire toute la validation.
4. Publier un nouveau tag et une nouvelle GitHub Release.
5. Mentionner clairement le problème corrigé dans les notes.

## Modèle de notes de version

```markdown
## Nouveautés

- ...

## Corrections

- ...

## Compatibilité et migrations

- PHP : >= 8.1
- Core : ...
- Migration automatique : oui/non

## Après la mise à jour

- ...
```

## Checklist courte

- [ ] Version SemVer choisie.
- [ ] Manifestes et contraintes alignés.
- [ ] Nouvelles migrations ajoutées sans modifier les anciennes.
- [ ] Arbre Git propre et branche `main` à jour.
- [ ] Validation Composer réussie.
- [ ] Tests automatisés réussis.
- [ ] Installation neuve testée.
- [ ] Mise à jour depuis la version stable précédente testée.
- [ ] Commit de release créé.
- [ ] Tag annoté créé et poussé.
- [ ] GitHub Release publiée.
- [ ] Version visible dans Packagist pour les addons.
- [ ] Détection et installation vérifiées depuis l’administration.

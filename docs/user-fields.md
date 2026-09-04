# Utilisateurs et identifiant de connexion

Les administrateurs peuvent créer un compte depuis **Utilisateurs > Créer un utilisateur**.
Le compte est un membre ordinaire, immédiatement utilisable, sans modifier la session
de l'administrateur. Les groupes se modifient ensuite dans la fiche du membre.

**Utilisateurs > Champs et connexion** regroupe les définitions des champs et le choix
de l'identifiant. Ces réglages sont réservés aux administrateurs.

## Champs personnalisés

- Types : texte, liste, choix unique (radio), interrupteur, cases à cocher.
- Présents à l'inscription, à la création administrative et dans les profils privés.
- Non publiés sur la fiche publique du membre.
- Nom technique unique et type immuables après création.
- Pour les listes, radios et cases : un choix `valeur|libellé` par ligne. La valeur
  utilise des lettres, chiffres, tirets ou underscores et reste stable.
- Le libellé et le caractère obligatoire restent modifiables. Un choix déjà utilisé
  ne peut pas être retiré. La suppression d'un champ demande une confirmation et
  supprime aussi les valeurs associées.
- Les textes sont limités à 190 caractères.

## Connexion exclusive

La migration 0.3.1 remplace la connexion mixte par **le pseudo seul**, par défaut.
Le réglage permet de choisir l'e-mail seul ou un champ texte personnalisé seul.
Le libellé du formulaire suit ce choix. Il n'existe aucun repli vers un autre champ.
La récupération du mot de passe continue à utiliser l'adresse e-mail.

Un champ personnalisé doit être rempli pour tous les comptes non supprimés et ne
contenir aucun doublon avant de devenir l'identifiant. L'unicité suit la collation
MySQL `utf8mb4_unicode_ci` (insensible à la casse et aux accents).
Ensuite, ce champ devient obligatoire, ne peut pas être supprimé, et son unicité est
protégée par un index SQL. Un utilisateur doit fournir son mot de passe actuel pour
modifier son propre identifiant personnalisé. Les sessions existantes sont conservées.

## Migration et tests

Les installations neuves intègrent les tables. Pour une installation existante :

```sh
php tools/core.php migrate
```

Tests d'intégration sur un serveur de développement disposant des privilèges
`CREATE DATABASE` / `DROP DATABASE` :

```sh
php tools/test-user-fields.php --isolated-database
```

Le script crée une base temporaire contenant les structures et les métadonnées des
addons, sans copier les comptes. Il la supprime à la fin. Les tests couvrent les
formulaires, le stockage, la connexion exclusive, les doublons, la suppression et
les protections CSRF / échappement HTML.

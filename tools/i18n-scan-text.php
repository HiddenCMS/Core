<?php
// Report source text, excluding comments, catalogues and third-party assets.
$root = realpath($argv[1] ?? dirname(__DIR__));
foreach (['hiddencms','modules','widgets','themes','install','src'] as $folder) {
    if (!is_dir($root.'/'.$folder)) continue;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$folder, FilesystemIterator::SKIP_DOTS)) as $file) {
        $path = str_replace('\\','/', $file->getPathname());
        if (!in_array($file->getExtension(), ['php','js']) || preg_match('~/langs/|/vendor/|/tinymce/|\.min\.js$~', $path)) continue;
        foreach (token_get_all(file_get_contents($path)) as $token) {
            if (!is_array($token) || !in_array($token[0], [T_CONSTANT_ENCAPSED_STRING,T_INLINE_HTML])) continue;
            if (preg_match('/[éèêàçùîôûëïÉÀ]|\b(?:Ajouter|Modifier|Supprimer|Toutes|Nombre|Choisir|Aucun|Veuillez|Impossible|Activer|Dossier|Haut|Contenu|Affichage|Disposition|Continuer|Annuler|Retour|Valider)\b/u', $token[1])) {
                if (strpos($token[1], '@author') !== FALSE) continue;
                echo substr($path, strlen(str_replace('\\','/',$root))+1).':'.$token[2].':'.trim($token[1]).PHP_EOL;
            }
        }
    }
}

<?php
// Check French catalogue coverage for literal English lang() calls.
$root = realpath($argv[1] ?? dirname(__DIR__));
if (!$root || !is_dir($root)) throw new RuntimeException('Provide a repository root.');
$missing = []; $calls = 0; $translated = 0; $catalogues = [];
$sharedPath = dirname(__DIR__).'/hiddencms/langs/fr.php';
$shared = is_file($sharedPath) ? include $sharedPath : [];
foreach (['hiddencms', 'modules', 'widgets', 'themes', 'addons', 'install'] as $folder) {
    if (!is_dir($root.'/'.$folder)) continue;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$folder, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!in_array($file->getExtension(), ['php', 'js'], TRUE) || strpos(str_replace('\\', '/', $file->getPathname()), '/langs/') !== FALSE) continue;
        $tokens = token_get_all(file_get_contents($file->getPathname()));
        for ($i = 0; $i < count($tokens); $i++) {
            if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_STRING || strtolower($tokens[$i][1]) !== 'lang') continue;
            $j = $i + 1;
            while (isset($tokens[$j]) && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
            if (($tokens[$j++] ?? NULL) !== '(') continue;
            while (isset($tokens[$j]) && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
            if (!isset($tokens[$j]) || !is_array($tokens[$j]) || $tokens[$j][0] !== T_CONSTANT_ENCAPSED_STRING) continue;
            $literal = $tokens[$j][1];
            // Single-quoted PHP strings have only two escape sequences.
            if ($literal[0] !== "'") continue;
            $text = str_replace(["\\'", '\\\\'], ["'", '\\'], substr($literal, 1, -1));
            $calls++; $directory = dirname($file->getPathname()); $catalogue = NULL;
            while (strpos($directory, $root) === 0 && $directory !== $root) {
                if (is_file($path = $directory.'/langs/fr.php')) {
                    if (!isset($catalogues[$path])) {
                        $catalogues[$path] = [];
                        $entries = token_get_all(file_get_contents($path));
                        foreach ($entries as $entry) {
                            if (is_array($entry) && $entry[0] === T_CONSTANT_ENCAPSED_STRING && preg_match('/^[\'\"]([a-f0-9]{8})[\'\"]$/', $entry[1], $match)) $catalogues[$path][$match[1]] = TRUE;
                        }
                    }
                    $catalogue = $catalogues[$path]; break;
                }
                $directory = dirname($directory);
            }
            $key = hash('crc32b', $text);
            if (array_key_exists($key, $shared) || (is_array($catalogue) && isset($catalogue[$key]))) $translated++;
            else $missing[] = ['file' => str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1)), 'line' => $tokens[$j][2], 'text' => $text, 'key' => $key];
        }
    }
}
echo json_encode(['literal_calls' => $calls, 'french_catalogued_calls' => $translated, 'missing_french_calls' => count($missing), 'missing' => $missing], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL;

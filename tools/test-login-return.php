<?php
require __DIR__.'/../hiddencms/helpers/location.php';
$cases = ['/fr/titi' => TRUE, '/fr/titi?tab=2' => TRUE, '/fr/user/profile' => TRUE,
    '//evil.test' => FALSE, 'https://evil.test' => FALSE, '/%2f/evil.test' => FALSE,
    '/fr/%0d%0aLocation:evil' => FALSE, '/fr/user/login' => FALSE, '/fr/user/auth/google' => FALSE,
    '/fr/../evil' => FALSE, '/fr/%252f/evil' => FALSE, '/fr\\evil' => FALSE];
foreach ($cases as $path => $allowed) {
    if ((user_return_path($path) !== NULL) !== $allowed) throw new RuntimeException('Invalid return validation: '.$path);
    echo "PASS $path\n";
}
if (user_return_path('/outside/page', '/cms/') !== NULL || user_return_path('/cms/fr/titi', '/cms/') === NULL || user_return_path([]) !== NULL) throw new RuntimeException('Base path validation');
echo "PASS Base paths and malformed values\n";

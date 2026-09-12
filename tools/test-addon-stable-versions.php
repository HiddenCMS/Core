<?php
namespace HB\HiddenCMS { class Library {} }
namespace {
require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/hiddencms/libraries/addon_packages.php';

class StablePackagesTest extends \HB\HiddenCMS\Libraries\Addon_Packages {
    public $constraint = 'dev-main';
    public $current = 'dev-main abc123';
    public $versions = ['dev-main', 'v0.4.0-beta.1', 'v0.4.0', 'v0.3.1', 'v0.3.0'];
    public $calls = [];
    protected function package_constraint($package) { return $this->constraint; }
    protected function validate_package($package, $constraint = FALSE) { return $package; }
    protected function is_core_compatible($constraint) { return $constraint !== '^0.8'; }
    protected function run_composer(array $arguments, $no_progress = TRUE) {
        $this->calls[] = $arguments;
        if ($arguments[0] === 'outdated') {
            return json_encode(['installed'=>[['name'=>'hiddencms/altitude','version'=>$this->current], ['name'=>'other/package','version'=>'1.0.0']]]);
        }
        if ($arguments[0] === 'show') {
            return json_encode(isset($arguments[2]) && $arguments[2] !== '--all'
                ? ['requires'=>['hiddencms/core'=>$arguments[2] === 'v0.4.0' ? '^0.8' : '^0.7']]
                : ['versions'=>$this->versions]);
        }
        return 'Updated';
    }
}
function check($condition, $message) {
    if (!$condition) { throw new \RuntimeException($message); }
    echo 'PASS '.$message.PHP_EOL;
}
$packages = new StablePackagesTest;
$updates = $packages->outdated();
check($updates['hiddencms/altitude']['latest'] === 'v0.3.1', 'Stable compatible tag selected, development and prereleases excluded');
check($updates['hiddencms/altitude']['current'] === 'dev-main abc123', 'Actual installed development version preserved');
check(count($updates) === 1, 'Only HiddenCMS packages advertised');
$packages->update_package('hiddencms/altitude');
check(end($packages->calls) === ['require','hiddencms/altitude:^0.3.1','--with-all-dependencies','--prefer-stable'], 'Development constraint replaced on explicit update');
$packages->constraint = '^0.3';
$packages->current = '0.3.1';
check($packages->outdated() === [], 'Up-to-date stable package not advertised');
$packages->current = '0.3.0';
check($packages->outdated()['hiddencms/altitude']['latest'] === 'v0.3.1', 'Existing stable constraint respected');
$packages->versions = ['dev-main','v0.4.0-beta.1'];
check($packages->outdated() === [], 'No development fallback without a stable release');
}

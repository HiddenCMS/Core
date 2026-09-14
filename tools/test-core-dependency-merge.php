<?php
namespace HB\HiddenCMS {
    class Library {}
}
namespace {
    define('HIDDENCMS_CMS', dirname(__DIR__));
    require HIDDENCMS_CMS.'/hiddencms/libraries/core_updater.php';
    $updater = new class extends \HB\HiddenCMS\Libraries\Core_Updater {
        public $result;
        public function check(array $requirements) { $this->restore_addon_requirements($requirements); }
        protected function write_json($file, array $data) { $this->result = $data; }
    };
    $updater->check(['hiddencms/altitude'=>'^0.3', 'hiddencms/gallery'=>'^0.2', 'third-party/addon'=>'^1.0']);
    if ($updater->result['require']['hiddencms/altitude'] !== '^0.4') throw new \RuntimeException('Old site constraint replaced the incoming core requirement');
    if ($updater->result['require']['hiddencms/gallery'] !== '^0.2') throw new \RuntimeException('Site addon lost');
    if ($updater->result['require']['third-party/addon'] !== '^1.0') throw new \RuntimeException('Third-party addon lost');
    echo "PASS incoming core constraints win; site addons preserved\n";
}

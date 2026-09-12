<?php

namespace HB\HiddenCMS\Libraries\Forms {
    class Textarea {
        public $url;
        public function access(...$args) { return TRUE; }
    }
}

namespace {
    function extension($path) { return pathinfo($path, PATHINFO_EXTENSION); }
    function url($path) { return 'https://cms.test/'.$path; }
    function HB() {
        return new class {
            public function model2($type, $id) {
                return new class($id) {
                    public $path = 'upload/files/test.pdf';
                    private $id;
                    public function __construct($id) { $this->id = $id; }
                    public function __invoke() { return $this->id === 7; }
                    public function path() { return url($this->path); }
                };
            }
        };
    }
    require __DIR__.'/../hiddencms/libraries/forms/editor.php';
    $editor = new \HB\HiddenCMS\Libraries\Forms\Editor();
    $editor->url = (object)['admin' => TRUE];
    $method = new \ReflectionMethod($editor, 'sanitize');
    $check = function($condition, $name) {
        if (!$condition) { throw new \RuntimeException($name); }
        echo 'PASS '.$name.PHP_EOL;
    };
    $clean = $method->invoke($editor, '<p style="color:#ff0000;text-align:center;background-image:url(https://evil.test)">Hello <img src="https://cms.test/fr/files/image" onerror="alert(1)"></p>');
    $check(strpos($clean, 'text-align:center') !== FALSE && strpos($clean, 'color:#ff0000') !== FALSE, 'Admin formatting survives');
    $check(strpos($clean, 'onerror') === FALSE && strpos($clean, 'evil.test') === FALSE, 'Unsafe attributes and CSS removed');
    $clean = $method->invoke($editor, '<iframe data-hb-pdf="7" src="https://evil.test" onload="alert(1)"></iframe>');
    $check(strpos($clean, 'https://cms.test/upload/files/test.pdf') !== FALSE && strpos($clean, 'evil.test') === FALSE && strpos($clean, 'onload') === FALSE, 'PDF source rebuilt from registered file');
    $check(strpos($method->invoke($editor, '<iframe data-hb-pdf="9" src="https://evil.test"></iframe>'), 'iframe') === FALSE, 'Unknown PDF rejected');
    $check(strpos($method->invoke($editor, '<iframe src="https://evil.test"></iframe><script>alert(1)</script>'), 'iframe') === FALSE, 'Arbitrary iframe rejected');
    $editor->url->admin = FALSE;
    $clean = $method->invoke($editor, '<p style="color:red">Hello</p><iframe data-hb-pdf="7"></iframe><img src="https://cms.test/fr/files/image">');
    $check(strpos($clean, 'iframe') === FALSE && strpos($clean, 'img') === FALSE && strpos($clean, 'style') === FALSE, 'Profile filtering remains strict');
}

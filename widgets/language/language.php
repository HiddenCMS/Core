<?php
namespace HB\Widgets\Language;
use HB\HiddenCMS\Addons\Widget;
class Language extends Widget
{
    protected function __info()
    {
        return ['title' => 'Language selector', 'description' => 'Switch between enabled site languages.', 'language' => 'en', 'icon' => 'fas fa-globe', 'author' => 'HiddenCMS', 'license' => 'GPLv3', 'version' => '1.0'];
    }
}

<?php

namespace HB\HiddenCMS\Addons;

interface Migration
{
	public function up($db);
	public function down($db);
}


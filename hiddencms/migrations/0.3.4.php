<?php

use HB\HiddenCMS\Addons\Migration;

return new class implements Migration
{
	public function up($db)
	{
		$installer = HIDDENCMS_CMS.'/install/index.php';
		$installed = HIDDENCMS_CMS.'/install/installed.txt';

		if (is_file($installer) && !is_file($installed) && !@touch($installed))
		{
			throw new RuntimeException('Impossible de sécuriser le répertoire d\'installation.');
		}
	}

	public function down($db)
	{
		// The installation marker must remain in place during a rollback.
	}
};

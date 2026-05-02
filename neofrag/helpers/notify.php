<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

function notify($message, $type = 'success')
{
	HiddenCMS()->session->append('notifications', [
		'message' => (string)$message,
		'type'    => get_colors($type) ? $type : 'success'
	]);
}

function notifications()
{
	if ($notifications = HiddenCMS()->session('notifications'))
	{
		foreach ($notifications as $notification)
		{
			HiddenCMS()->js_load('notify(\''.addcslashes($notification['message'], '\'').'\', \''.$notification['type'].'\');');
		}

		HiddenCMS()->session->destroy('notifications');
	}
}

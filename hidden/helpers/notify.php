<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

function notify($message, $type = 'success')
{
	Hidden()->session->append('notifications', [
		'message' => (string)$message,
		'type'    => get_colors($type) ? $type : 'success'
	]);
}

function notifications()
{
	if ($notifications = Hidden()->session('notifications'))
	{
		foreach ($notifications as $notification)
		{
			Hidden()->js_load('notify(\''.addcslashes($notification['message'], '\'').'\', \''.$notification['type'].'\');');
		}

		Hidden()->session->destroy('notifications');
	}
}

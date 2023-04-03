<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

function geolocalisation($address_ip)
{
	if (!is_empty($address_ip))
	{
		Hidden()->js('geolocalisation');
		return '<img src="'.image('ajax-loader.gif').'" style="margin-right: 10px;" data-geolocalisation="'.$address_ip.'" alt="" />';
	}
	else
	{
		return '<img src="'.image('icons/user-silhouette-question.png').'" alt="" />';
	}
}

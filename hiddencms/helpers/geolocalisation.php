<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

function geolocalisation($address_ip)
{
	if (!is_empty($address_ip))
	{
		return '<span class="session-network-icon" title="Adresse réseau">'.icon('fas fa-network-wired').'</span>';
	}
	else
	{
		return '<span class="session-network-icon" title="Adresse inconnue">'.icon('fas fa-question-circle').'</span>';
	}
}

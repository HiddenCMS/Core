<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace HD\Hidden;

interface Loadable
{
	static public function __load($caller, $args);
}

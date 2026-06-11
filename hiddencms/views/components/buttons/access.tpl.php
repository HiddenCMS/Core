<?php
$final_class = trim($class);
echo '<'.$tag.$attrs_except_class.($final_class ? ' class="'.utf8_htmlentities($final_class).'"' : '').'>'.$content.'</'.$tag.'>';
?>

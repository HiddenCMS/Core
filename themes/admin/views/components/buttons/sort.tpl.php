<?php
$final_class = trim('button is-text is-small is-square'.($class ? ' '.$class : ''));
echo '<'.$tag.$attrs_except_class.' class="'.utf8_htmlentities($final_class).'">'.$content.'</'.$tag.'>';
?>

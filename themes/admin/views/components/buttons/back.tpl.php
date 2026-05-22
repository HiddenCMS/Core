<?php
$final_class = trim('button is-light'.($class ? ' '.$class : ''));
echo '<'.$tag.$attrs_except_class.' class="'.utf8_htmlentities($final_class).'">'.$content.'</'.$tag.'>';
?>

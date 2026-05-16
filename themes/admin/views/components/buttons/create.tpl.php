<?php
$final_class = trim('hb-btn hb-btn-primary'.($class ? ' '.$class : ''));
echo '<'.$tag.$attrs_except_class.' class="'.utf8_htmlentities($final_class).'">'.$content.'</'.$tag.'>';
?>

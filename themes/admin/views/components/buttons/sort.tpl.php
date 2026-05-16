<?php
$final_class = trim('hb-btn hb-btn-link hb-btn-outline hb-btn-sm hb-btn-icon'.($class ? ' '.$class : ''));
echo '<'.$tag.$attrs_except_class.' class="'.utf8_htmlentities($final_class).'">'.$content.'</'.$tag.'>';
?>

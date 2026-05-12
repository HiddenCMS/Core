<?php
$final_class = trim((string)$class);
$attrs_output = $attrs_except_class.($final_class ? ' class="'.utf8_htmlentities($final_class).'"' : '');
?>
<?php echo '<'.$tag.$attrs_output.'>'.$content.'</'.$tag.'>' ?>

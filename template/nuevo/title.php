<?php
ob_start();
?>
<hgroup id=content-title>
<h1><?php echo il_get('title')?></h1>
</hgroup>
<?php
return ob_get_clean();
?>

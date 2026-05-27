<?php

/**
 * Vditor 编辑器 
 **/

declare(strict_types=1);

namespace TyporaMdVditor;

defined('ABSPATH') || exit();
?>
<?php do_action('vditor_before'); ?>

<div id="typora-md"></div>
<input type="hidden" id="vditor-ht-content" name="vditor_ht_content">
<input type="hidden" id="vditor-md-content" name="vditor_md_content">

<?php do_action('vditor_after'); ?>
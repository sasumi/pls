<?php

use function LFPhp\PLite\h;
use function LFPhp\PLite\include_page;

include_page('header.inc.php'); ?>

<h1>Page Error</h1>
<p>
	<?=h($exception->getMessage());?>
</p>

<?php
include_page('footer.inc.php'); ?>

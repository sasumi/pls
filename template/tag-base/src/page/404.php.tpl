<?php

use function LFPhp\PLite\include_page;
use function LFPhp\PLite\h;

include_page('header.inc.php'); ?>

<h1>Page Not Found</h1>
<p>
	<?=h($exception->getMessage());?>
</p>

<?php
include_page('footer.inc.php'); ?>

<?php

use function LFPhp\Func\h;
use function LFPhp\PLite\include_page;

/** @var \Exception $exception */
include_page('header.inc.php');
?>
<h1>Page Error</h1>
<p>
	<?=h($exception->getMessage());?>
</p>
<?php include_page('footer.inc.php'); ?>

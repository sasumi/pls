#!/usr/bin/env php

<?php

use LFPhp\Pls\ProjectBuilder;
use function LFPhp\Func\console_color;

include_once dirname(__DIR__, 2).'/autoload.php';

file_put_contents('pls.php', '#!/usr/bin/php 
<?php
include __DIR__."/vendor/lfphp/pls/run.php";
');
ProjectBuilder::addGitIgnore(['/pls.php']);

echo PHP_EOL.console_color(' PLS Installer ', 'white', 'yellow'),PHP_EOL,
"Congratulation!! PLS(Plite Scaffold) installed success.",PHP_EOL,
"Now you can run ./pls.php to maintains your project.",PHP_EOL,PHP_EOL;

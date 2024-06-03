<?php
namespace LFPhp\Pls;

use function LFPhp\Func\console_color;
use function LFPhp\Func\get_all_opt;
use function LFPhp\Func\readline;
use function LFPhp\Func\var_export_min;

/**
 * white php config file (with return statement in file)
 * @param $file
 * @param $config
 * @return void
 */
function write_php_config_file($file, $config){
	$cfg_arr_str = var_export_min($config, true);
	$str = <<<EOT
<?php 
return $cfg_arr_str;
EOT;
	file_put_contents($file, $str);
}

function console_confirm($confirm_msg = ''){
	$opt = get_all_opt();
	if(isset($opt['y'])){
		echo console_color('Msg Auto Confirmed ['.$confirm_msg."]\n", 'brown');
		return true;
	}
	$confirm_msg = $confirm_msg."\nType [y] or [yes] to confirm: ";
	$input = readline(console_color($confirm_msg, 'yellow'));
	$input = trim(strtolower($input));
	return in_array($input, ['y', 'yes']);
}

/**
 * @param $msg
 * @param $trim
 * @return string
 */
function console_read_required($msg, $trim = false){
	while(true){
		$input = readline(console_color($msg, 'yellow'));
		if($trim){
			$input = trim($input);
		}
		if($input){
			return $input;
		}
	}
}

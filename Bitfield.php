<?php

$wgExtensionCredits['validextensionclass'][] = array(
	'name' => 'Bitfield',
	'author' =>'Dave Wilkinson', 
	'url' => 'http://github.com/wilkie/mediawiki-bitfield',
	'description' => 'This extension provides a method of generating a visual description of a bitfield.',
	'version' => 1
);

$dir = dirname(__FILE__);
//require_once($dir."/Bitfield.body.php");
$wgAutoloadClasses['Bitfield'] = $dir."/Bitfield.body.php";

# Define a setup function
$wgHooks['ParserFirstCallInit'][] = 'Bitfield::Setup';
$wgHooks['LanguageGetMagic'][] = 'Bitfield::Magic';

?>

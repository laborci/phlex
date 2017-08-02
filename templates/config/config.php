<?php return [
	'devmode' => true,
	'dbDefault' => 'mysql://{{dbuser}}:{{dbpass}}@{{dbhost}}:{{dbport}}/{{db}}?charset=utf8',
	'thumbnailCrc32Salt' => "t-{{random}}",
	'passwordSalt' => "p-{{random}}",
];
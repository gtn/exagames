<?php

// check php version
if (!version_compare(PHP_VERSION, '5.2.0', '>=')) {
	print_header(get_string('error'));

	echo '<div style="text-align: center; padding-top: 40px;"><a href="http://gophp5.org" title="Support GoPHP5.org">
<img src="http://gophp5.org/sites/gophp5.org/buttons/goPHP5-283x100.png" 
height="100" width="283" alt="Support GoPHP5.org" />
</a></div>';

   	print_error("version_5.2.0_needed", "exabisgames");
}

function exabisgames_normalize_version($version) {
/// Replace everything but numbers and dots by dots
    $version = preg_replace('/[^\.\d]/', '.', $version);
/// Combine multiple dots in one
    $version = preg_replace('/(\.{2,})/', '.', $version);
/// Trim possible leading and trailing dots
    $version = trim($version, '.');

    return $version;
}

// check moodle version
if (!version_compare($CFG->release, '1.9', '>=')) {
	print_header(get_string('error'));

	$params = new StdClass;
	$params->needed = '1.9+';
	$params->current = $CFG->release;
   	print_error("environmentrequireversion", "admin", null, $params);
}

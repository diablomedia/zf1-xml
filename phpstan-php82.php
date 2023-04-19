<?php declare(strict_types = 1);

if (version_compare(PHP_VERSION, '8.2.0', '<')) {
    return array();
}

$ignoreErrors   = array();
$ignoreErrors[] = array(
    'message' => '#^Access to an undefined property DOMNamedNodeMap\\:\\:\\$length\\.$#',
    'count'   => 1,
    'path'    => __DIR__ . '/src/Zend/Xml/Security.php',
);

return array('parameters' => array('ignoreErrors' => $ignoreErrors));

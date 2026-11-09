<?php
require 'vendor/autoload.php';
$in = \Carbon\Carbon::parse('12:11');
$out = \Carbon\Carbon::parse('06:08');
if ($out->lessThan($in)) {
    $out->addDay();
}
echo "Minutes: " . $in->diffInMinutes($out) . "\n";
echo "Hours: " . ($in->diffInMinutes($out) / 60) . "\n";

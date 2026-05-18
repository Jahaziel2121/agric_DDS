<?php
header('Content-Type: text/plain');
echo "FILES IN images/:\n";
$files = scandir(__DIR__ . '/images');
foreach ($files as $f) {
    echo $f . "\n";
}
echo "\nFILES IN uploads/:\n";
$files = scandir(__DIR__ . '/uploads');
foreach ($files as $f) {
    echo $f . "\n";
}

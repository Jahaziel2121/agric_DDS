<?php
$pdo = new PDO("sqlite:" . __DIR__ . "/database.sqlite");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
echo "<h3>Tables in agric_system:</h3><ul>";
foreach($tables as $t) {
    echo "<li><strong>$t</strong></li>";
    $cols = $pdo->query("PRAGMA table_info(`$t`)")->fetchAll(PDO::FETCH_ASSOC);
    echo "<ul>";
    foreach($cols as $c) echo "<li>{$c['name']} — {$c['type']}</li>";
    echo "</ul>";
}
echo "</ul>";
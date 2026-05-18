<?php
$dir = '/Applications/XAMPP/xamppfiles/htdocs/agric_system';
$files = glob($dir . '/*.php');
foreach ($files as $file) {
    if (basename($file) == 'fix_quotes.php' || basename($file) == 'replace_emojis.php') continue;
    $content = file_get_contents($file);
    $original = $content;
    
    // Replace <i class="fas fa-xxx"></i> with <i class='fas fa-xxx'></i>
    $content = preg_replace('/<i class="(fas|far) ([^"]+)"><\/i>/', "<i class='\$1 \$2'></i>", $content);
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "Fixed " . basename($file) . "\n";
    }
}
echo "Done fixing quotes.\n";
?>

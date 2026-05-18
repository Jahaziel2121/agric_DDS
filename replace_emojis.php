<?php
$dir = '/Applications/XAMPP/xamppfiles/htdocs/agric_system';
$files = glob($dir . '/*.php');

$replacements = [
    '🌾' => '<i class="fas fa-wheat-awn"></i>',
    '💬' => '<i class="fas fa-comment-dots"></i>',
    '📦' => '<i class="fas fa-box"></i>',
    '🛒' => '<i class="fas fa-shopping-cart"></i>',
    '💰' => '<i class="fas fa-money-bill-wave"></i>',
    '💲' => '<i class="fas fa-dollar-sign"></i>',
    '👨‍🌾' => '<i class="fas fa-user-tie"></i>',
    '👤' => '<i class="fas fa-user"></i>',
    '📍' => '<i class="fas fa-map-marker-alt"></i>',
    '🔐' => '<i class="fas fa-lock"></i>',
    '🔒' => '<i class="fas fa-lock"></i>',
    '🔔' => '<i class="fas fa-bell"></i>',
    '🚪' => '<i class="fas fa-sign-out-alt"></i>',
    '📊' => '<i class="fas fa-chart-line"></i>',
    '🌱' => '<i class="fas fa-seedling"></i>',
    '⭐' => '<i class="fas fa-star"></i>',
    '💳' => '<i class="fas fa-credit-card"></i>',
    '📥' => '<i class="fas fa-inbox"></i>',
    '✅' => '<i class="fas fa-check-circle"></i>',
    '⬜' => '<i class="far fa-circle"></i>',
    '🔄' => '<i class="fas fa-sync-alt"></i>',
    '⏳' => '<i class="fas fa-hourglass-half"></i>',
    '📞' => '<i class="fas fa-phone-alt"></i>',
    '🎉' => '<i class="fas fa-award"></i>',
    '📭' => '<i class="fas fa-box-open"></i>',
    '🔕' => '<i class="fas fa-bell-slash"></i>',
    '📅' => '<i class="far fa-calendar-alt"></i>',
    '🔍' => '<i class="fas fa-search"></i>',
    '🚜' => '<i class="fas fa-tractor"></i>',
    '🚫' => '<i class="fas fa-ban"></i>',
    '⬅️' => '<i class="fas fa-arrow-left"></i>',
    '👋' => '<i class="fas fa-hand-sparkles"></i>',
    'ℹ️' => '<i class="fas fa-info-circle"></i>',
    '❌' => '<i class="fas fa-times-circle"></i>',
    '⚠️' => '<i class="fas fa-exclamation-triangle"></i>',
    '📱' => '<i class="fas fa-mobile-alt"></i>',
    '📝' => '<i class="fas fa-edit"></i>'
];

foreach ($files as $file) {
    if (basename($file) == 'replace_emojis.php') continue;
    
    $content = file_get_contents($file);
    $original = $content;
    
    foreach ($replacements as $emoji => $icon) {
        $content = str_replace($emoji, $icon, $content);
    }
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "Updated " . basename($file) . "\n";
    }
}
echo "Done.\n";
?>

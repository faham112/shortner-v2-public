<?php
function lang(): string {
    $l = $_GET['lang'] ?? $_SESSION['lang'] ?? 'en';
    if (in_array($l, ['en', 'ur'], true)) $_SESSION['lang'] = $l;
    return $_SESSION['lang'] ?? 'en';
}
function t(string $key): string {
    $dict = [
        'en' => ['app'=>'Shortner','login'=>'Sign in','email'=>'Email','password'=>'Password','remember'=>'Remember me','dashboard'=>'Dashboard','links'=>'Links','users'=>'Users','settings'=>'Settings','analytics'=>'Analytics','license'=>'License','logout'=>'Logout','create_link'=>'Create link','destination'=>'Destination URL','custom_code'=>'Custom code','preview'=>'Link preview','waste_url'=>'Android Chrome dump URL','save'=>'Save','clicks'=>'Clicks','waste'=>'Waste clicks','active'=>'Active'],
        'ur' => ['app'=>'شارٹنر','login'=>'لاگ ان','email'=>'ای میل','password'=>'پاس ورڈ','remember'=>'مجھے یاد رکھیں','dashboard'=>'ڈیش بورڈ','links'=>'لنکس','users'=>'یوزرز','settings'=>'سیٹنگز','analytics'=>'اینالیٹکس','license'=>'لائسنس','logout'=>'لاگ آؤٹ','create_link'=>'لنک بنائیں','destination'=>'اصل لنک','custom_code'=>'کسٹم کوڈ','preview'=>'لنک پریویو','waste_url'=>'اینڈرائیڈ کروم ڈمپ یو آر ایل','save'=>'محفوظ','clicks'=>'کلکس','waste'=>'ویسٹ کلکس','active'=>'ایکٹیو'],
    ];
    $l = lang();
    return $dict[$l][$key] ?? $dict['en'][$key] ?? $key;
}

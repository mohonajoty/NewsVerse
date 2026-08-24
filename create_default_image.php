<?php
// Create default image using GD library
function createDefaultImage() {
    $width = 800;
    $height = 600;
    
    // Create image
    $image = imagecreatetruecolor($width, $height);
    
    // Colors
    $color1 = imagecolorallocate($image, 102, 126, 234); // #667eea
    $color2 = imagecolorallocate($image, 118, 75, 162);  // #764ba2
    $white = imagecolorallocate($image, 255, 255, 255);
    $gray = imagecolorallocate($image, 200, 200, 200);
    $dark = imagecolorallocate($image, 50, 50, 80);
    
    // Gradient background
    for ($i = 0; $i < $height; $i++) {
        $ratio = $i / $height;
        $r = 102 - ($ratio * 20);
        $g = 126 - ($ratio * 51);
        $b = 234 - ($ratio * 72);
        $color = imagecolorallocate($image, $r, $g, $b);
        imageline($image, 0, $i, $width, $i, $color);
    }
    
    // Add decorative circles
    for ($i = 0; $i < 5; $i++) {
        $circle_color = imagecolorallocate($image, 
            rand(200, 255), 
            rand(200, 255), 
            rand(200, 255)
        );
        imagefilledellipse($image, 
            rand(0, $width), 
            rand(0, $height), 
            rand(100, 300), 
            rand(100, 300), 
            $circle_color
        );
    }
    
    // Add newspaper icon (using a simple representation)
    $icon = "📰";
    $font = 5;
    $font_width = imagefontwidth($font);
    $font_height = imagefontheight($font);
    
    // Draw a simple newspaper icon
    $x = ($width / 2) - 40;
    $y = ($height / 2) - 60;
    
    // Draw rectangle as newspaper
    imagefilledrectangle($image, $x, $y, $x + 80, $y + 60, $white);
    imagerectangle($image, $x, $y, $x + 80, $y + 60, $gray);
    
    // Draw lines as text
    for ($i = 0; $i < 4; $i++) {
        $line_y = $y + 10 + ($i * 12);
        imageline($image, $x + 5, $line_y, $x + 75, $line_y, $dark);
    }
    
    // Add "NewsVerse" text
    $text = "NewsVerse";
    $text_x = ($width - (strlen($text) * $font_width)) / 2;
    $text_y = ($height / 2) + 60;
    
    // Shadow
    imagestring($image, $font, $text_x + 2, $text_y + 2, $text, $gray);
    // Main text
    imagestring($image, $font, $text_x, $text_y, $text, $white);
    
    // Add "Smart Digital Media" subtitle
    $subtitle = "Smart Digital Media";
    $sub_font = 3;
    $sub_width = strlen($subtitle) * imagefontwidth($sub_font);
    $sub_x = ($width - $sub_width) / 2;
    $sub_y = $text_y + 30;
    imagestring($image, $sub_font, $sub_x, $sub_y, $subtitle, $white);
    
    // Save image
    imagejpeg($image, 'uploads/default.jpg', 90);
    imagedestroy($image);
    
    echo "✅ Default image created successfully at uploads/default.jpg\n";
    echo "📐 Image size: 800x600\n";
    echo "🎨 Created with PHP GD Library\n";
}

// Check if GD extension is loaded
if (!extension_loaded('gd')) {
    echo "❌ GD extension is not loaded. Installing...\n";
    echo "Please run: sudo apt-get install php8.3-gd\n";
    exit(1);
}

// Create uploads directory if it doesn't exist
if (!is_dir('uploads')) {
    mkdir('uploads', 0777, true);
    echo "📁 Created uploads directory\n";
}

// Create the image
createDefaultImage();

// Also create a smaller version for thumbnails
function createThumbnail() {
    $width = 400;
    $height = 300;
    $image = imagecreatetruecolor($width, $height);
    
    $bg = imagecolorallocate($image, 102, 126, 234);
    imagefilledrectangle($image, 0, 0, $width, $height, $bg);
    
    $white = imagecolorallocate($image, 255, 255, 255);
    $gray = imagecolorallocate($image, 200, 200, 200);
    
    // Add text
    $text = "NewsVerse";
    $font = 4;
    $font_width = imagefontwidth($font);
    $text_width = strlen($text) * $font_width;
    $x = ($width - $text_width) / 2;
    $y = ($height - imagefontheight($font)) / 2;
    
    imagestring($image, $font, $x + 1, $y + 1, $text, $gray);
    imagestring($image, $font, $x, $y, $text, $white);
    
    imagejpeg($image, 'uploads/default-thumb.jpg', 85);
    imagedestroy($image);
}

createThumbnail();
echo "✅ Thumbnail created at uploads/default-thumb.jpg\n";
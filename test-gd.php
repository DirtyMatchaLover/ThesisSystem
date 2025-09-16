<?php
// test-gd.php - Save this file in your project root
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GD Extension Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .test-image { margin: 20px 0; padding: 20px; background: #f9f9f9; border: 1px dashed #ccc; }
    </style>
</head>
<body>
    <h1>🎨 GD Extension Test for Thesis Management System</h1>
    
    <?php
    // Test 1: Check if GD extension is loaded
    echo "<h2>Test 1: GD Extension Status</h2>";
    if (extension_loaded('gd')) {
        echo "<p class='success'>✅ SUCCESS: GD Extension is loaded and ready!</p>";
        
        // Get GD information
        $gd_info = gd_info();
        echo "<table>";
        echo "<tr><th>GD Feature</th><th>Status</th></tr>";
        foreach ($gd_info as $feature => $status) {
            $status_text = is_bool($status) ? ($status ? 'Yes' : 'No') : $status;
            echo "<tr><td>$feature</td><td>$status_text</td></tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p class='error'>❌ ERROR: GD Extension is NOT loaded!</p>";
        echo "<p>Please follow the installation instructions above.</p>";
        exit;
    }

    // Test 2: Test basic image creation
    echo "<h2>Test 2: Image Creation Test</h2>";
    $test_image = @imagecreate(200, 100);
    if ($test_image) {
        echo "<p class='success'>✅ SUCCESS: Basic image creation works!</p>";
        
        // Create a simple test image
        $bg_color = imagecolorallocate($test_image, 240, 240, 240);
        $text_color = imagecolorallocate($test_image, 50, 50, 50);
        $border_color = imagecolorallocate($test_image, 100, 150, 200);
        
        // Fill background
        imagefill($test_image, 0, 0, $bg_color);
        
        // Draw border
        imagerectangle($test_image, 0, 0, 199, 99, $border_color);
        
        // Add text
        imagestring($test_image, 3, 60, 40, "GD Works!", $text_color);
        
        // Output image
        echo "<div class='test-image'>";
        echo "<h3>Generated Test Image:</h3>";
        
        // Convert to base64 for display
        ob_start();
        imagepng($test_image);
        $image_data = ob_get_contents();
        ob_end_clean();
        
        echo "<img src='data:image/png;base64," . base64_encode($image_data) . "' alt='Test Image' style='border: 1px solid #ccc;'>";
        echo "</div>";
        
        // Clean up
        imagedestroy($test_image);
        
    } else {
        echo "<p class='error'>❌ ERROR: Cannot create images!</p>";
    }

    // Test 3: Check image format support
    echo "<h2>Test 3: Image Format Support</h2>";
    $formats = [
        'JPEG' => 'imagetypes() & IMG_JPG',
        'PNG' => 'imagetypes() & IMG_PNG', 
        'GIF' => 'imagetypes() & IMG_GIF',
        'WebP' => 'imagetypes() & IMG_WEBP'
    ];

    echo "<table>";
    echo "<tr><th>Format</th><th>Supported</th><th>Read</th><th>Write</th></tr>";
    
    foreach ($formats as $format => $check) {
        $supported = eval("return $check;");
        $read_func = "imagecreatefrom" . strtolower($format);
        $write_func = "image" . strtolower($format);
        
        $can_read = function_exists($read_func) ? "Yes" : "No";
        $can_write = function_exists($write_func) ? "Yes" : "No";
        
        echo "<tr>";
        echo "<td>$format</td>";
        echo "<td>" . ($supported ? "✅ Yes" : "❌ No") . "</td>";
        echo "<td>$can_read</td>";
        echo "<td>$can_write</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Test 4: Memory and execution settings
    echo "<h2>Test 4: PHP Settings for Image Processing</h2>";
    $settings = [
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size')
    ];

    echo "<table>";
    echo "<tr><th>Setting</th><th>Current Value</th><th>Recommended</th></tr>";
    
    foreach ($settings as $setting => $value) {
        $recommended = '';
        switch ($setting) {
            case 'memory_limit':
                $recommended = '>= 128M for image processing';
                break;
            case 'max_execution_time':
                $recommended = '>= 30 seconds';
                break;
            case 'upload_max_filesize':
                $recommended = '>= 10M for thesis PDFs';
                break;
            case 'post_max_size':
                $recommended = '>= 12M (larger than upload_max)';
                break;
        }
        
        echo "<tr>";
        echo "<td>$setting</td>";
        echo "<td><strong>$value</strong></td>";
        echo "<td>$recommended</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Final status
    echo "<h2>🎯 Final Status</h2>";
    if (extension_loaded('gd')) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h3 style='color: #155724; margin-top: 0;'>🎉 GD Extension is Ready!</h3>";
        echo "<p style='color: #155724; margin-bottom: 0;'>Your PHP installation can now handle image processing for the thesis management system. You can proceed with file uploads and image generation features.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h3 style='color: #721c24; margin-top: 0;'>⚠️ GD Extension Required</h3>";
        echo "<p style='color: #721c24; margin-bottom: 0;'>Please install the GD extension following the instructions above, then refresh this page.</p>";
        echo "</div>";
    }
    ?>

    <hr style="margin: 30px 0;">
    <p><small>
        <strong>Instructions:</strong><br>
        1. Save this file as <code>test-gd.php</code> in your project root<br>
        2. Access it via: <code>http://localhost/your-project/test-gd.php</code><br>
        3. If GD is missing, follow the installation steps above<br>
        4. Delete this file after testing for security
    </small></p>
</body>
</html>
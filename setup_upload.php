<?php
// setup_uploads.php
// Run this script to ensure upload directories exist with proper permissions

echo "<h2>Setting Up Upload Directories</h2>";

$directories = [
    __DIR__ . '/uploads',
    __DIR__ . '/uploads/theses',
    __DIR__ . '/uploads/pending',
    __DIR__ . '/uploads/approved',
    __DIR__ . '/uploads/temp'
];

foreach ($directories as $dir) {
    echo "<p>Checking directory: " . $dir . "</p>";
    
    if (!is_dir($dir)) {
        if (mkdir($dir, 0777, true)) {
            echo "<span style='color: green;'>✓ Created directory: " . $dir . "</span><br>";
        } else {
            echo "<span style='color: red;'>✗ Failed to create directory: " . $dir . "</span><br>";
        }
    } else {
        echo "<span style='color: blue;'>ℹ Directory already exists: " . $dir . "</span><br>";
    }
    
    // Check if writable
    if (is_writable($dir)) {
        echo "<span style='color: green;'>✓ Directory is writable</span><br>";
    } else {
        // Try to make it writable
        if (chmod($dir, 0777)) {
            echo "<span style='color: green;'>✓ Made directory writable</span><br>";
        } else {
            echo "<span style='color: red;'>✗ Directory is not writable. Please set permissions manually.</span><br>";
        }
    }
    echo "<hr>";
}

// Create .htaccess file to protect uploads
$htaccess_content = "# Protect uploaded files
<FilesMatch '\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|sh|cgi)$'>
    Order Deny,Allow
    Deny from all
</FilesMatch>

# Allow PDF files
<FilesMatch '\.pdf$'>
    Order Allow,Deny
    Allow from all
</FilesMatch>

Options -Indexes";

$htaccess_path = __DIR__ . '/uploads/.htaccess';
if (file_put_contents($htaccess_path, $htaccess_content)) {
    echo "<span style='color: green;'>✓ Created .htaccess security file</span><br>";
} else {
    echo "<span style='color: red;'>✗ Could not create .htaccess file</span><br>";
}

echo "<hr>";
echo "<h3>Setup Complete!</h3>";
echo "<p>If you see any red error messages above, please:</p>";
echo "<ol>";
echo "<li>Ensure the web server user (www-data, apache, or nginx) has write permissions to the project directory</li>";
echo "<li>On Linux/Mac, run: <code>sudo chmod -R 777 uploads/</code> from the project root</li>";
echo "<li>On Windows, right-click the uploads folder → Properties → Security → Edit → Give 'Everyone' full control</li>";
echo "</ol>";
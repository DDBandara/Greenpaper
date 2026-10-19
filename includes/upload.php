<?php
// Image upload helpers. Include with: require_once __DIR__ . '/includes/upload.php';
//
// Example use in an "add article" page:
//
//   $error = '';
//   $imageName = saveUploadedImage('image', $error);      // 'image' = name of the <input type="file">
//   if ($error !== '') { /* show $error to the user */ }
//   // then save $imageName (or NULL if no file was chosen) in the articles.image column
//
// The form must be:  <form method="post" enctype="multipart/form-data"> ... <input type="file" name="image" accept="image/*">

// Saves the uploaded image into the "uploads" folder.
// Returns the NEW file name (for example "9f3a1c7e5b2d4a60.jpg"), or null if nothing was saved.
// If something is wrong, $error gets a message for the user.
function saveUploadedImage($field, &$error = '') {
    $maxBytes = 2 * 1024 * 1024;                                    // 2 MB
    $allowed  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;                                                // no file chosen: that is fine, the image is optional
    }
    $f = $_FILES[$field];

    if ($f['error'] !== UPLOAD_ERR_OK) { $error = 'The upload failed. Please try again.'; return null; }
    if ($f['size'] > $maxBytes)        { $error = 'The image must be 2 MB or smaller.';   return null; }

    // Check what the file REALLY is. Never trust the name or the type the browser reports.
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!isset($allowed[$mime]) || @getimagesize($f['tmp_name']) === false) {
        $error = 'Only JPG, PNG or WebP images are allowed.';
        return null;
    }

    // Make up our own random file name. The visitor's file name is never used.
    $name = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    $dir  = dirname(__DIR__) . '/uploads';
    if (!is_dir($dir)) { mkdir($dir, 0755, true); }

    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) {
        $error = 'The image could not be saved.';
        return null;
    }
    return $name;
}

// Deletes an image file (use when an article is deleted or its picture is replaced).
function deleteImage($name) {
    if ($name && preg_match('/^[a-f0-9]{16}\.(jpg|png|webp)$/', $name)) {   // only names this system created
        @unlink(dirname(__DIR__) . '/uploads/' . $name);
    }
}
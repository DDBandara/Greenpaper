<?php

function saveImage($field, &$error = '') {
    $maxSize = 2 * 1024 * 1024;
    $types  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $f = $_FILES[$field];

    if ($f['error'] !== UPLOAD_ERR_OK) { $error = 'The upload failed. Please try again.'; return null; }
    if ($f['size'] > $maxSize)        { $error = 'The image must be 2 MB or smaller.';   return null; }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!isset($types[$mime]) || @getimagesize($f['tmp_name']) === false) {
        $error = 'Only JPG, PNG or WebP images are allowed.';
        return null;
    }

    $name = bin2hex(random_bytes(8)) . '.' . $types[$mime];
    $dir  = dirname(__DIR__) . '/uploads';
    if (!is_dir($dir)) { mkdir($dir, 0755, true); }

    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) {
        $error = 'The image could not be saved.';
        return null;
    }
    return $name;
}

function removeImage($name) {
    if ($name && preg_match('/^[a-f0-9]{16}\.(jpg|png|webp)$/', $name)) {
        @unlink(dirname(__DIR__) . '/uploads/' . $name);
    }
}
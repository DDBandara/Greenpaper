<?php

function resizeImage($path, $mime, $maxWidth = 1600) {
    if (!function_exists('imagecreatetruecolor')) { return; }
    $info = @getimagesize($path);
    if (!$info || $info[0] <= $maxWidth || $info[0] * $info[1] > 40000000) { return; }

    $loaders = ['image/jpeg' => 'imagecreatefromjpeg', 'image/png' => 'imagecreatefrompng', 'image/webp' => 'imagecreatefromwebp'];
    $loader = $loaders[$mime] ?? null;
    if (!$loader || !function_exists($loader)) { return; }

    $src = @$loader($path);
    if (!$src) { return; }

    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($path);
        $angle = [3 => 180, 6 => -90, 8 => 90][$exif['Orientation'] ?? 1] ?? 0;
        if ($angle) {
            $rotated = imagerotate($src, $angle, 0);
            if ($rotated) { imagedestroy($src); $src = $rotated; }
        }
    }

    $w = imagesx($src);
    $h = imagesy($src);
    if ($w > $maxWidth) {
        $newW = $maxWidth;
        $newH = (int)round($h * $maxWidth / $w);
        $dst = imagecreatetruecolor($newW, $newH);
        if ($mime !== 'image/jpeg') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
        imagedestroy($src);
        $src = $dst;
    }

    if ($mime === 'image/jpeg')      { imagejpeg($src, $path, 82); }
    elseif ($mime === 'image/png')   { imagepng($src, $path, 6); }
    else                             { imagewebp($src, $path, 80); }
    imagedestroy($src);
}

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
    resizeImage($dir . '/' . $name, $mime);
    return $name;
}

function removeImage($name) {
    if ($name && preg_match('/^[a-f0-9]{16}\.(jpg|png|webp)$/', $name)) {
        @unlink(dirname(__DIR__) . '/uploads/' . $name);
    }
}
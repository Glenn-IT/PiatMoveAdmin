<?php
/**
 * Validates and stores an uploaded file, returning a relative path to save in the DB.
 *
 * @param string   $field    $_FILES key
 * @param string   $subdir   subdirectory under uploads/ to store the file in
 * @param string[] $allowExt allowed lowercase extensions
 * @return array{0: bool, 1: string} [ok, relative_path_or_error_message]
 */
function handle_proof_upload(string $field, string $subdir, array $allowExt = ['jpg', 'jpeg', 'png', 'pdf']): array {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return [false, 'This file is required.'];
    }

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [false, 'Upload failed. Please try again.'];
    }

    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return [false, 'File is too large (max 5MB).'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowExt, true)) {
        return [false, 'Invalid file type. Allowed: ' . implode(', ', $allowExt) . '.'];
    }

    $uploadRoot = __DIR__ . '/../uploads/' . trim($subdir, '/');
    if (!is_dir($uploadRoot) && !mkdir($uploadRoot, 0755, true) && !is_dir($uploadRoot)) {
        return [false, 'Could not create upload directory.'];
    }

    $filename = bin2hex(random_bytes(8)) . '.' . $ext;
    $destination = $uploadRoot . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return [false, 'Could not save uploaded file.'];
    }

    return [true, 'uploads/' . trim($subdir, '/') . '/' . $filename];
}

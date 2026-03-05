<?php
/**
 * Handles file uploads with basic validation.
 * @param array $file The $_FILES element.
 * @param string $targetDir Directory to save the file.
 * @param array $allowedExtensions Allowed file extensions.
 * @param string $prefix Optional prefix for the filename.
 * @return array ['success' => bool, 'message' => string, 'filename' => string|null]
 */
function uploadFile($file, $targetDir, $allowedExtensions = ['pdf'], $prefix = '') {
    if (!isset($file) || $file['error'] !== 0) {
        return ['success' => false, 'message' => 'Invalid file or upload error.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions)) {
        return ['success' => false, 'message' => 'File type not allowed. Allowed: ' . implode(', ', $allowedExtensions)];
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $filename = ($prefix ? $prefix . '-' : '') . date('YmdHis') . '.' . $ext;
    $targetPath = rtrim($targetDir, '/') . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'message' => 'Upload successful.', 'filename' => $filename];
    }

    return ['success' => false, 'message' => 'Failed to move uploaded file.'];
}

/**
 * Generates a consistent filename based on employee code.
 * @param string $empCode
 * @return string
 */
function generateEmployeeDocName($empCode) {
    return $empCode . '-' . date('YmdHis');
}

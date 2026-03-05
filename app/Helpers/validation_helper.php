<?php
/**
 * Cleans input data to prevent XSS and other issues.
 * @param mixed $data
 * @return mixed
 */
function cleanInput($data) {
    if (is_array($data)) {
        return array_map('cleanInput', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validates if an array of fields are present and not empty in given data.
 * @param array $data Typically $_POST or $_GET.
 * @param array $requiredFields List of required keys.
 * @return bool
 */
function validateRequired($data, $requiredFields) {
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            return false;
        }
    }
    return true;
}

/**
 * Validates an email address.
 * @param string $email
 * @return bool
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

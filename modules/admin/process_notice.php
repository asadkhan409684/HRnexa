<?php
// modules/admin/process_notice.php
require_once('../../app/Config/database.php');
session_start();

// Auth check (1 for Super Admin, 2 for Admin)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'] ?? $_SESSION['user_role'], [1, 2])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$action = $_REQUEST['action'] ?? '';

if (in_array($action, ['add', 'edit', 'delete'])) {
    if (!isset($_POST['csrf_token']) && !isset($_GET['csrf_token'])) {
        die(json_encode(['status' => 'error', 'message' => 'CSRF token missing']));
    }
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'];
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        die(json_encode(['status' => 'error', 'message' => 'CSRF token validation failed']));
    }
}

if ($action == 'add' || $action == 'edit') {
    $id = $_POST['id'] ?? null;
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $category = $_POST['category'] ?? 'update';
    $footer_label = $_POST['footer_label'] ?? '';
    $footer_value = $_POST['footer_value'] ?? '';
    $button_text = $_POST['button_text'] ?? 'Read More';
    $button_link = $_POST['button_link'] ?? '#';
    $status = $_POST['status'] ?? 'active';

    if ($action == 'add') {
        $stmt = $conn->prepare("INSERT INTO notices (title, content, category, footer_label, footer_value, button_text, button_link, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $title, $content, $category, $footer_label, $footer_value, $button_text, $button_link, $status);
    } else {
        $stmt = $conn->prepare("UPDATE notices SET title=?, content=?, category=?, footer_label=?, footer_value=?, button_text=?, button_link=?, status=? WHERE id=?");
        $stmt->bind_param("ssssssssi", $title, $content, $category, $footer_label, $footer_value, $button_text, $button_link, $status, $id);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Notice saved successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    exit();
}

if ($action == 'get') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $conn->prepare("SELECT * FROM notices WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($notice = $result->fetch_assoc()) {
            echo json_encode(['status' => 'success', 'notice' => $notice]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Notice not found']);
        }
    }
    exit();
}

if ($action == 'delete') {
    $id = $_POST['id'] ?? null;
    if ($id) {
        $stmt = $conn->prepare("DELETE FROM notices WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Notice deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
    }
    exit();
}

if (!$action) {
    echo json_encode(['status' => 'error', 'message' => 'No action specified']);
}
?>

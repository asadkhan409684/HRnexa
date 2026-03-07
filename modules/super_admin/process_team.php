<?php
// modules/super_admin/process_team.php
require_once('../../app/Config/database.php');
session_start();

// Auth check
if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] ?? $_SESSION['user_role']) != 1) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$action = $_REQUEST['action'] ?? '';

if ($action == 'add' || $action == 'edit') {
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? '';
    $designation = $_POST['designation'] ?? '';
    $email = $_POST['email'] ?? '';
    $linkedin = $_POST['linkedin'] ?? '';
    $twitter = $_POST['twitter'] ?? '';
    $instagram = $_POST['instagram'] ?? '';
    $status = $_POST['status'] ?? 'active';
    $sort_order = $_POST['sort_order'] ?? 0;

    $image_path = $_POST['existing_image'] ?? '';

    // Handle Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../../Upload/team/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $file_name = time() . "_" . uniqid() . "." . $file_ext;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = "Upload/team/" . $file_name;
        }
    }

    if ($action == 'add') {
        $stmt = $conn->prepare("INSERT INTO team_members (name, designation, email, linkedin, twitter, instagram, status, sort_order, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssis", $name, $designation, $email, $linkedin, $twitter, $instagram, $status, $sort_order, $image_path);
    } else {
        $stmt = $conn->prepare("UPDATE team_members SET name=?, designation=?, email=?, linkedin=?, twitter=?, instagram=?, status=?, sort_order=?, image_path=? WHERE id=?");
        $stmt->bind_param("sssssssisi", $name, $designation, $email, $linkedin, $twitter, $instagram, $status, $sort_order, $image_path, $id);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Team member saved successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
}

if ($action == 'get') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $conn->prepare("SELECT * FROM team_members WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($member = $result->fetch_assoc()) {
            echo json_encode(['status' => 'success', 'member' => $member]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Member not found']);
        }
    }
    exit();
}

if ($action == 'delete') {
    $id = $_POST['id'] ?? null;
    if ($id) {
        $stmt = $conn->prepare("DELETE FROM team_members WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Member deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
    }
}

if (!$action) {
    echo json_encode(['status' => 'error', 'message' => 'No action specified']);
}
?>

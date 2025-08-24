<?php
// parent_care_doctors_admin_delete.php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit; }

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && is_numeric($_POST['id'])) {
    $id = (int)$_POST['id'];

    // get photo
    $stmt = $conn->prepare("SELECT photo FROM Parent_portal_doctors WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $doc = $res->fetch_assoc();
    $stmt->close();

    // delete
    $stmt = $conn->prepare("DELETE FROM Parent_portal_doctors WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        if ($doc && $doc['photo'] && file_exists($doc['photo'])) unlink($doc['photo']);
    }
    $stmt->close();
}

header("Location: parent_care_doctors_admin.php");
exit;

<?php
//session_start();

require_once "./connectdb.php";

function getMember($username) {
    if (empty($username) || $username == "admin") {
        return NULL;
    } else {
        $sql = "SELECT memberID FROM Logins WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = $username;
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($memberID);
                    return $memberID;
                } else {
                    return NULL;
                }
            } else {
                return NULL;
            }
        } else {
            return NULL;
        }
    }
}

function checkPermissions($username, $permission) {
    $permission_id = 0;
    if (empty($username)) {
        return false;
    } else if ($username == "admin") {
        return true;
    } else {
        $sql = "SELECT permissionID FROM Permissions WHERE title = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_title);
            $param_title = $permission;
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($permission_id);
                }
            }
            $stmt->close();
        }
        if (empty($permission_id)) {
            return false;
        }

        $memberID = getMember($username);
        if (empty($memberID)) {
            return false;
        } else {
            $sql = "SELECT jobID FROM MembersJobs WHERE memberID = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("i", $param_member_ID);
                $param_member_ID = $memberID;
                if ($stmt->execute()) {
                    $stmt->store_result();
                    $foundPermission = false;
                    while ($row = $stmt->fetch_assoc()) {
                        $sql = "SELECT permissionID FROM JobsPermissions WHERE jobID = ? AND permissionID = ?";
                        if ($stmt2 = $conn->prepare($sql)) {
                            $stmt2->bind_param("ii", $param_job_ID, $param_perm_ID);
                            $param_job_ID = $row["jobID"];
                            $param_perm_ID = $permission_id;
                            if ($stmt2->execute()) {
                                $stmt2->store_result();
                                if ($stmt2->num_rows > 0) {
                                    $foundPermission = true;
                                    break;
                                }
                            }
                            $stmt2->close();
                        }
                    }
                    return $foundPermission;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }
}

function hasPermission($permission) {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != true) {
        return false;
    } else {
        return checkPermissions($_SESSION["username"], $permission);
    }
}
?>
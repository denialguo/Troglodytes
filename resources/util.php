<?php
require_once "connectdb.php";

function getMember($c, $username) {
    if (empty($username)) {
        return NULL;
    } else if ($username == "admin") {
        return 1;
    } else {
        $sql = "SELECT memberID FROM Logins WHERE username = ?";
        if ($stmt = $c->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = $username;
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($memberID);
                    if ($stmt->fetch()) {
                        $sql = "SELECT id FROM Members WHERE id = ?";
                        if ($stmt2 = $c->prepare($sql)) {
                            $stmt2->bind_param("i", $param_memberID);
                            $param_memberID = $memberID;
                            if ($stmt2->execute()) {
                                $stmt2->store_result();
                                if ($stmt2->num_rows == 1) {
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
                    } else {
                        return NULL;
                    }
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

function checkPermissions($c, $username, $permission) {
    $permission_id = 0;
    if (empty($username)) {
        return false;
    } else if ($username == "admin") {
        return true;
    } else {
        $sql = "SELECT id FROM Permissions WHERE title = ?";
        if ($stmt = $c->prepare($sql)) {
            $stmt->bind_param("s", $param_title);
            $param_title = $permission;
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($permission_id);
                    $stmt->fetch();
                }
            }
            $stmt->close();
        }
        if (empty($permission_id)) {
            return false;
        }

        $memberID = getMember($c, $username);
        if (empty($memberID)) {
            return false;
        } else {
            $sql = "SELECT jobID FROM MembersJobs WHERE memberID = ?";
            if ($stmt = $c->prepare($sql)) {
                $stmt->bind_param("i", $param_member_ID);
                $param_member_ID = $memberID;
                if ($stmt->execute()) {
                    $stmt->store_result();
                    $stmt->bind_result($j);
                    $foundPermission = false;
                    while ($stmt->fetch()) {
                        $sql = "SELECT permissionID FROM JobsPermissions WHERE jobID = ? AND (permissionID = 2 OR permissionID = ?)"; // Permission ID 2 is administrator
                        if ($stmt2 = $c->prepare($sql)) {
                            $stmt2->bind_param("ii", $param_job_ID, $param_perm_ID);
                            $param_job_ID = $j;
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

function hasPermission($c, $permission) {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != true) {
        return false;
    } else {
        return checkPermissions($c, $_SESSION["username"], $permission);
    }
}

function getActionID($c, $action) {
    if (empty($action)) {
        return NULL;
    }
    $sql = "SELECT id FROM Actions WHERE title = ?";
    if ($stmt = $c->prepare($sql)) {
        $stmt->bind_param("s", $param_action);
        $param_action = $action;
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows == 1) {
                $stmt->bind_result($actionID);
                if ($stmt->fetch()) {
                    return $actionID;
                } else {
                    return NULL;
                }
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

function createLog($c, $memberID, $affectedMemberID, $action, $description) {
    $sql = "INSERT INTO Logs (memberID, affectedMemberID, actionID, description) VALUES (?, ?, ?, ?);";
    if ($stmt = $c->prepare($sql)) {
        $stmt->bind_param("iiis", $param_memberID, $param_affectedMemberID, $param_actionID, $param_description);
        $param_memberID = $memberID;
        $param_affectedMemberID = $affectedMemberID;
        $param_actionID = getActionID($c, $action);
        $param_description = $description;
        $stmt->execute();
        $stmt->close();
    }
}

?>
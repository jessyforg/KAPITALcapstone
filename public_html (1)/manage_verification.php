    <?php
    session_start();
    include('db_connection.php');

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: sign_in.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Check if action is set
    if (!isset($_POST['action'])) {
        $_SESSION['error_message'] = "Invalid request.";
        header("Location: verify_account.php");
        exit;
    }

    $action = $_POST['action'];

    // Common validation function
    function validateDocument($file) {
        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowed_types)) {
            return "Invalid file type. Please upload PDF, JPEG, or PNG files only.";
        }

        if ($file['size'] > $max_size) {
            return "File size too large. Maximum size is 5MB.";
        }

        return null;
    }

    // Handle document upload
    function handleDocumentUpload($file, $user_id) {
        global $conn;
        
        $upload_dir = 'uploads/verification_documents/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Get file extension
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        // Create a more consistent filename that includes the user_id and timestamp
        $timestamp = time();
        $file_name = "doc_{$user_id}_{$timestamp}.{$file_extension}";
        $file_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            return [
                'success' => true,
                'file_name' => $file_name,
                'file_path' => $file_path
            ];
        }
        
        return [
            'success' => false,
            'error' => "Failed to upload file."
        ];
    }

    // Function to update user verification status based on document statuses
    function updateUserVerificationStatus($user_id) {
        global $conn;
        
        // First check if there are any approved documents
        $check_approved = "SELECT COUNT(*) as approved_count 
                        FROM Verification_Documents 
                        WHERE user_id = ? AND status = 'approved'";
        
        $stmt = $conn->prepare($check_approved);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $approved_count = $result->fetch_assoc()['approved_count'];
        
        if ($approved_count > 0) {
            // If there is at least one approved document, set status to verified
            $new_status = 'verified';
        } else {
            // Check if there are any pending documents
            $check_pending = "SELECT COUNT(*) as pending_count 
                            FROM Verification_Documents 
                            WHERE user_id = ? AND status = 'pending'";
            
            $stmt = $conn->prepare($check_pending);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $pending_count = $result->fetch_assoc()['pending_count'];
            
            if ($pending_count > 0) {
                // If there are pending documents, set status to pending
                $new_status = 'pending';
            } else {
                // Check if there are any not approved documents
                $check_not_approved = "SELECT COUNT(*) as not_approved_count 
                                    FROM Verification_Documents 
                                    WHERE user_id = ? AND status = 'not approved'";
                
                $stmt = $conn->prepare($check_not_approved);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $not_approved_count = $result->fetch_assoc()['not_approved_count'];
                
                if ($not_approved_count > 0) {
                    // If there are not approved documents, set status to not approved
                    $new_status = 'not approved';
                } else {
                    // If no documents at all, default to pending
                    $new_status = 'pending';
                }
            }
        }
        
        // Update the user's verification status
        $update_user = "UPDATE Users SET verification_status = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_user);
        $stmt->bind_param("si", $new_status, $user_id);
        return $stmt->execute();
    }

    // Check if this is an AJAX request
    $is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    switch ($action) {
        case 'apply':
            // Handle initial document submission
            if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Please upload a document.']);
                    exit;
                } else {
                    $_SESSION['error_message'] = 'Please upload a document.';
                    header("Location: verify_account.php");
                    exit;
                }
            }

            $file = $_FILES['document'];
            $error = validateDocument($file);
            
            if ($error) {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $error]);
                    exit;
                } else {
                    $_SESSION['error_message'] = $error;
                    header("Location: verify_account.php");
                    exit;
                }
            }
            
            // Handle document upload
            $upload_result = handleDocumentUpload($file, $user_id);
            if (!$upload_result['success']) {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $upload_result['error']]);
                    exit;
                } else {
                    $_SESSION['error_message'] = $upload_result['error'];
                    header("Location: verify_account.php");
                    exit;
                }
            }
            
            // Insert new document record
            $document_type = mysqli_real_escape_string($conn, $_POST['document_type']);
            $document_number = mysqli_real_escape_string($conn, $_POST['document_number']);
            $issue_date = !empty($_POST['issue_date']) ? $_POST['issue_date'] : NULL;
            $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : NULL;
            $issuing_authority = mysqli_real_escape_string($conn, $_POST['issuing_authority']);
            
            $insert_query = "INSERT INTO Verification_Documents (
                user_id,
                document_type,
                document_number,
                issue_date,
                expiry_date,
                issuing_authority,
                file_name,
                file_path,
                file_type,
                file_size,
                status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
            
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param(
                "issssssssi",
                $user_id,
                $document_type,
                $document_number,
                $issue_date,
                $expiry_date,
                $issuing_authority,
                $upload_result['file_name'],
                $upload_result['file_path'],
                $file['type'],
                $file['size']
            );
            
            if ($stmt->execute()) {
                updateUserVerificationStatus($user_id);
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Document submitted successfully. Your verification is now pending review.']);
                    exit;
                } else {
                    $_SESSION['success_message'] = 'Document submitted successfully. Your verification is now pending review.';
                    header("Location: verify_account.php");
                    exit;
                }
            } else {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Error submitting document.']);
                    exit;
                } else {
                    $_SESSION['error_message'] = 'Error submitting document.';
                    header("Location: verify_account.php");
                    exit;
                }
            }
            break;

        case 'update':
            // This case is now handled by update_document.php via AJAX
            $_SESSION['error_message'] = "Invalid request method.";
            break;

        case 'reapply':
            // Handle re-application
            if (!isset($_POST['document_id'])) {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Document ID is required.']);
                    exit;
                } else {
                    $_SESSION['error_message'] = 'Document ID is required.';
                    header("Location: verify_account.php");
                    exit;
                }
            }

            $document_id = intval($_POST['document_id']);
            
            // Validate document_id is not 0
            if ($document_id <= 0) {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Invalid document ID. Please try again.']);
                    exit;
                } else {
                    $_SESSION['error_message'] = 'Invalid document ID. Please try again.';
                    header("Location: verify_account.php");
                    exit;
                }
            }
            
            // Verify document ownership
            $check_ownership = "SELECT * FROM Verification_Documents WHERE document_id = ? AND user_id = ? AND status = 'not approved'";
            $stmt = $conn->prepare($check_ownership);
            if (!$stmt) {
                error_log("Prepare failed: " . $conn->error);
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
                    exit;
                } else {
                    $_SESSION['error_message'] = 'Database error occurred.';
                    header("Location: verify_account.php");
                    exit;
                }
            }
            
            $stmt->bind_param("ii", $document_id, $user_id);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
                    exit;
                } else {
                    $_SESSION['error_message'] = 'Database error occurred.';
                    header("Location: verify_account.php");
                    exit;
                }
            }

            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Unauthorized access or invalid document status.']);
                    exit;
                } else {
                    $_SESSION['error_message'] = 'Unauthorized access or invalid document status.';
                    header("Location: verify_account.php");
                    exit;
                }
            }

            $document = $result->fetch_assoc();
            
            // Validate new document
            if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Please upload a new document.']);
                    exit;
                } else {
                    $_SESSION['error_message'] = 'Please upload a new document.';
                    header("Location: verify_account.php");
                    exit;
                }
            }

            $file = $_FILES['document'];
            $error = validateDocument($file);
            
            if ($error) {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $error]);
                    exit;
                } else {
                    $_SESSION['error_message'] = $error;
                    header("Location: verify_account.php");
                    exit;
                }
            }
            
            // Handle document upload
            $upload_result = handleDocumentUpload($file, $user_id);
            if (!$upload_result['success']) {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $upload_result['error']]);
                    exit;
                } else {
                    $_SESSION['error_message'] = $upload_result['error'];
                    header("Location: verify_account.php");
                    exit;
                }
            }
            
            // Update document record
            $document_type = mysqli_real_escape_string($conn, $_POST['document_type']);
            $document_number = mysqli_real_escape_string($conn, $_POST['document_number']);
            $issue_date = !empty($_POST['issue_date']) ? $_POST['issue_date'] : NULL;
            $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : NULL;
            $issuing_authority = mysqli_real_escape_string($conn, $_POST['issuing_authority']);
            
            // Log the values for debugging
            error_log("Updating document ID: " . $document_id . ", User ID: " . $user_id);
            error_log("Document type: " . $document_type);
            error_log("Document number: " . $document_number);
            error_log("Issue date: " . $issue_date);
            error_log("Expiry date: " . $expiry_date);
            error_log("Issuing authority: " . $issuing_authority);
            error_log("File name: " . $upload_result['file_name']);
            error_log("File path: " . $upload_result['file_path']);
            error_log("File type: " . $file['type']);
            error_log("File size: " . $file['size']);
            
            $update_query = "UPDATE Verification_Documents SET 
                document_type = ?,
                document_number = ?,
                issue_date = ?,
                expiry_date = ?,
                issuing_authority = ?,
                file_name = ?,
                file_path = ?,
                file_type = ?,
                file_size = ?,
                status = 'pending',
                rejection_reason = NULL
                WHERE document_id = ? AND user_id = ?";
                
            $stmt = $conn->prepare($update_query);
            if (!$stmt) {
                error_log("Prepare failed: " . $conn->error);
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
                exit;
            }
            
            // Bind parameters in the exact order they appear in the query
            if (!$stmt->bind_param(
                "ssssssssii",
                $document_type,
                $document_number,
                $issue_date,
                $expiry_date,
                $issuing_authority,
                $upload_result['file_name'],
                $upload_result['file_path'],
                $file['type'],
                $file['size'],
                $document_id,
                $user_id
            )) {
                error_log("Binding parameters failed: " . $stmt->error);
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
                exit;
            }
            
            // Log the SQL query with bound values
            error_log("SQL Query: " . $update_query);
            error_log("Bound values: " . json_encode([
                $document_type,
                $document_number,
                $issue_date,
                $expiry_date,
                $issuing_authority,
                $upload_result['file_name'],
                $upload_result['file_path'],
                $file['type'],
                $file['size'],
                $document_id,
                $user_id
            ]));
            
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Error submitting re-application: ' . $stmt->error]);
                    exit;
                } else {
                    $_SESSION['error_message'] = 'Error submitting re-application.';
                    header("Location: verify_account.php");
                    exit;
                }
            }
            
            updateUserVerificationStatus($user_id);
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Re-application submitted successfully. Your document will be reviewed.']);
                exit;
            } else {
                $_SESSION['success_message'] = 'Re-application submitted successfully. Your document will be reviewed.';
                header("Location: verify_account.php");
                exit;
            }
            break;

        default:
            $_SESSION['error_message'] = "Invalid action.";
            break;
    }

    header("Location: verify_account.php");
    exit;
    ?> 
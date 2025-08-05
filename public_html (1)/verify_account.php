<?php
session_start();
include('db_connection.php');
include('navbar.php');
?>
<title>Account Verification</title>
<?php
// Redirect if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: sign_in.php");
    exit("Redirecting to login page...");
}

$user_id = $_SESSION['user_id'];

// Handle document upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $document_type = mysqli_real_escape_string($conn, $_POST['document_type']);
    $document_number = mysqli_real_escape_string($conn, $_POST['document_number']);
    $issue_date = !empty($_POST['issue_date']) ? $_POST['issue_date'] : NULL;
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : NULL;
    $issuing_authority = mysqli_real_escape_string($conn, $_POST['issuing_authority']);
    $file = $_FILES['document'];
    
    // Validate file type
    $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['error_message'] = "Invalid file type. Please upload PDF, JPEG, or PNG files only.";
    }
    // Validate file size (5MB max)
    elseif ($file['size'] > 5 * 1024 * 1024) {
        $_SESSION['error_message'] = "File size too large. Maximum size is 5MB.";
    }
    else {
        // Create upload directory if it doesn't exist
        $upload_dir = 'uploads/verification_documents/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '_' . $user_id . '.' . $file_extension;
        $file_path = $upload_dir . $file_name;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // Insert document record into database
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
                file_size
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param(
                "issssssssi", 
                $user_id, 
                $document_type, 
                $document_number,
                $issue_date,
                $expiry_date,
                $issuing_authority,
                $file_name, 
                $file_path, 
                $file['type'], 
                $file['size']
            );
            
            if ($stmt->execute()) {
                // After successful document upload, check document counts and update status accordingly
                $check_docs = "SELECT 
                    COUNT(CASE WHEN status = 'not approved' THEN 1 END) as not_approved_count,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_count
                    FROM Verification_Documents WHERE user_id = ?";
                $stmt = $conn->prepare($check_docs);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $counts = $result->fetch_assoc();

                // Update user status based on document counts
                if ($counts['not_approved_count'] > 0) {
                    $new_status = 'not approved';
                } elseif ($counts['pending_count'] > 0) {
                    $new_status = 'pending';
                } elseif ($counts['approved_count'] > 0 && $counts['approved_count'] == ($counts['not_approved_count'] + $counts['pending_count'] + $counts['approved_count'])) {
                    $new_status = 'verified';
                } else {
                    $new_status = 'pending';
                }

                // Update user's verification status
                $update_user = "UPDATE Users SET verification_status = ? WHERE user_id = ?";
                $stmt = $conn->prepare($update_user);
                $stmt->bind_param("si", $new_status, $user_id);
                $stmt->execute();
                
                $_SESSION['success_message'] = "Document uploaded successfully! Your verification request has been submitted and is pending review.";
                header("Location: verify_account.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Error: Unable to save document information. Please try again.";
                header("Location: verify_account.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Error: Unable to upload file. Please try again.";
            header("Location: verify_account.php");
            exit();
        }
    }
}

// Get messages from session
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';

// Clear messages from session after getting them
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// Fetch user's current verification status and documents
$user_query = "SELECT u.verification_status, 
    (SELECT COUNT(*) FROM Verification_Documents vd WHERE vd.user_id = u.user_id AND vd.status = 'not approved') as not_approved_count,
    (SELECT COUNT(*) FROM Verification_Documents vd WHERE vd.user_id = u.user_id AND vd.status = 'pending') as pending_count,
    (SELECT COUNT(*) FROM Verification_Documents vd WHERE vd.user_id = u.user_id AND vd.status = 'approved') as approved_count
FROM Users u 
WHERE u.user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Update user's verification status based on document statuses
if ($user['approved_count'] > 0) {
    // If there is at least one approved document, set user status to verified
    $update_status = "UPDATE Users SET verification_status = 'verified' WHERE user_id = ?";
    $stmt = $conn->prepare($update_status);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user['verification_status'] = 'verified';
} elseif ($user['pending_count'] > 0) {
    // If any document is pending, set user status to pending
    $update_status = "UPDATE Users SET verification_status = 'pending' WHERE user_id = ?";
    $stmt = $conn->prepare($update_status);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user['verification_status'] = 'pending';
} elseif ($user['not_approved_count'] > 0) {
    // If any document is not approved, set user status to not approved
    $update_status = "UPDATE Users SET verification_status = 'not approved' WHERE user_id = ?";
    $stmt = $conn->prepare($update_status);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user['verification_status'] = 'not approved';
} else {
    // Default to pending if no documents at all
    $update_status = "UPDATE Users SET verification_status = 'pending' WHERE user_id = ?";
    $stmt = $conn->prepare($update_status);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user['verification_status'] = 'pending';
}

// Fetch all documents for this user
$documents_query = "SELECT vd.*, 
                          COALESCE(vd.rejection_reason, 'Your document was not approved. Please re-upload a valid document.') as display_rejection_reason 
                   FROM Verification_Documents vd 
                   WHERE vd.user_id = ? 
                   ORDER BY vd.uploaded_at DESC";
$stmt = $conn->prepare($documents_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$documents_result = $stmt->get_result();
?>

<style>
    /* Modern Kapital theme: glassmorphism, spacing, and #ea580c accents */
    body {
        font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
        background: linear-gradient(120deg, #181818 0%, #232526 100%);
        color: #fff;
        min-height: 100vh;
    }
    .container {
        max-width: 900px;
        margin: 48px auto 0 auto;
        padding: 40px 24px 48px 24px;
        background: rgba(34, 34, 34, 0.7);
        border-radius: 24px;
        box-shadow: 0 8px 32px 0 rgba(0,0,0,0.18);
        backdrop-filter: blur(6px);
        border: 1.5px solid rgba(234, 88, 12, 0.08);
    }
    .verification-section {
        background: none;
        border-radius: 0;
        padding: 0;
        border: none;
        box-shadow: none;
    }
    h1, h2 {
        color: #ea580c;
        font-weight: 700;
        letter-spacing: 0.5px;
        margin-bottom: 18px;
    }
    h2 {
        font-size: 1.4em;
        margin-top: 32px;
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 22px;
        border-radius: 20px;
        font-size: 1em;
        font-weight: 600;
        margin-bottom: 24px;
        box-shadow: 0 2px 8px rgba(234,88,12,0.08);
        background: rgba(234, 88, 12, 0.10);
        border: 1.5px solid #ea580c;
        color: #ea580c;
        transition: background 0.2s, color 0.2s;
    }
    .status-badge.status-verified {
        background: rgba(40, 167, 69, 0.13);
        color: #28a745;
        border-color: #28a745;
    }
    .status-badge.status-not-approved {
        background: rgba(220, 53, 69, 0.13);
        color: #dc3545;
        border-color: #dc3545;
    }
    .alert {
        padding: 18px 22px;
        border-radius: 12px;
        margin-bottom: 24px;
        font-size: 1.05em;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        background: rgba(234, 88, 12, 0.07);
        border-left: 5px solid #ea580c;
    }
    .alert-success {
        background: rgba(40, 167, 69, 0.13);
        border-left: 5px solid #28a745;
        color: #28a745;
    }
    .alert-error {
        background: rgba(220, 53, 69, 0.13);
        border-left: 5px solid #dc3545;
        color: #dc3545;
    }
    .form-group label {
        color: #ea580c;
        font-weight: 600;
        margin-bottom: 6px;
        display: block;
        letter-spacing: 0.2px;
    }
    select, input[type="file"], input[type="text"], input[type="date"] {
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        border: 1.5px solid #ea580c;
        border-radius: 18px;
        background: rgba(30, 30, 30, 0.98);
        color: #fff;
        font-family: inherit;
        font-size: 1em;
        padding: 12px 16px;
        margin-bottom: 12px;
        transition: border 0.2s, box-shadow 0.2s;
        box-shadow: 0 1px 4px rgba(234,88,12,0.04);
    }
    select:focus, input:focus {
        outline: none;
        border-color: #ea580c;
        box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.18);
        background: rgba(30, 30, 30, 1);
    }
    select option {
        background: #232323;
        color: #fff;
        font-size: 1em;
        padding: 10px 16px;
    }
    select:disabled, select option:disabled {
        color: #bbb !important;
        background: #232323 !important;
    }
    .submit-btn, .btn-edit, .btn-reupload, .btn-new-application {
        background: linear-gradient(90deg, #ea580c 60%, #ff7e1b 100%);
        color: #fff;
        border: none;
        border-radius: 22px;
        font-weight: 700;
        font-size: 1.08em;
        padding: 12px 32px;
        margin-top: 8px;
        margin-bottom: 8px;
        box-shadow: 0 2px 8px rgba(234,88,12,0.10);
        cursor: pointer;
        transition: background 0.2s, color 0.2s, transform 0.15s;
        letter-spacing: 0.2px;
    }
    .submit-btn:hover, .btn-edit:hover, .btn-reupload:hover, .btn-new-application:hover {
        background: linear-gradient(90deg, #ff7e1b 0%, #ea580c 100%);
        color: #181818;
        transform: translateY(-2px) scale(1.03);
    }
    .documents-list {
        margin-top: 36px;
    }
    .document-item {
        background: rgba(30,30,30,0.85);
        padding: 28px 24px 20px 24px;
        border-radius: 18px;
        margin-bottom: 24px;
        border: 1.5px solid rgba(234, 88, 12, 0.13);
        box-shadow: 0 4px 24px rgba(234,88,12,0.07);
        transition: box-shadow 0.2s, border 0.2s;
        position: relative;
    }
    .document-item:hover {
        box-shadow: 0 8px 32px rgba(234,88,12,0.13);
        border-color: #ea580c;
    }
    .document-info {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 18px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .document-type {
        font-size: 1.13em;
        color: #ea580c;
        font-weight: 600;
        margin-bottom: 8px;
        text-transform: capitalize;
        letter-spacing: 0.2px;
    }
    .document-actions {
        display: flex;
        gap: 12px;
    }
    .action-buttons a, .action-buttons button {
        min-width: 110px;
    }
    .view-btn {
        background: linear-gradient(90deg, #8b5cf6 0%, #a855f7 100%);
        color: #ffffff;
        padding: 12px 24px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        font-size: 0.95em;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 8px rgba(139, 92, 246, 0.2);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .view-btn:hover {
        background: linear-gradient(90deg, #a855f7 0%, #8b5cf6 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        color: #ffffff;
        text-decoration: none;
    }
    .view-btn i {
        font-size: 1.1em;
    }
    .rejection-reason-box {
        margin-top: 18px;
        padding: 16px 18px;
        background: rgba(220, 53, 69, 0.13);
        border: 1.5px solid #dc3545;
        border-radius: 10px;
        color: #dc3545;
        font-size: 1em;
        font-weight: 500;
        box-shadow: 0 2px 8px rgba(220,53,69,0.07);
    }
    .approval-info, .pending-info {
        margin-top: 12px;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 1em;
        font-weight: 500;
        box-shadow: 0 2px 8px rgba(40,167,69,0.07);
    }
    .approval-info {
        background: rgba(40, 167, 69, 0.13);
        color: #28a745;
        border: 1.5px solid #28a745;
    }
    .pending-info {
        background: rgba(234, 88, 12, 0.10);
        color: #ea580c;
        border: 1.5px solid #ea580c;
    }
    .document-details {
        margin-top: 18px;
        padding: 18px 0 0 0;
        border-top: 1px solid rgba(234,88,12,0.10);
    }
    .document-preview {
        margin: 0 0 18px 0;
        text-align: center;
    }
    .document-image {
        max-width: 100%;
        max-height: 220px;
        border-radius: 10px;
        box-shadow: 0 2px 12px rgba(234,88,12,0.10);
        border: 1.5px solid rgba(234,88,12,0.13);
    }
    .pdf-preview {
        display: flex;
        flex-direction: column;
        align-items: center;
        color: #ea580c;
        font-size: 1.1em;
        gap: 6px;
    }
    .document-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 18px;
        margin-top: 10px;
    }
    .info-item {
        background: rgba(234, 88, 12, 0.06);
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid rgba(234, 88, 12, 0.10);
        font-size: 0.98em;
        color: #fff;
        min-height: 56px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .info-item strong {
        color: #ea580c;
        margin-bottom: 3px;
        font-weight: 600;
        font-size: 1em;
    }
    .info-item span {
        color: #fff;
        font-size: 0.98em;
    }
    /* Modal improvements */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(20, 20, 20, 0.75);
        backdrop-filter: blur(2px);
        overflow-y: auto;
        transition: background 0.2s;
    }
    .modal-content {
        background: rgba(34, 34, 34, 0.98);
        margin: 5% auto;
        padding: 32px 28px 24px 28px;
        border-radius: 18px;
        width: 95%;
        max-width: 480px;
        position: relative;
        box-shadow: 0 8px 32px rgba(234,88,12,0.13);
        border: 1.5px solid #ea580c;
        animation: modalPop 0.25s cubic-bezier(.4,2,.6,1) 1;
    }
    @keyframes modalPop {
        0% { transform: scale(0.92); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    .close-modal {
        position: absolute;
        right: 24px;
        top: 16px;
        font-size: 30px;
        cursor: pointer;
        color: #ea580c;
        transition: color 0.2s;
    }
    .close-modal:hover {
        color: #fff;
    }
    .modal h2 {
        color: #ea580c;
        margin-bottom: 18px;
        padding-right: 30px;
        font-size: 1.18em;
        font-weight: 700;
    }
    /* Responsive tweaks */
    @media (max-width: 700px) {
        .container {
            padding: 10px 2vw 20px 2vw;
        }
        .modal-content {
            padding: 18px 6vw 16px 6vw;
        }
        .document-info-grid {
            grid-template-columns: 1fr;
        }
        select, input[type="file"], input[type="text"], input[type="date"] {
            font-size: 0.98em;
            padding: 10px 10px;
        }
    }
    @media (max-width: 480px) {
        .container {
            padding: 2vw 1vw 10vw 1vw;
        }
        .modal-content {
            padding: 10px 2vw 10px 2vw;
        }
    }
    /* Style the calendar icon for date inputs (Webkit browsers) */
    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
        opacity: 1;
        cursor: pointer;
    }
    /* For Firefox */
    input[type="date"]::-moz-calendar-picker-indicator {
        filter: invert(1);
        opacity: 1;
        cursor: pointer;
    }
    /* For Edge/IE (if supported) */
    input[type="date"]::-ms-input-placeholder {
        color: #fff;
    }
    /* Optional: make the icon a bit larger for better touch targets */
    input[type="date"]::-webkit-calendar-picker-indicator {
        width: 1.6em;
        height: 1.6em;
    }
</style>
<div class="container">
    <div class="verification-section">
        <h1>Account Verification</h1>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="status-badge status-<?php echo strtolower($user['verification_status']); ?>">
            <i class="fas <?php
                switch($user['verification_status']) {
                    case 'pending':
                        echo 'fa-clock';
                        break;
                    case 'verified':
                        echo 'fa-check-circle';
                        break;
                    case 'not approved':
                        echo 'fa-times-circle';
                        break;
                }
            ?>"></i>
            <?php 
                $status_message = '';
                switch($user['verification_status']) {
                    case 'pending':
                        if ($user['pending_count'] > 0) {
                            $status_message = 'Your verification is pending review';
                        } else {
                            $status_message = 'Please upload verification documents';
                        }
                        break;
                    case 'verified':
                        $status_message = 'Your account is verified';
                        break;
                    case 'not approved':
                        $status_message = 'Your verification was not approved';
                        break;
                }
                echo $status_message;
            ?>
        </div>

        <?php
        // Check if user has any documents at all
        $docs_query = "SELECT COUNT(*) as doc_count FROM Verification_Documents WHERE user_id = ?";
        $stmt = $conn->prepare($docs_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $docs_result = $stmt->get_result();
        $has_documents = $docs_result->fetch_assoc()['doc_count'] > 0;

        // Check if user has any pending documents
        $pending_query = "SELECT COUNT(*) as pending_count FROM Verification_Documents WHERE user_id = ? AND status = 'pending'";
        $stmt = $conn->prepare($pending_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $pending_result = $stmt->get_result();
        $pending_count = $pending_result->fetch_assoc()['pending_count'];
        ?>

        <?php if (!$has_documents): ?>
            <!-- Show application form directly for new users -->
            <div class="verification-section">
                <h2>Submit Verification Documents</h2>
                <p>Please upload the required documents to verify your account. We accept government-issued IDs, passports, or driver's licenses.</p>
                
                <form action="manage_verification.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="apply">
                    
                    <div class="form-group">
                        <label for="document_type">Document Type</label>
                        <select name="document_type" id="document_type" class="form-control" required>
                            <option value="">Select document type</option>
                            <option value="government_id">Government ID</option>
                            <option value="passport">Passport</option>
                            <option value="drivers_license">Driver's License</option>
                            <option value="business_registration">Business Registration</option>
                            <option value="professional_license">Professional License</option>
                            <option value="tax_certificate">Tax Certificate</option>
                            <option value="bank_statement">Bank Statement</option>
                            <option value="utility_bill">Utility Bill</option>
                            <option value="proof_of_address">Proof of Address</option>
                            <option value="employment_certificate">Employment Certificate</option>
                            <option value="educational_certificate">Educational Certificate</option>
                            <option value="other">Other Document</option>
                        </select>
                    </div>

                    <div class="document-details-form">
                        <div class="form-group">
                            <label for="document_number">Document Number</label>
                            <input type="text" name="document_number" id="document_number" class="form-control" placeholder="Enter document number">
                        </div>

                        <div class="form-group">
                            <label for="issue_date">Issue Date</label>
                            <input type="date" name="issue_date" id="issue_date" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="expiry_date">Expiry Date</label>
                            <input type="date" name="expiry_date" id="expiry_date" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="issuing_authority">Issuing Authority</label>
                            <input type="text" name="issuing_authority" id="issuing_authority" class="form-control" placeholder="Enter issuing authority">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="document">Upload Document</label>
                        <input type="file" name="document" id="document" required accept=".pdf,.jpg,.jpeg,.png">
                        <small>Supported formats: PDF, JPEG, PNG (Max size: 5MB)</small>
                    </div>

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-upload"></i> Submit Verification Application
                    </button>
                </form>
            </div>
        <?php elseif ($user['verification_status'] === 'not approved' && $pending_count == 0): ?>
            <div class="verification-actions">
                <button onclick="openNewApplicationModal()" class="submit-btn">
                    <i class="fas fa-plus"></i> New Application
                </button>
            </div>
        <?php endif; ?>

        <?php if ($documents_result->num_rows > 0): ?>
            <div class="documents-list">
                <h2>Uploaded Documents</h2>
                <?php 
                $processed_documents = array();
                while ($document = $documents_result->fetch_assoc()): 
                    // Skip if we've already processed this document type
                    if (in_array($document['document_type'], $processed_documents)) {
                        continue;
                    }
                    $processed_documents[] = $document['document_type'];
                ?>
                    <div class="document-item">
                        <div class="document-info">
                            <div>
                                <div class="document-type">
                                    <?php echo ucwords(str_replace('_', ' ', $document['document_type'])); ?>
                                </div>
                                <span class="status-badge status-<?php echo strtolower($document['status']); ?>">
                                    <i class="fas <?php
                                        switch($document['status']) {
                                            case 'pending':
                                                echo 'fa-clock';
                                                break;
                                            case 'approved':
                                                echo 'fa-check-circle';
                                                break;
                                            case 'not approved':
                                                echo 'fa-times-circle';
                                                break;
                                        }
                                    ?>"></i>
                                    <?php echo ucfirst($document['status']); ?>
                                </span>
                            </div>
                            <div class="document-actions">
                                <div class="action-buttons">
                                    <a href="<?php echo htmlspecialchars($document['file_path']); ?>" target="_blank" class="view-btn">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <?php if ($document['status'] !== 'not approved'): ?>
                                    <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode([
                                        'documentId' => $document['document_id'],
                                        'documentType' => $document['document_type'],
                                        'documentNumber' => $document['document_number'],
                                        'issueDate' => $document['issue_date'],
                                        'expiryDate' => $document['expiry_date'],
                                        'issuingAuthority' => $document['issuing_authority']
                                    ]), ENT_QUOTES, 'UTF-8'); ?>)" class="btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($document['status'] === 'not approved'): ?>
                                        <!-- Re-apply button removed -->
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($document['status'] === 'not approved'): ?>
                            <div class="rejection-reason-box">
                                <strong><i class="fas fa-exclamation-circle"></i> Rejection Reason:</strong>
                                <?php echo !empty($document['display_rejection_reason']) ? htmlspecialchars($document['display_rejection_reason']) : 'Your document was not approved. Please re-upload a valid document.'; ?>
                            </div>
                        <?php elseif ($document['status'] === 'approved'): ?>
                            <div class="approval-info">
                                <i class="fas fa-check-circle"></i> Your document has been verified and approved.
                            </div>
                        <?php elseif ($document['status'] === 'pending'): ?>
                            <div class="pending-info">
                                <i class="fas fa-clock"></i> Your document is currently under review.
                            </div>
                        <?php endif; ?>

                        <div class="document-details">
                            <div class="document-preview">
                                <?php if (in_array(pathinfo($document['file_path'], PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png'])): ?>
                                    <img src="<?php echo htmlspecialchars($document['file_path']); ?>" alt="Document Preview" class="document-image">
                                <?php else: ?>
                                    <div class="pdf-preview">
                                        <i class="fas fa-file-pdf fa-4x" style="color: #f3c000;"></i>
                                        <p>PDF Document</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="document-info-grid">
                                <div class="info-item">
                                    <strong>Document Type:</strong>
                                    <span><?php echo ucwords(str_replace('_', ' ', $document['document_type'])); ?></span>
                                </div>
                                <div class="info-item">
                                    <strong>Document Number:</strong>
                                    <span><?php echo !empty($document['document_number']) ? htmlspecialchars($document['document_number']) : 'Not Specified'; ?></span>
                                </div>
                                <div class="info-item">
                                    <strong>Issue Date:</strong>
                                    <span><?php echo !empty($document['issue_date']) ? date('F j, Y', strtotime($document['issue_date'])) : 'Not Specified'; ?></span>
                                </div>
                                <div class="info-item">
                                    <strong>Expiry Date:</strong>
                                    <span><?php echo !empty($document['expiry_date']) ? date('F j, Y', strtotime($document['expiry_date'])) : 'Not Specified'; ?></span>
                                </div>
                                <div class="info-item">
                                    <strong>Issuing Authority:</strong>
                                    <span><?php echo !empty($document['issuing_authority']) ? htmlspecialchars($document['issuing_authority']) : 'Not Specified'; ?></span>
                                </div>
                                <div class="info-item">
                                    <strong>Upload Date:</strong>
                                    <span><?php echo date('F j, Y', strtotime($document['uploaded_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Document Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeEditModal()">&times;</span>
        <h2>Edit Document Details</h2>
        <form id="editDocumentForm">
            <input type="hidden" name="document_id" id="edit_document_id">
            <div class="edit-document-details">
                <div class="form-group">
                    <label for="edit_document_type">Document Type</label>
                    <select name="document_type" id="edit_document_type" class="form-control" required>
                        <option value="">Select document type</option>
                        <option value="government_id">Government ID</option>
                        <option value="passport">Passport</option>
                        <option value="drivers_license">Driver's License</option>
                        <option value="business_registration">Business Registration</option>
                        <option value="professional_license">Professional License</option>
                        <option value="tax_certificate">Tax Certificate</option>
                        <option value="bank_statement">Bank Statement</option>
                        <option value="utility_bill">Utility Bill</option>
                        <option value="proof_of_address">Proof of Address</option>
                        <option value="employment_certificate">Employment Certificate</option>
                        <option value="educational_certificate">Educational Certificate</option>
                        <option value="other">Other Document</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_document_number">Document Number</label>
                    <input type="text" name="document_number" id="edit_document_number" class="form-control" placeholder="Enter document number">
                </div>

                <div class="form-group">
                    <label for="edit_issue_date">Issue Date</label>
                    <input type="date" name="issue_date" id="edit_issue_date" class="form-control">
                </div>

                <div class="form-group">
                    <label for="edit_expiry_date">Expiry Date</label>
                    <input type="date" name="expiry_date" id="edit_expiry_date" class="form-control">
                </div>

                <div class="form-group">
                    <label for="edit_issuing_authority">Issuing Authority</label>
                    <input type="text" name="issuing_authority" id="edit_issuing_authority" class="form-control" placeholder="Enter issuing authority">
                </div>

                <div class="form-group">
                    <label for="edit_document">Upload New Document</label>
                    <input type="file" name="document" id="edit_document" accept=".pdf,.jpg,.jpeg,.png">
                    <small>Supported formats: PDF, JPEG, PNG (Max size: 5MB)</small>
                    <small class="text-muted">Leave empty to keep the current document</small>
                </div>
            </div>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> You can update your document details and upload a new document here. The document will need to be re-verified after any changes.
            </div>
            <button type="submit" class="submit-btn">Update Document Details</button>
        </form>
    </div>
</div>

<!-- Re-apply Modal -->
<div id="reapplyModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeReapplyModal()">&times;</span>
        <h2>Re-apply for Verification</h2>
        <form id="reapplyDocumentForm" onsubmit="handleReapplySubmit(event)">
            <input type="hidden" name="action" value="reapply">
            <input type="hidden" name="document_id" id="reapply_document_id">
            
            <div class="form-group">
                <label for="reapply_document_type">Document Type</label>
                <select name="document_type" id="reapply_document_type" class="form-control" required>
                    <option value="">Select document type</option>
                    <option value="government_id">Government ID</option>
                    <option value="passport">Passport</option>
                    <option value="drivers_license">Driver's License</option>
                    <option value="business_registration">Business Registration</option>
                    <option value="professional_license">Professional License</option>
                    <option value="tax_certificate">Tax Certificate</option>
                    <option value="bank_statement">Bank Statement</option>
                    <option value="utility_bill">Utility Bill</option>
                    <option value="proof_of_address">Proof of Address</option>
                    <option value="employment_certificate">Employment Certificate</option>
                    <option value="educational_certificate">Educational Certificate</option>
                    <option value="other">Other Document</option>
                </select>
            </div>

            <div class="document-details-form">
                <div class="form-group">
                    <label for="reapply_document_number">Document Number</label>
                    <input type="text" name="document_number" id="reapply_document_number" class="form-control" placeholder="Enter document number">
                </div>

                <div class="form-group">
                    <label for="reapply_issue_date">Issue Date</label>
                    <input type="date" name="issue_date" id="reapply_issue_date" class="form-control">
                </div>

                <div class="form-group">
                    <label for="reapply_expiry_date">Expiry Date</label>
                    <input type="date" name="expiry_date" id="reapply_expiry_date" class="form-control">
                </div>

                <div class="form-group">
                    <label for="reapply_issuing_authority">Issuing Authority</label>
                    <input type="text" name="issuing_authority" id="reapply_issuing_authority" class="form-control" placeholder="Enter issuing authority">
                </div>
            </div>

            <div class="form-group">
                <label for="reapply_document">Upload New Document</label>
                <input type="file" name="document" id="reapply_document" required accept=".pdf,.jpg,.jpeg,.png">
                <small>Supported formats: PDF, JPEG, PNG (Max size: 5MB)</small>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> This will submit a new verification application for this document. The previous rejection reason will be considered during review.
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-upload"></i> Submit Re-application
            </button>
        </form>
    </div>
</div>

<!-- New Application Modal -->
<div id="newApplicationModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeNewApplicationModal()">&times;</span>
        <h2>New Verification Application</h2>
        <form id="newApplicationForm">
            <input type="hidden" name="action" value="apply">
            
            <div class="form-group">
                <label for="new_document_type">Document Type</label>
                <select name="document_type" id="new_document_type" class="form-control" required>
                    <option value="">Select document type</option>
                    <option value="government_id">Government ID</option>
                    <option value="passport">Passport</option>
                    <option value="drivers_license">Driver's License</option>
                    <option value="business_registration">Business Registration</option>
                    <option value="professional_license">Professional License</option>
                    <option value="tax_certificate">Tax Certificate</option>
                    <option value="bank_statement">Bank Statement</option>
                    <option value="utility_bill">Utility Bill</option>
                    <option value="proof_of_address">Proof of Address</option>
                    <option value="employment_certificate">Employment Certificate</option>
                    <option value="educational_certificate">Educational Certificate</option>
                    <option value="other">Other Document</option>
                </select>
            </div>

            <div class="document-details-form">
                <div class="form-group">
                    <label for="new_document_number">Document Number</label>
                    <input type="text" name="document_number" id="new_document_number" class="form-control" placeholder="Enter document number">
                </div>

                <div class="form-group">
                    <label for="new_issue_date">Issue Date</label>
                    <input type="date" name="issue_date" id="new_issue_date" class="form-control">
                </div>

                <div class="form-group">
                    <label for="new_expiry_date">Expiry Date</label>
                    <input type="date" name="expiry_date" id="new_expiry_date" class="form-control">
                </div>

                <div class="form-group">
                    <label for="new_issuing_authority">Issuing Authority</label>
                    <input type="text" name="issuing_authority" id="new_issuing_authority" class="form-control" placeholder="Enter issuing authority">
                </div>
            </div>

            <div class="form-group">
                <label for="new_document">Upload Document</label>
                <input type="file" name="document" id="new_document" required accept=".pdf,.jpg,.jpeg,.png">
                <small>Supported formats: PDF, JPEG, PNG (Max size: 5MB)</small>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> This will submit a new verification application. Your previous rejected document will remain in your history.
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-upload"></i> Submit New Application
            </button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 only on location dropdown
        $('#location').select2({
            theme: 'default',
            width: '100%',
            placeholder: 'Search or select a location',
            allowClear: true,
            minimumInputLength: 1
        });
    });

    function openEditModal(documentData) {
        try {
            console.log('Raw document data:', documentData);
            
            // Parse the data if it's a string, otherwise use it directly
            const data = typeof documentData === 'string' ? JSON.parse(documentData) : documentData;
            
            // Log the parsed data
            console.log('Parsed document data:', data);
            
            // Validate document data
            if (!data) {
                console.error('Document data is null or undefined');
                alert('Error: Invalid document data - data is null');
                return;
            }
            
            // Convert documentId to number and validate
            const documentId = Number(data.documentId);
            console.log('Parsed document ID:', documentId, 'Original value:', data.documentId);
            
            if (isNaN(documentId) || documentId <= 0) {
                console.error('Invalid document ID:', data.documentId, 'Parsed as:', documentId);
                alert('Error: Invalid document ID format');
                return;
            }
            
            // Show the modal
            const modal = document.getElementById('editModal');
            if (!modal) {
                console.error('Edit modal not found');
                return;
            }
            modal.style.display = 'block';
            
            // Set form values with validation
            document.getElementById('edit_document_id').value = documentId;
            
            // Log the set document ID
            console.log('Set document ID:', document.getElementById('edit_document_id').value);
            
            // Set other form values with null checks
            document.getElementById('edit_document_type').value = data.documentType || '';
            document.getElementById('edit_document_number').value = data.documentNumber || '';
            document.getElementById('edit_issue_date').value = data.issueDate || '';
            document.getElementById('edit_expiry_date').value = data.expiryDate || '';
            document.getElementById('edit_issuing_authority').value = data.issuingAuthority || '';
            
            // Log all form values for debugging
            console.log('Form values set:', {
                documentId: document.getElementById('edit_document_id').value,
                documentType: document.getElementById('edit_document_type').value,
                documentNumber: document.getElementById('edit_document_number').value,
                issueDate: document.getElementById('edit_issue_date').value,
                expiryDate: document.getElementById('edit_expiry_date').value,
                issuingAuthority: document.getElementById('edit_issuing_authority').value
            });
        } catch (error) {
            console.error('Error in openEditModal:', error);
            alert('An error occurred while opening the edit modal: ' + error.message);
        }
    }

    function handleEditSubmit(event) {
        event.preventDefault();
        
        try {
            // Get the document ID and validate it
            const documentIdInput = document.getElementById('edit_document_id');
            const documentId = parseInt(documentIdInput.value);
            
            console.log('Document ID from form:', documentId, 'Raw value:', documentIdInput.value);
            
            if (!documentIdInput.value) {
                throw new Error('Document ID is missing');
            }
            
            if (isNaN(documentId) || documentId <= 0) {
                throw new Error('Invalid document ID format');
            }
            
            // Create FormData object
            const formData = new FormData();
            
            // Add all form fields with validation
            formData.append('document_id', documentId);
            
            const documentType = document.getElementById('edit_document_type').value;
            if (!documentType) {
                throw new Error('Document type is required');
            }
            formData.append('document_type', documentType);
            
            // Add optional fields
            formData.append('document_number', document.getElementById('edit_document_number').value || '');
            formData.append('issue_date', document.getElementById('edit_issue_date').value || '');
            formData.append('expiry_date', document.getElementById('edit_expiry_date').value || '');
            formData.append('issuing_authority', document.getElementById('edit_issuing_authority').value || '');
            
            // Handle file upload
            const fileInput = document.getElementById('edit_document');
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    throw new Error('File size exceeds 5MB limit');
                }
                // Validate file type
                const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    throw new Error('Invalid file type. Please upload PDF, JPEG, or PNG files only.');
                }
                formData.append('document', file);
            }
            
            // Show loading state
            const submitButton = document.querySelector('#editDocumentForm .submit-btn');
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            submitButton.disabled = true;
            
            // Log form data for debugging
            console.log('Submitting form data:');
            for (let [key, value] of formData.entries()) {
                console.log(key + ':', value instanceof File ? `File: ${value.name}` : value);
            }
            
            // Send the request
            fetch('update_document.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`Network response was not ok (${response.status})`);
                }
                return response.text().then(text => {
                    console.log('Raw response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON Parse Error:', e);
                        console.error('Response text:', text);
                        throw new Error('Invalid JSON response from server');
                    }
                });
            })
            .then(data => {
                console.log('Parsed response:', data);
                if (data.success) {
                    // Close the modal first
                    closeEditModal();
                    // Show success message and reload only once
                    alert('Document updated successfully');
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'Unknown error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the document: ' + error.message);
            })
            .finally(() => {
                // Reset button state
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            });
        } catch (error) {
            console.error('Form submission error:', error);
            alert('Error: ' + error.message);
            return;
        }
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    function openReapplyModal(documentData) {
        try {
            console.log('Raw document data for reapply:', documentData);
            
            // Parse the data if it's a string, otherwise use it directly
            const data = typeof documentData === 'string' ? JSON.parse(documentData) : documentData;
            
            // Log the parsed data
            console.log('Parsed document data for reapply:', data);
            
            // Validate document data
            if (!data) {
                console.error('Document data is null or undefined');
                alert('Error: Invalid document data - data is null');
                return;
            }
            
            // Convert documentId to number and validate
            const documentId = Number(data.documentId);
            console.log('Parsed document ID for reapply:', documentId, 'Original value:', data.documentId);
            
            if (isNaN(documentId) || documentId <= 0) {
                console.error('Invalid document ID:', data.documentId, 'Parsed as:', documentId);
                alert('Error: Invalid document ID format');
                return;
            }
            
            // Show the modal
            const modal = document.getElementById('reapplyModal');
            if (!modal) {
                console.error('Reapply modal not found');
                return;
            }
            modal.style.display = 'block';
            
            // Set form values with validation
            document.getElementById('reapply_document_id').value = documentId;
            
            // Log the set document ID
            console.log('Set document ID for reapply:', document.getElementById('reapply_document_id').value);
            
            // Set other form values with null checks
            document.getElementById('reapply_document_type').value = data.documentType || '';
            document.getElementById('reapply_document_number').value = data.documentNumber || '';
            document.getElementById('reapply_issue_date').value = data.issueDate || '';
            document.getElementById('reapply_expiry_date').value = data.expiryDate || '';
            document.getElementById('reapply_issuing_authority').value = data.issuingAuthority || '';
            
            // Log all form values for debugging
            console.log('Form values set for reapply:', {
                documentId: document.getElementById('reapply_document_id').value,
                documentType: document.getElementById('reapply_document_type').value,
                documentNumber: document.getElementById('reapply_document_number').value,
                issueDate: document.getElementById('reapply_issue_date').value,
                expiryDate: document.getElementById('reapply_expiry_date').value,
                issuingAuthority: document.getElementById('reapply_issuing_authority').value
            });
        } catch (error) {
            console.error('Error in openReapplyModal:', error);
            alert('An error occurred while opening the reapply modal: ' + error.message);
        }
    }

    function closeReapplyModal() {
        document.getElementById('reapplyModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const editModal = document.getElementById('editModal');
        const reapplyModal = document.getElementById('reapplyModal');
        const newApplicationModal = document.getElementById('newApplicationModal');
        if (event.target == editModal || event.target == reapplyModal || event.target == newApplicationModal) {
            editModal.style.display = 'none';
            reapplyModal.style.display = 'none';
            newApplicationModal.style.display = 'none';
        }
    }

    // Helper function to format document types
    function formatDocumentType(type) {
        const formatMap = {
            'drivers_license': "Driver's License",
            'government_id': "Government ID",
            'business_registration': "Business Registration",
            'professional_license': "Professional License",
            'tax_certificate': "Tax Certificate",
            'bank_statement': "Bank Statement",
            'utility_bill': "Utility Bill",
            'proof_of_address': "Proof of Address",
            'employment_certificate': "Employment Certificate",
            'educational_certificate': "Educational Certificate",
            'passport': "Passport",
            'other': "Other Document"
        };
        
        return formatMap[type] || type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Format document types in the select options
        const documentTypeSelect = document.getElementById('document_type');
        if (documentTypeSelect) {
            Array.from(documentTypeSelect.options).forEach(option => {
                if (option.value) {
                    option.textContent = formatDocumentType(option.value);
                }
            });
        }

        // Format document types in the document list
        document.querySelectorAll('.document-type').forEach(element => {
            const type = element.textContent.trim();
            element.textContent = formatDocumentType(type);
        });

        // Add event listener for edit form submission
        const editForm = document.getElementById('editDocumentForm');
        if (editForm) {
            editForm.addEventListener('submit', handleEditSubmit);
        }
    });

    function handleReapplySubmit(event) {
        event.preventDefault();
        
        try {
            // Get the document ID and validate it
            const documentIdInput = document.getElementById('reapply_document_id');
            const documentId = parseInt(documentIdInput.value);
            
            console.log('Document ID from reapply form:', documentId, 'Raw value:', documentIdInput.value);
            
            if (!documentIdInput.value) {
                throw new Error('Document ID is missing');
            }
            
            if (isNaN(documentId) || documentId <= 0) {
                throw new Error('Invalid document ID format');
            }
            
            // Create FormData object
            const formData = new FormData(document.getElementById('reapplyDocumentForm'));
            
            // Show loading state
            const submitButton = document.querySelector('#reapplyDocumentForm .submit-btn');
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            submitButton.disabled = true;
            
            // Log form data for debugging
            console.log('Submitting reapply form data:');
            for (let [key, value] of formData.entries()) {
                console.log(key + ':', value instanceof File ? `File: ${value.name}` : value);
            }
            
            // Send the request
            fetch('manage_verification.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`Network response was not ok (${response.status})`);
                }
                return response.text().then(text => {
                    console.log('Raw response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON Parse Error:', e);
                        console.error('Response text:', text);
                        throw new Error('Invalid JSON response from server');
                    }
                });
            })
            .then(data => {
                console.log('Parsed response:', data);
                if (data.success) {
                    // Close the modal first
                    closeReapplyModal();
                    // Show success message and reload only once
                    alert('Document re-application submitted successfully');
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'Unknown error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting the re-application: ' + error.message);
            })
            .finally(() => {
                // Reset button state
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            });
        } catch (error) {
            console.error('Form submission error:', error);
            alert('Error: ' + error.message);
            return;
        }
    }

    function openNewApplicationModal() {
        document.getElementById('newApplicationModal').style.display = 'block';
    }

    function closeNewApplicationModal() {
        document.getElementById('newApplicationModal').style.display = 'none';
    }

    function handleNewApplicationSubmit(event) {
        event.preventDefault();
        
        try {
            // Create FormData object
            const formData = new FormData(document.getElementById('newApplicationForm'));
            
            // Show loading state
            const submitButton = document.querySelector('#newApplicationForm .submit-btn');
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            submitButton.disabled = true;
            
            // Log form data for debugging
            console.log('Submitting new application form data:');
            for (let [key, value] of formData.entries()) {
                console.log(key + ':', value instanceof File ? `File: ${value.name}` : value);
            }
            
            // Send the request
            fetch('manage_verification.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`Network response was not ok (${response.status})`);
                }
                return response.text().then(text => {
                    console.log('Raw response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON Parse Error:', e);
                        console.error('Response text:', text);
                        throw new Error('Invalid JSON response from server');
                    }
                });
            })
            .then(data => {
                console.log('Parsed response:', data);
                if (data.success) {
                    // Close the modal first
                    closeNewApplicationModal();
                    // Show success message and reload only once
                    alert('New verification application submitted successfully');
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'Unknown error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting the new application: ' + error.message);
            })
            .finally(() => {
                // Reset button state
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            });
        } catch (error) {
            console.error('Form submission error:', error);
            alert('Error: ' + error.message);
            return;
        }
    }

    // Add event listener for new application form submission
    document.getElementById('newApplicationForm').addEventListener('submit', handleNewApplicationSubmit);
</script> 
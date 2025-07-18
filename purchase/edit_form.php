<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_login();

// Initialize variables
$error = '';
$success = '';
$form = null;

// Get form ID from URL
$form_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get companies for dropdown
$companies_query = "SELECT name FROM companies ORDER BY name ASC";
$companies_result = $conn->query($companies_query);
$companies = [];
while ($row = $companies_result->fetch_assoc()) {
    $companies[] = $row['name'];
}

// Fetch existing form data
if ($form_id > 0) {
    $query = "SELECT * FROM purchases WHERE id = ? AND created_by = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $form_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: ../user/view_forms.php");
        exit;
    }
    
    $form = $result->fetch_assoc();
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        $company_name = trim($_POST['company_name']);
        $award_no = trim($_POST['award_no']);
        $award_date = $_POST['award_date'];
        $amount = (float)$_POST['amount'];
        $description = trim($_POST['description']);
        
        if (empty($company_name) || empty($award_no) || empty($award_date) || $amount <= 0) {
            throw new Exception("All fields are required and amount must be greater than 0");
        }

        // Handle file upload if new file is provided
        $attachment_path = $form['attachment_path']; // Keep existing path by default
        if (!empty($_FILES['attachment']['name'])) {
            $upload_dir = "../uploads/";
            $file_ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            $allowed_types = ['pdf', 'doc', 'docx'];
            
            if (!in_array($file_ext, $allowed_types)) {
                throw new Exception("Only PDF and DOC files are allowed");
            }
            
            $new_filename = uniqid() . "." . $file_ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_path)) {
                // If upload successful and there was an old file, delete it
                if ($form['attachment_path'] && file_exists($form['attachment_path'])) {
                    unlink($form['attachment_path']);
                }
                $attachment_path = $upload_path;
            }
        }

        // Update form in database
        $update_query = "UPDATE purchases SET 
                        company_name = ?, 
                        award_no = ?, 
                        award_date = ?, 
                        amount = ?, 
                        description = ?, 
                        attachment_path = ?,
                        updated_at = CURRENT_TIMESTAMP 
                        WHERE id = ? AND created_by = ?";
                        
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sssdssii", 
            $company_name, 
            $award_no, 
            $award_date, 
            $amount, 
            $description, 
            $attachment_path,
            $form_id,
            $_SESSION['user_id']
        );

        if ($stmt->execute()) {
            $success = "Form updated successfully!";
            // Refresh form data
            $form['company_name'] = $company_name;
            $form['award_no'] = $award_no;
            $form['award_date'] = $award_date;
            $form['amount'] = $amount;
            $form['description'] = $description;
            $form['attachment_path'] = $attachment_path;
        } else {
            throw new Exception("Error updating form: " . $conn->error);
        }
        $stmt->close();

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Purchase Form | KIMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-info mb-4">
        <div class="container">
            <a class="navbar-brand" href="../user/view_forms.php">
                <i class="bi bi-arrow-left"></i> Back to Forms
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-pencil-square"></i> Edit Purchase Form
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="company_name" class="form-label">Company Name</label>
                                <select class="form-select" id="company_name" name="company_name" required>
                                    <option value="">Select Company</option>
                                    <?php foreach ($companies as $company): ?>
                                        <option value="<?= htmlspecialchars($company) ?>" 
                                            <?= ($form['company_name'] === $company) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($company) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="award_no" class="form-label">Award Number</label>
                                <input type="text" class="form-control" id="award_no" name="award_no" 
                                       value="<?= htmlspecialchars($form['award_no']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="award_date" class="form-label">Award Date</label>
                                <input type="date" class="form-control" id="award_date" name="award_date" 
                                       value="<?= htmlspecialchars($form['award_date']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount (GHC)</label>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" 
                                       value="<?= htmlspecialchars($form['amount']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" 
                                          required><?= htmlspecialchars($form['description']) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="attachment" class="form-label">Update Attachment (PDF/DOC)</label>
                                <input type="file" class="form-control" id="attachment" name="attachment" 
                                       accept=".pdf,.doc,.docx">
                                <?php if ($form['attachment_path']): ?>
                                    <div class="form-text">
                                        Current file: 
                                        <a href="<?= htmlspecialchars($form['attachment_path']) ?>" target="_blank">
                                            View existing attachment
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-info text-white">
                                    <i class="bi bi-save"></i> Save Changes
                                </button>
                                <a href="../user/view_forms.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>
<?php $conn->close(); ?>
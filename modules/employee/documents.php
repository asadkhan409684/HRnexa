<?php
// Fetch employee documents
$docs_query = "SELECT * FROM employee_documents WHERE employee_id = $employee_id ORDER BY uploaded_at DESC";
$docs_result = $conn->query($docs_query);
?>
<!-- Documents Section -->
<div id="documents-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>My Documents</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadDocModal"><i class="fas fa-plus"></i> Upload Document</button>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-file-upload"></i>
            Submitted Documents
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>Upload Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($docs_result && $docs_result->num_rows > 0) {
                        while($doc = $docs_result->fetch_assoc()) {
                            $status_class = 'bg-warning';
                            if ($doc['verification_status'] == 'verified') $status_class = 'bg-success';
                            elseif ($doc['verification_status'] == 'rejected') $status_class = 'bg-danger';
                            
                            // Adjust path if needed
                            $file_url = $doc['file_path'];
                            if (strpos($file_url, '../') === 0) {
                                $file_url = str_replace('../', '', $file_url);
                            }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($doc['document_type']); ?></td>
                        <td><?php echo date('d-M-Y', strtotime($doc['uploaded_at'])); ?></td>
                        <td><span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($doc['verification_status']); ?></span></td>
                        <td><a href="../<?php echo htmlspecialchars($file_url); ?>" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fas fa-download"></i></a></td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center text-muted'>No documents uploaded yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocModal" tabindex="-1" aria-labelledby="uploadDocModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="uploadDocModalLabel">Upload New Document</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="uploadDocumentForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="upload_document">
                    <input type="hidden" name="employee_id" value="<?php echo $employee_id; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Document Type</label>
                        <select class="form-select" name="document_type" required>
                            <option value="">Select Category</option>
                            <option value="NID/Passport">NID/Passport</option>
                            <option value="Educational Certificate">Educational Certificate</option>
                            <option value="Experience Letter">Experience Letter</option>
                            <option value="Medical Certificate">Medical Certificate</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select File (PDF only)</label>
                        <input type="file" class="form-control" name="document_file" accept=".pdf" required>
                        <small class="text-muted">Maximum file size: 5MB</small>
                    </div>
                    <div class="alert alert-info py-2" style="font-size: 13px;">
                        <i class="fas fa-info-circle"></i> After uploading, your document will be sent to HR for approval.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Start Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

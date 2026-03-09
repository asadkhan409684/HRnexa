<div id="documents-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Employee Documents</h1>
        <button class="btn btn-primary" onclick="openUploadModal()"><i class="fas fa-upload"></i> Upload Document</button>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-file-upload"></i>
            Document Verification & Upload
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Document Type</th>
                        <th>Uploaded Date</th>
                        <th>Status</th>
                        <th>View </th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($docs_result && $docs_result->num_rows > 0): ?>
                        <?php while($doc = $docs_result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($doc['employee_code']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($doc['document_type']); ?></td>
                            <td><?php echo date('d-M-Y H:i', strtotime($doc['uploaded_at'])); ?></td>
                            <td>
                                <?php 
                                    $b_class = 'bg-info';
                                    if ($doc['verification_status'] == 'verified') $b_class = 'bg-success';
                                    if ($doc['verification_status'] == 'rejected') $b_class = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $b_class; ?>"><?php echo ucfirst($doc['verification_status']); ?></span>
                            </td>
                            <td>
                                <a href="../<?php echo htmlspecialchars(str_replace('../', '', $doc['file_path'])); ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-warning" onclick='openUpdateDocStatusModal(<?php echo json_encode($doc); ?>)'>
                                    <i class="fas fa-edit"></i> Status
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No documents found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="dashboard.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Employee Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Choose Employee...</option>
                            <?php 
                                $emps_dropdown = $conn->query("SELECT id, first_name, last_name, employee_code FROM employees ORDER BY first_name ASC");
                                while($e = $emps_dropdown->fetch_assoc()) {
                                    echo "<option value='".$e['id']."'>".$e['first_name']." ".$e['last_name']." (".$e['employee_code'].")</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Document Type</label>
                        <select name="document_type" class="form-select" required>
                            <option value="National ID card">National ID card</option>
                            <option value="Passport">Passport</option>
                            <option value="Experience Letter">Experience Letter</option>
                            <option value="Educational Certificate">Educational Certificate</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select PDF File</label>
                        <input type="file" name="document_file" class="form-control" accept=".pdf" required>
                        <small class="text-muted">Only PDF files are allowed.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="upload_document" class="btn btn-primary">Upload PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Document Status Update Modal -->
<div class="modal fade" id="updateDocStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="dashboard.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Update Document Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="doc_id" id="update_doc_id">
                    <p>Employee: <strong id="update_doc_emp_name"></strong></p>
                    <p>Document: <strong id="update_doc_type_disp"></strong></p>
                    
                    <div class="mb-3">
                        <label class="form-label">Verification Status</label>
                        <select name="verification_status" id="update_doc_status_val" class="form-select" required>
                            <option value="pending">Pending</option>
                            <option value="verified">Verified</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_doc_status" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

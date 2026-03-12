<?php
// Fetch Candidates with their last application status
$candidates_query = "SELECT c.*, ca.status as app_status, jr.title as job_title 
                    FROM candidates c
                    LEFT JOIN candidate_applications ca ON c.id = ca.candidate_id
                    LEFT JOIN job_requisitions jr ON ca.job_requisition_id = jr.id
                    ORDER BY c.applied_date DESC";
$candidates_result = $conn->query($candidates_query);
?>

<div id="candidates-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Candidate Database</h1>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-address-book"></i>
            Candidate Pipeline
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Candidate Name</th>
                        <th>Applied For</th>
                        <th>Email / Phone</th>
                        <th>Applied Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($candidates_result && $candidates_result->num_rows > 0): ?>
                        <?php while($cand = $candidates_result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($cand['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($cand['job_title'] ?? 'General Pool'); ?></td>
                            <td>
                                <?php echo htmlspecialchars($cand['email']); ?><br>
                                <small class="text-muted"><?php echo htmlspecialchars($cand['phone']); ?></small>
                            </td>
                            <td><?php echo date('d M Y', strtotime($cand['applied_date'])); ?></td>
                            <td>
                                <?php 
                                    $c_badge = 'bg-info';
                                    if ($cand['status'] == 'interview') $c_badge = 'bg-warning';
                                    if ($cand['status'] == 'selected') $c_badge = 'bg-success';
                                    if ($cand['status'] == 'rejected') $c_badge = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $c_badge; ?>"><?php echo ucfirst($cand['status']); ?></span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick='viewCandidateDetails(<?php echo json_encode($cand); ?>)'>
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No candidates found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Candidate Detail Modal -->
<div class="modal fade" id="candidateDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header d-print-none">
                <h5 class="modal-title">Candidate Resume View</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="candidateDetailContent">
                <!-- Content loaded via JS -->
            </div>
            <div class="modal-footer d-print-none">
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print me-2"></i> Print Resume
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    #candidateDetailModal, #candidateDetailModal * { visibility: visible; }
    #candidateDetailModal { position: absolute; left: 0; top: 0; width: 100%; border: none; box-shadow: none; }
    .modal-content { border: none !important; }
    .modal-header, .modal-footer, .btn-close { display: none !important; }
    .d-print-none { display: none !important; }
}

.resume-container {
    background: #fff;
    padding: 20px;
    font-family: 'Inter', sans-serif;
    color: #333;
    max-width: 1000px;
    margin: 0 auto;
}

.resume-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0px;
}

.resume-table th, .resume-table td {
    border: 1px solid #94a3b8;
    padding: 6px 10px;
    font-size: 13px;
}

.resume-table th {
    background-color: #f1f5f9;
    font-weight: 600;
    text-align: left;
    width: 30%;
}

.section-head {
    background: #e2e8f0;
    text-align: center;
    font-weight: 800;
    padding: 8px;
    font-size: 16px;
    border: 1px solid #94a3b8;
    text-transform: uppercase;
}

.candidate-photo {
    width: 150px;
    height: 180px;
    border: 1px solid #94a3b8;
    object-fit: cover;
}

.signature-img {
    max-height: 60px;
    max-width: 200px;
    border-bottom: 1px solid #333;
}
</style>

<script>
function viewCandidateDetails(cand) {
    // Parse JSON data
    let edu = [];
    try { edu = cand.education ? JSON.parse(cand.education) : []; } catch(e) { console.error(e); }
    
    let certs = [];
    try { certs = cand.professional_certs ? JSON.parse(cand.professional_certs) : []; } catch(e) { console.error(e); }

    const hostPath = window.location.pathname.split('/modules/')[0];
    const photoUrl = cand.photo_path ? `${hostPath}/public/${cand.photo_path}` : 'https://via.placeholder.com/150x180?text=No+Photo';
    const sigUrl = cand.signature_path ? `${hostPath}/public/${cand.signature_path}` : '';

    let eduRows = '';
    for(let i=0; i<4; i++) {
        const item = edu[i] || {};
        let gradeDisplay = '-';
        if (item.div && item.grade) gradeDisplay = `${item.div} / ${item.grade}`;
        else if (item.div) gradeDisplay = item.div;
        else if (item.grade) gradeDisplay = item.grade;

        eduRows += `
            <tr>
                <td class="text-center">${item.exam || '-'}</td>
                <td class="text-center">${item.board || '-'}</td>
                <td class="text-center">${item.year || '-'}</td>
                <td class="text-center">${gradeDisplay}</td>
                <td class="text-center">${item.subject || '-'}</td>
            </tr>
        `;
    }

    let certRows = '';
    for(let i=0; i<2; i++) {
        const item = certs[i] || {};
        certRows += `
            <tr>
                <td class="text-center">${item.name || '-'}</td>
                <td class="text-center">${item.year || '-'}</td>
                <td class="text-center">${item.grade || '-'}</td>
            </tr>
        `;
    }

    const content = `
        <div class="resume-container">
            <div class="section-head mb-3">View Resume</div>
            
            <table class="resume-table mb-0">
                <tr>
                    <td colspan="3" class="p-0">
                        <table class="w-100 border-0">
                             <tr>
                                <th style="width: 25%;">Full Name of Candidate</th>
                                <td style="width: 50%;">${cand.name}</td>
                                <td rowspan="8" style="width: 25%; text-align: center; vertical-align: middle;">
                                    <img src="${photoUrl}" class="candidate-photo" alt="Photo">
                                </td>
                             </tr>
                             <tr><th>Name of Father</th><td>${cand.father_name || '-'}</td></tr>
                             <tr><th>Name of Mother</th><td>${cand.mother_name || '-'}</td></tr>
                             <tr><th>Name of Spouse</th><td>${cand.spouse_name || '-'}</td></tr>
                             <tr><th>Date of Birth</th><td>${cand.dob || '-'}</td></tr>
                             <tr><th>Home District</th><td>${cand.home_district || '-'}</td></tr>
                             <tr><th>Upazila</th><td>${cand.thana_upazila || '-'}</td></tr>
                             <tr><th>Nationality</th><td>${cand.nationality || 'Bangladeshi'}</td></tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div class="mt-0">
                <table class="resume-table">
                    <thead>
                        <tr class="bg-light">
                            <th colspan="5" class="py-1 px-2" style="background:#f1f5f9; width:100%">Educational Qualification</th>
                        </tr>
                        <tr class="text-center">
                            <th class="text-center" style="width: 20%;">Examination</th>
                            <th class="text-center" style="width: 25%;">Board / University</th>
                            <th class="text-center" style="width: 15%;">Passing Year</th>
                            <th class="text-center" style="width: 20%;">Div / Class / Grade</th>
                            <th class="text-center" style="width: 20%;">Subj / Group</th>
                        </tr>
                    </thead>
                    <tbody>${eduRows}</tbody>
                </table>
            </div>

            <div class="mt-0">
                <table class="resume-table border-top-0">
                    <thead>
                        <tr class="bg-light">
                            <th colspan="3" class="py-1 px-2" style="background:#f1f5f9; width:100%">Professional Certification</th>
                        </tr>
                        <tr class="text-center">
                            <th class="text-center" style="width: 33%;">Certification Name</th>
                            <th class="text-center" style="width: 33%;">Attending Year</th>
                            <th class="text-center" style="width: 33%;">Achieved Grade</th>
                        </tr>
                    </thead>
                    <tbody>${certRows}</tbody>
                </table>
            </div>

            <div class="mt-0">
                <table class="resume-table">
                    <tr class="bg-light">
                        <th colspan="5" class="py-1 px-2" style="background:#f1f5f9; width:100%">Experience Details</th>
                    </tr>
                    <tr class="text-center">
                        <th class="text-center" style="width: 15%;">Sector</th>
                        <th class="text-center" style="width: 25%;">Organization</th>
                        <th class="text-center" style="width: 15%;">Position</th>
                        <th class="text-center" style="width: 30%;">Responsibility</th>
                        <th class="text-center" style="width: 15%;">Period</th>
                    </tr>
                    <tr>
                        <td class="text-center">${cand.experience_level || '-'}</td>
                        <td class="text-center">${cand.last_company || '-'}</td>
                        <td class="text-center">${cand.last_job_title || '-'}</td>
                        <td class="text-center">${cand.responsibilities || '-'}</td>
                        <td class="text-center">${cand.experience_duration || '-'}</td>
                    </tr>
                </table>
            </div>

            <table class="resume-table border-top-0">
                <tr><th style="width: 25%;">Gender</th><td>${cand.gender || '-'}</td></tr>
                <tr><th>Marital Status</th><td>${cand.marital_status || '-'}</td></tr>
                <tr><th>National ID Card No.</th><td>${cand.nid_no || '-'}</td></tr>
                <tr><th>E-mail</th><td>${cand.email}</td></tr>
                <tr><th>Tel / Mobile Number</th><td>${cand.phone}</td></tr>
                <tr><th>Present (Mailing) Address</th><td>${cand.present_address || '-'}</td></tr>
                <tr><th>Permanent Address</th><td>${cand.permanent_address || '-'}</td></tr>
                <tr><th>Professional Skills</th><td>${cand.skills || '-'}</td></tr>
                <tr><th>Other Skill</th><td>${cand.other_skills || '-'}</td></tr>
                <tr><th>Portfolio Link</th><td>${cand.portfolio_link ? `<a href="${cand.portfolio_link}" target="_blank">${cand.portfolio_link}</a>` : '-'}</td></tr>
            </table>

            <div class="mt-4 text-center d-flex flex-column align-items-center" style="margin-top: 30px;">
                ${sigUrl ? `<img src="${sigUrl}" class="signature-img mb-1">` : '<div style="height: 60px; width: 200px; border-bottom: 1px solid #333;"></div>'}
                <div class="small fw-bold">Candidate Signature</div>
            </div>
        </div>
    `;
    document.getElementById('candidateDetailContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('candidateDetailModal')).show();
}
</script>

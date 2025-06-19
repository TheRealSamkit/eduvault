<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report']) && isLoggedIn()) {
    $reported_id = (int) $_POST['reported_id'];
    $reported_content = mysqli_real_escape_string($mysqli, $_POST['reported_content']);
    $reason = mysqli_real_escape_string($mysqli, $_POST['report_reason']);
    $user_id = $_SESSION['user_id'];

    if (!empty($reason) && $reported_id > 0) {
        $stmt = $mysqli->prepare("INSERT INTO reported_content (reporter_id, content_type, content_id, reason, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("isis", $user_id, $reported_content, $reported_id, $reason);
        $stmt->execute();
        $stmt->close();
        flash('success', 'Thank you for your report. We will review it shortly.');
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        flash('error', 'Please provide a valid reason for reporting.');
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

?>
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportModalLabel">Report
                    </h5>
                    <button type="button" class="btn fs-3 color" data-bs-dismiss="modal" aria-label="Close"><i
                            class="fas fa-times"></i></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="hidden" name="reported_id" id="modalReportedId">
                        <input type="hidden" name="reported_content" id="modalReportedContent">
                        <label for="report_reason" class="form-label">Reason for reporting:</label>
                        <textarea class="form-control bg-dark-body" id="report_reason" name="report_reason" rows="3"
                            required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit_report" class="btn btn-danger">Submit Report</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $additionalScripts[] = 'modal.js' ?>
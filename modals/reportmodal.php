<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="/eduvault/handlers/report_handler.php">
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
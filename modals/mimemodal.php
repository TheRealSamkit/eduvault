<div class="modal fade" id="addMimeModal" tabindex="-1" aria-labelledby="addMimeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMimeModalLabel">Add New MIME Type</h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Extension (without dot)</label>
                        <input type="text" class="form-control bg-dark-body" name="extension" required
                            placeholder="e.g., pdf, docx">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">MIME Type</label>
                        <input type="text" class="form-control bg-dark-body" name="mime_types" required
                            placeholder="e.g., application/pdf">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" name="add_mime">Add MIME Type</button>
                </div>
            </div>
        </form>
    </div>
</div>
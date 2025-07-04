<!-- Add Year Modal -->
<div class="modal fade" id="addYearModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Year</h5>
                <button type="button" class="btn-close bg-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Year</label>
                        <input type="text" class="form-control bg-dark-body" name="year" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_year" class="btn btn-primary">Add Year</button>
                </div>
            </form>
        </div>
    </div>
</div>
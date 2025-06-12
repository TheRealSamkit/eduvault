<div class="modal fade" id="addSubjectModal" tabindex="-1" aria-labelledby="addSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSubjectModalLabel">Add New Subject</h5>
                <button type="button" class="btn-close bg-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="subjectName" class="form-label">Subject Name</label>
                    <input type="text" class="form-control bg-dark-body" name="subject_name" id="subjectName" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_subject" class="btn btn-primary">Add Subject</button>
            </div>
        </form>
    </div>
</div>
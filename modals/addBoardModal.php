<!-- Add Board Modal -->
<div class="modal fade" id="addBoardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Board</h5>
                <button type="button" class="btn-close bg-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Board Name</label>
                        <input type="text" class="form-control bg-dark-body" name="board_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_board" class="btn btn-primary">Add Board</button>
                </div>
            </form>
        </div>
    </div>
</div>
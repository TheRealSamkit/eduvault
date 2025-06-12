<div class="modal fade" id="addBoardModal" tabindex="-1" aria-labelledby="addBoardModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBoardModalLabel">Add New Board</h5>
                <button type="button" class="btn-close bg-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="boardName" class="form-label">Board Name</label>
                    <input type="text" class="form-control bg-dark-body" name="board_name" id="boardName" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_board" class="btn btn-primary">Add Board</button>
            </div>
        </form>
    </div>
</div>
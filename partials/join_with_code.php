<?php
require_once '../includes/db.php';
?>

<div class="card border-0 p-4 mt-5 mb-5 slide-right" id="duel-config">
    <div class="text-center">

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="index.php" method="POST">
            <input type="hidden" name="form_type" value="join_with_code">

            <h1 class="mb-4">Join a <span class="text-highlight">Duel</span></h1>
            <div class="mb-3">
                <label for="duelCode" class="form-label">Enter Duel Code</label>
                <input type="text" class="form-control" name="duelCode" id="duelCode" required>
            </div>
            <button type="submit" class="btn btn-primary btn-lg px-5 mb-4" id="joinDuelBtn">Join Duel</button>
        </form>
    </div>
</div>
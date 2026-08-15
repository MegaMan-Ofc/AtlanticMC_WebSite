<?php $flashes = consume_flashes(); ?>
<?php if ($flashes !== []): ?>
    <div class="container flash-stack" aria-live="polite">
        <?php foreach ($flashes as $flashMessage): ?>
            <div class="flash flash--<?= e($flashMessage['type'] ?? 'info') ?>">
                <?= e($flashMessage['message'] ?? '') ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

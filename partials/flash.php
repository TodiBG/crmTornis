<?php
/**
 * Ce partiel centralise l'affichage des messages flash.
 * Une page peut donc simplement l'inclure sans recopier le meme bloc HTML.
 */
if (isset($_SESSION['flash_message']) && isset($_SESSION['flash_type'])):
?>
    <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_type']) ?> alert-dismissible fade show shadow-sm" role="alert">
        <?= htmlspecialchars($_SESSION['flash_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
<?php endif; ?>

<?php
// Cette page affiche la liste des utilisateurs du CRM.
session_start();
require_once __DIR__ . '/../config/db_connect.php';

// On trie les utilisateurs du plus recent au plus ancien pour retrouver facilement les derniers comptes crees.
$users = fetchManyFromDB(
    'SELECT id, firstname, lastname, email, role, avatar_url, created_at FROM users ORDER BY created_at DESC, id DESC'
);

$pageTitle = 'CRM Tornis - Utilisateurs';
$activePage = 'users';
$basePath = '../';

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<main class="container my-5">
    <div class="bg-white p-4 p-md-5 rounded-3">
        <?php if (isset($_SESSION['flash_message']) && isset($_SESSION['flash_type'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_type']) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['flash_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-2">Liste des utilisateurs</h1>
                <p class="text-muted mb-0">Consultez les comptes qui peuvent administrer le CRM.</p>
            </div>
            <a href="create.php" class="btn text-white" style="background-color: #0B3041;">
                Ajouter un utilisateur
            </a>
        </div>

        <?php if ($users === false): ?>
            <div class="alert alert-danger mb-0" role="alert">
                Une erreur est survenue lors du chargement des utilisateurs.
            </div>
        <?php elseif ($users === []): ?>
            <div class="border rounded-3 p-4 text-center text-muted">
                Aucun utilisateur n'est encore enregistre.
            </div>
        <?php else: ?>
            <!-- Le tableau donne une vue d'ensemble des comptes d'administration. -->
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Utilisateur</th>
                            <th scope="col">Email</th>
                            <th scope="col">Role</th>
                            <th scope="col">Avatar</th>
                            <th scope="col">Ajoute le</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="fw-semibold py-3"><?= htmlspecialchars(trim($user['firstname'] . ' ' . $user['lastname'])) ?></td>
                                <td class="py-3"><?= htmlspecialchars($user['email']) ?></td>
                                <td class="py-3">
                                    <?php if ($user['role'] === 'admin'): ?>
                                        <span class="badge bg-dark">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($user['role']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3">
                                    <?php
                                    // Si aucun avatar personnalise n'est enregistre, on utilise une image par defaut.
                                    $avatarPath = isset($user['avatar_url']) && trim((string) $user['avatar_url']) !== ''
                                        ? $user['avatar_url']
                                        : '/assets/images/avatars/default.jpg';
                                    ?>
                                    <img
                                        src="../<?= htmlspecialchars($avatarPath) ?>"
                                        alt="Avatar de <?= htmlspecialchars(trim($user['firstname'] . ' ' . $user['lastname'])) ?>"
                                        class="rounded-circle border"
                                        style="width: 48px; height: 48px; object-fit: cover;">
                                </td>
                                <td class="py-3"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($user['created_at']))) ?></td>
                                <td class="text-end text-nowrap py-3">
                                    <a href="edit.php?id=<?= htmlspecialchars((string) $user['id']) ?>" class="btn btn-sm text-secondary border-0" title="Modifier l'utilisateur" aria-label="Modifier l'utilisateur">
                                        <i class="bi bi-pencil"></i>
                                        <span class="visually-hidden">Modifier</span>
                                    </a>
                                    <button
                                        type="button"
                                        class="btn btn-sm text-danger border-0"
                                        title="Supprimer l'utilisateur"
                                        aria-label="Supprimer l'utilisateur"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteUserModal"
                                        data-user-id="<?= htmlspecialchars((string) $user['id']) ?>"
                                        data-user-name="<?= htmlspecialchars(trim($user['firstname'] . ' ' . $user['lastname'])) ?>">
                                        <i class="bi bi-trash"></i>
                                        <span class="visually-hidden">Supprimer</span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="deleteUserModalLabel">Supprimer l'utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Voulez-vous vraiment supprimer cet utilisateur ?</p>
                <p class="mb-0 fw-semibold" id="deleteUserName"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <form action="delete.php" method="post" class="m-0">
                    <input type="hidden" name="id" id="deleteUserId">
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var deleteModal = document.getElementById('deleteUserModal');

        if (!deleteModal) {
            return;
        }

        deleteModal.addEventListener('show.bs.modal', function (event) {
            var triggerButton = event.relatedTarget;
            deleteModal.querySelector('#deleteUserId').value = triggerButton.getAttribute('data-user-id');
            deleteModal.querySelector('#deleteUserName').textContent = triggerButton.getAttribute('data-user-name');
        });
    });
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

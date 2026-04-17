<?php

/**
 * Cette fonction recupere le chemin du script courant a l'interieur du projet.
 * Exemple : "products/index.php" ou "auth/login.php".
 */
function getCurrentRelativePhpPath(): string
{
    $projectRoot = realpath(__DIR__ . '/..');
    $scriptFilename = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');

    if ($projectRoot === false || $scriptFilename === false) {
        return '';
    }

    $relativePath = substr($scriptFilename, strlen($projectRoot));

    return ltrim(str_replace('\\', '/', (string) $relativePath), '/');
}

/**
 * Cette fonction reconstruit l'URL de base du projet.
 * Elle permet de faire des redirections fiables meme si le projet n'est pas
 * installe a la racine du serveur web.
 */
function getProjectBaseUrl(): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $relativePath = getCurrentRelativePhpPath();

    if ($relativePath !== '' && str_ends_with($scriptName, $relativePath)) {
        return rtrim(substr($scriptName, 0, -strlen($relativePath)), '/');
    }

    return rtrim(dirname($scriptName), '/');
}

/**
 * Construit une URL absolue a l'echelle du projet a partir d'un chemin interne.
 * Exemple : "/auth/login.php" devient "/crm/auth/login.php".
 */
function buildProjectUrl(string $path): string
{
    $baseUrl = getProjectBaseUrl();

    return ($baseUrl !== '' ? $baseUrl : '') . '/' . ltrim($path, '/');
}

/**
 * Renvoie l'utilisateur connecte ou null si aucune session d'authentification n'existe.
 */
function getAuthenticatedUser(): ?array
{
    return isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user'])
        ? $_SESSION['auth_user']
        : null;
}

/**
 * Teste simplement la presence d'un utilisateur dans la session.
 */
function isAuthenticated(): bool
{
    return getAuthenticatedUser() !== null;
}

/**
 * Memorise l'utilisateur connecte dans la session.
 * On regenere l'identifiant de session pour limiter les risques de fixation de session.
 */
function loginUser(array $user): void
{
    session_regenerate_id(true);

    $_SESSION['auth_user'] = [
        'id' => (int) $user['id'],
        'firstname' => $user['firstname'],
        'lastname' => $user['lastname'],
        'email' => $user['email'],
        'role' => $user['role'],
        'avatar_url' => $user['avatar_url'] ?? null,
    ];
}

/**
 * Supprime uniquement les informations d'authentification.
 * On conserve la session pour pouvoir afficher un message flash apres la deconnexion.
 */
function logoutUser(): void
{
    unset($_SESSION['auth_user'], $_SESSION['intended_path']);
    session_regenerate_id(true);
}

/**
 * Conserve la page demandee initialement afin d'y revenir apres connexion.
 */
function storeIntendedPath(): void
{
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $baseUrl = getProjectBaseUrl();

    if ($requestUri === '' || !str_starts_with($requestUri, $baseUrl)) {
        return;
    }

    $intendedPath = substr($requestUri, strlen($baseUrl));

    if ($intendedPath !== false && $intendedPath !== '') {
        $_SESSION['intended_path'] = $intendedPath;
    }
}

/**
 * Redirige vers une page interne du projet.
 */
function redirectToPath(string $path): void
{
    header('Location: ' . buildProjectUrl($path));
    exit;
}

/**
 * Utilise l'URL memorisee avant authentification, sinon une page par defaut.
 */
function redirectToIntendedOr(string $defaultPath): void
{
    $intendedPath = $_SESSION['intended_path'] ?? null;
    unset($_SESSION['intended_path']);

    if (is_string($intendedPath) && str_starts_with($intendedPath, '/')) {
        redirectToPath($intendedPath);
    }

    redirectToPath($defaultPath);
}

/**
 * Redirige les visiteurs non connectes vers la page de connexion.
 */
function requireAuthentication(): void
{
    if (isAuthenticated()) {
        return;
    }

    storeIntendedPath();
    $_SESSION['flash_message'] = 'Veuillez vous connecter pour acceder a cette page.';
    $_SESSION['flash_type'] = 'warning';
    redirectToPath('/auth/login.php');
}

/**
 * Si l'utilisateur est deja connecte, on evite de lui re-montrer le formulaire de connexion.
 */
function redirectIfAuthenticated(string $defaultPath = '/customers/index.php'): void
{
    if (isAuthenticated()) {
        redirectToIntendedOr($defaultPath);
    }
}

/**
 * Toutes les routes du CRM sont protegees, sauf les pages techniques du dossier auth
 * qui servent justement a se connecter ou se deconnecter.
 */
function currentRouteNeedsAuthentication(): bool
{
    $relativePath = getCurrentRelativePhpPath();
    $publicRoutes = [
        'auth/login.php',
        'auth/authenticate.php',
        'auth/logout.php',
    ];

    return $relativePath !== '' && !in_array($relativePath, $publicRoutes, true);
}

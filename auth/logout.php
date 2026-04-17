<?php
session_start();
require_once __DIR__ . '/../config/auth.php';

// La deconnexion retire l'utilisateur de la session puis renvoie vers l'accueil public.
logoutUser();
redirectToPath('/index.php');

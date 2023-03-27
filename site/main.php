<?php




if (isset($_POST['username']) && isset($_POST['password'])) {
    // Vérifie si les données d'identification sont correctes
    if ($_POST['username'] === 'utilisateur' && $_POST['password'] === 'motdepasse') {
        // Démarre une session et redirige l'utilisateur vers une page protégée
        session_start();
        $_SESSION['username'] = $_POST['username'];
        header('Location: page_protegee.php');
    } else {
        // Affiche un message d'erreur si les données d'identification sont incorrectes
        echo "Nom d'utilisateur ou mot de passe incorrect.";
    }
}
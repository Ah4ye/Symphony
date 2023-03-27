<?php

session_start();
bd_connexion();


include_once('bd.php');


// TODO 3.7 : déconnexion du serveur de bases de données
bd_deconnexion();


function setAuthticate()
{
    // TODO 3.7
    $_SESSION['auth'] = true;
}

// fonction indiquant que l'internaute courant n'est pas/plus correctement authentifié
function unsetAuthenticate()
{
    // TODO 3.7
    $_SESSION['auth'] = false;
}

// function indiquant si l'internaute courant est correctement authentifié
function isAuthenticate()
{
    return isset($_SESSION['auth']) && ($_SESSION['auth'] === true);  // TODO 3.7 : remplacer par le code correct
}

// fonction redirigeant l'internaute vers la page main.php s'il n'est pas authentifié
function exitIfNotAuthenticate()
{
    // TODO 3.7
    if (! isAuthenticate())
    {
        return redirectToRoute('accueil_index');
        exit(1);   // code de paranoïaque
    }
}
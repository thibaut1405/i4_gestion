<?php

include 'connect.php';
include 'ressources.php';

function upload_files($PRO_id, $link)
{
    foreach ($_FILES["PRO_ressources"]["error"] as $key => $error) {
        if ($error == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES["PRO_ressources"]["tmp_name"][$key];
            $extension = pathinfo($_FILES["PRO_ressources"]["name"][$key], PATHINFO_EXTENSION);
            $md5 = md5_file($tmp_name);
            $name = $md5 . "." . $extension;
            $content = file_get_contents($tmp_name);
            upload($name, $content);
            $sql = "INSERT INTO ressources (RE_type,RE_url,PRO_id) VALUES ('img', '$name', $PRO_id)";
            mysqli_query($link, $sql);
        }
    }
}
function delete_file($RE_id, $link, $key)
{
    $sql = "DELETE FROM ressources WHERE RE_id = '$RE_id'";
    if (mysqli_query($link, $sql)) {
        delete($key);
    }
}
$action = (isset($_POST['action'])) ? $_POST['action'] : $_GET['action'];
switch ($action) {
    case 'ajout_produit':
        $PRO_lib = ($_POST['PRO_lib'] != '') ? "'" . mysqli_real_escape_string($link, $_POST['PRO_lib']) . "'" : 'null';
        $PRO_description = ($_POST['PRO_description'] != '') ? "'" . mysqli_real_escape_string($link, $_POST['PRO_description']) . "'" : 'null';
        $PRO_prix = ($_POST['PRO_prix'] != '') ? "'" . mysqli_real_escape_string($link, str_replace(',', '.', $_POST['PRO_prix'])) . "'" : 'null';
        $sql = "INSERT INTO produits (PRO_lib, PRO_description, PRO_prix) VALUES ($PRO_lib,$PRO_description,$PRO_prix)";
        if (mysqli_query($link, $sql)) {
            $PRO_id = mysqli_insert_id($link);
            upload_files($PRO_id, $link);
            header('Location: home.php');
        } else {
            die("Erreur SQL");
        }
        break;
    case 'modification_produit':
        $PRO_id = ($_POST['PRO_id'] != '') ? "'" . mysqli_real_escape_string($link, $_POST['PRO_id']) . "'" : 'null';
        $PRO_lib = ($_POST['PRO_lib'] != '') ? "'" . mysqli_real_escape_string($link, $_POST['PRO_lib']) . "'" : 'null';
        $PRO_description = ($_POST['PRO_description'] != '') ? "'" . mysqli_real_escape_string($link, $_POST['PRO_description']) . "'" : 'null';
        $PRO_prix = ($_POST['PRO_prix'] != '') ? "'" . mysqli_real_escape_string($link, str_replace(',', '.', $_POST['PRO_prix'])) . "'" : 'null';
        $sql = "UPDATE produits SET PRO_lib = $PRO_lib, PRO_description = $PRO_description, PRO_prix = $PRO_prix WHERE PRO_id = $PRO_id";
        if (mysqli_query($link, $sql)) {
            upload_files($PRO_id, $link);
            header('Location: produit.php?id=' . $_POST['PRO_id']);
        } else {
            die("Erreur SQL");
        }
        break;
    case 'supprimer_ressource':
        if (isset($_POST['RE_id'])) {
            $RE_id = mysqli_real_escape_string($link, $_POST['RE_id']);
            $sql = "SELECT * FROM ressources WHERE RE_id = $RE_id";
            $res = mysqli_query($link, $sql);
            if (mysqli_num_rows($res) > 0) {
                $ressource = mysqli_fetch_assoc($res);
                delete_file($RE_id, $link, $ressource['RE_url']);
            } else {
                echo 'NOK';
            }
        }
        break;
    case 'supprimer_produit':
        if (isset($_POST['PRO_id'])) {
            $PRO_id = mysqli_real_escape_string($link, $_POST['PRO_id']);
            $sql = "SELECT * FROM produits WHERE PRO_id = $PRO_id";
            $res = mysqli_query($link, $sql);
            if (mysqli_num_rows($res) > 0) {
                $produit = mysqli_fetch_assoc($res);
                $sql = "SELECT * FROM ressources WHERE PRO_id = $PRO_id";
                $res = mysqli_query($link, $sql);
                if (mysqli_num_rows($res) > 0) {
                    while ($ressource = mysqli_fetch_assoc($res)) {
                        $RE_id = $ressource['RE_id'];
                        delete_file($RE_id, $link, $ressource['RE_url']);
                    }
                }
                $sql = "DELETE FROM produits WHERE PRO_id = $PRO_id";
                if (mysqli_query($link, $sql)) {
                    echo 'OK';
                } else {
                    echo 'NOK';
                }
            } else {
                echo 'NOK';
            }
        }
        break;
    default:
        # code...
        break;
}
?>


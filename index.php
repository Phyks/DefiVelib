<?php
    session_start();
    date_default_timezone_set("Europe/Paris");

    function search($array) {
        global $_POST;
        return in_array($_POST['start_search'], $array) && in_array($_POST['end_search'], $array);
    }

    // Generates a token against CSRF
    // ==============================
    function generate_token($name = '') {
        if(session_id() == '')
            session_start();

        $token = uniqid(rand(), true);

        $_SESSION[$name.'_token'] = $token;
        $_SESSION[$name.'_token_time'] = time();

        return $token;
    }

    // Checks that the anti-CSRF token is correct
    // ==========================================
    function check_token($time, $name = '') {
        if(session_id() == '')
            session_start();

        if(isset($_SESSION[$name.'_token']) && isset($_SESSION[$name.'_token_time']) && (isset($_POST['token']) || isset($_GET['token']))) {
            if(!empty($_POST['token']))
                $token = $_POST['token'];
            else
                $token = $_GET['token'];

            if($_SESSION[$name.'_token'] == $token) {
                if($_SESSION[$name.'_token_time'] >= (time() - (int) $time))
                    return true;
            }
        }
        return false;
    }

    if(is_file('data/data')) {
        $data = unserialize(gzinflate(base64_decode(file_get_contents('data/data'))));
    }
    else {
        $data = array();
    }

    if(!empty($_GET['suppr']) && !empty($_SESSION['admin']) && !empty($_GET['token'])) {
        if(check_token(600, 'defivelib')) {
            unlink($data[$_GET['suppr']]);
        }
        else {
            exit("Mauvais token, veuillez réessayer.");
        }
    }

    if(!empty($_GET['deco'])) {
        session_destroy();
    }

    if(is_file('data/config')) {
        $config = unserialize(gzinflate(base64_decode(file_get_contents('data/config'))));

        if(!empty($_GET['code']) && $_GET['code'] == $config[0]) {
            $_SESSION['admin'] = true;
        }
    }

    $search = false;
    if(!empty($_POST['start_search']) && !empty($_POST['end_search']) & !empty($_POST['token'])) {
        if(check_token(600, 'defivelib')) {
            $search = true;
            $data = array_filter($data, "search");
        }
        else {
            exit("Mauvais token, veuillez réessayer.");
        }
    }

    if((!empty($_POST['time_min']) || !empty($_POST['time_sec'])) && !empty($_POST['start']) && !empty($_POST['end'])) {
        if(check_token(600, 'defivelib')) {
            $min = (!empty($_POST['time_min'])) ? (int) $_POST['time_min'] : 0;
            $sec = (!empty($_POST['time_sec'])) ? (int) $_POST['time_sec'] : 0;
            $pseudo = (!empty($_POST['pseudo'])) ? $_POST['pseudo'] : "Anonyme";

            $data[] = array("date"=>time(), "start"=>(int) $_POST['start'], "end"=>(int) $_POST['end'], "min"=>$min, "sec"=>$sec, "pseudo"=>$pseudo);

            // TODO : Upload + taille max de l'upload

            if(count($data) == 1 || $min != $data[count($data)-2]['min'] || $sec != $data[count($data)-2]['sec'] || $_POST['start'] != $data[count($data)-2]['start'] || $_POST['end'] != $data[count($data)-2]['end'] || $pseudo != $data[count($data)-2]['pseudo']) {
                file_put_contents('data/data', base64_encode(gzdeflate(serialize($data))));
            }
        }
        else {
            exit("Mauvais token, veuillez réessayer.");
        }
    }

    $token = generate_token('defivelib');
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
		<title>DéfiVélib</title>
		<meta name="author" content="phyks">
        <style type="text/css" media="screen">
html, body {
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100%;
    font-family: sans-serif;
    background-color: lightgrey;
    background-image: url(data:image/png;base64,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);
}

#main {
    width: 50%;
    min-height: 50%;
    margin: auto;
    background-color: white;
    text-align: center;
    padding: 1em;
    -moz-box-sizing: border-box; -webkit-box-sizing: border-box; box-sizing: border-box;
}

@media (max-width: 1050px) {
    #main {
        width: 100% !important;
    }
}

#main h1 {
    margin: 0;
    border: 1px solid black;
    background-color: rgb(117, 170, 39);
}

#main h2 {
    text-align: left;
}

#main h1 a {
    color: black;
    text-decoration: none;
    font-size: 1.5em;
}

table td, th {
    padding: 0.5em;
    border: 1px solid black;
}

table th {
    background-color: rgba(117, 170, 39, 0.4);
}

table {
    border-collapse: collapse;
    margin: auto;
}

fieldset {
    background-color: rgba(17, 78, 121, 0.1);
}

#disclaimer {
    font-size: 0.75em;
}

#disclaimer p {
    text-align: left;
}
</style>
	</head>
	<body>
<div id="main">
		<h1><a href="index.php">DéfiVélib</a></h1>
        <?php
        if(!is_dir('data/')) {
            mkdir('data/');
        }

        if(!is_file('data/config')) //First run
        {
            //Define a new synchronisation code
            $code_synchro = substr(sha1(rand(0,30).time().rand(0,30)),0,10);

            file_put_contents('data/config', base64_encode(gzdeflate(serialize(array($code_synchro))))); //Save it in data/data file

            $_GET['code'] = $code_synchro;

            echo "<p>
                Définition du code de synchronisation.<br/>
                Vous pouvez désormais mettre à jour la liste des stations en visitant l'adresse suivante (update URL) :<br/>
                <a href='http://" . $_SERVER["SERVER_NAME"].$_SERVER['REQUEST_URI']."?update=1&code=".$code_synchro."'>http://" . $_SERVER["SERVER_NAME"].$_SERVER['REQUEST_URI']."?update=1&code=".$code_synchro."</a>
                </p>
                <p>
                Il est possible d'automatiser la tâche via une tâche cron. Par exemple (see README) :<br/>
                * * * * * wget -q -O <a href='http://" . $_SERVER["SERVER_NAME"].$_SERVER['REQUEST_URI']."?update=1&code=".$code_synchro."'>http://" . $_SERVER["SERVER_NAME"].$_SERVER['REQUEST_URI']."?update=1&code=".$code_synchro."</a> #Commande de mise a jour des stations de velib
                </p>";
        }

        if(!empty($_GET['update']) || !empty($code_synchro)) //If we want to make an update (or first run)
        {
            if(empty($code_synchro) && is_file('data/config')) //If not first run, get the synchronisation code from data file
            {
                $data = unserialize(gzinflate(base64_decode(file_get_contents('data/config'))));
                $code_synchro = $data[0];
            }

            if(!empty($_GET['code']) && $_GET['code'] == $code_synchro) //Once we have the code and it is correct
            {
                $stations_xml = simplexml_load_file('http://www.velib.paris.fr/service/carto');

                $liste_stations = array();
                foreach($stations_xml->markers->marker as $station) {
                    $liste_stations[(int) $station['number']] = array('name'=>(string) $station['name'], 'address'=>(string) $station['fullAddress'], 'lat'=>(float) $station['lat'], 'lng'=>(float) $station['lng']);
                }

                file_put_contents('data/stations', base64_encode(gzdeflate(serialize($liste_stations))));

                echo "<p>Mise à jour de la liste des stations effectuée avec succès (Update successful).</p>";
            }
            else
            {
                echo "<p>Mauvais code de vérification (Error : bad synchronisation code). Veuillez réessayer la mise à jour. Se référer au README pour plus d'informations sur la mise à jour.</p>";
            }
            echo "<p><a href='index.php'>Retourner à l'application (Back to index)</a></p></body></html>";
            exit();
        }
        $liste_stations = unserialize(gzinflate(base64_decode(file_get_contents('data/stations'))));
    ?>
<div id="disclaimer">
    <h2>Disclaimer</h2>
    <p>Les temps rentrés sur cette page ne sont qu'indicatifs et sont fournis par les internautes eux-mêmes. Ils peuvent donc ne pas refléter les temps réels de parcours. En particulier, il est important de rappeler que le code de la route s'applique aussi aux vélos et que l'obtention d'un meilleur temps ne doit pas se faire au détriment du respect du code de la route.</p>
    <p>Le respect des données personnelles étant particulièrement important, ce script ne conserve aucune information particulière si vous ne souhaitez pas en renseigner. Votre adresse IP est néanmoins stockée dans les logs de connexion au serveur, comme pour tout serveur web, conformément à l'article 6 de la LCEN.</p>
    <p><a href="README.md">Plus d'informations sur DefiVelib</a></p>
</div>
    <h2>Ajouter un trajet</h2>
    <form method="post" action="index.php"> <!-- enctype="multipart/form-data"-->
        <fieldset>
            <legend>Trajet</legend>
        <p><label name="start">Station de départ : </label>
            <select name="start" id="start">
                <?php
                    foreach($liste_stations as $key=>$station) {
                        if(!empty($_POST['start_search']) && $_POST['start_search'] == $key)
                            $selected = "selected";
                        else
                            $selected = "";

                        echo "<option value=\"".$key."\" ".$selected.">".$station['name']."</option>";
                    }
                ?>
            </select>
        </p>
        <p><label for="end">Station d'arrivée : </label>
            <select name="end" id="end">
                <?php
                    foreach($liste_stations as $key=>$station) {
                        if(!empty($_POST['end_search']) && $_POST['end_search'] == $key)
                            $selected = "selected";
                        else
                            $selected = "";

                        echo "<option value=\"".$key."\" ".$selected.">".$station['name']."</option>";
                    }
                ?>
            </select>
        </p>
        <p><label for="time_min">Durée du trajet : </label><input type="int" name="time_min" id="time_min" size="2"/>min <input type="int" name="time_sec" id="time_sec" size="2"/>s</p>
        </fieldset>
        <fieldset>
        <legend>Informations</legend>
        <p><label for="pseudo">Votre pseudo (optionnel) : </label><input type="text" name="pseudo" id="pseudo"/></p>
<!--        <p><label for="photo">Photo du ticket (? max) : </label><input type="file" name="photo" id="photo"></p>-->
        </fieldset>
        <p>
            <input type="submit" value="Envoyer"/>
            <input type="hidden" name="token" value="<?php echo $token; ?>"/>
<!--            <input type="hidden" name="MAX_FILE_SIZE" value="2097152">-->
        </p>
    </form>
    <h2><?php if($search) {?>Résultats<?php } else {?>Derniers trajets ajoutés<?php }?></h2>
    <?php
        if(!empty($data)) {
    ?>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Départ</th>
                    <th>Arrivée</th>
                    <th>Temps</th>
                    <th>Pseudo</th>
                    <?php
                        if(!empty($_SESSION['admin'])) {
                    ?>
                            <th>Supprimer</th>
                    <?php
                        }
                    ?>
                </tr>
                <?php
                    if($search) {
                        $min = array();
                        $sec = array();
                        foreach($data as $key => $result) {
                            $min[$key] = $result['min'];
                            $sec[$key] = $result['sec'];
                        }
                        array_multisort($min, SORT_DESC, $sec, SORT_DESC, $data);

                        foreach($data as $key=>$result) {
                            if(!empty($_SESSION['admin'])) {
                                $delete = "<td><a href=\"?suppr=".$key."&token=".$token."\">Supprimer</a></td>";
                            }
                            else {
                                $delete = "";
                            }

                            echo "<tr><td>".date('d/m/Y à H:i', $result['date'])."</td><td>".htmlspecialchars($liste_stations[$result['start']]['name'])."</td><td>".htmlspecialchars($liste_stations[$result['end']]['name'])."</td><td>".(int) $result['min']."min ".(int) $result['sec']."s</td><td>".htmlspecialchars($result['pseudo'])."</td>".$delete."</tr>";
                        }
                    }
                    else {
                        for($i = count($data) - 1; $i >= max(count($data) - 10, 0); $i--) {
                            if(!empty($_SESSION['admin'])) {
                                $delete = "<td><a href=\"?suppr=".$i."&token=".$token."\">Supprimer</a></td>";
                            }
                            else {
                                $delete = "";
                            }

                            echo "<tr><td>".date('d/m/Y à H:i', $data[$i]['date'])."</td><td>".htmlspecialchars($liste_stations[$data[$i]['start']]['name'])."</td><td>".htmlspecialchars($liste_stations[$data[$i]['end']]['name'])."</td><td>".(int) $data[$i]['min']."min ".(int) $data[$i]['sec']."s</td><td>".htmlspecialchars($data[$i]['pseudo'])."</td>".$delete."</tr>";
                        }
                    }
                ?>
            </table>
    <?php
        }
        else {
    ?>
            <p>Aucun trajet enregistré.</p>
    <?php
        }
    ?>
    <h2>Recherche de trajets</h2>
    <form method="post" action="index.php">
        <fieldset>
        <p><label name="start_search">Station de départ : </label>
            <select name="start_search" id="start_search">
                <?php
                    foreach($liste_stations as $key=>$station) {
                        if(!empty($_POST['start_search']) && $_POST['start_search'] == $key)
                            $selected = "selected";
                        else
                            $selected = "";

                        echo "<option value=\"".$key."\" ".$selected.">".$station['name']."</option>";
                    }
                ?>
            </select>
        </p>
        <p><label for="end_search">Station d'arrivée : </label>
            <select name="end_search" id="end_search">
                <?php
                    foreach($liste_stations as $key=>$station) {
                        if(!empty($_POST['end_search']) && $_POST['end_search'] == $key)
                            $selected = "selected";
                        else
                            $selected = "";

                        echo "<option value=\"".$key."\" ".$selected.">".$station['name']."</option>";
                    }
                ?>
            </select>
        </p>
</fieldset>
        <p>
            <input type="submit" value="Rechercher"/>
            <input type="hidden" name="token" value="<?php echo $token; ?>"/>
        </p>
    </form>
    <?php
        if(!empty($_SESSION['admin'])) {
    ?>
            <p><a href="?deco=1">Déconnexion</a></p>
    <?php
        }
    ?>
    </div>
    </body>
</html>

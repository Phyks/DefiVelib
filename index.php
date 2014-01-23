<?php
    if(is_file('data/data')) {
        $data = unserialize(gzinflate(base64_decode(file_get_contents('data/data'))));
    }
    else {
        $data = array();
    }

    if((!empty($_POST['time_min']) || !empty($_POST['time_sec'])) && !empty($_POST['start']) && !empty($_POST['end'])) {
        $min = (!empty($_POST['time_min'])) ? (int) $_POST['time_min'] : 0;
        $sec = (!empty($_POST['time_sec'])) ? (int) $_POST['time_sec'] : 0;

        $data[] = array("start"=>(int) $_POST['start'], "end"=>(int) $_POST['end'], "min"=>$min, "sec"=>$sec);

        $last_data = end($data);

        if($min != $last_data['min'] || $sec != $last_data['sec'] || $_POST['start'] != $last_data['start'] || $_POST['end'] != $last_data['end']) {
            file_put_contents('data/data', base64_encode(gzdeflate(serialize($data))));
        }
    }
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
		<title>Vélibs à proximité</title>
		<meta name="author" content="phyks">
		<link rel="stylesheet" href="main.css" type="text/css" media="screen">
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
    <h2>Ajouter un trajet</h2>
    <form method="post" action="index.php">
        <p><label name="start">Station de départ : </label>
            <select name="start">
                <?php
                    foreach($liste_stations as $key=>$station) {
                        echo "<option value=\"".$key."\">".$station['name']."</option>";
                    }
                ?>
            </select>
        </p>
        <p><label for="end">Station d'arrivée : </label>
            <select name="end">
                <?php
                    foreach($liste_stations as $key=>$station) {
                        echo "<option value=\"".$key."\">".$station['name']."</option>";
                    }
                ?>
            </select>
        </p>
        <p><label for="time_min">Durée du trajet : </label><input type="int" name="time_min" id="time_min" size="2"/>min <input type="int" name="time_sec" id="time_sec" size="2"/>s</p>
        <p><input type="submit" value="Envoyer"></p>
    </form>
    <h2>Derniers trajets ajoutés</h2>
    <?php
        if(!empty($data)) {
    ?>
            <table>
                <tr>
                    <th>Départ</th>
                    <th>Arrivée</th>
                    <th>Temps</th>
                </tr>
                <?php
                    for($i = count($data) - 1; $i > max(count($data) - 11, 0); $i--) {
                        echo "<tr><td>".htmlspecialchars($liste_stations[$data[$i]['start']]['name'])."</td><td>".htmlspecialchars($liste_stations[$data[$i]['end']]['name'])."</td><td>".(int) $data[$i]['min']."min ".(int) $data[$i]['sec']."s</td></tr>";
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
    </div>
    </body>
</html>

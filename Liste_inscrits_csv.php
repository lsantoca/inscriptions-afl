<?php

session_start();
if (!isset($_SESSION["ID_regate"])) {
    header("Location: LoginClub.php");
}

require "partage.php";

$csv_sep = ";";
$csv_newline = "\r\n"; // we use DOS-WINDOWS

function add_csv_field($line, $str) {
    global $csv_sep;
    return $line . $str . $csv_sep;
}

function add_csv_newline($cvs) {
    global $csv_newline;
    return $cvs . $csv_newline;
}

$bat_fields = array(
    3 => array('VOILE', 'C', '9'),
    9 => array('GROUPE', 'C', '3'),
    10 => array('CLASSE_CAT', 'C', '5'),
    11 => array('E1_EMAIL', 'C', '80')
);

$equip_fields = array(
    3 => array('E1_LIC', 'C', '8'),
    4 => array('E1_NOM', 'C', '30'),
    5 => array('E1_PRE', 'C', '15'),
    6 => array('E1_NAIS', 'C', '4'),
    7 => array('E1_SEXE', 'C', '1'),
    8 => array('E1_PAYS', 'C', '3'),
    9 => array('E1_CLUB', 'C', '5'),
    11 => array('E1_ISAF', 'C', '12')
);

$correspondence = array(
    'VOILE' => array('num_voile', ''),
    'CODE_PAYS' => array('prefix_voile', ''),
    'INS_EN' => array('', 'VL'),
    'BAT_TYPE' => array('serie', ''),
    'NB_EQUIP' => array('', '1'),
    'GROUPE' => array('serie', 'LASER'),
    'CLAS_CAT' => array('serie', ''),
    'E1_NOM' => array('nom', ''),
    'E1_PRE' => array('prenom', ''),
    'E1_SEXE' => array('sexe', ''),
    'E1_NAIS' => array('E1_NAIS', ''),
    'E1_LIC' => array('num_lic', ''),
    'E1_ISAF' => array('isaf_no', ''),
    'E1_CLUB' => array('num_club', ''),
    'E1_CLUB_LI' => array('nom_club', ''),
    'E1_PAYS' => array('prefix_voile', ''),
    'E1_EMAIL' => array('mail', '')
);


function compute_csv_value($field, $row) {

    global $correspondence;

    $ascii_str = '';
    if (isset($correspondence[$field[0]])) {

        $value = $correspondence[$field[0]];
        $ascii_str = $value[1];

        if ($value[1] == '') {
            // If this is not defined in the 
            // $correspondence array
            // look for it in the db
            $ascii_str = $row[$value[0]];
            if ($value[0] == 'num_voile')
                $ascii_str = $row['prefix_voile'] . $ascii_str;

            $ascii_str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $ascii_str);
            $ascii_str = str_replace(array('\'', '^', '"'), array(), $ascii_str);

            if (($value[0] == 'prenom')
                    or ($value[0] == 'nom')
                    or ($value[0] == 'num_voile')
            )
                $ascii_str = strtoupper($ascii_str);
        }
    }

    return $ascii_str;
}

function do_bat($row, $cle) {
    global $bat_fields;

    $line = "";
    $line = add_csv_field($line, 'BAT');
    $line = add_csv_field($line, $cle);

    for ($i = 3; $i <= 11; $i++) {

        $ascii_str = "";
        if (isset($bat_fields[$i])) {
            $field = $bat_fields[$i];
            $ascii_str = compute_csv_value($field, $row);
        }
        $line = add_csv_field($line, $ascii_str);
    }
    return $line;
}

function do_equip($row, $cle) {
    global $equip_fields;
    global $correspondence;

    $line = "";
    $line = add_csv_field($line, 'E01');
    $line = add_csv_field($line, $cle);

    for ($i = 3; $i <= 11; $i++) {

        $ascii_str = '';

        if (isset($equip_fields[$i])) {

            $field = $equip_fields[$i];
            $ascii_str = compute_csv_value($field, $row);
        }
        $line = add_csv_field($line, $ascii_str);
    }
    return $line;
}

function do_entete() {
    $line = add_csv_field('', 'CodeLigne');
    $line = add_csv_field($line, 'Cle');
    for ($i = 1; $i <= 10; $i++)
        $line = add_csv_field($line, sprintf('Rubrique%d', $i));

    return $line;
}

function do_aut() {
    $line = add_csv_field('', 'AUT');
    $line = add_csv_field($line, '0');
    $line = add_csv_field($line, 'Luigi Santocanale, luigi.santocanale@lif.univ-mrs.fr,inscriptions-afl');

    return $line;
}

function generate_csv() {

    setlocale(LC_ALL, 'fr_FR');

//    global $bat_fields;
//    global $correpondance;
    // query the database
    $condition = sprintf("WHERE  `ID_regate` = '%s'", $_SESSION['ID_regate']);
    if (isset($_GET['confirme']) and $_GET['confirme'] == '1')
        $condition.=' AND `conf`=\'1\'';
    $query = "SELECT *, DATE_FORMAT(`naissance`,'%Y') as `E1_NAIS` FROM `Inscrit` " . $condition;
    $conn = connect();
    $res = mysql_query($query, $conn) or die('Problème lors de la réception des enregistrements' . $query . mysql_error()); //Exécution de la requête
 

    $csv = do_entete();
    $csv = add_csv_newline($csv);

    $csv .= do_aut();
    $csv = add_csv_newline($csv);

    $cle = 1;
    while ($row = mysql_fetch_assoc($res)) {
        //Parcours du résultat de la requête

        $csv.=do_bat($row, $cle);
        $csv = add_csv_newline($csv);
        $csv.=do_equip($row, $cle);
        $csv = add_csv_newline($csv);
        $cle++;
    }

    mysql_close($conn);
 
    return $csv;
}

$csv = generate_csv();

$date = date("ymd");
// Direct output to a client’s web browser (Excel5)
header('Content-Type: text/csv');
header("Content-Disposition: attachment;filename=\"LASER_$date.csv\"");
header('Cache-Control: max-age=0');
echo $csv;

exit;
?>
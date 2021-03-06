<?php

class Regate {

    static private $htmlDateFormat = 'd/m/Y';

    private static function setLimite($regate) {
        date_default_timezone_set('Europe/Paris');
        $limite = new DateTime($regate['date_limite_preinscriptions']);
        $limite->setTime(23, 59);
        if ($limite == NULL) {
            throw new Exception('Problème avec la date');
        }
        $regate['limite'] = $limite;

        return $regate;
    }

    static public function selectById($ID_regate, $bd = NULL) {
        $series = new Series();

        $sql = 'SELECT * FROM Regate '
                . 'WHERE ID_regate = ?';
        $assoc = array($ID_regate);
        $req = executePreparedQuery($sql, $assoc, $bd);
        if ($req->RowCount() == 0) {
            pageErreur('La régate demandée n\'existe pas.');
            exit(0);
        }
        $regate = $req->fetch();
        $seriesOfThis = explode(",", $regate['series']);
        unset($regate['series']);
        $regate['series'] = array();
        foreach ($seriesOfThis as $serie) {
            if (isset($series->available[$serie])) {
                $regate['series'][$serie] = $series->available[$serie];
            }
        }

        return self::setLimite($regate);
    }

    static public function availableSeries($regate) {
        return $regate['series'];
    }

    static public function availableSeriesNames($regate) {
        $series=self::availableSeries($regate);
        $ret = array();
        foreach($series as $serie){
            array_push($ret, $serie['nom']);
        }
        return $ret;
    }

    static public function estLaser($regate) {
        $laserSeries = array('LAS', 'LAR', 'LAS4');
        $names=self::availableSeriesNames($regate);
        //print implode(",",$names);
        foreach ($names as $serie) {
            if (!in_array($serie, $laserSeries)) {
                return false;
            }
        }
        return true;
    }

    static public function estOuverte($regate) {

        if ($regate['date_limite_preinscriptions'] == '') {
            return true;
        } else {
            if (!isset($regate['limite'])) {
                $regate = self::setLimite($regate);
            }

            date_default_timezone_set('Europe/Paris');
            $now = new DateTime;
        }
        return ($now <= $regate['limite']);
    }

    static public function estDestructible($regate) {

        date_default_timezone_set('Europe/Paris');
        $now = new DateTime;
        $destruction = new DateTime($regate['destruction']);
//    echo $destruction->format('d/m/Y');
//    echo $now->format('d/m/Y');
//    exit;
        return ($destruction < $now);
    }

    static public function formatDeadline($regate) {

        return $regate['limite']->format(Regate::$htmlDateFormat);
    }

    static public function formatDebut($regate) {

        list($year, $month, $day) = sscanf($regate['date_debut'], "%4d-%2d-%2d");

        $ret = str_replace(array('d', 'm', 'Y'), array($day, $month, $year), Regate::$htmlDateFormat);

        return $ret;
    }

    static public function formatFin($regate) {

        list($year, $month, $day) = sscanf($regate['date_fin'], "%4d-%2d-%2d");
        $ret = str_replace(array('d', 'm', 'Y'), array($day, $month, $year), Regate::$htmlDateFormat);
        return $ret;
    }

    function setField($ID_regate, $field, $value) {

        $sql = "UPDATE `Regate` SET `$field`=:value "
                . "WHERE `ID_REGATE`=:ID";
        $assoc = array('value' => $value, 'ID' => $ID_regate);
        executePreparedQuery($sql, $assoc);
    }

}

<?php

class Month
{
    public $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

    // Tableau des mois, le mois entré dans le code calendrier et l'année
    private $months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    public $month;
    public $year;

    // = null = par défaut, l'attribut est null (si on ne le définit pas)
    public function __construct($month = null, $year = null)
    {
        if($month === null)
        {
            // intval() prend une valeur pour la convertir en entier, la fonction date renvoie une chaîne de caractères
            $month = intval(date('n'));
        }
        if($year === null)
        {
            $year = intval(date('Y'));
        }
        if($month < 1 || $month > 12)
        {
            $month = 1;
            //throw new \Exception("Le mois " . $month . " n'est pas valide.");
        }
        $this->_month = $month;
        $this->_year = $year;
    }

    /**
     * Renvoie le premier jour du mois (+ l'année et le n° du mois) en objet DateTime
     */
    public function getFirstDay()
    {
        return new DateTime("{$this->_year}-{$this->_month}-01");
    }

    /**
     * Retourne le mois
     */
    public function getMonth()
    {
        return $this->_month;
    }

    /**
     * Retourne l'année
     */
    public function getYear()
    {
        return $this->_year;
    }

    /**
     * Retourne le mois + l'année en toutes lettres
     */
    public function showMonthYear()
    {
        return $this->months[$this->_month - 1] . ' ' . $this->_year;
    }

    /**
     * Renvoie le nombre de semaines dans le mois
     */
    public function getCountWeeks()
    {
        // On définit $start = premier jour du mois
        $start = $this->getFirstDay();
        // On définit $end comme étant le dernier jour du mois. Pour cela, on clone la variable $start pour ne pas la remplacer par la nouvelle valeur de $end. Et on utilise la méthode mofidy() de DateTime pour rajouter 1 mois et enlever 1 jour. On obtient ainsi le dernier jour du mois.
        $end = (clone $start)->modify('+1 month -1 day');
        // On soustrait le numéro de semaine du dernier jour du mois moins celui du premier jour du mois pour avoir le nombre de semaines du mois
        // On rajoute 1 car les soustractions se font avec des nombres qui ne commencent pas à 0
        // Première semaine du mois
        $startWeek = intval($start->format('W'));
        // Dernière semaine du mois
        $endWeek = intval($end->format('W'));
        // La dernière semaine du mois pourrait avoir la valeur 1 à cause de son paramètre (ISO 8601), du coup il n'apparaît qu'1 seule semaine pour le mois (exemple : Décembre 2018). Donc si la dernière semaine du mois a pour numéro 1, celle-ci prend la valeur de la semaine précédente + 1.
        if($endWeek === 1)
        {
            $endWeek = intval((clone $end)->modify('- 7 days')->format('W')) + 1 ;
        }
        $weeks = $endWeek - $startWeek + 1;
        if($weeks < 0)
        {
            $weeks = intval($end->format('W'));
        }
        return $weeks;
    }

    /**
     * Détermine si le jour fait partie du mois en cours ou non, renvoie true si oui, false si non
     */
    public function withinMonth($date)
    {
        return $this->getFirstDay()->format('Y-m') === $date->format('Y-m') ;
    }

    /**
     * Renvoie le mois et l'année suivants, objet Month
     */
    public function nextMonth()
    {
        $month = $this->_month + 1;
        $year = $this->_year;
        if($month > 12)
        {
            $month = 1;
            $year += 1;
        }
        return new Month($month, $year);
    }

    /**
     * Renvoie le mois et l'année précédents, objet Month
     */
    public function previousMonth()
    {
        $month = $this->_month - 1;
        $year = $this->_year;
        if($month < 1)
        {
            $month = 12;
            $year -= 1;
        }
        return new Month($month, $year);
    }
    
    public function getCountDays()
    {
        $start = $this->getFirstDay();
        $end = intval((clone $start)->modify('+1 month -1 day')->format('d'));
        return $end;
    }
}

?>
<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Linea class
 *
 * @author a.esposito
 */
class Linea 
{
    public $Id;
    public $conn;
    public $DatiGenerali;

    /**
     * Constructor
     *
     * @param int|null $Id
     */
    function __construct($Id = null) 
    {
        $this->Id = $Id;
    }

    /**
     * Initialize general data
     */
    public function inizializzaDatiGenerali()
    {
        global $user;
        $db = $this->conn;
        $Id = $this->Id;

        $sql = "SELECT * FROM RT_Linea WHERE LineaId = $Id AND OdcIdRef = $user->OdcId";
        // echo($sql);

        $row = $db->query_first($sql);

        if (!empty($row['OdcIdRef'])) {
            $this->DatiGenerali = $row;
        } else {
            print("errore");
            exit();
        }
    }

    /**
     * Get all lines for select input
     *
     * @return array
     */
    public function getAllForSelect()
    {
        global $user;
        $db = $this->conn;
        $LineaId = $this->Id;

        if (!$this->Id) {
            $LineaId = 0;
        }

        $sql = "SELECT LineaId, LineaNome 
                FROM RT_Linea 
                WHERE ((Cancella = 0 AND Stato = 1) OR (LineaId = $LineaId)) 
                  AND OdcIdRef = $user->OdcId 
                ORDER BY LineaNome";

        return $db->fetch_array($sql);
    }
}
?>

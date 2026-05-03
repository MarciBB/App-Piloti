<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author a.esposito
 */
class Materia {
    
public $MateriaId;
public $Materia;
public $MateriaTipoId;
public $ArrMateria=Array();



public $conn;



function __construct($MateriaId) {
    $this->MateriaId = $MateriaId;
}



public function getAll()
{
        $db=$this->conn;
        $sql = "SELECT MateriaId,Materia,MateriaTipoId From Materia where Stato=1 and Cancella=0 order by MateriaTipoId asc,Materia asc";
        $this->ArrMateria=$db->fetch_array($sql);
       
        return ($this->ArrMateria);
        
    
}

public function getMateriaById($IdMateria)
{
        $db=$this->conn;
        $sql = "SELECT MateriaId,Materia,MateriaTipoId From Materia where MateriaId=$IdMateria";
        $this->ArrMateria=$db->fetch_array($sql);
       
        return ($this->ArrMateria);
        
    
}

public function getTipoMateriaByIdMateria($IdMateria)
{
        $db=$this->conn;
        $sql = "SELECT MateriaTipoId From Materia where MateriaId=$IdMateria";
         $row = $db->query_first($sql);                
      
        return $row['MateriaTipoId'];
        
        
    
}
    
    

}
?>


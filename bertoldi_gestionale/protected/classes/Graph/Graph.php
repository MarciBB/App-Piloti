<?php

class Node{
	public $id;
	public $children;
	public $parents;
	public $isRoot;
	public $isLeaf;
	public $descents;
	
	public $salite;
	public $discese;
	public $operazioni;
	
	function __construct($id = null){
		if(isset($id)){
			$this->id = $id;
		}else{
			$this->id = '';
		}
		$this->children = array();
		$this->parents = array();
		$this->isRoot = true;
		$this->isLeaf = true;
	}
	
	public function addChild($node){
		if(is_object($node)){
			$this->children[$node->id]=$node->id;
		}else{
			$this->children[$node]=$node;
		}
		$this->isLeaf = false;
	}
	
	public function addParent($node){
		if(is_object($node)){
			$this->parents[$node->id]=$node->id;
		}else{
			$this->parents[$node]=$node;
		}
		$this->isRoot = false;
	}
}

class Edge{
	public $nodeA;
	public $nodeB;
	public $info2;
	public $info;
	public $info3;
	public $info4;
	public $peso;
	public $trattaPeso;
	public $direction; //0 unidirectional, 1 bidirectional
	
	function __construct($nodeA=null, $nodeB=null, $direction=null){
		if(isset($nodeA) && isset($nodeB) && isset($direction)){
			if(is_object($nodeA) && is_object($nodeB)){
				$this->nodeA = $nodeA->id;
				$this->nodeB = $nodeB->id;
			}else{
				$this->nodeA = $nodeA;
				$this->nodeB = $nodeB;
			}
			$this->direction = $direction;
		}else{
			$this->nodeA = '';
			$this->nodeB = '';
			$this->direction = 0;
		}
		
		$this->direction = $direction;
		$this->info2 = 0;
		$this->peso = 0;
		$this->info = array();
		$this->info4 = array();
	}
	
}

class Graph{
	public $edges;
	public $nodes;
	public $roots;
	public $idNodes;
	
	function __construct(){
		$this->edges = array();
		$this->nodes = array();
		$this->roots = array();
		$this->idNodes = array();
	}
	
	public function isNode($id){
		return in_array($id, $this->idNodes);
	}
	
	public function addNode($node){
		if(is_object($node)){
 			if(!$this->isNode($node->id)){
				$this->nodes[$node->id] = $node;
				$this->roots[$node->id] = $node->id;
				$this->idNodes[] = $node->id;
 			}
		}else{
			if(!$this->isNode($node)){
				$nodeA = new Node($node);
				$this->nodes[$node] = $nodeA;
				$this->roots[$node] = $node;
				$this->idNodes[] = $node;
			}
		}
	}
	
	public function isConnected($idA, $idB){
		if($this->isNode($idA) && $this->isNode($idB)){
			return in_array($idB, $this->nodes[$idA]->children);
		}else{
			return false;
		}
	}
	
	public function connectNodes($nodeA, $nodeB, $direction, $peso, $tratta = null){
		if(is_object($nodeA) && is_object($nodeB)){
			$idA = $nodeA->id;
			$idB = $nodeB->id;
		}else{
			$idA = $nodeA;
			$idB = $nodeB;
		}
		if($this->isNode($idA) && $this->isNode($idB) && !$this->isConnected($idA, $idB)){
			$edge = new Edge($idA, $idB, $direction);
			$this->edges["$idA-$idB"] = $edge;
			$this->edges["$idA-$idB"]->peso = $peso;
			$this->edges["$idA-$idB"]->trattaPeso = $tratta;
			$this->nodes[$idA]->addChild($idB);
			$this->nodes[$idB]->addParent($idA);
			
			if($direction==1){
				$this->nodes[$idB]->addChild($idA);
				$this->nodes[$idA]->addParent($idB);
			}
			if($this->nodes[$idA]->isRoot ){
				$this->roots[$idA] = $idA;
			}else{
				unset($this->roots[$idA]);
			}
			if($this->nodes[$idB]->isRoot && !in_array($idB, $this->roots)){
				$this->roots[$idB] = $idB;
			}else{
				unset($this->roots[$idB]);
			}
		}else if($this->isNode($idA) && $this->isNode($idB) && $this->isConnected($idA, $idB)){
			if($peso<$this->edges["$idA-$idB"]->trattaPeso){
				$this->edges["$idA-$idB"]->peso = $peso;
				$this->edges["$idA-$idB"]->trattaPeso = $tratta;
			}
		}
	}
	
	public function addRoot($id){
		if(!is_object($id) && $this->isNode($id)){
			$this->roots[$id] = $id;
		}
	}
	
	public function calculateDescent(){
 		foreach ($this->roots as $root){
 			$this->getDescent($root);
 		}
	}
	
	public function calculateDescent2(){
		foreach ($this->roots as $root){
			$level = 0;
			$this->getDescent2($root, $level);
		}
	}
	
	
	private function getDescent($nodeId){
		
		//base case
		if($this->nodes[$nodeId]->isLeaf){
			$this->nodes[$nodeId]->descents = array();
			$descents = array();
			$descents[$nodeId] = $nodeId;
			
			return $descents;
		}else{
			$descents = array();
			foreach ($this->nodes[$nodeId]->children as $child){
 				if(isset($this->nodes[$child]->descents)){
					$this->nodes[$nodeId]->descents = $this->mergeNode($this->nodes[$nodeId]->descents, $this->nodes[$child]->descents);
					$this->nodes[$nodeId]->descents[$child] = $child;
					$descents = $this->nodes[$nodeId]->descents;
 				}else{
 					$descentsChild = $this->getDescent($child);
   					$this->nodes[$nodeId]->descents = $this->mergeNode($this->nodes[$nodeId]->descents, $descentsChild);
 					$descents = $this->nodes[$nodeId]->descents;
 				}	
			}
			$descents[$nodeId] = $nodeId;
			return $descents;
		}
	}
	
	private function getDescent2($nodeId, $level){
 		//echo "<br>$level $nodeId";
 		if($level == 80){
 			return;
 		}else{
 			$level++;
			
 		}
		//base case
		if($this->nodes[$nodeId]->isLeaf){
			$this->nodes[$nodeId]->descents = array();
			$descents = array();
			$descents[$nodeId] = $nodeId;
			$this->nodes[$nodeId]->operazioni = $this->nodes[$nodeId]->salite + $this->nodes[$nodeId]->discese;
				
			return $descents;
		}else{
			$descents = array();
			$this->nodes[$nodeId]->operazioni = $this->nodes[$nodeId]->salite + $this->nodes[$nodeId]->discese;
			foreach ($this->nodes[$nodeId]->children as $child){
				if($child != $nodeId){
					if(isset($this->nodes[$child]->descents)){
						$this->nodes[$nodeId]->descents = $this->mergeNode($this->nodes[$nodeId]->descents, $this->nodes[$child]->descents);
						$this->nodes[$nodeId]->descents[$child] = $child;
						$descents = $this->nodes[$nodeId]->descents;
					}else{
						$descentsChild = $this->getDescent2($child, $level);
						$this->nodes[$nodeId]->descents = $this->mergeNode($this->nodes[$nodeId]->descents, $descentsChild);
						$descents = $this->nodes[$nodeId]->descents;		
					}
				}
			}
				
			foreach ($descents as $dd){
				$this->nodes[$nodeId]->operazioni += $this->nodes[$dd]->salite + $this->nodes[$dd]->discese;
			}
			$descents[$nodeId] = $nodeId;
			return $descents;
		}
	}
	
	
	private function mergeNode($listNode1, $listNode2){
		$result = array();
		if(isset($listNode1) && count($listNode1)>0){
			foreach ($listNode1 as $node1){
				$result[$node1] = $node1;
			}
		}
		if(isset($listNode2) && count($listNode2)>0){
			foreach ($listNode2 as $node2){
				if(!in_array($node2, $result)){
					$result[$node2] = $node2;
				}
			}
		}
		return $result;
	}
	
	public function percorsoBreve2($idA, $idB, &$minPercorso, $g, $level){
		$level++;
		if($level > 10){
			return null;
		}

		if($this->nodes[$idA]->isLeaf){
			return null;
		}
		if(in_array($idB,$this->nodes[$idA]->children)){
// 			$percorso[] = "$idA-$idB";
			$min['id'] = $idA;
			$min['peso'] = $this->edges["$idA-$idB"]->peso;
		
			if($minPercorso== null || $g<$minPercorso){
				$minPercorso = $g;
				return $min;
			}else{
				return null;
			}
			
		}else{
			$minChild = array();
			foreach ($this->nodes[$idA]->children as $child){
				
				$d = $g + $this->edges[$idA."-".$child]->peso;
				
				if($minPercorso == null || $minPercorso>$d){
// 					$percorso[] = "$idA-$child";
					
					$temp = $this->percorsoBreve2($child, $idB, $minPercorso, $d, $level);
										
					if($temp!=null){
						$temp['peso'] = $temp['peso'] + $this->edges["$idA-$child"]->peso;
						$temp['id'] = $child;
						$minChild[] = $temp;
					}
				}
			}
			if(sizeof($minChild)>0){
				$min = $minChild[0];
				for($i=1;$i<sizeof($minChild);$i++){
					if($min['peso'] > $minChild[$i]['peso']){
						$min = $minChild[$i];
					}
				}
				return $min;
			}else{
				return null;
			}
		}
	}
	
	public function dijkstra($sorgente) {
		$dist = array();
		$precedente = array();
		$Q = array();
		foreach ($this->nodes[$sorgente]->descents as $v) {
			$dist[$v] = -1;
			$Q[$v] = $v;
		}
		
		$dist[$sorgente] = 0;
		$Q[$sorgente] = $sorgente;
		
		
		while(!empty($Q)) {
			$u = -1;
			$min = -1;
			
			$this->getMinNode($dist, $Q, $min, $u);
			
			unset($Q[$u]);
			
			if ($dist[$u] < 0) {
				break;
			}
			
			
			foreach ($this->nodes[$u]->children as $v) {
				$alt = $dist[$u] + $this->edges[$u . "-" . $v]->peso;
				if ($dist[$v] < 0 || $alt < $dist[$v]) { 
					$dist[$v] = $alt;
					$precedente[$v] = $u;
				}
			}
		}
		unset($Q);
		unset($dist);
		return $precedente;
	}
	
	public function percorsoBreve($partenza, $destinazione){
		$precendente = $this->dijkstra($partenza);
		
		$ultimo = $destinazione;
		$penultimo = null;
		$count = 0;
		while($ultimo != $partenza && $count<200){
			$penultimo = $ultimo;
			$ultimo = $precendente[$ultimo];
			$count++;
		}
		if($count == 200){
			return -1;
		}
		unset($precedente);
		return $penultimo;
	}
	
	public function getKmPercorsoBreve($partenza, $destinazione){
		$precendente = $this->dijkstra($partenza);

		$ultimo = $destinazione;
		$penultimo = null;
		$km = 0;
		$ii=0;
		while($ultimo != $partenza){
			$penultimo = $ultimo;
			$ultimo = $precendente[$ultimo];
			if(!isset($ultimo)){
				$ultimo = $destinazione;
				$km += $this->edges[$partenza."-".$destinazione]->peso;
				break;
			}
// 			echo "<br>km: $km".$ultimo."-".$penultimo;
// 			print_r($precendente);
			$ii++;
// 			if($ii>5) {echo "fineeee"; break;}
			if(isset($penultimo)){
				$km += $this->edges[$ultimo."-".$penultimo]->peso;
// 				echo "<br>km mod: $km";
			}
		}
		return $km;
	}
	
	private function getMinNode($a, $Q, &$min_val, &$min_key) {
		foreach($a as $key => $val) {
			if(array_key_exists($key,$Q)){
				if ($min_val<0 ||($val >= 0 && $val <= $min_val)) {
					$min_val = $val;
					$min_key = $key;
				}
			}
		}
	}
}
?>
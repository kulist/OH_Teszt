<?php

namespace App\Models;

class ExtraPoint
{
	public $category = "";		// Nyelvvizsga, Emelet szintű
	public $type = "";			// B2.. C1...
	public $language = "";		// 
	public $point = 0;
	
	public function __construct($exam_results) {
		$this->category = $exam_results['kategoria'];
		
		if($this->category === "Nyelvvizsga") {
			$this->type = $exam_results['tipus'];
			$this->language = $exam_results['nyelv'];
		}
		
		if($this->category === "Nyelvvizsga") {
			if($this->type === "B2")
				$this->point = 28;
			else if($this->type === "C1") {
				$this->point = 40;
			}
		}
		else if($this->category === "Emeltszint") {
			$this->point = 50;
		}
	}
}
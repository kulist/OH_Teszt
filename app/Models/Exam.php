<?php

namespace App\Models;

class Exam
{
	public $name = "";
	public $type = "közép";		// közép | emelt
	public $result = 0.0;		// percentage of the exam
	
	public function __construct($exam_results) {
		$this->name = $exam_results['nev'];
		$this->type = $exam_results['tipus'];
		
		if(isset($exam_results['eredmeny'])) {
			$this->result = (float)str_replace("%", "", $exam_results['eredmeny']);
		}
	}
}
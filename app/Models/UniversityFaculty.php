<?php

namespace App\Models;

class UniversityFaculty
{
	public $university = "";
	public $faculty = "";
	public $department = "";
	
	public $required_subjects = [];
	public $required_subjects_optional = [];
	
	public function __construct($array) {
		$this->university = $array['egyetem'];
		$this->faculty = $array['kar'];
		$this->department = $array['szak'];
	}
	
	public function SetRequirementsBy($requirements) {
		$key = $this->GetHash();
		
		if(isset($requirements[$key])) {
			$this->required_subjects = $requirements[$key]['required'];
			$this->required_subjects_optional = $requirements[$key]['required_optional'];
		}
	}
	
	public function GetHash() {
		return md5($this->university . "_" . $this->faculty . "_" . $this->department);
	}
}
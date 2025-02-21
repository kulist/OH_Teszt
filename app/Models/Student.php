<?php

namespace App\Models;

class Student
{
	public $applied_faculty = NULL;
	public $exams = [];
	public $extra_points = [];
	
	public function __construct() {
	}
}
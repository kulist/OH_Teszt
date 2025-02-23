<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Exam;
use App\Models\Student;
use App\Models\UniversityFaculty;
use App\Models\ExtraPoint;

class EvaluationController extends Controller {
	// _evaluate státuszkódok
	public const STR_ERROR = [
		0	=> 'OK',
		-1	=> 'Alaptantárgy nem sikerült!',
		-2	=> 'Nem minden kötelező tárgyból tett vizsgát!',
		-3	=> 'Kötelező vizsga nem teljesült!',
		-4	=> 'Kötelezően választható vizsga nem teljesült!',
		-5	=> 'Egy tantárgyi vizsga eredménye nem érite el a 20%-ot'
	];
	
	// Oktatási intézmény felvételi követelmények definíciója
	private function __faculty_requirements() {
		return [
			md5("ELTE_IK_Programtervező informatikus") => [
				'required' => [ 
					new Exam([ 'nev' => "matematika", 'tipus' => "közép" ]) 
				],
				'required_optional' => [ 
					new Exam([ 'nev' => "biológia", 'tipus' => "közép" ]),
					new Exam([ 'nev' => "fizika", 'tipus' => "közép" ]),
					new Exam([ 'nev' => "informatika", 'tipus' => "közép" ]),
					new Exam([ 'nev' => "kémia", 'tipus' => "közép" ])
				]
			],
			md5("PPKE_BTK_Anglisztika") => [
				'required' => [ 
					new Exam([ 'nev' => "angol nyelv", 'tipus' => "emelt" ]) 
				],
				'required_optional' => [ 
					new Exam([ 'nev' => "francia", 'tipus' => "közép" ]),
					new Exam([ 'nev' => "német", 'tipus' => "közép" ]),
					new Exam([ 'nev' => "olasz", 'tipus' => "közép" ]),
					new Exam([ 'nev' => "spanyol", 'tipus' => "közép" ]),
					new Exam([ 'nev' => "történelem", 'tipus' => "közép" ])
				]
			]
		];
	}
	
	/**************************************************************************
	* [PUBLIC] index függvény a router elérést biztosítja. 
	* 	Diákok adatainak betöltése és feldoldolgozása.
	*
	*	Arguments:
	*		(void)
	*
	*	Return:
	*		(void)
	*--------------------------------------------------------------------------
	* Date: 2025.02.21.			Author: kulist		Checked:
	*/
	public function index()
    {
		$student_data = $this->_load_student_data();
		
		foreach($student_data as $id => $student) {
			$result = $this->_evaluate($student);
			
			if(isset($result['error'])) {
				echo ($id + 1) . ". Sikertelen vizsga: " . $result['error'] . '<br/>';
			} else {
				echo ($id + 1) . ". Pontszám: " . ($result['base'] + $result['extra']) . " (Alappont: " . $result['base'] . " Pluszpont: " . $result['extra'] . ")<br/>";
			}
		}
    }
	
	/**************************************************************************
	* [PROTECTED] Ez a függvény végzi el a bejövő adatok beolvasását és 
	*	feldolgozását.
	*
	*	Arguments:
	*		($input)	- Mixed(NULL|array)
	*
	*	Return:
	*		(array)		- Betöltött és feldolgozott vizsga adatok
	*--------------------------------------------------------------------------
	* Date: 2025.02.21.			Author: kulist		Checked:
	*/
	protected function _load_student_data($input = null) {
		if($input === NULL) {
			$path = storage_path('app/public/homework_input.php');
			$students_data = require_once($path);
		} else {
			$students_data = $input;
		}
		
		if(!is_array($students_data)) {
			echo "Érvénytelen bemeneti adat!";
			return [];
		}
		
		$students = [];
		foreach($students_data as $id => $student_data) {
			$student = new Student();
			
			// Student applied faculty
			$student->applied_faculty = new UniversityFaculty($student_data['valasztott-szak']);
			$student->applied_faculty->SetRequirementsBy($this->__faculty_requirements());
			
			// Student graduation results
			foreach($student_data['erettsegi-eredmenyek'] as $exam) {
				$exam = new Exam($exam);
				if($exam->type === 'emelt') {
					$student->extra_points[] = new ExtraPoint([ 'kategoria' => 'Emeltszint' ]);
				}
				$student->exams[] = $exam;
			}
			
			// Student extra points
			foreach($student_data['tobbletpontok'] as $points) {
				$student->extra_points[] = new ExtraPoint($points);
			}
			
			$students[] = $student;
		}
		
		return $students;
	}
	
	/**************************************************************************
	* [PROTECTED] Ez a függvény végzi el a beolvasott adatok kiértékelését
	*
	*	Arguments:
	*		($student)	- Student model
	*
	*	Return:
	*		(array)		- A diák pontszámai vagy sikeretelen kiértékelés oka
	*--------------------------------------------------------------------------
	* Date: 2025.02.21.			Author: kulist		Checked:
	*/
	protected function _evaluate($student) {
		$base_points = 0;
		$result = $this->__get_base_points($student, $base_points);
		if($result !== 0) {
			return [
				'base' => 0,
				'extra' =>0,
				'error' => self::STR_ERROR[$result]
			];
		}
		
		$extra_points = 0;
		$result = $this->__get_extra_points($student, $extra_points);
		if($result !== 0) {
			return [
				'base' => 0,
				'extra' =>0,
				'error' => self::STR_ERROR[$result]
			];
		}
		
		return [
			'base' => $base_points * 2,
			'extra' => $extra_points
		];
	}
	
	/**************************************************************************
	* [PRIVATE] Ez a függvény végzi el az adott diák alappontszámának számítását
	*
	*	Arguments:
	*		($student)		- Student model
	*		($base_point)	- Referencia(float) Az alap pontszámok összege
	*
	*	Return:
	*		(int)			- STR_ERROR Hibakód, 0 - OK
	*--------------------------------------------------------------------------
	* Date: 2025.02.21.			Author: kulist		Checked:
	*/
	private function __get_base_points($student, &$base_points) {
		$base_subjects = [ 'magyar nyelv és irodalom', 'történelem', 'matematika' ];
		
		$base_points = 0;
		$passed_base_exams = 0;
		foreach($student->exams as $exam) {
			if(in_array($exam->name, $base_subjects)) {
				if($exam->result < 20) {
					return -1;
				}
				$passed_base_exams++;
			}
			
			if($exam->result < 20) {
				return -5;
			}
		}
		
		if($passed_base_exams < count($base_subjects)) {
			return -2;
		}
		
		$faculty_required = $this->__get_base_points_required($student, $base_points);
		if($faculty_required !== 0) {
			return $faculty_required;
		}
		
		$faculty_req_optional = $this->__get_base_points_required_optional($student, $base_points);
		if($faculty_req_optional !== 0) {
			return $faculty_req_optional;
		}
		
		return 0;
	}
	
	/**************************************************************************
	* [PRIVATE] Ez a függvény végzi el az adott diák alappontszámához a kötelező
	*	tantárgy kiértékelését és pontszámítását
	*
	*	Arguments:
	*		($student)		- Student model
	*		($base_point)	- Referencia(float) Az alap pontszámok összege
	*
	*	Return:
	*		(int)			- STR_ERROR Hibakód, 0 - OK
	*--------------------------------------------------------------------------
	* Date: 2025.02.21.			Author: kulist		Checked:
	*/
	private function __get_base_points_required($student, &$base_points) {
		// Check Faculty required subject
		$faculty_required = $student->applied_faculty->required_subjects;
		
		foreach($faculty_required as $required_exam) {
			$requirement_passed = false;
			
			foreach($student->exams as $exam) {
				if($required_exam->name === $exam->name && $exam->result >= 20) {
					if($required_exam->type === 'emelt' && $exam->type != 'emelt') {
						break;
					}
					$requirement_passed = true;
					$base_points += $exam->result;
					break;
				}
			}
			
			if(!$requirement_passed) {
				return -3;
			}
		}
		
		return 0;
	}
	
	/**************************************************************************
	* [PRIVATE] Ez a függvény végzi el az adott diák alappontszámához a kötelezően
	*	választható tantárgy kiértékelését és pontszámítását
	*
	*	Arguments:
	*		($student)		- Student model
	*		($base_point)	- Referencia(float) Az alap pontszámok összege
	*
	*	Return:
	*		(int)			- STR_ERROR Hibakód, 0 - OK
	*--------------------------------------------------------------------------
	* Date: 2025.02.21.			Author: kulist		Checked:
	*/
	private function __get_base_points_required_optional($student, &$base_points) {
		// Check Faculty required optional subjects
		// One at least has to be successfully
		$faculty_required_optional = $student->applied_faculty->required_subjects_optional;
		
		$matched_exams = [];
		foreach($faculty_required_optional as $optional_exam) {
			foreach($student->exams as $exam) {
				if($optional_exam->name === $exam->name && $exam->result >= 20) {
					$matched_exams[] = $exam;
				}
			}
		}
		
		if(count($matched_exams) === 0) {
			return -4;
		}
		
		// Select the highted scored exam
		$max_point = $matched_exams[0]->result;
		
		for($i = 1; $i < count($matched_exams); $i++) {
			if($matched_exams[$i]->result > $max_point) {
				$max_point = $matched_exams[$i]->result;
			}
		}
		
		$base_points += $max_point;
		
		return 0;
	}
	
	/**************************************************************************
	* [PRIVATE] Ez a függvény végzi el az adott diák extra pontszámainak számtását
	*	A többletpontok maximuma 100p.
	*
	*	Arguments:
	*		($student)		- Student model
	*		($extra_point)	- Referencia(float) Az extra pontszámok összege
	*
	*	Return:
	*		(int)			- STR_ERROR Hibakód, 0 - OK
	*--------------------------------------------------------------------------
	* Date: 2025.02.21.			Author: kulist		Checked:
	*/
	private function __get_extra_points($student, &$extra_points) {
		$extra_points = 0;
		
		$language_exams = [];
		
		foreach($student->extra_points as $extra) {
			if($extra->category === 'Emeltszint') {
				$extra_points += 50;
			} else if($extra->category === 'Nyelvvizsga') {
				if(!isset($language_exams[$extra->language])) {
					$language_exams[$extra->language] = $extra->point;
				} else {
					// Student has more advanced exam from this language
					if($language_exams[$extra->language] < $extra->point) {
						$language_exams[$extra->language] = $extra->point;
					}
				}
			}
		}
		
		foreach($language_exams as $language => $point) {
			$extra_points += $point;
		}
		
		if($extra_points > 100) {
			$extra_points = 100;
		}
		
		return 0;
	}
}
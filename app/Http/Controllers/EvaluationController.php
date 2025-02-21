<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Exam;
use App\Models\Student;
use App\Models\UniversityFaculty;
use App\Models\ExtraPoint;

class EvaluationController extends Controller {
	public const STR_ERROR = [
		-1	=> 'Alaptantárgy nem sikerült!',
		-2	=> 'Nem minden kötelező tárgyból tett vizsgát!',
		-3	=> 'Kötelező vizsga nem teljesült!',
		-4	=> 'Kötelezően választható vizsga nem teljesült!'
	];
	
	// University Faculty requirements
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
					new Exam([ 'nev' => "matematika", 'tipus' => "emelt" ]) 
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
	
	public function index()
    {
		$student_data = $this->_load_student_data();
		
		foreach($student_data as $id => $student) {
			$result = $this->__evaluate($student);
			
			if(isset($result['error'])) {
				echo ($id + 1) . ". Sikertelen vizsga: " . $result['error'] . '<br/>';
			} else {
				echo ($id + 1) . ". Alappont: " . $result['base'] . " Pluszpont: " . $result['extra'] . '<br/>';
			}
		}
    }
	
	protected function _load_student_data() {
		$path = storage_path('app/public/homework_input.php');
		$students_data = require_once($path);
		
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
	
	private function __evaluate($student) {
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
	
	private function __get_base_points($student, &$base_points) {
		$base_subjects = [ 'magyar nyelv és irodalom', 'történelem', 'matematika' ];
		
		$base_points = 0;
		$passed_base_exams = 0;
		foreach($student->exams as $exam) {
			if(in_array($exam->name, $base_subjects)) {
				if($exam->result < 20) {
					return -1;
				}
				//$base_points += $exam->result;
				$passed_base_exams++;
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
	
	private function __get_base_points_required($student, &$base_points) {
		// Check Faculty required subject
		$faculty_required = $student->applied_faculty->required_subjects;
		
		foreach($faculty_required as $required_exam) {
			$requirement_passed = false;
			
			foreach($student->exams as $exam) {
				if($required_exam->name === $exam->name && $exam->result >= 20) {
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
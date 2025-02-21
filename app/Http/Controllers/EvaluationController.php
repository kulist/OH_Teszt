<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Exam;
use App\Models\Student;
use App\Models\UniversityFaculty;
use App\Models\ExtraPoint;

class EvaluationController extends Controller {
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
					$student->extra_points[] = new ExtraPoint([ 'kategoria' => 'Emeltszing' ]);
				}
				$student->exams[] = $exam;
			}
			
			// Student extra points
			foreach($student_data['tobbletpontok'] as $points) {
				$student->extra_points[] = new ExtraPoint($points);
			}
			
			$students[] = $student;
		}
		
		return $students_data;
	}
}
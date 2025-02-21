<?php

namespace App\Http\Controllers\Tests;

use App\Http\Controllers\EvaluationController;

use App\Models\Exam;
use App\Models\Student;
use App\Models\UniversityFaculty;
use App\Models\ExtraPoint;

class EvaluationTest extends EvaluationController {
	private $test_data = [
		[
			'case' => [
				'valasztott-szak' => [ 'egyetem' => 'ELTE', 'kar' => 'IK', 'szak' => 'Programtervező informatikus' ],
				'erettsegi-eredmenyek' => [
					[ 'nev' => 'magyar nyelv és irodalom', 'tipus' => 'közép', 'eredmeny' => '70%' ],
					[ 'nev' => 'történelem', 'tipus' => 'közép', 'eredmeny' => '80%' ],
					[ 'nev' => 'matematika', 'tipus' => 'emelt', 'eredmeny' => '90%' ],
					[ 'nev' => 'angol nyelv', 'tipus' => 'közép', 'eredmeny' => '94%' ],
					[ 'nev' => 'informatika', 'tipus' => 'közép', 'eredmeny' => '95%' ]
				],
				'tobbletpontok' => [
					[ 'kategoria' => 'Nyelvvizsga', 'tipus' => 'B2', 'nyelv' => 'angol' ],
					[ 'kategoria' => 'Nyelvvizsga', 'tipus' => 'C1', 'nyelv' => 'német' ]
				]
			],
			'assert' => [ 'base' => 370, 'extra' => 100, 'error' => parent::STR_ERROR[0] ]
		],
		[
			'case' => [
				'valasztott-szak' => [ 'egyetem' => 'ELTE', 'kar' => 'IK', 'szak' => 'Programtervező informatikus' ],
				'erettsegi-eredmenyek' => [
					[ 'nev' => 'magyar nyelv és irodalom','tipus' => 'közép','eredmeny' => '70%' ],
					[ 'nev' => 'történelem', 'tipus' => 'közép','eredmeny' => '80%' ],
					[ 'nev' => 'matematika','tipus' => 'emelt','eredmeny' => '90%' ],
					[ 'nev' => 'angol nyelv','tipus' => 'közép','eredmeny' => '94%' ],
					[ 'nev' => 'informatika','tipus' => 'közép','eredmeny' => '95%' ],
					[ 'nev' => 'fizika','tipus' => 'közép','eredmeny' => '98%' ]
				],
				'tobbletpontok' => [
					[ 'kategoria' => 'Nyelvvizsga','tipus' => 'B2','nyelv' => 'angol' ],
					[ 'kategoria' => 'Nyelvvizsga','tipus' => 'C1','nyelv' => 'német' ]
				]
			],
			'assert' => [ 'base' => 376, 'extra' => 100, 'error' => parent::STR_ERROR[0] ]
		],
		[
			'case' => [
				'valasztott-szak' => [ 'egyetem' => 'ELTE', 'kar' => 'IK', 'szak' => 'Programtervező informatikus' ],
				'erettsegi-eredmenyek' => [
					[ 'nev' => 'matematika','tipus' => 'emelt','eredmeny' => '90%' ],
					[ 'nev' => 'angol nyelv','tipus' => 'közép','eredmeny' => '94%' ],
					[ 'nev' => 'informatika','tipus' => 'közép','eredmeny' => '95%' ],
					[ 'nev' => 'fizika','tipus' => 'közép','eredmeny' => '98%' ]
				],
				'tobbletpontok' => [
					[ 'kategoria' => 'Nyelvvizsga','tipus' => 'B2','nyelv' => 'angol' ],
					[ 'kategoria' => 'Nyelvvizsga','tipus' => 'C1','nyelv' => 'német' ]
				]
			],
			'assert' => [ 'base' => 0, 'extra' => 0, 'error' => parent::STR_ERROR[-2] ]
		],
		[
			'case' => [
				'valasztott-szak' => [ 'egyetem' => 'ELTE', 'kar' => 'IK', 'szak' => 'Programtervező informatikus' ],
				'erettsegi-eredmenyek' => [
					[ 'nev' => 'magyar nyelv és irodalom', 'tipus' => 'közép', 'eredmeny' => '15%' ],
					[ 'nev' => 'történelem', 'tipus' => 'közép', 'eredmeny' => '80%' ],
					[ 'nev' => 'matematika', 'tipus' => 'emelt', 'eredmeny' => '90%' ],
					[ 'nev' => 'angol nyelv', 'tipus' => 'közép', 'eredmeny' => '94%' ],
					[ 'nev' => 'informatika', 'tipus' => 'közép', 'eredmeny' => '95%' ]
				],
				'tobbletpontok' => [
					[ 'kategoria' => 'Nyelvvizsga','tipus' => 'B2','nyelv' => 'angol' ],
					[ 'kategoria' => 'Nyelvvizsga','tipus' => 'C1','nyelv' => 'német' ]
				]
			],
			'assert' => [ 'base' => 0, 'extra' => 0, 'error' => parent::STR_ERROR[-1] ]
		],
		[
			'case' => [
				'valasztott-szak' => [ 'egyetem' => 'ELTE', 'kar' => 'IK', 'szak' => 'Programtervező informatikus' ],
				'erettsegi-eredmenyek' => [
					[ 'nev' => 'magyar nyelv és irodalom','tipus' => 'közép','eredmeny' => '70%' ],
					[ 'nev' => 'történelem', 'tipus' => 'közép','eredmeny' => '80%' ],
					[ 'nev' => 'matematika','tipus' => 'közép','eredmeny' => '90%' ],
					[ 'nev' => 'angol nyelv','tipus' => 'közép','eredmeny' => '94%' ],
					[ 'nev' => 'informatika','tipus' => 'közép','eredmeny' => '95%' ],
					[ 'nev' => 'fizika','tipus' => 'közép','eredmeny' => '98%' ]
				],
				'tobbletpontok' => [
					[ 'kategoria' => 'Nyelvvizsga','tipus' => 'B2','nyelv' => 'angol' ],
					[ 'kategoria' => 'Nyelvvizsga','tipus' => 'C1','nyelv' => 'angol' ]
				]
			],
			'assert' => [ 'base' => 376, 'extra' => 40, 'error' => parent::STR_ERROR[0] ]
		],
		[
			'case' => [
				'valasztott-szak' => [ 'egyetem' => 'PPKE', 'kar' => 'BTK', 'szak' => 'Anglisztika' ],
				'erettsegi-eredmenyek' => [
					[ 'nev' => 'magyar nyelv és irodalom', 'tipus' => 'közép', 'eredmeny' => '70%' ],
					[ 'nev' => 'történelem', 'tipus' => 'közép', 'eredmeny' => '80%' ],
					[ 'nev' => 'matematika', 'tipus' => 'emelt', 'eredmeny' => '90%' ],
					[ 'nev' => 'angol nyelv', 'tipus' => 'közép', 'eredmeny' => '94%' ],
					[ 'nev' => 'informatika', 'tipus' => 'közép', 'eredmeny' => '95%' ]
				],
				'tobbletpontok' => [
					[ 'kategoria' => 'Nyelvvizsga', 'tipus' => 'B2', 'nyelv' => 'angol' ],
					[ 'kategoria' => 'Nyelvvizsga', 'tipus' => 'C1', 'nyelv' => 'német' ]
				]
			],
			'assert' => [ 'base' => 0, 'extra' => 0, 'error' => parent::STR_ERROR[-3] ]
		],
		[
			'case' => [
				'valasztott-szak' => [ 'egyetem' => 'PPKE', 'kar' => 'BTK', 'szak' => 'Anglisztika' ],
				'erettsegi-eredmenyek' => [
					[ 'nev' => 'magyar nyelv és irodalom', 'tipus' => 'közép', 'eredmeny' => '70%' ],
					[ 'nev' => 'történelem', 'tipus' => 'emelt', 'eredmeny' => '80%' ],
					[ 'nev' => 'matematika', 'tipus' => 'közép', 'eredmeny' => '90%' ],
					[ 'nev' => 'angol nyelv', 'tipus' => 'emelt', 'eredmeny' => '94%' ],
					[ 'nev' => 'informatika', 'tipus' => 'közép', 'eredmeny' => '95%' ]
				],
				'tobbletpontok' => [
					[ 'kategoria' => 'Nyelvvizsga', 'tipus' => 'B2', 'nyelv' => 'angol' ],
					[ 'kategoria' => 'Nyelvvizsga', 'tipus' => 'C1', 'nyelv' => 'német' ]
				]
			],
			'assert' => [ 'base' => 348, 'extra' => 100, 'error' => parent::STR_ERROR[0] ]
		],
	];
	
	public function test() {
		foreach($this->test_data as $id => $test) {
			$student_data = $this->_load_student_data([ $test['case'] ]);
			
			foreach($student_data as $student) {
				$result = $this->_evaluate($student);
				if(isset($result['error'])) {
					if($result['error'] === $test['assert']['error']) {
						$status = "PASSED";
					} else {
						$status = "FAILED";
					}
				} else {
					if((float)$result['base'] === (float)$test['assert']['base'] && (float)$result['extra'] === (float)$test['assert']['extra']) {
						$status = "PASSED";
					} else {
						$status = "FAILED";
					}
				}
				echo "Test case " . $id . ". Result: " . $status . "<br/>";
			}
		}	
	}
}
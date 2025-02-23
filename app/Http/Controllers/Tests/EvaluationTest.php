<?php

namespace App\Http\Controllers\Tests;

use App\Http\Controllers\EvaluationController;

use App\Models\Exam;
use App\Models\Student;
use App\Models\UniversityFaculty;
use App\Models\ExtraPoint;

class EvaluationTest extends EvaluationController {
	// Teszt esetek
	private $test_data = [
		// Pontszámítás lehetséges, emelet szintű tantárgy és közép- és emeletszintű nyelvvizsga felszámolása
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
		// Pontszámítás lehetséges, több kötelező tantárgy teljesítése esetén nagyobb pontszám felszámolása
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
		// Alaptantárgyak közül az egyiket nem teljesítette a diák (történelem)
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
		// Egyik tantárgyból nem sikerült a vizsga (20% alatti eredmény)
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
		// Ugyanabból a nyelvből két szinten is vizsgázott a diák
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
		// Intézményi kötelező tantárgy nem teljesült
		[
			'case' => [
				'valasztott-szak' => [ 'egyetem' => 'PPKE', 'kar' => 'BTK', 'szak' => 'Anglisztika' ],
				'erettsegi-eredmenyek' => [
					[ 'nev' => 'magyar nyelv és irodalom', 'tipus' => 'közép', 'eredmeny' => '70%' ],
					[ 'nev' => 'történelem', 'tipus' => 'közép', 'eredmeny' => '80%' ],
					[ 'nev' => 'matematika', 'tipus' => 'emelt', 'eredmeny' => '90%' ],
					[ 'nev' => 'angol nyelv', 'tipus' => 'közép', 'eredmeny' => '94%' ],		// Nem teljesült
					[ 'nev' => 'informatika', 'tipus' => 'közép', 'eredmeny' => '95%' ]
				],
				'tobbletpontok' => [
					[ 'kategoria' => 'Nyelvvizsga', 'tipus' => 'B2', 'nyelv' => 'angol' ],
					[ 'kategoria' => 'Nyelvvizsga', 'tipus' => 'C1', 'nyelv' => 'német' ]
				]
			],
			'assert' => [ 'base' => 0, 'extra' => 0, 'error' => parent::STR_ERROR[-3] ]
		],
		// Diák minden feltételnek megfelelt - pontszámítás
		[
			'case' => [
				'valasztott-szak' => [ 'egyetem' => 'PPKE', 'kar' => 'BTK', 'szak' => 'Anglisztika' ],
				'erettsegi-eredmenyek' => [
					[ 'nev' => 'magyar nyelv és irodalom', 'tipus' => 'közép', 'eredmeny' => '70%' ],
					[ 'nev' => 'történelem', 'tipus' => 'közép', 'eredmeny' => '80%' ],
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
		// Diák olyan tantárgyból bukott, ami nincs a kötelező három tantárgy között és nem felsőoktatási intézmény által megkövetelt
		[
			'case' => [
				'valasztott-szak' => [ 'egyetem' => 'ELTE', 'kar' => 'IK', 'szak' => 'Programtervező informatikus' ],
				'erettsegi-eredmenyek' => [
					[ 'nev' => 'magyar nyelv és irodalom','tipus' => 'közép','eredmeny' => '70%' ],
					[ 'nev' => 'történelem', 'tipus' => 'közép','eredmeny' => '80%' ],
					[ 'nev' => 'matematika', 'tipus' => 'közép','eredmeny' => '90%' ],
					[ 'nev' => 'angol nyelv', 'tipus' => 'közép','eredmeny' => '19%' ],		// Nem teljesült
					[ 'nev' => 'informatika', 'tipus' => 'közép','eredmeny' => '95%' ],
					[ 'nev' => 'testnevelés', 'tipus' => 'közép','eredmeny' => '98%' ]
				],
				'tobbletpontok' => [
					[ 'kategoria' => 'Nyelvvizsga','tipus' => 'B2','nyelv' => 'angol' ]
				]
			],
			'assert' => [ 'base' => 0, 'extra' => 0, 'error' => parent::STR_ERROR[-5] ]
		],
		// Diák nem teljesített egy az intézmény által kötelezően választható tantárgyat se
		[
			'case' => [
				'valasztott-szak' => [ 'egyetem' => 'ELTE', 'kar' => 'IK', 'szak' => 'Programtervező informatikus' ],
				'erettsegi-eredmenyek' => [
					[ 'nev' => 'magyar nyelv és irodalom','tipus' => 'közép','eredmeny' => '70%' ],
					[ 'nev' => 'történelem', 'tipus' => 'közép','eredmeny' => '80%' ],
					[ 'nev' => 'matematika', 'tipus' => 'közép','eredmeny' => '90%' ],
					[ 'nev' => 'angol nyelv', 'tipus' => 'közép','eredmeny' => '76%' ],
					[ 'nev' => 'német nyelv', 'tipus' => 'közép','eredmeny' => '95%' ],
					[ 'nev' => 'testnevelés', 'tipus' => 'közép','eredmeny' => '98%' ]		// Kötelezően választható nem teljesült
				],
				'tobbletpontok' => [
					[ 'kategoria' => 'Nyelvvizsga','tipus' => 'B2','nyelv' => 'angol' ]
				]
			],
			'assert' => [ 'base' => 0, 'extra' => 0, 'error' => parent::STR_ERROR[-4] ]
		]
	];
	
	/**************************************************************************
	* [PUBLIC] Router ide továbbítja a /test kérést. Ez a függvény a fentebb
	*	definiált teszteseteket futtatja le a szülő osztályon és a kapott
	*	értéket összeveti a várt [assert] értékel.
	*
	*	Arguments:
	*		(void)			-
	*
	*	Return:
	*		(void)			- 
	*--------------------------------------------------------------------------
	* Date: 2025.02.21.			Author: kulist		Checked:
	*/
	public function test() {
		$start = microtime(true);
		
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
				echo "Test case " . ($id + 1) . ". Result: " . $status . "<br/>";
			}
		}
		
		echo "<br/>Runtime: " . round((microtime(true) - $start) * 1000, 2) . " ms"; 
	}
}
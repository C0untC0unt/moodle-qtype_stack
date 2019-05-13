<?php
// This file is part of Stack - http://stack.maths.ed.ac.uk/
//
// Stack is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Stack is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Stack.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

// Unit tests for verious AST filters.
//
// @copyright  2019 Aalto University
// @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.

require_once(__DIR__ . '/../stack/cas/parsingrules/002_log_candy.php');
require_once(__DIR__ . '/../stack/cas/parsingrules/040_common_function_name_multiplier.php');
require_once(__DIR__ . '/../stack/cas/parsingrules/050_split_floats.php');
require_once(__DIR__ . '/../stack/maximaparser/utils.php');
require_once(__DIR__ . '/fixtures/test_base.php');

/**
 * @group qtype_stack
 */
class stack_astfilter_test extends qtype_stack_testcase {



    public function test_002_log_candy() {
        $teststring  = 'log_5(x)+log_x+y(x)+log_x^y(y);';
        $result      = 'lg(x,5)+lg(x,x+y)+lg(y,x^y);' . "\n";
        $ast         = maxima_parser_utils::parse($teststring);
        $answernotes = array();
        $errors      = array();

        $astfilter   = new stack_ast_log_candy_002();

        // This test might allow functions that are allowed, but not yet.
        $security    = new stack_cas_security();
        $filtered    = $astfilter->filter($ast, $errors, $answernotes, $security);

        $this->assertEquals(0, count($errors));
        $this->assertContains('logsubs', $answernotes);
        $this->assertEquals($result, $filtered->toString());
    }



	public function test_040_function_prefix() {
		$teststring  = 'foosin(x)+ratan(ylg(y))+sinsin;';
        $result      = 'foo*sin(x)+r*atan(y*lg(y))+sinsin;' . "\n";
        $ast         = maxima_parser_utils::parse($teststring);
        $answernotes = array();
        $errors      = array();

        $astfilter   = new stack_ast_common_function_name_multiplier_040();

		// This test might allow functions that are allowed, but not yet.
		$security    = new stack_cas_security();
        $filtered    = $astfilter->filter($ast, $errors, $answernotes, $security);

        $this->assertEquals(0, count($errors));
        $this->assertContains('missing_stars', $answernotes);
        $this->assertEquals($result, $filtered->toString());
    }

	public function test_050_float_split() {
		$teststring  = '[xsin(x)*1.0*2.0e-1,2e2,sqrt(2E-1),.1e-90];';
        $result      = '[xsin(x)*1.0*2.0*e-1,2*e*2,sqrt(2*E-1),.1*e-90];' . "\n";
        $ast         = maxima_parser_utils::parse($teststring);
        $answernotes = array();
        $errors      = array();

        $astfilter   = new stack_ast_filter_split_floats_050();

        // This test does not require knowledge of security but the interface does.
        $security    = new stack_cas_security();
        $filtered    = $astfilter->filter($ast, $errors, $answernotes, $security);

        $this->assertEquals(0, count($errors));
        $this->assertContains('missing_stars', $answernotes);
        $this->assertEquals($result, $filtered->toString());
    }


}
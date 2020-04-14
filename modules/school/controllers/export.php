<?php
/**
 * @filesource modules/school/controllers/export.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace School\Export;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=school-export.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{
    /**
     * ส่งออกไฟล์ csv หรือ การพิมพ์.
     *
     * @param Request $request
     */
    public function execute(Request $request)
    {
        switch ($request->get('type')->toString()) {
            case 'student':
                $this->student($request);
                break;
            case 'grade':
                $this->grade($request);
                break;
            case 'mygrade':
                $this->mygrade($request);
                break;
            case 'course':
                $this->course($request);
                break;
        }
    }

    /**
     * ส่งออกเกรดของนักเรียนที่เลือก
     */
    private function mygrade(Request $request)
    {
        // อ่านข้อมูลนักเรียน
        $student = \School\User\Model::get($request->get('id')->toInt());
        // สมาชิก
        $login = Login::isMember();
        if ($student && $login) {
            if (Login::checkPermission($login, array('can_manage_student', 'can_manage_course', 'can_teacher', 'can_rate_student'))) {
                // ครู-อาจารย์, สามารถจัดการนักเรียนได้ ดูได้ทุกคน
            } elseif ($login['status'] == self::$cfg->student_status) {
                // นักเรียน ดูได้เฉพาะของตัวเอง
                if ($login['id'] != $student->id) {
                    $student = null;
                }
            }
            if ($student) {
                // ค่าที่ส่งมา
                $student->year = $request->get('year', self::$cfg->academic_year)->toInt();
                $student->term = $request->get('term', self::$cfg->term)->toInt();
                // header
                $header = array(
                    Language::get('Course Code'),
                    Language::get('Course Name'),
                    Language::get('Type'),
                    Language::get('Credit'),
                    Language::get('Grade'),
                );
                // content
                $datas = array();
                $course_typies = Language::get('COURSE_TYPIES');
                $credit = 0;
                $total = 0;
                foreach (\School\Grade\Model::toDataTable($student)->toArray()->cacheOn()->execute() as $item) {
                    if ($item['credit'] == 0.0) {
                        $item['credit'] = '';
                    } else {
                        $credit += $item['credit'];
                        $total += ($item['grade'] * $item['credit']);
                    }
                    $datas[] = array(
                        $item['course_code'],
                        $item['course_name'],
                        isset($course_typies[$item['type']]) ? $course_typies[$item['type']] : '',
                        $item['credit'],
                        $item['grade'],
                    );
                }
                $grade = \Gcms\View::div($total, $credit);
                if ($request->get('export')->toString() == 'print') {
                    // ส่งออกเป็น HTML สำหรับพิมพ์
                    return \School\Export\View::render($student, $header, $datas, $credit, $grade);
                } else {
                    $title = array(
                        array(Language::get('Name'), $student->name),
                        array(Language::get('Student ID'), $student->student_id),
                        array(Language::get('Academic year'), $student->year.'/'.$student->term),
                    );
                    $datas[] = array(Language::get('Credits in this semester'), $credit);
                    $datas[] = array(Language::get('Grades in this semester'), $grade);
                    // ส่งออกไฟล์ csv

                    return \Kotchasan\Csv::send(implode('_', array(
                        $student->student_id,
                        $student->name,
                        $student->year,
                        $student->term,
                    )), null, array_merge($title, array($header), $datas));
                }
            }
        }

        return false;
    }

    /**
     * ส่งออกข้อมูลตัวอย่าง grade เป็นไฟล์ CSV (grade.csv).
     */
    private function grade(Request $request)
    {
        // ค่าที่ส่งมา
        $course = $request->get('course')->topic();
        $room = $request->get('room')->toInt();
        $year = $request->get('year')->toInt();
        $term = $request->get('term')->toInt();
        // header
        $header = array(
            Language::get('Course'),
            Language::get('Number'),
            Language::get('Student ID'),
            Language::get('Midterm'),
            Language::get('Final'),
            Language::get('Grade'),
            Language::get('Room'),
            Language::get('Academic year'),
            Language::get('Term'),
        );
        $datas = array();
        foreach (\School\Students\Model::lists($course, $room) as $item) {
            $datas[] = array($course, $item['number'], $item['student_id'], '', '', '', $room, $year, $term);
        }
        if (empty($datas)) {
            $datas = array(
                array($course, 1, 1000, 50, 50, 4, $room, $year, $term),
                array($course, 2, 1001, 0, 0, 'ร.', $room, $year, $term),
            );
        }
        // ส่งออกไฟล์ grade.csv

        return \Kotchasan\Csv::send('grade', $header, $datas);
    }

    /**
     * ส่งออกข้อมูลตัวอย่าง นักเรียน เป็นไฟล์ CSV (student.csv).
     */
    private function student(Request $request)
    {
        // header
        $header = array(
            Language::get('Number'),
            Language::trans('{LNG_Student ID} *, **'),
            Language::trans('{LNG_Name} *'),
            Language::trans('{LNG_Identification number}, **'),
            Language::get('Birthday'),
            Language::get('Phone'),
            Language::get('Sex'),
            Language::get('Address'),
            Language::trans('{LNG_Name} ({LNG_Parent})'),
            Language::trans('{LNG_Phone} ({LNG_Parent})'),
        );
        $birthday = ((int) date('Y') + (int) Language::get('YEAR_OFFSET')).'-01-31';
        $datas = array(
            array(1, '1000', 'นาย สมชาย มาดแมน', '', $birthday, '0123456789', 'f', '', '', ''),
            array(2, '1001', 'นางสาว สมหญิง สวยงาม', '', $birthday, '0123456788', 'm', '', '', ''),
        );
        foreach (Language::get('SCHOOL_CATEGORY') as $k => $v) {
            $header[] = $v;
            $datas[0][] = $request->get($k)->toInt();
            $datas[1][] = $request->get($k)->toInt();
        }
        // ส่งออกไฟล์ student.csv

        return \Kotchasan\Csv::send('student', $header, $datas);
    }

    /**
     * ส่งออกข้อมูลตัวอย่าง course เป็นไฟล์ CSV (course.csv).
     */
    private function course(Request $request)
    {
        // header
        $header = array(
            Language::trans('{LNG_Course Code} *, **'),
            Language::trans('{LNG_Course Name} *'),
            Language::trans('{LNG_Credit} *'),
            Language::get('Period'),
            Language::get('Type'),
            Language::get('Class'),
            Language::trans('Academic year'),
            Language::get('Term'),
            Language::get('Teacher'),
        );
        // ข้อมูล
        $teacher_id = $request->get('teacher_id')->toInt();
        $datas = array(
            '',
            '',
            '',
            '',
            $request->get('typ')->toInt(),
            $request->get('class')->toInt(),
            $request->get('year')->toInt(),
            $request->get('term')->toInt(),
            $teacher_id == 0 ? '' : $teacher_id,
        );
        // ส่งออกไฟล์ course.csv

        return \Kotchasan\Csv::send('course', $header, array($datas));
    }
}

<?php
/**
 * @filesource modules/school/controllers/init.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace School\Init;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * Init Module.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\KBase
{
    /**
     * ฟังก์ชั่นเริ่มต้นการทำงานของโมดูลที่ติดตั้ง
     * และจัดการเมนูของโมดูล.
     *
     * @param Request                $request
     * @param \Index\Menu\Controller $menu
     * @param array                  $login
     */
    public static function execute(Request $request, $menu, $login)
    {
        $submenus1 = array();
        $submenus2 = array();
        // ครู-อาจาร์ย, สามารถจัดการรายชื่อนักเรียนได้, สามารถจัดการรายวิชาได้
        if (Login::checkPermission($login, array('can_manage_student', 'can_manage_course', 'can_teacher', 'can_rate_student'))) {
            $submenus2[] = array(
                'text' => '{LNG_Course}',
                'url' => 'index.php?module=school-courses',
            );
            $submenus1[] = array(
                'text' => '{LNG_Student list}',
                'url' => 'index.php?module=school-students',
            );
        }
        // สามารถจัดการนักเรียนได้
        if (Login::checkPermission($login, array('can_manage_student', 'can_teacher'))) {
            $submenus2[] = array(
                'text' => '{LNG_Add New} {LNG_Course}',
                'url' => 'index.php?module=school-course',
            );
        }
        // ครู-อาจาร์ย, สามารถจัดการรายชื่อนักเรียนได้
        if (Login::checkPermission($login, array('can_teacher', 'can_manage_student'))) {
            $submenus2[] = array(
                'text' => '{LNG_Import} {LNG_Course}',
                'url' => 'index.php?module=school-import&amp;type=course',
            );
        }
        // ครู-อาจาร์ย, สามารถจัดการรายวิชาได้, ให้คะแนนได้
        if (Login::checkPermission($login, array('can_teacher', 'can_manage_course', 'can_rate_student'))) {
            $submenus2[] = array(
                'text' => '{LNG_Import} {LNG_Grade}',
                'url' => 'index.php?module=school-import&amp;type=grade',
            );
        }
        //  สามารถจัดการนักเรียนได้
        if (Login::checkPermission($login, 'can_manage_student')) {
            $submenus1[] = array(
                'text' => '{LNG_Add New} {LNG_Student}',
                'url' => 'index.php?module=school-student',
            );
            $submenus1[] = array(
                'text' => '{LNG_Import} {LNG_Student list}',
                'url' => 'index.php?module=school-import&amp;type=student',
            );
        }
        // นักเรียน
        if ($login['status'] == self::$cfg->student_status) {
            $submenus1[] = array(
                'text' => '{LNG_Grade Report}',
                'url' => 'index.php?module=school-grade&amp;id='.$login['id'],
            );
        }
        if (!empty($submenus1) || !empty($submenus2)) {
            $menu->addTopLvlMenu('school', '{LNG_School}', null, array_merge($submenus1, $submenus2), 'module');
        }
        // สามารถตั้งค่าระบบได้
        if (Login::checkPermission($login, 'can_config')) {
            $submenus = array(
                array(
                    'text' => '{LNG_Settings}',
                    'url' => 'index.php?module=school-settings',
                ),
                array(
                    'text' => '{LNG_Grade calculation}',
                    'url' => 'index.php?module=school-gradesettings',
                ),
            );

            foreach (Language::get('SCHOOL_CATEGORY') as $type => $text) {
                $submenus[] = array(
                    'text' => $text,
                    'url' => 'index.php?module=school-categories&amp;type='.$type,
                );
            }
            $submenus[] = array(
                'text' => '{LNG_Term}',
                'url' => 'index.php?module=school-categories&amp;type=term',
            );
            $menu->add('settings', '{LNG_School}', null, $submenus);
        }
    }

    /**
     * รายการ permission ของโมดูล.
     *
     * @param array $permissions
     *
     * @return array
     */
    public static function updatePermissions($permissions)
    {
        $permissions['can_rate_student'] = '{LNG_Can rate students in responsible courses}';
        $permissions['can_teacher'] = '{LNG_Teachers can manage their own courses}';
        $permissions['can_manage_student'] = '{LNG_Can manage students}';
        $permissions['can_manage_course'] = '{LNG_Can manage all courses}';

        return $permissions;
    }
}

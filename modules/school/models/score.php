<?php
/**
 * @filesource modules/school/models/score.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace School\Score;

/**
 * คำนวณเกรด
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{
    /**
     * คืนค่ารายการตัดเกรด
     * ถ้ายังไม่เคยตั้งค่าคืนค่าเริ่มต้น
     *
     * @return array
     */
    public static function get()
    {
        if (empty(self::$cfg->school_grade_caculations)) {
            // ค่าเริ่มต้น
            self::$cfg->school_grade_caculations = array(
                49 => 0,
                54 => 1,
                59 => 1.5,
                64 => 2,
                69 => 2.5,
                74 => 3,
                79 => 3.5,
                100 => 4,
            );
        }

        return self::$cfg->school_grade_caculations;
    }

    /**
     * แปลงคะแนนเป็นเกรด
     *
     * @param int $type
     * @param int $midterm
     * @param int $final
     *
     * @return string
     */
    public static function toGrade($type, $midterm, $final)
    {
        if (empty($type)) {
            // คำนวณเกรด
            $value = $midterm + $final;
            foreach (self::get() as $k => $v) {
                if ($value <= $k) {
                    return $v;
                }
            }

            return 'Err';
        } else {
            // เกรดที่เลือกจากภาษา
            return \Kotchasan\Language::find('SCHOOL_TYPIES', 0, $type);
        }
    }
}

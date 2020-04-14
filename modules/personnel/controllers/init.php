<?php
/**
 * @filesource modules/personnel/controllers/init.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Personnel\Init;

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
        $menu->addTopLvlMenu('personnel', '{LNG_Personnel}', 'index.php?module=personnel-setup', null, 'module');
        $submenus = array();
        // สามารถตั้งค่าระบบได้
        if (Login::checkPermission(Login::isMember(), 'can_config')) {
            $submenus[] = array(
                'text' => '{LNG_Settings}',
                'url' => 'index.php?module=personnel-settings',
            );
            foreach (Language::get('CATEGORIES') as $type => $text) {
                $submenus[] = array(
                    'text' => $text,
                    'url' => 'index.php?module=index-categories&amp;type='.$type,
                );
            }
        }
        // สามารถจัดการ personnel ได้
        if (Login::checkPermission($login, 'can_manage_personnel')) {
            $submenus[] = array(
                'text' => '{LNG_Import} {LNG_Personnel list}',
                'url' => 'index.php?module=personnel-import',
            );
            $submenus[] = array(
                'text' => '{LNG_Add New} {LNG_Personnel}',
                'url' => 'index.php?module=personnel-write',
            );
        }
        if (!empty($submenus)) {
            $menu->add('settings', '{LNG_Personnel}', null, $submenus);
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
        $permissions['can_manage_personnel'] = '{LNG_Can manage personnel}';

        return $permissions;
    }
}

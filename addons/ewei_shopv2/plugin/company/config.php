<?php
/**官方网站
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/26
 * Time: 22:02
 */
if(!defined('IN_IA')){
    exit('Access Denied');
}

return array(
    'version'   => '1.0',
    'id'        => 'company',
    'name'      => '官方网站',
    'v3'        => true,
    'menu'      => array(
        'plugincom'     => 1,
        'icon'          => 'page',
        'items'         => array(
            array('title' => '企业管理', 'route' => 'bussiness'),
            array('title' => '视频管理', 'route' => 'video'),
            array(
                'title' => '设置',
                'items' => array(
                    array('title' => '文章设置', 'route' => 'set'),
                )
            )
        )
    )
);
<?php
/**
 * 首页
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/26
 * Time: 22:09
 */
if(!defined('IN_IA')){
    exit('Access Denied');
}

class Index_EweiShopV2Page extends PluginMobilePage{
    public function main(){
        global $_W,$_GPC;
        $companys = pdo_fetchall('select * from ' . tablename('ewei_shop_company') . ' where uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
        foreach ($companys as &$item){
            $item['logo'] = tomedia($item['logo']);
            $item['url'] = mobileUrl('company/index/detail', array('id' => $item['id']));
        }
        unset($item);

        include $this->template('company/index/index');
    }

    public function detail(){
        global $_W,$_GPC;
        $id = intval($_GPC['id']);
        $company = pdo_fetch('select * from ' . tablename('ewei_shop_company') . ' where id=:id and uniacid=:uniacid', array(':id' => $id, ':uniacid' => $_W['uniacid']));
        $company['logo'] = tomedia($company['logo']);

        include $this->template('company/index/detail');
    }

    /**
     * 获取距离
     */
    public function distance(){
        global $_W,$_GPC;
        $id = intval($_GPC['id']);
        $lat = $_GPC['lat'];
        $lng = $_GPC['lng'];

        $company = pdo_fetch('select lat,lng from ' . tablename('ewei_shop_company') . ' where id=:id and uniacid=:uniacid', array(':id' => $id, ':uniacid' => $_W['uniacid']));

        $distance = m('util')->GetDistance($lat, $lng, $company['lat'], $company['lng'], 2);

        show_json(1, array('distance' => $distance));
    }
}
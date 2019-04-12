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

        $conditions = 'uniacid=:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        $keyword = $_GPC['keywords'];
        if(!empty($keyword)){
            $conditions .= ' and storename like :keyword';
            $params[':keyword'] = "%{$keyword}%";
        }

        $companys = pdo_fetchall('select * from ' . tablename('ewei_shop_company') . ' where ' . $conditions, $params);
        foreach ($companys as &$item){
            $item['logo'] = tomedia($item['logo']);
            $item['url'] = mobileUrl('company/index/detail', array('id' => $item['id']));
        }
        unset($item);

        if(!empty($companys)){
            $item = $companys[0];
        }

        include $this->template('company/index/index');
    }

    public function detail(){
        global $_W,$_GPC;
        $id = intval($_GPC['id']);
        $company = pdo_fetch('select * from ' . tablename('ewei_shop_company') . ' where id=:id and uniacid=:uniacid', array(':id' => $id, ':uniacid' => $_W['uniacid']));
        $company['logo'] = tomedia($company['logo']);

        $lat = $_SESSION['lat'];
        $lng = $_SESSION['lng'];

        $distance = m('util')->GetDistance($lat, $lng, $company['lat'], $company['lng'], 2);
        $company['distance'] = $distance;

        include $this->template('company/index/detail');
    }

    /**
     * 获取距离
     */
    public function distance(){
        global $_W,$_GPC;
        $id = intval($_GPC['id']);
        $lat = $_SESSION['lat'];
        $lng = $_SESSION['lng'];

        $company = pdo_fetch('select lat,lng from ' . tablename('ewei_shop_company') . ' where id=:id and uniacid=:uniacid', array(':id' => $id, ':uniacid' => $_W['uniacid']));

        $distance = m('util')->GetDistance($lat, $lng, $company['lat'], $company['lng'], 2);
        if(empty($distance) || $distance > 100000){
            $distance = 100000;
        }

        show_json(1, array('distance' => $distance));
    }

    public function get_distance(){
        global $_GPC;
        $_SESSION['lng'] = $_GPC['lng'];
        $_SESSION['lat'] = $_GPC['lat'];
        show_json(1);
    }

    public function get_company(){
        global $_W,$_GPC;
        $id = intval($_GPC['id']);
        $company = pdo_fetch('select * from ' . tablename('ewei_shop_company') . ' where id=:id and uniacid=:uniacid', array(':id' => $id, ':uniacid' => $_W['uniacid']));
        $company['logo'] = tomedia($company['logo']);
        $company['url'] = mobileUrl('company/index/detail', array('id' => $company['id']));
        $lat = $_SESSION['lat'];
        $lng = $_SESSION['lng'];

        $distance = m('util')->GetDistance($lat, $lng, $company['lat'], $company['lng'], 2);
        if(empty($distance) || $distance > 100000){
            $distance = 100000;
        }

        show_json(1, array('distance' => $distance, 'item' => $company));
    }
}
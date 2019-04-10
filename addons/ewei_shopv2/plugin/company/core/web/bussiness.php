<?php
/**
 * 企业管理
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/30
 * Time: 10:53
 */
if(!defined('IN_IA')){
    exit('Access Denied');
}

class Bussiness_EweiShopV2Page extends PluginWebPage{

    public function main(){
        global $_W,$_GPC;

        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $paras = array(':uniacid' => $_W['uniacid']);
        $condition = ' uniacid = :uniacid';

        if (!empty($_GPC['keyword'])) {
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $condition .= ' AND (storename LIKE \'%' . $_GPC['keyword'] . '%\' OR address LIKE \'%' . $_GPC['keyword'] . '%\' OR tel LIKE \'%' . $_GPC['keyword'] . '%\')';
        }

        $sql = 'SELECT * FROM ' . tablename('ewei_shop_company') . (' WHERE ' . $condition . ' ORDER BY displayorder desc,id desc');
        $sql .= ' LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
        $sql_count = 'SELECT count(1) FROM ' . tablename('ewei_shop_company') . (' WHERE ' . $condition);
        $total = pdo_fetchcolumn($sql_count, $paras);
        $pager = pagination2($total, $pindex, $psize);
        $list = pdo_fetchall($sql, $paras);

        include $this->template('company/bussiness/index');
    }

    public function add(){
        $this->post();
    }

    public function edit(){
        $this->post();
    }

    protected function post(){
        global $_W,$_GPC;

        $id = intval($_GPC['id']);
        $area_set = m('util')->get_area_config_set();
        $new_area = intval($area_set['new_area']);
        $address_street = intval($area_set['address_street']);

        if ($_W['ispost']){
            if (empty($_GPC['logo'])) {
                show_json(0, '门店LOGO不能为空');
            }

            if (empty($_GPC['map']['lng']) || empty($_GPC['map']['lat'])) {
                show_json(0, '门店位置不能为空');
            }

            if (empty($_GPC['address'])) {
                show_json(0, '门店地址不能为空');
            }
            else {
                if (30 < mb_strlen($_GPC['address'], 'UTF-8')) {
                    show_json(0, '门店地址不能超过30个字符');
                }
            }
            if (empty($_GPC['tel']) || strlen(trim($_GPC['tel'])) <= 0) {
                show_json(0, '门店电话不能为空');
            }
            else {
                if (20 < strlen($_GPC['tel'])) {
                    show_json(0, '门店电话不能大于20个字符');
                }
            }

            $data = array('uniacid' => $_W['uniacid'], 'storename' => trim($_GPC['storename']), 'address' => trim($_GPC['address']), 'province' => trim($_GPC['province']), 'city' => trim($_GPC['city']), 'area' => trim($_GPC['area']), 'provincecode' => trim($_GPC['chose_province_code']), 'citycode' => trim($_GPC['chose_city_code']), 'areacode' => trim($_GPC['chose_area_code']), 'tel' => trim($_GPC['tel']), 'lng' => $_GPC['map']['lng'], 'lat' => $_GPC['map']['lat'], 'realname' => trim($_GPC['realname']), 'logo' => save_media($_GPC['logo']), 'desc' => trim($_GPC['desc']), 'content' => $_GPC['content']);

            if (!empty($id)) {
                pdo_update('ewei_shop_company', $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
            }
            else {
                pdo_insert('ewei_shop_company', $data);
            }

            show_json(1, array('url' => webUrl('company/bussiness')));
        }

        $item = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_company') . ' WHERE id =:id and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $id));
        include $this->template('company/bussiness/post');
    }

    public function delete(){
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);

        if (empty($id)) {
            $id = is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0;
        }

        $items = pdo_fetchall('SELECT id,storename FROM ' . tablename('ewei_shop_company') . (' WHERE id in( ' . $id . ' ) AND uniacid=') . $_W['uniacid']);

        foreach ($items as $item) {
            pdo_delete('ewei_shop_company', array('id' => $item['id']));
        }

        show_json(1, array('url' => referer()));
    }
}
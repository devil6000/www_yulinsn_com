<?php
/**
 * 送礼订单
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/21
 * Time: 19:48
 */

if( !defined("IN_IA") ){
    exit( "Access Denied" );
}

class Gift_EweiShopV2Page extends MobileLoginPage{

    public function detail(){
        global $_W;
        global $_GPC;
        $openid = $_W["openid"];
        $uniacid = $_W["uniacid"];
        $member = m("member")->getMember($openid, true);
        $orderid = intval($_GPC["id"]);
        if(empty($orderid)){
            header("location: " . mobileUrl("order", array('status' => 6)));
            exit();
        }
        $order = pdo_fetch("select * from " . tablename("ewei_shop_order") . " where id=:id and uniacid=:uniacid limit 1", array( ":id" => $orderid, ":uniacid" => $uniacid));
        if( empty($order) )
        {
            header("location: " . mobileUrl("order", array('status' => 6)));
            exit();
        }
        $giftMember = m('member')->getMember($order['gift_openid'], true);
        if($order['gift_openid'] == $_W['openid']){
            $gifFinish = 1;
        }

        $isonlyverifygoods = m("order")->checkisonlyverifygoods($order["id"]);
        if( $order["refundid"] != 0 )
        {
            $refund = pdo_fetch("SELECT *  FROM " . tablename("ewei_shop_order_refund") . " WHERE orderid = :orderid and uniacid=:uniacid order by id desc", array( ":orderid" => $order["id"], ":uniacid" => $_W["uniacid"] ));
        }
        $area_set = m("util")->get_area_config_set();
        $new_area = intval($area_set["new_area"]);
        $address_street = intval($area_set["address_street"]);
        $merchdata = $this->merchData();
        extract($merchdata);
        $merchid = $order["merchid"];
        $diyform_plugin = p("diyform");
        $diyformfields = "";
        if( $diyform_plugin )
        {
            $diyformfields = ",og.diyformfields,og.diyformdata";
        }
        $param = array( );
        $param[":uniacid"] = $_W["uniacid"];
        if( $order["isparent"] == 1 )
        {
            $scondition = " og.parentorderid=:parentorderid";
            $param[":parentorderid"] = $orderid;
        }
        else
        {
            $scondition = " og.orderid=:orderid";
            $param[":orderid"] = $orderid;
        }
        $condition1 = "";
        if( p("ccard") )
        {
            $condition1 .= ",g.ccardexplain,g.ccardtimeexplain";
        }
        $goodsid_array = array( );
        $goods = pdo_fetchall("select og.goodsid,og.price,g.title,og.title as gtitle,g.thumb,g.status, g.cannotrefund, og.total,g.credit,og.optionid,\r\n            og.optionname as optiontitle,g.isverify,g.storeids,og.seckill,g.isfullback,\r\n            og.seckill_taskid" . $diyformfields . $condition1 . ",og.prohibitrefund  from " . tablename("ewei_shop_order_goods") . " og " . " left join " . tablename("ewei_shop_goods") . " g on g.id=og.goodsid " . " where " . $scondition . " and og.uniacid=:uniacid ", $param);
        $prohibitrefund = false;
        foreach( $goods as &$g )
        {
            if( $g["isfullback"] )
            {
                $fullbackgoods = pdo_fetch("SELECT * FROM " . tablename("ewei_shop_fullback_goods") . " WHERE goodsid = :goodsid and uniacid = :uniacid  limit 1 ", array( ":goodsid" => $g["goodsid"], ":uniacid" => $uniacid ));
                if( $g["optionid"] )
                {
                    $option = pdo_fetch("select `day`,allfullbackprice,fullbackprice,allfullbackratio,fullbackratio,isfullback\r\n                      from " . tablename("ewei_shop_goods_option") . " where id = :id and uniacid = :uniacid ", array( ":id" => $g["optionid"], ":uniacid" => $uniacid ));
                    $fullbackgoods["minallfullbackallprice"] = $option["allfullbackprice"];
                    $fullbackgoods["fullbackprice"] = $option["fullbackprice"];
                    $fullbackgoods["minallfullbackallratio"] = $option["allfullbackratio"];
                    $fullbackgoods["fullbackratio"] = $option["fullbackratio"];
                    $fullbackgoods["day"] = $option["day"];
                }
                $g["fullbackgoods"] = $fullbackgoods;
                unset($fullbackgoods);
                unset($option);
            }
            $g["seckill_task"] = false;
            if( $g["seckill"] )
            {
                $g["seckill_task"] = plugin_run("seckill::getTaskInfo", $g["seckill_taskid"]);
            }
            if( !empty($g["prohibitrefund"]) )
            {
                $prohibitrefund = true;
            }
            if( empty($g["gtitle"]) != true )
            {
                $g["title"] = $g["gtitle"];
            }
        }
        unset($g);
        $goodsrefund = true;
        if( !empty($goods) )
        {
            foreach( $goods as &$g )
            {
                $goodsid_array[] = $g["goodsid"];
                if( !empty($g["optionid"]) )
                {
                    $thumb = m("goods")->getOptionThumb($g["goodsid"], $g["optionid"]);
                    if( !empty($thumb) )
                    {
                        $g["thumb"] = $thumb;
                    }
                }
                if( !empty($g["cannotrefund"]) && $order["status"] == 2 )
                {
                    $goodsrefund = false;
                }
            }
            unset($g);
        }
        $diyform_flag = 0;
        if( $diyform_plugin )
        {
            foreach( $goods as &$g )
            {
                $g["diyformfields"] = iunserializer($g["diyformfields"]);
                $g["diyformdata"] = iunserializer($g["diyformdata"]);
                unset($g);
            }
            if( !empty($order["diyformfields"]) && !empty($order["diyformdata"]) )
            {
                $order_fields = iunserializer($order["diyformfields"]);
                $order_data = iunserializer($order["diyformdata"]);
            }
        }
        $address = false;
        if( !empty($order["addressid"]) )
        {
            $address = iunserializer($order["address"]);
            if( !is_array($address) )
            {
                $address = pdo_fetch("select * from  " . tablename("ewei_shop_member_address") . " where id=:id limit 1", array( ":id" => $order["addressid"] ));
            }
        }
        $carrier = @iunserializer($order["carrier"]);
        if( !is_array($carrier) || empty($carrier) )
        {
            $carrier = false;
        }
        $store = false;
        if( !empty($order["storeid"]) )
        {
            if( 0 < $merchid )
            {
                $store = pdo_fetch("select * from  " . tablename("ewei_shop_merch_store") . " where id=:id limit 1", array( ":id" => $order["storeid"] ));
            }
            else
            {
                $store = pdo_fetch("select * from  " . tablename("ewei_shop_store") . " where id=:id limit 1", array( ":id" => $order["storeid"] ));
            }
        }
        $stores = false;
        $showverify = false;
        $canverify = false;
        $verifyinfo = false;
        if( com("verify") )
        {
            $showverify = $order["dispatchtype"] || $order["isverify"];
            if( $order["isverify"] )
            {
                if( 0 < $order["verifyendtime"] && $order["verifyendtime"] < time() )
                {
                    $order["status"] = -1;
                }
                $storeids = array( );
                foreach( $goods as $g )
                {
                    if( !empty($g["storeids"]) )
                    {
                        $storeids = array_merge(explode(",", $g["storeids"]), $storeids);
                    }
                }
                if( empty($storeids) )
                {
                    if( 0 < $merchid )
                    {
                        $stores = pdo_fetchall("select * from " . tablename("ewei_shop_merch_store") . " where  uniacid=:uniacid and merchid=:merchid and status=1 and type in(2,3)", array( ":uniacid" => $_W["uniacid"], ":merchid" => $merchid ));
                    }
                    else
                    {
                        $stores = pdo_fetchall("select * from " . tablename("ewei_shop_store") . " where  uniacid=:uniacid and status=1 and type in(2,3)", array( ":uniacid" => $_W["uniacid"] ));
                    }
                }
                else
                {
                    if( 0 < $merchid )
                    {
                        $stores = pdo_fetchall("select * from " . tablename("ewei_shop_merch_store") . " where id in (" . implode(",", $storeids) . ") and uniacid=:uniacid and merchid=:merchid and status=1 and type in(2,3)", array( ":uniacid" => $_W["uniacid"], ":merchid" => $merchid ));
                    }
                    else
                    {
                        $stores = pdo_fetchall("select * from " . tablename("ewei_shop_store") . " where id in (" . implode(",", $storeids) . ") and uniacid=:uniacid and status=1 and type in(2,3)", array( ":uniacid" => $_W["uniacid"] ));
                    }
                }
                if( $order["verifytype"] == 0 || $order["verifytype"] == 1 || $order["verifytype"] == 3 )
                {
                    $vs = iunserializer($order["verifyinfo"]);
                    $verifyinfo = array( array( "verifycode" => $order["verifycode"], "verified" => ($order["verifytype"] == 0 || $order["verifytype"] == 3 ? $order["verified"] : $goods[0]["total"] <= count($vs)) ) );
                    if( $order["verifytype"] == 0 || $order["verifytype"] == 3 )
                    {
                        $canverify = empty($order["verified"]) && $showverify;
                    }
                    else
                    {
                        if( $order["verifytype"] == 1 )
                        {
                            $canverify = count($vs) < $goods[0]["total"] && $showverify;
                        }
                    }
                }
                else
                {
                    $verifyinfo = iunserializer($order["verifyinfo"]);
                    $last = 0;
                    foreach( $verifyinfo as $v )
                    {
                        if( !$v["verified"] )
                        {
                            $last++;
                        }
                    }
                    $canverify = 0 < $last && $showverify;
                }
            }
            else
            {
                if( !empty($order["dispatchtype"]) )
                {
                    $verifyinfo = array( array( "verifycode" => $order["verifycode"], "verified" => $order["status"] == 3 ) );
                    $canverify = $order["status"] == 1 && $showverify;
                }
            }
        }
        $order["canverify"] = $canverify;
        $order["showverify"] = $showverify;
        $order["virtual_str"] = str_replace("\n", "<br/>", $order["virtual_str"]);
        $canreturn = false;
        if( $order["status"] == 1 || $order["status"] == 2 )
        {
            $canrefund = true;
            if( $order["status"] == 2 && $order["price"] == $order["dispatchprice"] )
            {
                if( 0 < $order["refundstate"] )
                {
                    $canrefund = true;
                }
                else
                {
                    $canrefund = false;
                    if( !$goodsrefund )
                    {
                        $canreturn = false;
                    }
                    else
                    {
                        $canreturn = true;
                    }
                }
            }
        }
        else
        {
            if( $order["status"] == 3 && $order["isverify"] != 1 && empty($order["virtual"]) )
            {
                if( 0 < $order["refundstate"] )
                {
                    $canrefund = true;
                }
                else
                {
                    $tradeset = m("common")->getSysset("trade");
                    $refunddays = intval($tradeset["refunddays"]);
                    if( 0 < $refunddays )
                    {
                        $days = intval((time() - $order["finishtime"]) / 3600 / 24);
                        if( $days <= $refunddays )
                        {
                            $canrefund = true;
                        }
                    }
                }
            }
        }
        if( !empty($order["isnewstore"]) && 1 < $order["status"] )
        {
            $canrefund = false;
        }
        if( $prohibitrefund )
        {
            $canrefund = false;
        }
        if( !$goodsrefund && $canrefund )
        {
            $canrefund = false;
        }
        if( p("ccard") )
        {
            if( !empty($order["ccard"]) && 1 < $order["status"] )
            {
                $canrefund = false;
            }
            $comdata = m("common")->getPluginset("commission");
            if( !empty($comdata["become_goodsid"]) && !empty($goodsid_array) && in_array($comdata["become_goodsid"], $goodsid_array) )
            {
                $canrefund = false;
            }
        }
        $haveverifygoodlog = m("order")->checkhaveverifygoodlog($orderid);
        if( $haveverifygoodlog )
        {
            $canrefund = false;
        }
        $order["canrefund"] = $canrefund;
        $express = false;
        $order_goods = array( );
        if( 2 <= $order["status"] && empty($order["isvirtual"]) && empty($order["isverify"]) )
        {
            $expresslist = m("util")->getExpressList($order["express"], $order["expresssn"]);
            if( 0 < count($expresslist) )
            {
                $express = $expresslist[0];
            }
        }
        if( 0 < $order["sendtype"] && 1 <= $order["status"] )
        {
            $order_goods = pdo_fetchall("select orderid,goodsid,sendtype,expresscom,expresssn,express,sendtime from " . tablename("ewei_shop_order_goods") . "\r\n            where orderid = " . $orderid . " and uniacid = " . $uniacid . " and sendtype > 0 group by sendtype order by sendtime asc ");
            $expresslist = m("util")->getExpressList($order["express"], $order["expresssn"]);
            if( 0 < count($expresslist) )
            {
                $express = $expresslist[0];
            }
            $order["sendtime"] = $order_goods[0]["sendtime"];
        }
        $shopname = $_W["shopset"]["shop"]["name"];
        if( $order["canverify"] && $order["status"] != -1 && $order["status"] != 0 )
        {
            $query = array( "id" => $order["id"], "verifycode" => $order["verifycode"] );
            if( !$isonlyverifygoods )
            {
                if( empty($order["istrade"]) )
                {
                    $url = mobileUrl("verify/detail", $query, true);
                }
                else
                {
                    $url = mobileUrl("verify/tradedetail", $query, true);
                }
                $verifycode = $order["verifycode"];
                $qrcodeimg = m("qrcode")->createQrcode($url);
                if( strlen($verifycode) == 8 )
                {
                    $verifycode = substr($verifycode, 0, 4) . " " . substr($verifycode, 4, 4);
                }
                else
                {
                    if( strlen($verifycode) == 9 )
                    {
                        $verifycode = substr($verifycode, 0, 3) . " " . substr($verifycode, 3, 3) . " " . substr($verifycode, 6, 3);
                    }
                }
            }
        }
        if( !empty($order["merchid"]) && $is_openmerch == 1 )
        {
            $merch_user = $merch_plugin->getListUser($order["merchid"]);
            $shopname = $merch_user["merchname"];
            $shoplogo = tomedia($merch_user["logo"]);
        }
        if( com("coupon") )
        {
            $activity = com("coupon")->activity($order["price"]);
        }
        if( !empty($order["virtual"]) && !empty($order["virtual_str"]) )
        {
            $ordervirtual = m("order")->getOrderVirtual($order);
            $virtualtemp = pdo_fetch("SELECT linktext, linkurl FROM " . tablename("ewei_shop_virtual_type") . " WHERE id=:id AND uniacid=:uniacid LIMIT 1", array( ":id" => $order["virtual"], ":uniacid" => $_W["uniacid"] ));
        }
        if( 0 < $order["seckilldiscountprice"] && p("diypage") )
        {
            $diypagedata = m("common")->getPluginset("diypage");
            $diypage = p("diypage")->seckillPage($diypagedata["seckill"]);
            if( !empty($diypage) )
            {
                $seckill_color = $diypage["seckill_color"];
            }
        }
        $use_membercard = false;
        $membercard_info = array( );
        $plugin_membercard = p("membercard");
        if( $plugin_membercard )
        {
            $ifuse = $plugin_membercard->if_order_use_membercard($orderid);
            if( $ifuse )
            {
                $use_membercard = true;
                $card_text = $ifuse["name"] . "优惠";
                $card_dec_price = $ifuse["dec_price"];
            }
        }
        if( $order["isvirtualsend"] && $order["isvirtual"] )
        {
            $order["canrefund"] = false;
        }
        include($this->template('order/gift_detail'));
    }
}
/* ims_ewei_shop_order */
alter table `ims_ewei_shop_order` add `order_type` tinyint(1) null default 0 comment '订单类型0普通订单，1送礼订单';
alter table `ims_ewei_shop_order` add `gift_mobile` varchar(20) null comment '收礼手机号';
alter table `ims_ewei_shop_order` add `gift_openid` varchar(100) null comment '收礼人openid';

/* ims_ewei_shop_company_set */
create table `ims_ewei_shop_company_set`(
  `id` int(10) not null auto_increment,
  ``
);

/* ims_ewei_shop_shop_company */
create table `ims_ewei_shop_company`(
  `id` int(10) not null auto_increment,
  `uniacid` int(4) not null,
  `storename` varchar(255) not null,
  `address` varchar(255) not null,
  `tel` varchar(255) null,
  `lat` varchar(255) null,
  `lng` varchar(255) null,
  `realname` varchar(255) null,
  `logo` varchar(255) null,
  `desc` text null,
  `displayorder` int(4) null default 0,
  `province` varchar(30) null,
  `city` varchar(30) null,
  `area` varchar(30) null,
  `provincecode` varchar(30) null,
  `citycode` varchar(30) null,
  `areacode` varchar(30) null,
  `banner` text null,
  `content` text null,
  primary key(`id`)
);

/* ims_ewei_shop_company_page */
create table `ims_ewei_shop_company_page`(
  `id` int(10) not null auto_increment,
  `uniacid` int(4) null,
  `title` varchar(255) not null,
  `cates` varchar(255) not null,
  `type` int(4) null default 0,
  primary key(`id`)
);

/* ims_ewei_shop_company_video */
create table `ims_ewei_shop_company_video`(
  `id` int(10) not null auto_increment,
  `uniacid` int(4) null,
  `title` varchar(255) not null,
  `createtime` int(6) null default 0,
  `url` VARCHAR(255) not null,
  primary key(`id`)
);

insert into `ims_ewei_shop_plugin`(`displayorder`,`identity`,`category`,`name`,`version`,`author`,`status`,`thumb`,`isv2`)
values(55,'company','tool','公司网站',1.0,'官方',1,'../addons/ewei_shopv2/static/images/pc.jpg',1)
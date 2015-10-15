<?php

$sql = '';
$sql = "

    CREATE DATABASE IF NOT EXISTS `yangcong_private_cloud`
    DEFAULT CHARACTER SET utf8
    COLLATE utf8_general_ci;

    USE `yangcong_private_cloud`;

    CREATE TABLE IF NOT EXISTS `user`(
        `user_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户id',
        `user_name` VARCHAR(20) NOT NULL COMMENT '用户名',
        `yangcong_uid` VARCHAR(64) NOT NULL COMMENT '洋葱公有云id',
        `true_name` VARCHAR(20) NOT NULL COMMENT '姓名',
        `phone` VARCHAR(11) NOT NULL COMMENT '手机号',
        `intro` VARCHAR(100) NOT NULL COMMENT '用户简介',
        `is_open` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '开启状态，0为关闭，1为开启',
        `create_time` DATETIME NOT NULL COMMENT '创建时间',
        `update_time` DATETIME NOT NULL COMMENT '更改时间',
        `is_admin` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0为普通用户，1是管理员',
        PRIMARY KEY(`user_id`)
    )ENGINE=`MyISAM` COMMENT='企业用户';

    CREATE TABLE IF NOT EXISTS `group_info`(
        `gid` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '组ID',
        `name` VARCHAR(20) NOT NULL COMMENT '组名',
        `inner` TINYINT NOT NULL COMMENT '内置分组，不可删除',
        PRIMARY KEY(`gid`)
    )ENGINE=`MyISAM` COMMENT='企业用户组';

    CREATE TABLE IF NOT EXISTS `user_group`(
        `user_id` INT(11) UNSIGNED NOT NULL  COMMENT '用户id',
        `gid` SMALLINT UNSIGNED NOT NULL COMMENT '组ID'
    )ENGINE=`MyISAM` COMMENT='用户所属组';

    CREATE TABLE IF NOT EXISTS `power`(
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
        `name` VARCHAR(20) NOT NULL COMMENT '权限名称',
        `intro` VARCHAR(50) NOT NULL COMMENT '权限介绍',
        `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '权限开启状态',
        `power_id` VARCHAR(20) NOT NULL COMMENT '权限验证ID',
        `power_key` VARCHAR(32) NOT NULL COMMENT '权限验证key',
        PRIMARY KEY(`id`)
    )ENGINE=`MyISAM` COMMENT='企业权限表';

    CREATE TABLE IF NOT EXISTS `group_power`(
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '设置权限ID',
        `gid` SMALLINT UNSIGNED NOT NULL COMMENT '组ID',
        `power_id` INT(11) UNSIGNED NOT NULL  COMMENT '权限ID',
        PRIMARY KEY(`id`)
    )ENGINE=`MyISAM` COMMENT='设置权限表';

    CREATE TABLE IF NOT EXISTS `auth_log`(
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '日志ID',
        `auth_user` VARCHAR(20) NOT NULL COMMENT '用户名',
        `auth_type` TINYINT(1) UNSIGNED NOT NULL COMMENT '验证类型，1为点击按钮验证，2手势验证，3人脸验证，4声音验证',
        `power_name` VARCHAR(20) NOT NULL COMMENT '权限名称',
        `power_id`  INT(11) UNSIGNED NOT NULL COMMENT '权限ID',
        `event_id` VARCHAR(20) NOT NULL COMMENT '事件id',
        `auth_result` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '事件结果',
        `auth_time` DATETIME NOT NULL COMMENT '验证时间',
        PRIMARY KEY(`id`)
    )ENGINE=`MyISAM` COMMENT='验证日志表';

    CREATE TABLE IF NOT EXISTS `op_log`(
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '日志ID',
        `op_user` VARCHAR(20) NOT NULL COMMENT '操作者',
        `op_name` VARCHAR(20) NOT NULL COMMENT '操作名称',
        `op_intro` VARCHAR(50) NOT NULL COMMENT '操作简述',
        `op_time` DATETIME NOT NULL COMMENT '操作时间',
        `op_status` TINYINT(1) NOT NULL COMMENT '操作结果',
        PRIMARY KEY(`id`)
    )ENGINE=`MyISAM` COMMENT='访问日志表';

    CREATE TABLE IF NOT EXISTS `company`(
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
        `name` VARCHAR(20) NOT NULL COMMENT '企业名称',
        `intro` VARCHAR(100) NOT NULL COMMENT '企业简介',
        `logo` VARCHAR(100) NOT NULL COMMENT '企业logo',
        PRIMARY KEY(`id`)
    )ENGINE=`MyISAM` COMMENT='企业信息';

    CREATE TABLE IF NOT EXISTS `setting`(
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
        `service_type` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0为未设置，1为使用公有云，2为使用私有云',
        `app_id` VARCHAR(32) NOT NULL COMMENT 'app_id, 洋葱分配',
        `app_key` VARCHAR(28) NOT NULL COMMENT 'app_key,洋葱分配',
        PRIMARY KEY(`id`)
    )ENGINE=`MyISAM` COMMENT='应用配置信息';

    CREATE TABLE IF NOT EXISTS `version`(
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
        `dependent_info` VARCHAR(20) NOT NULL COMMENT '私有云依赖',
        `dependent_code` VARCHAR(20) NOT NULL COMMENT '依赖code',
        `version_name` VARCHAR(10) NOT NULL COMMENT '当前依赖版本',
        `version_code` TINYINT NOT NULL COMMENT '版本code，根据此项来检查是否有升级信息',
        `upgrade_content` VARCHAR(255) NOT NULL COMMENT '本次更新内容',
        `upgrade_time` DATETIME NOT NULL COMMENT '最近升级时间',
        PRIMARY KEY(`id`)
     )ENGINE=`MyISAM` COMMENT='版本信息';

    CREATE TABLE IF NOT EXISTS `auth_statistics`(
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
        `power_id` INT(11) UNSIGNED NOT NULL COMMENT '权限ID',
        `click_day_auth_count` INT(11) UNSIGNED NOT NULL COMMENT '验证次数',
        `hand_day_auth_count` INT(11) UNSIGNED NOT NULL COMMENT '验证次数',
        `face_day_auth_count` INT(11) UNSIGNED NOT NULL COMMENT '验证次数',
        `noice_day_auth_count` INT(11) UNSIGNED NOT NULL COMMENT '验证次数',
        `statistics_time` DATETIME NOT NULL COMMENT '统计时间',
        PRIMARY KEY(`id`)
    )ENGINE=`MyISAM` COMMENT='验证统计信息';

    CREATE TABLE IF NOT EXISTS `device_statistics`(
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
        `divice_type` TINYINT UNSIGNED NOT NULL COMMENT '用户持有设备, 1为ios,2为android,3为其他',
        `divice_count` INT(11) NOT NULL COMMENT '设备统计数',
        `statistics_time` DATETIME NOT NULL COMMENT '统计时间',
        PRIMARY KEY(`id`)
    )ENGINE=`MyISAM` COMMENT='设备统计信息';

    CREATE TABLE IF NOT EXISTS `user_event`(
        `user_name` VARCHAR(20) NOT NULL COMMENT '验证用户名',
        `event_id` VARCHAR(40) NOT NULL COMMENT '事件id',
        `power_id` INT(11) UNSIGNED NOT NULL COMMENT '权限id'
    )ENGINE=`MyISAM` COMMENT='用户事件';

    CREATE TABLE `Test` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
      `event_id` varchar(20) NOT NULL COMMENT '事件id',
      `rand_type` tinyint(1) NOT NULL COMMENT '随机结果类型，0为成功，1失败，2为无响应',
      `send_time` varchar(11) NOT NULL COMMENT '发起时间',
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COMMENT='测试用';
";

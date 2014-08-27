DROP TABLE IF EXISTS `eva_permission_apikeys`;
CREATE TABLE IF NOT EXISTS `eva_permission_apikeys` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `apikey` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `userId` int(10) NOT NULL,
  `level` enum('basic','starter','business','unlimited','extreme','customize','blocked') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'basic',
  `minutelyRate` int(11) NOT NULL DEFAULT '0',
  `hourlyRate` int(11) NOT NULL DEFAULT '0',
  `dailyRate` int(13) NOT NULL DEFAULT '0',
  `createdAt` int(10) NOT NULL DEFAULT '0',
  `expiredAt` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`),
  UNIQUE KEY `apikey` (`apikey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `eva_permission_operations`;
CREATE TABLE IF NOT EXISTS `eva_permission_operations` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `operationKey` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `resourceId` int(10) NOT NULL,
  `resourceKey` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `operationKey` (`operationKey`,`resourceKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `eva_permission_resources`;
CREATE TABLE IF NOT EXISTS `eva_permission_resources` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `resourceKey` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `resourceGroup` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'app' COMMENT 'app | api | backend',
  `description` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `resourceKey` (`resourceKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `eva_permission_roles`;
CREATE TABLE IF NOT EXISTS `eva_permission_roles` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `roleKey` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roleKey` (`roleKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `eva_permission_roles_operations`;
CREATE TABLE IF NOT EXISTS `eva_permission_roles_operations` (
  `roleId` int(4) NOT NULL,
  `operationId` int(10) NOT NULL,
  PRIMARY KEY (`roleId`,`operationId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `eva_permission_users_roles`;
CREATE TABLE IF NOT EXISTS `eva_permission_users_roles` (
  `userId` int(10) NOT NULL,
  `roleId` int(4) NOT NULL,
  PRIMARY KEY (`userId`,`roleId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

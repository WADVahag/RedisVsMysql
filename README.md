# RedisVsMysql
Основа взята из сайта
https://blog.devso.io/mysql-database-vs-redis/


# Операторы создания MySQL следующие::

CREATE TABLE `counter` (
  `OrganisationID` int(11) NOT NULL,
  `counter` int(11) NOT NULL,
  PRIMARY KEY (`OrganisationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `storage` (
  `StorageID` int(11) NOT NULL AUTO_INCREMENT,
  `DateTimeReceived` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `index` int(11) NOT NULL,
  PRIMARY KEY (`StorageID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


## Whole Processing time
![This is an image](https://blog.devso.io/content/images/2018/09/Whole-Processing-Time-1.png)

## Storage Processing Time
![This is an image](https://blog.devso.io/content/images/2018/09/Storage-Processing-Time.png)


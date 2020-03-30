CREATE TABLE `Email`
(
    `emailId`               int unsigned NOT NULL AUTO_INCREMENT,
    `timeCreated`           timestamp    NOT NULL                                         DEFAULT CURRENT_TIMESTAMP,
    `email`                 varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
    `tags`                  varchar(1000)                                                 DEFAULT '',
    `sentToExternalService` timestamp    NULL                                             DEFAULT NULL,
    `emailHash`             binary(20)   NOT NULL,
    `date`                  date         NOT NULL,
    `sentWelcomeEmail`      timestamp    NULL                                             DEFAULT NULL,
    `confirm`               timestamp    NULL                                             DEFAULT NULL,
    `bloodType`             varchar(255)                                                  DEFAULT NULL,
    `phone`                 varchar(255)                                                  DEFAULT NULL,
    PRIMARY KEY (`emailId`),
    UNIQUE KEY `mailHash` (`emailHash`),
    KEY `date` (`date`)
)

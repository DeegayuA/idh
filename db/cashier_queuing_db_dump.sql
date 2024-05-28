SET FOREIGN_KEY_CHECKS=0;
BEGIN;

CREATE TABLE `user_list` (
    `user_id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `fullname` VARCHAR(255) NOT NULL,
    `username` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `status` INT NOT NULL DEFAULT 1,
    `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO user_list (user_id, fullname, username, password, status, date_created) VALUES
(1, 'Administrator', 'admin', '0192023a7bbd73250516f069df18b500', 1, '2024-05-28 03:33:13'),
(2, 'Claire Blake', 'cblake', '4744ddea876b11dcb1d169fadf494418', 1, '2024-05-28 02:58:33');

CREATE TABLE `cashier_list` (
    `cashier_id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `log_status` INT NOT NULL DEFAULT 0,
    `status` INT NOT NULL DEFAULT 1
);

INSERT INTO cashier_list (cashier_id, name, log_status, status) VALUES
(1, 'Cashier 1', 0, 1);

CREATE TABLE `queue_list` (
    `queue_id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `queue` VARCHAR(255) NOT NULL,
    `customer_name` VARCHAR(255) NOT NULL,
    `status` INT NOT NULL DEFAULT 0,
    `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO queue_list (queue_id, queue, customer_name, status, date_created) VALUES
(1, '0001', 'John Smith', 0, '2021-11-16 06:01:43');

COMMIT;
SET FOREIGN_KEY_CHECKS=1;

-- =====================================================================
-- Smart University Complaint Portal — Database Schema
-- MySQL 8.x / MariaDB 10.4+ — UTF-8 (utf8mb4)
-- Import via phpMyAdmin or:  mysql -u root -p < database/schema.sql
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `university_complaints`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `university_complaints`;

-- ---------------------------------------------------------------------
-- Drop in reverse dependency order (safe re-import in dev)
-- ---------------------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `audit_log`;
DROP TABLE IF EXISTS `chatbot_logs`;
DROP TABLE IF EXISTS `feedback`;
DROP TABLE IF EXISTS `complaint_messages`;
DROP TABLE IF EXISTS `complaints`;
DROP TABLE IF EXISTS `staff`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `departments`;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 1. departments — complaint categories / handling units
-- ---------------------------------------------------------------------
CREATE TABLE `departments` (
    `department_id`  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`           VARCHAR(100) NOT NULL UNIQUE,
    `description`    VARCHAR(255) DEFAULT NULL,
    `head_of_dept`   VARCHAR(150) DEFAULT NULL,
    `is_active`      TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. students
-- ---------------------------------------------------------------------
CREATE TABLE `students` (
    `student_id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `roll_no`            VARCHAR(30) NOT NULL UNIQUE,
    `name`               VARCHAR(150) NOT NULL,
    `email`              VARCHAR(150) NOT NULL UNIQUE,
    `password_hash`      VARCHAR(255) NOT NULL,
    `phone`              VARCHAR(20) DEFAULT NULL,
    `department_id`      INT UNSIGNED DEFAULT NULL,
    `is_active`          TINYINT(1) NOT NULL DEFAULT 1,
    `email_verified_at`  TIMESTAMP NULL DEFAULT NULL,
    `created_at`         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_students_dept` (`department_id`),
    CONSTRAINT `fk_students_dept`
        FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. staff (department employees who resolve complaints)
-- ---------------------------------------------------------------------
CREATE TABLE `staff` (
    `staff_id`       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`           VARCHAR(150) NOT NULL,
    `email`          VARCHAR(150) NOT NULL UNIQUE,
    `password_hash`  VARCHAR(255) NOT NULL,
    `role`           VARCHAR(50) NOT NULL DEFAULT 'staff',
    `department_id`  INT UNSIGNED NOT NULL,
    `is_active`      TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_staff_dept` (`department_id`),
    CONSTRAINT `fk_staff_dept`
        FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. admins (Super Administrators — separate role from staff)
-- ---------------------------------------------------------------------
CREATE TABLE `admins` (
    `admin_id`       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`           VARCHAR(150) NOT NULL,
    `email`          VARCHAR(150) NOT NULL UNIQUE,
    `password_hash`  VARCHAR(255) NOT NULL,
    `is_active`      TINYINT(1) NOT NULL DEFAULT 1,
    `last_login_at`  TIMESTAMP NULL DEFAULT NULL,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. complaints (the central ticket entity)
-- ---------------------------------------------------------------------
CREATE TABLE `complaints` (
    `complaint_id`        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `reference_no`        VARCHAR(20) NOT NULL UNIQUE,
    `title`               VARCHAR(200) NOT NULL,
    `description`         TEXT NOT NULL,
    `category`            VARCHAR(50) NOT NULL,
    `status`              ENUM('pending','in_progress','on_hold','resolved','closed','reopened')
                              NOT NULL DEFAULT 'pending',
    `priority`            ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
    `attachment_path`     VARCHAR(255) DEFAULT NULL,
    `student_id`          INT UNSIGNED NOT NULL,
    `department_id`       INT UNSIGNED DEFAULT NULL,
    `assigned_staff_id`   INT UNSIGNED DEFAULT NULL,
    `resolved_at`         TIMESTAMP NULL DEFAULT NULL,
    `created_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_complaints_student` (`student_id`),
    INDEX `idx_complaints_dept`    (`department_id`),
    INDEX `idx_complaints_staff`   (`assigned_staff_id`),
    INDEX `idx_complaints_status`  (`status`),
    CONSTRAINT `fk_complaints_student`
        FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_complaints_dept`
        FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT `fk_complaints_staff`
        FOREIGN KEY (`assigned_staff_id`) REFERENCES `staff` (`staff_id`)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. complaint_messages — student ↔ staff communication thread
--    (per TC-D-05 and "View Communication History" requirement)
-- ---------------------------------------------------------------------
CREATE TABLE `complaint_messages` (
    `message_id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `complaint_id`       INT UNSIGNED NOT NULL,
    `sender_type`        ENUM('student','staff','admin') NOT NULL,
    `sender_id`          INT UNSIGNED NOT NULL,
    `message`            TEXT NOT NULL,
    `is_internal_note`   TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_messages_complaint` (`complaint_id`),
    CONSTRAINT `fk_messages_complaint`
        FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`complaint_id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. feedback — 1:1 with complaint, captured after resolution
-- ---------------------------------------------------------------------
CREATE TABLE `feedback` (
    `feedback_id`    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `complaint_id`   INT UNSIGNED NOT NULL UNIQUE,
    `rating`         TINYINT UNSIGNED NOT NULL,
    `comments`       TEXT DEFAULT NULL,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `chk_feedback_rating` CHECK (`rating` BETWEEN 1 AND 5),
    CONSTRAINT `fk_feedback_complaint`
        FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`complaint_id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 8. chatbot_logs — every AI query/response for analytics + training
-- ---------------------------------------------------------------------
CREATE TABLE `chatbot_logs` (
    `log_id`          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `student_id`      INT UNSIGNED DEFAULT NULL,
    `query_text`      TEXT NOT NULL,
    `response_text`   TEXT DEFAULT NULL,
    `intent`          VARCHAR(100) DEFAULT NULL,
    `was_escalated`   TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_chatbot_student` (`student_id`),
    INDEX `idx_chatbot_created` (`created_at`),
    CONSTRAINT `fk_chatbot_student`
        FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 9. audit_log — admin-visible trail of all sensitive actions
-- ---------------------------------------------------------------------
CREATE TABLE `audit_log` (
    `audit_id`       BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `actor_type`     ENUM('student','staff','admin','system') NOT NULL,
    `actor_id`       INT UNSIGNED DEFAULT NULL,
    `action`         VARCHAR(100) NOT NULL,
    `target_table`   VARCHAR(50) DEFAULT NULL,
    `target_id`      INT UNSIGNED DEFAULT NULL,
    `details`        TEXT DEFAULT NULL,
    `ip_address`     VARCHAR(45) DEFAULT NULL,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_actor`   (`actor_type`, `actor_id`),
    INDEX `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- Seed data — minimal departments so login/complaint forms have options
-- =====================================================================
INSERT INTO `departments` (`name`, `description`) VALUES
('Academics',    'Academic-related complaints and inquiries'),
('Hostel',       'Hostel maintenance and accommodation issues'),
('Finance',      'Fees, refunds, and financial matters'),
('Examinations', 'Exam schedules, results, and grievances'),
('IT Support',   'Network, software, and technical issues');

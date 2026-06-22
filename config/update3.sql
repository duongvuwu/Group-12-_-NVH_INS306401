-- update3.sql
-- Add persistent, privacy-conscious storage for LicenseOS Assistant.
-- Safe to rerun: both tables are created only when missing.
USE license_management_db;

CREATE TABLE IF NOT EXISTS assistant_conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_token CHAR(32) NOT NULL,
    language ENUM('vi', 'en') NOT NULL DEFAULT 'vi',
    metadata_json JSON NULL,
    started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_activity_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_assistant_conversation_token (session_token),
    INDEX idx_assistant_conversation_activity (last_activity_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS assistant_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,
    sender ENUM('User', 'Assistant', 'System') NOT NULL,
    intent VARCHAR(64) NULL,
    message_text TEXT NOT NULL,
    response_json JSON NULL,
    status ENUM('Success', 'NoMatch', 'Error') NOT NULL DEFAULT 'Success',
    duration_ms INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_assistant_message_conversation (conversation_id, created_at),
    INDEX idx_assistant_message_intent (intent, created_at),
    CONSTRAINT fk_assistant_message_conversation
      FOREIGN KEY (conversation_id) REFERENCES assistant_conversations(id)
      ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

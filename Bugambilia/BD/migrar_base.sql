-- Apply ONLY to an existing database imported from the original ZIP; back it up first.
-- Duplicate/null emails must be resolved before this migration. No data is deleted here.
USE bugambilia;
ALTER TABLE users MODIFY id_user INT NOT NULL AUTO_INCREMENT,
 MODIFY name VARCHAR(80) NOT NULL, MODIFY email VARCHAR(254) NOT NULL,
 MODIFY psswd VARCHAR(255) NOT NULL, MODIFY rol BOOLEAN NOT NULL DEFAULT 0,
 ADD UNIQUE KEY uq_users_email(email);
CREATE TABLE auth_attempts (key_hash CHAR(64) PRIMARY KEY, attempts INT NOT NULL DEFAULT 0, expires_at BIGINT NOT NULL, INDEX(expires_at)) ENGINE=InnoDB;

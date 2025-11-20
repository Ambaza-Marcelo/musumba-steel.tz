-- Migration script to add role column to users table
-- Run this if the users table already exists without the role column

USE musumbasteeltz;

-- Check if role column exists, if not add it
SET @dbname = DATABASE();
SET @tablename = 'users';
SET @columnname = 'role';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' ENUM(\'user\', \'admin\') DEFAULT \'user\' AFTER password_hash')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Update existing users to be admin (you can change this as needed)
UPDATE users SET role = 'admin' WHERE role IS NULL OR role = '';

-- Set the default user as admin
UPDATE users SET role = 'admin' WHERE username = 'marcellin@gmail.com';


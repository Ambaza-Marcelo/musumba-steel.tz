-- Simple migration script to add role column to users table
-- This version works with all MySQL versions

USE musumbasteeltz;

-- Add role column (will fail if column already exists, that's okay)
ALTER TABLE users 
ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user' AFTER password_hash;

-- Update existing users to be admin
UPDATE users SET role = 'admin' WHERE username = 'marcellin@gmail.com';


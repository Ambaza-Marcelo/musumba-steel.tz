-- Migration to simplify the videos table to YouTube-only entries
-- Run after the initial gallery migration

USE musumbasteeltz;

-- Drop legacy columns if they exist
ALTER TABLE videos
    DROP COLUMN IF EXISTS title_en,
    DROP COLUMN IF EXISTS title_sw,
    DROP COLUMN IF EXISTS description_en,
    DROP COLUMN IF EXISTS description_sw,
    DROP COLUMN IF EXISTS video_path;

-- Ensure youtube_id is required and shorten column list
ALTER TABLE videos
    MODIFY youtube_id VARCHAR(100) NOT NULL,
    ADD COLUMN IF NOT EXISTS display_order INT DEFAULT 0 AFTER youtube_id,
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Clean up any NULL youtube IDs accidentally stored
DELETE FROM videos WHERE youtube_id IS NULL OR youtube_id = '';


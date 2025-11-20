-- Migration script to create gallery tables (pictures and videos)
-- Run this to add gallery functionality

USE musumbasteeltz;

-- Create pictures table
CREATE TABLE IF NOT EXISTS pictures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title_en VARCHAR(255) NOT NULL,
    title_sw VARCHAR(255) NOT NULL,
    description_en TEXT,
    description_sw TEXT,
    image_path VARCHAR(500) NOT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_display_order (display_order),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Create videos table
CREATE TABLE IF NOT EXISTS videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    youtube_id VARCHAR(100) NOT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_display_order (display_order),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;


-- Migration script to create publications table and support multiple images
-- Fixed version - compatible with all MySQL versions

USE musumbasteeltz;

-- Drop tables if they exist (for clean migration)
-- Uncomment the following lines if you want to recreate the tables
-- DROP TABLE IF EXISTS publication_images;
-- DROP TABLE IF EXISTS publications;

-- Create publications table
CREATE TABLE IF NOT EXISTS publications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('News','training','national-holidays','international-holidays','calls-for-tenders','communicates') NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    title_sw VARCHAR(255) NOT NULL,
    body_en TEXT,
    body_sw TEXT,
    attachment VARCHAR(255),
    published_on DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Create publication_images table for multiple images
-- Note: This table must be created AFTER publications table
CREATE TABLE IF NOT EXISTS publication_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    publication_id INT NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    image_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE,
    INDEX idx_publication_id (publication_id),
    INDEX idx_image_order (image_order)
) ENGINE=InnoDB;


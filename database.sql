CREATE DATABASE IF NOT EXISTS blog_ai;

USE blog_ai;

-- ----------------------------
-- POSTS TABLE (MAIN CONTENT)
-- ----------------------------
CREATE TABLE
    IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content LONGTEXT NOT NULL,
        seo_title VARCHAR(255) NULL,
        seo_description TEXT NULL,
        tags VARCHAR(255) NULL,
        views INT DEFAULT 0,
        status VARCHAR(20) DEFAULT 'draft',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
-- ----------------------------
-- TOPICS TABLE (ANTI-DUPLICATE)
-- ----------------------------
CREATE TABLE
    IF NOT EXISTS topics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        topic TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

-- ----------------------------
-- OPTIONAL INDEXES (SPEED BOOST)
-- ----------------------------
CREATE INDEX idx_posts_status ON posts (status);

CREATE INDEX idx_posts_created ON posts (created_at);

CREATE INDEX idx_posts_views ON posts (views);

CREATE INDEX idx_posts_slug ON posts (slug);

-- ----------------------------
-- OPTIONAL FULL-TEXT SEARCH (ADVANCED)
-- ----------------------------
ALTER TABLE posts ADD FULLTEXT (title, seo_title, tags);
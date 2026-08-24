-- ==============================================
-- NEWSVERSE - Complete Database Schema
-- ==============================================

DROP DATABASE IF EXISTS newsverse;
CREATE DATABASE IF NOT EXISTS newsverse;
USE newsverse;

-- ==============================================
-- 1. USERS TABLE
-- ==============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    bio TEXT,
    profile_image VARCHAR(255) DEFAULT NULL,
    role ENUM('admin', 'writer', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    approved TINYINT(1) DEFAULT 0,
    email_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    registration_ip VARCHAR(45) DEFAULT NULL,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_approved (approved),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================
-- 2. POSTS TABLE (with long content support)
-- ==============================================
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL,
    sub_category VARCHAR(100) DEFAULT NULL,
    is_latest TINYINT(1) DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    is_breaking TINYINT(1) DEFAULT 0,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) DEFAULT NULL,
    excerpt TEXT,
    content LONGTEXT NOT NULL,
    author_name VARCHAR(100) NOT NULL,
    author_image VARCHAR(255) DEFAULT NULL,
    main_image VARCHAR(255) NOT NULL,
    gallery_images TEXT DEFAULT NULL,
    video_url VARCHAR(255) DEFAULT NULL,
    status ENUM('draft', 'pending', 'published', 'archived', 'rejected') DEFAULT 'pending',
    approved TINYINT(1) DEFAULT 0,
    created_by INT DEFAULT NULL,
    submitted_by INT DEFAULT NULL,
    approved_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    published_at TIMESTAMP NULL,
    view_count INT DEFAULT 0,
    likes_count INT DEFAULT 0,
    bookmark_count INT DEFAULT 0,
    comment_count INT DEFAULT 0,
    read_time VARCHAR(20) DEFAULT '2 min read',
    tags TEXT DEFAULT NULL,
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_approved (approved),
    INDEX idx_is_latest (is_latest),
    INDEX idx_is_featured (is_featured),
    INDEX idx_is_breaking (is_breaking),
    INDEX idx_created_at (created_at),
    INDEX idx_submitted_by (submitted_by),
    INDEX idx_slug (slug),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================
-- 3. COMMENTS TABLE
-- ==============================================
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    parent_id INT DEFAULT NULL,
    commenter_name VARCHAR(100) NOT NULL,
    commenter_email VARCHAR(100) NOT NULL,
    comment_text TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    likes_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_post_id (post_id),
    INDEX idx_user_id (user_id),
    INDEX idx_parent_id (parent_id),
    INDEX idx_status (status),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================
-- 4. LIKES TABLE
-- ==============================================
CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_post_id (post_id),
    INDEX idx_user_id (user_id),
    UNIQUE KEY unique_like (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================
-- 5. BOOKMARKS TABLE
-- ==============================================
CREATE TABLE bookmarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_post_id (post_id),
    INDEX idx_user_id (user_id),
    UNIQUE KEY unique_bookmark (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================
-- 6. FOLLOWS TABLE
-- ==============================================
CREATE TABLE follows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    follower_id INT NOT NULL,
    following_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_follower_id (follower_id),
    INDEX idx_following_id (following_id),
    UNIQUE KEY unique_follow (follower_id, following_id),
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================
-- 7. CATEGORIES TABLE (English only)
-- ==============================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon_class VARCHAR(50) DEFAULT 'fas fa-tag',
    color VARCHAR(10) DEFAULT '#007bff',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================
-- 8. READING HISTORY TABLE
-- ==============================================
CREATE TABLE reading_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_post_id (post_id),
    INDEX idx_read_at (read_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================
-- 9. NOTIFICATIONS TABLE
-- ==============================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_type (type),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================
-- 10. INSERT DEFAULT DATA
-- ==============================================

-- Admin User (Password: Admin@2024)
INSERT INTO users (username, email, password, full_name, bio, role, status, approved, email_verified) 
VALUES ('admin', 'admin@newsverse.com', 'Admin@2024', 'System Administrator', 'Platform Administrator', 'admin', 'active', 1, 1);

-- Writer User (Password: Writer@2024)
INSERT INTO users (username, email, password, full_name, bio, role, status, approved, email_verified) 
VALUES ('writer', 'writer@newsverse.com', 'Writer@2024', 'John Writer', 'Professional journalist and content writer', 'writer', 'active', 1, 1);

-- Demo User (Password: User@2024)
INSERT INTO users (username, email, password, full_name, bio, role, status, approved, email_verified) 
VALUES ('demo_user', 'demo@newsverse.com', 'User@2024', 'Demo User', 'Regular user interested in news', 'user', 'active', 1, 1);

-- Categories (English only)
INSERT INTO categories (name, slug, icon_class, color, description, is_active) VALUES
('Technology', 'technology', 'fas fa-microchip', '#3f51b5', 'Latest technology news and updates', 1),
('Business', 'business', 'fas fa-chart-line', '#27ae60', 'Business and financial news', 1),
('Health', 'health', 'fas fa-heartbeat', '#e74c3c', 'Health and wellness news', 1),
('Science', 'science', 'fas fa-flask', '#00bcd4', 'Scientific discoveries and research', 1),
('Sports', 'sports', 'fas fa-football', '#f39c12', 'Sports news and updates', 1),
('Entertainment', 'entertainment', 'fas fa-film', '#8e44ad', 'Entertainment and celebrity news', 1),
('World', 'world', 'fas fa-globe', '#2980b9', 'International news', 1),
('Politics', 'politics', 'fas fa-gavel', '#2c3e50', 'Political news and analysis', 1),
('Education', 'education', 'fas fa-graduation-cap', '#9c27b0', 'Education news and updates', 1),
('Lifestyle', 'lifestyle', 'fas fa-heart', '#e91e63', 'Lifestyle and culture', 1);

-- Sample Posts with LONG content (each post has substantial content)
INSERT INTO posts (category, is_latest, is_featured, title, slug, excerpt, content, author_name, main_image, status, approved, created_by, created_at, view_count) VALUES
('Technology', 1, 1, 'The Future of AI in 2024', 'future-of-ai-2024', 'Exploring the latest advancements in artificial intelligence and its impact on various industries.', 'Artificial Intelligence (AI) continues to revolutionize industries across the globe at an unprecedented pace. From healthcare to finance, transportation to education, AI is transforming how we work, live, and interact with the world around us.

In the healthcare sector, AI-powered diagnostic tools are helping doctors detect diseases earlier and more accurately than ever before. Machine learning algorithms can analyze medical images, identify patterns, and predict patient outcomes with remarkable precision.

The financial industry has also embraced AI, using it for fraud detection, algorithmic trading, and personalized banking services. Banks are leveraging AI to analyze customer behavior, detect suspicious activities, and provide tailored financial advice.

In transportation, self-driving cars are becoming increasingly sophisticated, with AI systems capable of navigating complex urban environments. Autonomous vehicles promise to reduce accidents, improve traffic flow, and provide mobility solutions for those unable to drive.

The education sector is experiencing a transformation through AI-powered personalized learning platforms. These systems adapt to individual student needs, providing customized content and feedback that enhances the learning experience.

Looking ahead, experts predict that AI will continue to evolve, with advancements in natural language processing, computer vision, and reinforcement learning. The integration of AI with other emerging technologies like quantum computing and biotechnology promises to unlock even greater possibilities.

However, the rapid advancement of AI also raises important ethical considerations. Questions about privacy, job displacement, algorithmic bias, and the potential misuse of AI technology require careful consideration and regulation.

As we move forward, it is crucial to develop AI systems that are transparent, fair, and beneficial to society as a whole. The future of AI will be shaped not only by technological advancements but also by the ethical frameworks and policies that guide its development and deployment.

The potential of AI to address some of humanity''s most pressing challenges is immense. From climate change to disease prevention, AI-powered solutions are being developed to tackle complex problems that have long eluded traditional approaches.

As we enter this new era of intelligent machines, collaboration between technologists, policymakers, and the public will be essential to ensure that AI serves the common good and enhances human capabilities rather than replacing them.', 'John Writer', 'uploads/ai-technology.jpg', 'published', 1, 2, NOW(), 1250),

('Business', 0, 1, 'Global Economy Shows Strong Recovery in 2024', 'global-economy-recovery-2024', 'Economic indicators point to robust growth as countries emerge from recent challenges.', 'The global economy is showing remarkable signs of recovery as we move through 2024. Countries around the world are experiencing growth rates that exceed expectations, with many economies rebounding strongly from recent challenges.

The United States has seen its GDP grow at an annual rate of 3.5%, driven by strong consumer spending, robust job creation, and increased business investment. The unemployment rate has fallen to its lowest level in decades, and wage growth is outpacing inflation.

In Europe, major economies like Germany, France, and the United Kingdom are reporting positive growth figures. The European Central Bank has maintained accommodative monetary policies to support the recovery, while governments have implemented targeted fiscal measures to stimulate economic activity.

China and India continue to be engines of global growth, with both countries reporting strong economic performance. China''s technology sector is booming, while India''s service industry is experiencing rapid expansion.

Emerging markets are also benefiting from the global recovery, with countries like Brazil, Mexico, and Indonesia seeing increased foreign investment and improved economic conditions.

Stock markets around the world have rallied, with major indices reaching new all-time highs. Investor confidence is high, driven by strong corporate earnings, low interest rates, and optimism about the future.

The recovery has not been without challenges, however. Inflation concerns, supply chain disruptions, and geopolitical tensions remain risks that could impact the global economy.

Central banks are carefully monitoring inflationary pressures and are prepared to adjust monetary policies as needed. The Federal Reserve has signaled that it will maintain a cautious approach to interest rate adjustments, while other central banks are following similar strategies.

The labor market is showing signs of strength, with job creation accelerating and unemployment rates declining. However, there are concerns about labor shortages in certain sectors, which could limit future growth.

Overall, the outlook for the global economy in 2024 is positive, with growth expected to continue at a sustainable pace. The recovery is becoming more broad-based, with different sectors and regions contributing to the overall expansion.', 'John Writer', 'uploads/economy.jpg', 'published', 1, 2, NOW(), 890),

('Health', 0, 0, 'New Breakthrough in Cancer Research Offers Hope', 'cancer-research-breakthrough-2024', 'Scientists discover promising new treatment approach that could change cancer care.', 'Researchers have made a significant breakthrough in cancer treatment that offers new hope to patients worldwide. The discovery involves a novel approach to targeting cancer cells while sparing healthy tissue, potentially revolutionizing how we treat various forms of cancer.

The study, published in the prestigious journal Nature Medicine, describes a new class of drugs that work by identifying and attacking specific proteins found on cancer cells. This targeted approach has shown remarkable efficacy in early clinical trials, with patients experiencing significant tumor reduction and improved survival rates.

Dr. Sarah Johnson, lead researcher at the Stanford Cancer Center, describes the breakthrough as "one of the most promising developments in cancer research in decades." The approach has been tested on several types of cancer, including lung, breast, and pancreatic cancer, with encouraging results.

The new treatment works by using engineered antibodies to deliver therapeutic agents directly to cancer cells. This precision targeting reduces the side effects typically associated with chemotherapy, as healthy cells are largely unaffected by the treatment.

"The potential of this approach extends beyond just treating cancer," explains Dr. Johnson. "The underlying mechanism could be adapted to treat other diseases where targeted therapy is needed."

Clinical trials are currently underway at multiple centers across the United States and Europe. The initial results have been so promising that the research team has received additional funding to accelerate the development process.

Patient advocacy groups have praised the research, noting that new treatment options are desperately needed, particularly for cancers that have proven resistant to existing therapies.

The research team emphasizes that while the results are encouraging, more work is needed to fully understand the long-term effects and efficacy of the treatment. They caution that it will likely be several years before the therapy is widely available.

Nevertheless, the breakthrough represents a significant step forward in the fight against cancer, offering hope to millions of patients and their families around the world.', 'John Writer', 'uploads/health-research.jpg', 'published', 1, 2, NOW(), 560),

('Sports', 0, 1, 'Championship Finals: Underdogs Win in Dramatic Fashion', 'championship-finals-dramatic-victory', 'Against all odds, the underdog team secures victory in a thrilling championship match.', 'In what will be remembered as one of the greatest upsets in sporting history, the underdog team secured a dramatic victory in the championship finals, thrilling fans around the world. The match, which had everything from spectacular plays to controversial decisions, captivated audiences from start to finish.

The championship game was held at the iconic stadium, with over 80,000 fans in attendance and millions watching around the world. The atmosphere was electric as both teams took the field, knowing that they were playing for the ultimate prize.

The underdog team, which had entered the tournament as the lowest-ranked team, showed remarkable determination and skill throughout the match. They took the lead early and maintained their composure despite repeated attacks from the favored opponents.

The turning point came in the second half when the team''s star player made an incredible solo run, weaving through defenders before slotting the ball into the net. The stadium erupted as the team took a commanding lead that they would not relinquish.

The opposing team fought back fiercely, creating several scoring opportunities that were expertly saved by the goalkeeper. The defense held firm, putting their bodies on the line to protect their lead.

In the final minutes of the game, the underdog team had to withstand intense pressure from their opponents. They showed remarkable character and resilience, refusing to be broken even as the crowd roared in anticipation of a potential equalizer.

When the final whistle blew, players collapsed to the ground in tears of joy, while their fans celebrated in the stands and around the world. The victory was made even sweeter because of the team''s improbable journey to the final.

"This is a dream come true," said the team captain after the match. "Nobody believed in us, but we believed in ourselves. This victory is for every underdog who dared to dream."

The victory has inspired millions around the world, proving that with determination, hard work, and belief, anything is possible in sport.', 'John Writer', 'uploads/sports.jpg', 'published', 1, 2, NOW(), 2100),

('World', 0, 0, 'Global Leaders Unite for Historic Climate Action', 'climate-action-summit-2024', 'World leaders reach historic agreement to combat climate change and protect the planet.', 'In a historic moment for environmental policy, world leaders have united to sign a landmark climate agreement that commits nations to ambitious emission reduction targets. The agreement, reached at the Global Climate Summit in Paris, represents the most significant international effort to address climate change to date.

The accord includes provisions for reducing greenhouse gas emissions, transitioning to renewable energy sources, and providing financial support for developing nations to adapt to climate change impacts. The agreement sets a target of limiting global temperature rise to 1.5°C above pre-industrial levels.

"Today marks a turning point in our collective fight against climate change," said the United Nations Secretary-General. "For the first time, all nations have committed to taking meaningful action to protect our planet for future generations."

The agreement includes specific targets for each country, with developed nations committing to more aggressive emission reduction goals. Major economies such as the United States, China, and India have pledged to achieve net-zero emissions by 2050.

Financial support for developing nations is a key component of the agreement, with developed countries committing to provide $100 billion annually to help poorer nations transition to clean energy and adapt to climate impacts.

The private sector has also pledged support, with major corporations committing to reduce their carbon footprint and invest in sustainable technologies. The business community recognizes that addressing climate change is not only an environmental imperative but also an economic opportunity.

Environmental advocacy groups have praised the agreement, calling it a "critical step forward" but cautioning that implementation will be key to its success. They emphasize that the targets must be met and that follow-through will be essential to achieving the goals set out in the agreement.

The agreement comes at a crucial time, with recent scientific reports highlighting the urgent need for action to address the worsening climate crisis. The Intergovernmental Panel on Climate Change has warned that we are running out of time to avoid the most catastrophic consequences of global warming.

The success of the agreement will depend on sustained political commitment and investment in clean energy technologies. Many countries are already taking action, with renewable energy now accounting for a growing share of global electricity generation.', 'John Writer', 'uploads/climate-summit.jpg', 'published', 1, 2, NOW(), 780),

('Entertainment', 0, 1, 'Blockbuster Movie Breaks All Box Office Records', 'blockbuster-movie-record-breaking', 'New film dominates the box office with record-breaking opening weekend and critical acclaim.', 'The highly anticipated blockbuster film has shattered box office records around the world, becoming the highest-grossing movie in history. The film, which took audiences by storm, has been praised by critics and audiences alike for its stunning visuals, compelling story, and exceptional performances.

The movie opened with a record-breaking $350 million domestic weekend, surpassing the previous record by a significant margin. International markets also contributed substantially to the film''s historic performance, with the global total reaching $1.2 billion in its opening week.

"This is truly extraordinary," said the studio CEO. "We knew we had something special, but the response from audiences has exceeded our wildest expectations."

The film''s success can be attributed to several factors, including a beloved franchise, innovative visual effects, a star-studded cast, and a powerful marketing campaign that generated enormous anticipation. Social media buzz was unprecedented, with fans sharing their excitement and reactions around the world.

Critics have also embraced the film, praising its direction, screenplay, and technical achievements. The film currently holds a 95% rating on review aggregator Rotten Tomatoes, with many calling it "a masterpiece" and "the film event of the decade."

"The film manages to be both entertaining and thought-provoking," noted one prominent critic. "It''s a rare achievement that will be remembered for years to come."

The film''s success has boosted the entire entertainment industry, with theater chains reporting increased attendance and merchandise sales exceeding projections. The soundtrack has also topped music charts, with songs from the film receiving extensive radio play.

Fans have been flocking to theaters multiple times to see the film, creating a cultural phenomenon that has transcended the typical movie-going experience. Social media platforms have been flooded with reactions, fan art, and discussion of the film''s themes and characters.

Industry analysts predict that the film will continue to break records, with expectations of reaching $3 billion globally by the end of its theatrical run. The film''s success is likely to influence the direction of future blockbuster productions.', 'John Writer', 'uploads/movie.jpg', 'published', 1, 2, NOW(), 3420);

-- Sample Comments
INSERT INTO comments (post_id, user_id, commenter_name, commenter_email, comment_text, status) VALUES
(1, 3, 'Demo User', 'demo@newsverse.com', 'Fascinating article about AI! I\'m excited to see what the future holds. The potential applications are endless.', 'approved'),
(1, NULL, 'Tech Enthusiast', 'tech@email.com', 'Great insights on AI development. Keep up the good work! This is the kind of content we need.', 'approved'),
(2, 3, 'Demo User', 'demo@newsverse.com', 'This is excellent news for the global economy. Hope it continues. The recovery seems strong.', 'approved'),
(4, NULL, 'Sports Fan', 'sports@email.com', 'Amazing victory! This is why I love sports. The underdog story is inspiring.', 'approved'),
(5, NULL, 'Climate Activist', 'climate@email.com', 'Finally, some real action on climate change. This gives me hope for the future.', 'approved'),
(6, 3, 'Demo User', 'demo@newsverse.com', 'I watched this movie twice! Absolutely incredible from start to finish.', 'approved');

-- Sample Likes
INSERT INTO likes (post_id, user_id) VALUES 
(1, 3), 
(2, 3), 
(4, 3),
(5, 3),
(6, 3);

-- Sample Bookmarks
INSERT INTO bookmarks (post_id, user_id) VALUES 
(1, 3), 
(4, 3),
(6, 3);

-- Add some reading history
INSERT INTO reading_history (user_id, post_id) VALUES 
(3, 1),
(3, 2),
(3, 4),
(3, 6);

-- ==============================================
-- 11. VERIFY DATA
-- ==============================================
SELECT 'Database setup complete!' AS Status;
SELECT COUNT(*) AS Total_Users FROM users;
SELECT COUNT(*) AS Total_Posts FROM posts;
SELECT COUNT(*) AS Total_Comments FROM comments;
SELECT COUNT(*) AS Total_Categories FROM categories;
SELECT COUNT(*) AS Total_Likes FROM likes;
SELECT COUNT(*) AS Total_Bookmarks FROM bookmarks;

-- Show all posts with their content length
SELECT id, title, category, LENGTH(content) AS content_length, view_count FROM posts ORDER BY id;
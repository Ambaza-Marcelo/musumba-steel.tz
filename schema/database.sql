CREATE DATABASE IF NOT EXISTS musumbasteeltz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE musumbasteeltz;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO users (username, password_hash, role)
VALUES ('marcellin@gmail.com', '$2b$12$/93dtS9MNqoodUnU.Pnzy.9dG25fZ7RgowAkQ9AlCwdwnRbC1emZ2', 'admin')
ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = COALESCE(VALUES(role), role);

CREATE TABLE IF NOT EXISTS pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL UNIQUE,
    section VARCHAR(120) NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    title_sw VARCHAR(255) NOT NULL,
    summary_en TEXT,
    summary_sw TEXT,
    content_en LONGTEXT,
    content_sw LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO pages (slug, section, title_en, title_sw, summary_en, summary_sw, content_en, content_sw)
VALUES
('welcome', 'home', 'Welcome to Musumba Steel', 'Karibu Musumba Steel', 'Precision steel for East Africa — Kahama manufacturing plant.', 'Chuma sahihi kwa Afrika Mashariki — kiwanda cha Kahama.', '<p>Musumba Steel (Tanzania) Ltd is a private company in Kahama, Shinyanga, registered in 2022 under TIC. Led by Francois Uwiragiye and family, the plant targets an initial annual capacity of <strong>33,000 metric tons</strong>.</p>', '<p>Musumba Steel (Tanzania) Ltd ni kampuni binafsi Kahama, Shinyanga, iliyosajiliwa 2022 chini ya TIC. Chini ya Francois Uwiragiye na familia, kiwanda kinalenga <strong>tani 33,000</strong> kwa mwaka.</p>'),
('parent-company', 'about', 'Parent Company — Musumba Holding', 'Kampuni Mama — Musumba Holding', 'Part of Musumba Holding, Chairman & CEO Francois Uwiragiye (Bujumbura).', 'Sehemu ya Musumba Holding, Mwenyekiti Francois Uwiragiye (Bujumbura).', '<p>Group companies: Musumba Steel (Burundi &amp; Tanzania), Musumba Cargo, NLCOM, Musumba Petroleum, Fabrimetal, Eden Garden Resort, Uwiragiye Foundation, SOTB Mining, Bigger Steel Limited (Kenya), Bigger Global Services.</p>', '<p>Kampuni za kikundi: Musumba Steel, Musumba Cargo, NLCOM, Musumba Petroleum, Fabrimetal, Eden Garden Resort, Uwiragiye Foundation, SOTB, Bigger Steel, Bigger Global Services.</p>'),
('about', 'about', 'About Musumba Steel', 'Kuhusu Musumba Steel', 'Steel manufacturer based in Kahama, Shinyanga, Tanzania.', 'Mtengenezaji wa chuma Kahama, Shinyanga, Tanzania.', '<p>Factory: Plot 71, Chapulwa Industrial Area, Mwendakulima. Corporate: 2nd Floor, Mongo Complex, Kahama. TIN 157-898-175 · VAT 40-315814-F.</p>', '<p>Kiwanda: Kiwanja 71, Chapulwa, Mwendakulima. Ofisi: Mongo Complex, Kahama. TIN 157-898-175 · VAT 40-315814-F.</p>'),
('historic', 'about', 'Our History', 'Historia Yetu', 'Registered in 2022 under the Tanzania Investment Center (TIC).', 'Ilisajiliwa mwaka 2022 chini ya TIC.', '<p>Registered in 2022 with significant authorized share capital under TIC. Owned by Burundian nationals led by Francois Uwiragiye and family.</p>', '<p>Ilisajiliwa 2022 chini ya TIC. Inamilikiwa na raia wa Burundi chini ya Francois Uwiragiye na familia.</p>'),
('membership', 'about', 'Memberships', 'Uanachama', 'Member of TPSF, CTI and Tanzania Steel Manufacturers Association.', 'Mwanachama wa TPSF, CTI na Tanzania Steel Manufacturers Association.', '<p>We actively contribute to policy dialogues on industrialisation through multiple associations and chambers.</p>', '<p>Tunachangia mijadala ya sera kupitia vyama na mabaraza mbalimbali ya sekta.</p>'),
('partners', 'about', 'Strategic Partners', 'Wadau wa Mkakati', 'Allied with regional EPC contractors and raw material suppliers.', 'Tumeungana na wakandarasi na wasambazaji wa malighafi wa kikanda.', '<p>Partnerships with logistics firms, EPC contractors and technology licensors allow us to deliver turnkey solutions.</p>', '<p>Ushirikiano na kampuni za usafirishaji, wakandarasi na watoa teknolojia hutusaidia kutoa suluhisho kamili.</p>'),
('management', 'about', 'Management', 'Uongozi', 'Chairman & CEO: Mr. Francois Uwiragiye — Musumba Holding.', 'Mwenyekiti: Bw. Francois Uwiragiye — Musumba Holding.', '<p>Mr. Francois Uwiragiye, Chairman and CEO of Musumba Holding (Bujumbura, Burundi), leads Musumba Steel Tanzania.</p>', '<p>Bw. Francois Uwiragiye, Mwenyekiti na Mkurugenzi Mkuu wa Musumba Holding, anaongoza Musumba Steel Tanzania.</p>'),
('distributors', 'about', 'Distributors', 'Wauzaji', 'Distribution across Tanzania and East Africa.', 'Usambazaji Tanzania na Afrika Mashariki.', '<p>We empower distributors with technical assistance and timely deliveries from Kahama.</p>', '<p>Tunawawezesha wasambazaji kwa msaada wa kiufundi na usafirishaji wa wakati kutoka Kahama.</p>'),
('vision', 'about', 'Vision', 'Dira', 'Advance Africa\'s steel independence.', 'Kuendeleza uhuru wa chuma Afrika.', '<p>Our vision is to become the most trusted East African source for engineered steel solutions.</p>', '<p>Dira ni kuwa chanzo kinachoaminika cha suluhisho za chuma Afrika Mashariki.</p>'),
('mission', 'about', 'Mission', 'Misheni', 'Deliver quality, affordable steel with Tanzanian talent.', 'Kutoa chuma bora na nafuu kwa vipaji vya Tanzania.', '<p>We invest in people, technology and sustainability to deliver value to every stakeholder.</p>', '<p>Tunawekeza katika watu, teknolojia na uendelevu ili kuleta thamani kwa kila mdau.</p>'),
('roofings', 'services', 'Roofing Portfolio', 'Bidhaa za Paa', 'Galvanized, AZ and colour-coated roofing lines.', 'Mistari ya bati za galvanized, AZ na rangi.', '<p>Corrugated, box profile, Versa Tile, Rangi Max, ridges and flashing — nationwide distribution.</p>', '<p>Corrugated, box profile, Versa Tile, Rangi Max, ridge na flashing — usambazaji nchi nzima.</p>'),
('services', 'services', 'Our Products', 'Bidhaa Zetu', 'Roofing systems and construction steel from Kahama.', 'Paa na chuma cha ujenzi kutoka Kahama.', '<p>Browse Roofing Portfolio and Construction Materials manufactured for East African builders.</p>', '<p>Angalia Bidhaa za Paa na Vifaa vya Ujenzi kwa wajenzi wa Afrika Mashariki.</p>'),
('construction-materials', 'services', 'Construction Materials', 'Vifaa vya Ujenzi', 'Hollow sections, pipes, MS plates and nails.', 'Hollow sections, mabomba, sahani za MS na misumari.', '<p>Hollow sections, round/square pipes, MS plates and common mild steel nails from the Kahama plant.</p>', '<p>Hollow sections, mabomba, sahani za MS na misumari kutoka kiwanda cha Kahama.</p>'),
('our-projects', 'projects', 'Flagship Projects', 'Miradi Mikubwa', 'A snapshot of regional deliveries.', 'Muhtasari wa miradi ya kikanda.', '<p>Musumba Steel supports national infrastructure, industrial parks and private developments.</p>', '<p>Musumba Steel inaunga mkono miundombinu ya kitaifa, mbuga za viwanda na miradi binafsi.</p>'),
('publications', 'publications', 'News & Publications', 'Habari na Machapisho', 'Stay informed with events, trainings and tenders.', 'Pata taarifa kuhusu matukio, mafunzo na zabuni.', '<p>Browse the latest programs and regulatory notices shared by Musumba Steel.</p>', '<p>Tazama programu na matangazo mapya kutoka Musumba Steel.</p>'),
('contact-us', 'contact', 'Contact Musumba Steel', 'Wasiliana na Musumba Steel', 'Factory and corporate offices in Kahama.', 'Ofisi za kiwanda na kampuni Kahama.', '<p>info@musumba-steel.com · sales@musumba-steel.com · +255 761 037 271 · +255 781 502 260</p>', '<p>info@musumba-steel.com · sales@musumba-steel.com · +255 761 037 271 · +255 781 502 260</p>'),
('news', 'publications', 'News', 'Habari', 'Latest news and updates.', 'Habari na matukio ya hivi karibuni.', '', ''),
('training', 'publications', 'Training', 'Mafunzo', 'Capacity building programs.', 'Programu za kujenga uwezo.', '', ''),
('national-holidays', 'publications', 'National Holidays', 'Sikukuu za Kitaifa', 'Public holiday notices.', 'Taarifa za sikukuu.', '', ''),
('international-holidays', 'publications', 'International Holidays', 'Sikukuu za Kimataifa', 'International observances.', 'Maadhimisho ya kimataifa.', '', ''),
('calls-for-tenders', 'publications', 'Calls for Tenders', 'Mialiko ya Zabuni', 'Procurement opportunities.', 'Fursa za manunuzi.', '', ''),
('communicates', 'publications', 'Communicates', 'Taarifa', 'Press releases and statements.', 'Taarifa kwa vyombo vya habari.', '', ''),
('general-management', 'contact', 'General Management', 'Usimamizi Mkuu', 'Reach the executive office.', 'Wasiliana na ofisi ya utendaji.', '', ''),
('sales-management', 'contact', 'Sales Management', 'Idara ya Mauzo', 'Talk to national account managers.', 'Zungumza na mameneja wa mauzo.', '', '')
ON DUPLICATE KEY UPDATE title_en = VALUES(title_en);

CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category ENUM('roofings','construction-materials') NOT NULL,
    name_en VARCHAR(255) NOT NULL,
    name_sw VARCHAR(255) NOT NULL,
    description_en TEXT,
    description_sw TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO services (category, name_en, name_sw, description_en, description_sw)
VALUES
('roofings', 'Alu-Zinc Corrugated Sheet (Plain)', 'Bati za Alu-Zinc (Bila Rangi)', 'High-tensile AZ corrugated sheets. Gauges G32–G28; 2.5 m & 3.0 m.', 'Bati za AZ. Vipimo G32–G28; 2.5 m na 3.0 m.'),
('roofings', 'Pre-Painted Corrugated Sheet', 'Bati Zilizopakwa Rangi', 'Colour-coated corrugated sheets with UV shield.', 'Bati zilizopakwa rangi zenye kinga ya UV.'),
('roofings', 'IT4 Box Profile Sheet (Colour)', 'IT4 Box Profile (Rangi)', 'Colour-coated IT4 box profile for modern roofing.', 'Profaili za IT4 box zilizopakwa rangi.'),
('roofings', 'IT4 Box Profile Sheet (Non-Colour)', 'IT4 Box Profile (Bila Rangi)', 'Unpainted IT4 box profile sheets.', 'Profaili za IT4 box bila rangi.'),
('roofings', 'Musumba Rangi Max / Rangi Max+', 'Musumba Rangi Max / Rangi Max+', 'Rangi Max 840 mm and Rangi Max+ 780 mm profiled systems.', 'Rangi Max 840 mm na Rangi Max+ 780 mm.'),
('roofings', 'Versa Tile', 'Versa Tile', 'Tile-effect roofing profile.', 'Profaili ya paa yenye muonekano wa tiles.'),
('roofings', 'Crimp / Curve Sheet & Ridges', 'Crimp / Curve na Ridge', 'Crimp/curve sheets and ridge accessories.', 'Crimp/curve na ridge.'),
('roofings', 'Flashing', 'Flashing', 'Gauge 30 and 28 flashing, 2.5 m & 3.0 m.', 'Flashing gauge 30 na 28.'),
('construction-materials', 'Hollow Sections', 'Hollow Sections', 'Square, rectangular and round hollow sections from Kahama.', 'Hollow sections kutoka Kahama.'),
('construction-materials', 'Square Pipes', 'Mabomba ya Mraba', 'Precision-rolled square sections for industrial builds.', 'Mabomba ya mraba kwa ujenzi wa viwanda.'),
('construction-materials', 'Round Pipes', 'Mabomba ya Pande Zote', 'Round pipes for furniture and construction.', 'Mabomba kwa samani na ujenzi.'),
('construction-materials', 'MS Plates', 'Sahani za MS', 'MS plates 0.80–3.00 mm, 2400×1200 mm. JIS G 3131.', 'Sahani za MS 0.80–3.00 mm.'),
('construction-materials', 'Common Mild Steel Nails', 'Misumari ya Chuma', 'Nails from 1" to 6", 50 kg bags.', 'Misumari inchi 1–6, mifuko kilo 50.')
ON DUPLICATE KEY UPDATE name_en = VALUES(name_en);

CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title_en VARCHAR(255) NOT NULL,
    title_sw VARCHAR(255) NOT NULL,
    summary_en TEXT,
    summary_sw TEXT,
    location VARCHAR(255),
    status VARCHAR(60) DEFAULT 'Ongoing',
    launched_on DATE DEFAULT CURRENT_DATE
) ENGINE=InnoDB;

INSERT INTO projects (title_en, title_sw, summary_en, summary_sw, location, status, launched_on)
VALUES
('Dodoma Industrial Park Roofing', 'Paa la Eneo la Viwanda Dodoma', 'Supply and installation of 45,000 sqm of sandwich panels.', 'Utoaji na ufungaji wa paneli za sandwich mita 45,000.', 'Dodoma, Tanzania', 'Completed', '2024-06-01'),
('Standard Gauge Railway Steel Supply', 'Ugavi wa Chuma SGR', 'Fabrication of bridge beams for the SGR lots 3 and 4.', 'Uundaji wa boriti za madaraja kwa SGR sehemu ya 3 na 4.', 'Morogoro - Makutupora', 'Ongoing', '2025-01-10')
ON DUPLICATE KEY UPDATE title_en = VALUES(title_en);

CREATE TABLE IF NOT EXISTS publications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('events','training','national-holidays','international-holidays','calls-for-tenders','communicates') NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    title_sw VARCHAR(255) NOT NULL,
    body_en TEXT,
    body_sw TEXT,
    attachment VARCHAR(255),
    published_on DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO publications (type, title_en, title_sw, body_en, body_sw, published_on)
VALUES
('events', 'Supplier Open Day', 'Siku ya Wazabuni', 'Join us in Dar es Salaam to explore new supply opportunities.', 'Jiunge nasi Dar es Salaam kujadili fursa mpya za usambazaji.', '2025-02-14'),
('calls-for-tenders', 'Tender: Warehousing Partner', 'Zabuni: Mshirika wa Ghala', 'We invite licensed logistics firms to manage Mwanza depot.', 'Tunakaribisha kampuni za lojistiki zenye leseni kusimamia ghala la Mwanza.', '2025-01-30')
ON DUPLICATE KEY UPDATE title_en = VALUES(title_en);

CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department ENUM('general-management','sales-management') NOT NULL,
    name VARCHAR(255) NOT NULL,
    position VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(60),
    priority INT DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO contacts (department, name, position, email, phone, priority)
VALUES
('general-management', 'Francois Uwiragiye', 'Chairman & CEO — Musumba Holding', 'info@musumba-steel.com', '+255 761 037 271', 1),
('sales-management', 'Corporate Sales Office', 'Sales — Kahama', 'sales@musumba-steel.com', '+255 761 037 271', 1),
('sales-management', 'Kahama Factory Board Line', 'Factory Office', 'info@musumba-steel.com', '+255 781 502 260', 2)
ON DUPLICATE KEY UPDATE email = VALUES(email);

CREATE TABLE IF NOT EXISTS visitors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    country VARCHAR(100),
    region VARCHAR(100),
    city VARCHAR(100),
    user_agent TEXT,
    page_url VARCHAR(500),
    referrer VARCHAR(500),
    visited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ip (ip_address),
    INDEX idx_country (country),
    INDEX idx_region (region),
    INDEX idx_visited_at (visited_at),
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB;

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
    INDEX idx_pictures_display (display_order),
    INDEX idx_pictures_created (created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    youtube_id VARCHAR(100) NOT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_videos_display (display_order),
    INDEX idx_videos_created (created_at)
) ENGINE=InnoDB;


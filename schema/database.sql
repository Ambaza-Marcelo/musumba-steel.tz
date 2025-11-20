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
('welcome', 'home', 'Welcome to Musumba Steel', 'Karibu Musumba Steel', 'Trusted Tanzanian manufacturer of premium steel solutions.', 'Mtengenezaji anayeaminika wa bidhaa za chuma Tanzania.', '<p>Musumba Steel is a Tanzanian leader in flat and long steel products, delivering roofing, structural steel and fabrication support to regional partners.</p>', '<p>Musumba Steel ni kinara nchini Tanzania katika uzalishaji wa bidhaa za chuma, tukitoa paa, vyuma vya ujenzi na huduma za ufundi kwa washirika wa kikanda.</p>'),
('parent-company', 'about', 'Parent Company', 'Kampuni Mama', 'Musumba Steel belongs to Musumba Group, a diversified East African conglomerate.', 'Musumba Steel ni sehemu ya Musumba Group, kampuni yenye uwekezaji Afrika Mashariki.', '<p>Musumba Group invests in energy, logistics and advanced manufacturing to accelerate regional industrialisation. Musumba Steel anchors the metals division with integrated cold rolling, profiling and finishing capabilities.</p>', '<p>Musumba Group inawekeza katika nishati, usafirishaji na utengenezaji wa kisasa. Musumba Steel inaongoza kitengo cha metali kwa uwezo wa uzalishaji uliounganishwa.</p>'),
('about', 'about', 'About Musumba Steel', 'Kuhusu Musumba Steel', 'Steel manufacturer headquartered in Dar es Salaam with depots across Tanzania.', 'Mtengenezaji wa chuma mwenye makao Dar es Salaam na maghala nchi nzima.', '<p>We connect Tanzanian ingenuity with global technology partners to deliver consistent quality across roofing, structural steel and fabrication inputs.</p>', '<p>Tunaunganisha ubunifu wa Watanzania na washirika wa teknolojia duniani ili kutoa ubora wa kudumu katika paa, vyuma vya miundo na vifaa vya ufundi.</p>'),
('historic', 'about', 'Historic Background', 'Historia', 'From a trading shop in Mwanza to a continental supplier.', 'Kutoka duka dogo Mwanza hadi msambazaji bara zima.', '<p>Founded in 1998, Musumba Steel expanded with Tanzania\'s construction boom, adding galvanizing lines, service centers and regional depots.</p>', '<p>Tangu kuanzishwa mwaka 1998, Musumba Steel imekua sambamba na boom ya ujenzi Tanzania kwa kuongeza mitambo ya galvanizing, vituo vya huduma na maghala ya kikanda.</p>'),
('membership', 'about', 'Memberships', 'Uanachama', 'Member of TPSF, CTI and Tanzania Steel Manufacturers Association.', 'Mwanachama wa TPSF, CTI na Tanzania Steel Manufacturers Association.', '<p>We actively contribute to policy dialogues on industrialisation through multiple associations and chambers.</p>', '<p>Tunachangia mijadala ya sera kupitia vyama na mabaraza mbalimbali ya sekta.</p>'),
('partners', 'about', 'Strategic Partners', 'Wadau wa Mkakati', 'Allied with regional EPC contractors and raw material suppliers.', 'Tumeungana na wakandarasi na wasambazaji wa malighafi wa kikanda.', '<p>Partnerships with logistics firms, EPC contractors and technology licensors allow us to deliver turnkey solutions.</p>', '<p>Ushirikiano na kampuni za usafirishaji, wakandarasi na watoa teknolojia hutusaidia kutoa suluhisho kamili.</p>'),
('management', 'about', 'Management', 'Uongozi', 'Experienced Tanzanian leadership team.', 'Uongozi wenye uzoefu.', '<p>Our board blends industrial, financial and export experience ensuring compliance and sustainable growth.</p>', '<p>Bodi yetu ina uzoefu katika viwanda, fedha na biashara ya nje inayohakikisha ukuaji endelevu.</p>'),
('distributors', 'about', 'Distributors', 'Wauzaji', '30+ distributors across Tanzania, Rwanda and Burundi.', 'Wasambazaji zaidi ya 30 Tanzania, Rwanda na Burundi.', '<p>We empower distributors with technical assistance, just-in-time deliveries and localized marketing toolkits.</p>', '<p>Tunawawezesha wasambazaji kwa msaada wa kiufundi, usafirishaji wa wakati na zana za uuzaji zilizobinafsishwa.</p>'),
('vision', 'about', 'Vision', 'Dira', 'Advance Africa\'s steel independence.', 'Kuendeleza uhuru wa chuma Afrika.', '<p>Our vision is to become the most trusted East African source for engineered steel solutions.</p>', '<p>Dira ni kuwa chanzo kinachoaminika cha suluhisho za chuma Afrika Mashariki.</p>'),
('mission', 'about', 'Mission', 'Misheni', 'Deliver quality, affordable steel with Tanzanian talent.', 'Kutoa chuma bora na nafuu kwa kutumia vipaji vya Tanzania.', '<p>We invest in people, technology and sustainability to deliver value to every stakeholder.</p>', '<p>Tunawekeza katika watu, teknolojia na uendelevu ili kuleta thamani kwa kila mdau.</p>'),
('roofings', 'services', 'Roofing Portfolio', 'Bidhaa za Paa', 'Galvanized, AZ and color-coated roofing lines.', 'Mistari ya uzalishaji ya bati za galvanized, AZ na zilizopakwa rangi.', '<p>Our roofing line includes corrugated, box profile and concealed-fix systems with nationwide distribution.</p>', '<p>Paa zetu zinajumuisha bati za corrugated, box profile na mifumo ya concealed-fix yenye usambazaji nchi nzima.</p>'),
('services', 'services', 'Our Services', 'Huduma Zetu', 'Two specialized divisions covering roofing and construction inputs.', 'Vitengo viwili maalumu vinavyohudumia paa na vifaa vya ujenzi.', '<p>From cold rolled coils to turnkey roofing support, Musumba Steel delivers speed, compliance and traceability.</p>', '<p>Toka coil hadi usaidizi wa ufungaji wa paa, Musumba Steel inatoa kasi, ufuasi wa viwango na ufuatiliaji.</p>'),
('construction-materials', 'services', 'Construction Materials', 'Vifaa vya Ujenzi', 'Rebars, structural steel and accessories.', 'Nondo, vyuma vya muundo na vifaa vingine.', '<p>We supply reinforcement, beams, hollow sections and plates tested in our in-house laboratory.</p>', '<p>Tunatoa nondo, boriti, hollow sections na mabati yaliyopimwa katika maabara yetu.</p>'),
('our-projects', 'projects', 'Flagship Projects', 'Miradi Mikubwa', 'A snapshot of regional deliveries.', 'Muhtasari wa miradi ya kikanda.', '<p>Musumba Steel supports national infrastructure, industrial parks and private developments.</p>', '<p>Musumba Steel inaunga mkono miundombinu ya kitaifa, mbuga za viwanda na miradi binafsi.</p>'),
('publications', 'publications', 'News & Publications', 'Habari na Machapisho', 'Stay informed with events, trainings and tenders.', 'Pata taarifa kuhusu matukio, mafunzo na zabuni.', '<p>Browse the latest programs and regulatory notices shared by Musumba Steel.</p>', '<p>Tazama programu na matangazo mapya kutoka Musumba Steel.</p>'),
('contact-us', 'contact', 'Contact Musumba Steel', 'Wasiliana na Musumba Steel', 'Reach our head office or sales desks.', 'Wasiliana na ofisi kuu au dawati la mauzo.', '<p>We operate 24/7 hotlines for urgent plant supply and aftersales support.</p>', '<p>Tuna namba za dharura kwa usambazaji wa kiwandani na msaada wa baada ya mauzo.</p>'),
('news', 'publications', 'News', 'Habari', 'Latest news and updates.', 'Habari na matukio ya hivi karibuni.', '', ''),
('training', 'publications', 'Training', 'Mafunzo', 'Capacity building programs.', 'Programu za kujenga uwezo.', '', ''),
('national-holidays', 'publications', 'National Holidays', 'Sikukuu za Kitaifa', 'Public holiday notices.', 'Taarifa za sikukuu.', '', ''),
('international-holidays', 'publications', 'International Holidays', 'Sikukuu za Kimataifa', 'International observances.', 'Maadhimisho ya kimataifa.', '', ''),
('calls-for-tenders', 'publications', 'Calls for Tenders', 'Mialiko ya Zabuni', 'Procurement opportunities.', 'Fursa za manunuzi.', '', ''),
('communicates', 'publications', 'Communiqués', 'Taarifa', 'Press releases and statements.', 'Taarifa kwa vyombo vya habari.', '', ''),
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
('roofings', 'Galvanized Roofing Sheets', 'Bati za Galvanized', 'High tensile AZ roofing sheets with anti-corrosion layers.', 'Bati za nguvu zenye kinga dhidi ya kutu.'),
('roofings', 'Colour Coated Profiles', 'Profaili za Rangi', 'Factory-painted roofing profiles with UV shield.', 'Profaili zilizopakwa rangi zenye kinga ya jua.'),
('construction-materials', 'Reinforcement Bars', 'Nondo', 'BS4449 compliant reinforcement steel for high-rise projects.', 'Nondo zinazokidhi viwango vya BS4449 kwa miradi mikubwa.'),
('construction-materials', 'H-Beams & Channels', 'Boriti za H na Channels', 'Precision-rolled structural sections for industrial builds.', 'Boriti na channel zilizotengenezwa kwa usahihi kwa ujenzi wa viwanda.')
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
('general-management', 'Yvonne Kweka', 'Managing Director', 'md@musumbasteel.co.tz', '+255 22 2000 111', 1),
('sales-management', 'Bakari Mussa', 'Head of Sales', 'sales@musumbasteel.co.tz', '+255 757 000 222', 1)
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


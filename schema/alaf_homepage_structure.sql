-- ALAF-style dynamic homepage tables for Musumba Steel
USE musumbasteeltz;

CREATE TABLE IF NOT EXISTS home_slides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title_en VARCHAR(255) NOT NULL,
    title_sw VARCHAR(255) NOT NULL,
    subtitle_en TEXT,
    subtitle_sw TEXT,
    cta_label_en VARCHAR(120) DEFAULT 'Learn More',
    cta_label_sw VARCHAR(120) DEFAULT 'Jifunze Zaidi',
    cta_url VARCHAR(255) DEFAULT '?page=about',
    image_path VARCHAR(500) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS home_help_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title_en VARCHAR(255) NOT NULL,
    title_sw VARCHAR(255) NOT NULL,
    body_en TEXT,
    body_sw TEXT,
    link_url VARCHAR(255) DEFAULT '?page=contact-us',
    link_label_en VARCHAR(120) DEFAULT 'Learn More',
    link_label_sw VARCHAR(120) DEFAULT 'Jifunze Zaidi',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

TRUNCATE TABLE home_slides;
INSERT INTO home_slides (title_en, title_sw, subtitle_en, subtitle_sw, cta_label_en, cta_label_sw, cta_url, sort_order) VALUES
(
  'Building Tanzania. Strengthening the Lake Zone.',
  'Kujenga Tanzania. Kuimarisha Ukanda wa Ziwa.',
  'Musumba Steel Tanzania Limited is a leading manufacturer of high-quality steel products, proudly serving Tanzania and the wider Lake Zone with reliable, durable, and locally produced steel solutions.',
  'Musumba Steel Tanzania Limited ni mtengenezaji wa bidhaa za chuma bora, ikitumikia Tanzania na Ukanda wa Ziwa.',
  'Who We Are',
  'Sisi Ni Nani',
  '?page=about',
  1
),
(
  'Strong Steel. Local Production. Lasting Impact.',
  'Chuma Imara. Uzalishaji wa Ndani. Athari ya Kudumu.',
  'As the only manufacturer of high-quality steel products in Lake Zone, we bring quality steel closer to customers while supporting industrial growth and economic transformation.',
  'Kama mtengenezaji pekee wa bidhaa bora za chuma katika Ukanda wa Ziwa, tunaleta chuma bora karibu na wateja.',
  'Our Products',
  'Bidhaa Zetu',
  '?page=roofings',
  2
),
(
  'Why Musumba Steel?',
  'Kwa Nini Musumba Steel?',
  'Locally Manufactured. Quality Assured. Built to Last. Choose Musumba Steel — the strength of Tanzania’s Lake Zone.',
  'Imetengenezwa Ndani. Ubora Umehakikishwa. Imejengwa Kudumu. Chagua Musumba Steel — nguvu ya Ukanda wa Ziwa.',
  'Talk to Us',
  'Wasiliana Nasi',
  '?page=contact-us',
  3
);

TRUNCATE TABLE home_help_cards;
INSERT INTO home_help_cards (title_en, title_sw, body_en, body_sw, link_url, link_label_en, link_label_sw, sort_order) VALUES
(
  'Homeowners & Builders',
  'Wamiliki wa Nyumba na Wajenzi',
  'From homes to commercial buildings, get durable roofing sheets and steel products manufactured in Kahama for the Lake Zone.',
  'Kutoka nyumba hadi majengo ya biashara — bati na chuma bora kutoka Kahama kwa Ukanda wa Ziwa.',
  '?page=roofings',
  'Explore Roofing',
  'Angalia Paa',
  1
),
(
  'Contractors & Industry',
  'Wakandarasi na Viwanda',
  'Hollow sections, pipes, MS plates and nails for infrastructure, industrial and agricultural projects.',
  'Hollow sections, mabomba, sahani za MS na misumari kwa miundombinu, viwanda na kilimo.',
  '?page=construction-materials',
  'Construction Materials',
  'Vifaa vya Ujenzi',
  2
),
(
  'Distributors & Partners',
  'Wasambazaji na Washirika',
  'Partner with Musumba Steel Tanzania Limited for reliable supply, quality assurance and lasting local impact.',
  'Shirikiana na Musumba Steel Tanzania Limited kwa usambazaji wa kuaminika na athari ya kudumu.',
  '?page=distributors',
  'Work With Us',
  'Fanya Kazi Nasi',
  3
);

UPDATE pages SET
  title_en = 'Who We Are',
  title_sw = 'Sisi Ni Nani',
  summary_en = 'Building Tanzania. Strengthening the Lake Zone.',
  summary_sw = 'Kujenga Tanzania. Kuimarisha Ukanda wa Ziwa.',
  content_en = '<p>Musumba Steel Tanzania Limited is a Tanzanian steel manufacturing company established to meet the growing demand for quality steel products in the country.</p><p>Located in Kahama, Shinyanga, we manufacture steel products that serve the construction, infrastructure, industrial, commercial, and agricultural sectors.</p><p>Our strategic location enables us to produce locally, supply efficiently, reduce dependence on distant sources, and bring quality steel closer to customers across the Lake Zone and beyond.</p><p>We are more than a steel manufacturer. We are an industrial partner committed to building Tanzania through local production, quality, innovation, and sustainable growth.</p>',
  content_sw = '<p>Musumba Steel Tanzania Limited ni kampuni ya Tanzania inayotengeneza chuma ili kukidhi mahitaji yanayoongezeka ya bidhaa bora za chuma nchini.</p><p>Iko Kahama, Shinyanga, na inatengeneza chuma kwa sekta za ujenzi, miundombinu, viwanda, biashara na kilimo.</p><p>Eneo letu la kimkakati linatuwezesha kuzalisha ndani, kusambaza kwa ufanisi, na kuleta chuma bora karibu na wateja katika Ukanda wa Ziwa na zaidi.</p><p>Sisi ni zaidi ya mtengenezaji wa chuma. Sisi ni mshirika wa viwanda unaojitoa kujenga Tanzania.</p>'
WHERE slug = 'about';

<?php
/**
 * One-click Mabati-structure rebuild for Musumba Steel.
 * Open: /musumba_steel/admin/migrate_mabati.php
 */
declare(strict_types=1);

// Allow local migration without login for setup convenience
require_once __DIR__ . '/../config/config.php';

header('Content-Type: text/html; charset=utf-8');

$messages = [];
$db = db();

function runSql(mysqli $db, string $sql, array &$messages): void
{
    if ($db->multi_query($sql)) {
        do {
            if ($result = $db->store_result()) {
                $result->free();
            }
        } while ($db->more_results() && $db->next_result());
        $messages[] = 'OK batch';
    } else {
        $messages[] = 'ERR: ' . $db->error;
    }
}

$sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS faqs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  question_en VARCHAR(500) NOT NULL,
  question_sw VARCHAR(500) NOT NULL,
  answer_en TEXT NOT NULL,
  answer_sw TEXT NOT NULL,
  category VARCHAR(120) DEFAULT 'general',
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS testimonials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name_en VARCHAR(255) NOT NULL,
  name_sw VARCHAR(255) NOT NULL,
  quote_en TEXT NOT NULL,
  quote_sw TEXT NOT NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

CREATE TABLE IF NOT EXISTS product_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(120) NOT NULL UNIQUE,
  parent_slug VARCHAR(120) DEFAULT NULL,
  menu_group VARCHAR(60) NOT NULL DEFAULT 'building',
  title_en VARCHAR(255) NOT NULL,
  title_sw VARCHAR(255) NOT NULL,
  summary_en TEXT,
  summary_sw TEXT,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

runSql($db, $sql, $messages);

// Expand services.category if needed
@$db->query("ALTER TABLE services MODIFY category VARCHAR(120) NOT NULL");

$pages = [
    ['about', 'about', 'About Us', 'Kuhusu Sisi',
        'Building Tanzania. Strengthening the Lake Zone.',
        'Kujenga Tanzania. Kuimarisha Ukanda wa Ziwa.',
        '<p>Musumba Steel Tanzania Limited is a Tanzanian steel manufacturing company established to meet the growing demand for quality steel products in the country.</p><p>Located in Kahama, Shinyanga, we manufacture steel products that serve the construction, infrastructure, industrial, commercial, and agricultural sectors.</p><p>Our strategic location enables us to produce locally, supply efficiently, reduce dependence on distant sources, and bring quality steel closer to customers across the Lake Zone and beyond.</p><p>We are more than a steel manufacturer. We are an industrial partner committed to building Tanzania through local production, quality, innovation, and sustainable growth.</p><p><strong>Strong Steel. Local Production. Lasting Impact.</strong></p>',
        '<p>Musumba Steel Tanzania Limited ni kampuni ya Tanzania inayotengeneza chuma ili kukidhi mahitaji yanayoongezeka ya bidhaa bora za chuma nchini.</p><p>Iko Kahama, Shinyanga, na inatengeneza chuma kwa sekta za ujenzi, miundombinu, viwanda, biashara na kilimo.</p><p>Eneo letu la kimkakati linatuwezesha kuzalisha ndani, kusambaza kwa ufanisi, na kuleta chuma bora karibu na wateja katika Ukanda wa Ziwa na zaidi.</p><p><strong>Chuma Imara. Uzalishaji wa Ndani. Athari ya Kudumu.</strong></p>'],
    ['vision', 'about', 'Our Vision', 'Dira Yetu',
        'Tanzania’s most trusted and preferred steel manufacturer.',
        'Mtengenezaji wa chuma anayeaminika zaidi Tanzania.',
        '<p>To be Tanzania’s most trusted and preferred steel manufacturer, driving industrial growth and building a stronger, self-reliant future.</p>',
        '<p>Kuwa mtengenezaji wa chuma anayeaminika na anayependelewa zaidi Tanzania, kukuza ukuaji wa viwanda na kujenga mustakabali imara wa kujitegemea.</p>'],
    ['mission', 'about', 'Our Mission', 'Misheni Yetu',
        'High-quality, durable, competitively priced steel.',
        'Chuma bora, cha kudumu, na bei shindani.',
        '<p>To manufacture high-quality, durable, and competitively priced steel products that meet customer needs, strengthen local industries, create employment, and contribute to Tanzania’s sustainable development.</p>',
        '<p>Kutengeneza bidhaa za chuma bora, za kudumu, na za bei shindani zinazokidhi mahitaji ya wateja, kuimarisha viwanda vya ndani, kuunda ajira, na kuchangia maendeleo endelevu ya Tanzania.</p>'],
    ['values', 'about', 'Our Core Values', 'Maadili Yetu',
        'Quality, Integrity, Customer Focus, Innovation, Safety, Excellence, Local Impact.',
        'Ubora, Uadilifu, Mteja, Ubunifu, Usalama, Ubora wa Juu, Athari ya Ndani.',
        '<ul><li><strong>Quality First</strong> — We never compromise on the quality and reliability of our products.</li><li><strong>Integrity</strong> — We do business with honesty, transparency, and accountability.</li><li><strong>Customer Focus</strong> — Our customers are at the heart of everything we do.</li><li><strong>Innovation</strong> — We continuously improve our products, processes, and technology.</li><li><strong>Safety</strong> — We prioritize the safety of our people, customers, and communities.</li><li><strong>Excellence</strong> — We strive for high standards in everything we deliver.</li><li><strong>Local Impact</strong> — We believe in creating value through local manufacturing, skills, employment, and economic development.</li></ul>',
        '<ul><li><strong>Ubora Kwanza</strong> — Hatutoi maafikiano kuhusu ubora na uaminifu wa bidhaa zetu.</li><li><strong>Uadilifu</strong> — Tunafanya biashara kwa uaminifu, uwazi na uwajibikaji.</li><li><strong>Kuzingatia Mteja</strong> — Wateja wetu ni kiini cha kila tunachofanya.</li><li><strong>Ubunifu</strong> — Tunaendelea kuboresha bidhaa, michakato na teknolojia.</li><li><strong>Usalama</strong> — Tunatanguliza usalama wa watu wetu, wateja na jamii.</li><li><strong>Ubora</strong> — Tunajitahidi viwango vya juu katika kila tunachotoa.</li><li><strong>Athari ya Ndani</strong> — Tunaamini kuunda thamani kupitia uzalishaji wa ndani, ujuzi, ajira na maendeleo ya kiuchumi.</li></ul>'],
    ['management', 'about', 'Our People', 'Watu Wetu',
        'Leadership and teams behind Musumba Steel Tanzania.',
        'Uongozi na timu za Musumba Steel Tanzania.',
        '<p>Musumba Steel Tanzania Limited is guided by experienced leadership focused on quality manufacturing, customer service, and industrial growth in the Lake Zone.</p><p>Our factory and corporate teams in Kahama work together to deliver reliable steel solutions every day.</p>',
        '<p>Musumba Steel Tanzania Limited inaongozwa na uongozi wenye uzoefu unaozingatia uzalishaji bora, huduma kwa wateja, na ukuaji wa viwanda katika Ukanda wa Ziwa.</p>'],
    ['our-community', 'about', 'Our Community', 'Jamii Yetu',
        'Creating local value through manufacturing, skills and employment.',
        'Kuunda thamani ya ndani kupitia uzalishaji, ujuzi na ajira.',
        '<p>As a Kahama-based manufacturer, we are committed to local impact — skills development, employment, and supporting construction and infrastructure across Tanzania’s Lake Zone.</p><p>Choose Musumba Steel — the strength of Tanzania’s Lake Zone.</p>',
        '<p>Kama mtengenezaji wa Kahama, tumejitolea kwa athari ya ndani — ujuzi, ajira, na kuunga mkono ujenzi na miundombinu katika Ukanda wa Ziwa.</p>'],
    ['why-musumba', 'about', 'Why Musumba Steel?', 'Kwa Nini Musumba Steel?',
        'Locally Manufactured. Quality Assured. Built to Last.',
        'Imetengenezwa Ndani. Ubora Umehakikishwa. Imejengwa Kudumu.',
        '<p><strong>Locally Manufactured. Quality Assured. Built to Last.</strong></p><p>From homes and commercial buildings to major infrastructure and industrial projects, Musumba Steel provides the strength behind development.</p><p>Choose Musumba Steel — the strength of Tanzania’s Lake Zone.</p>',
        '<p><strong>Imetengenezwa Ndani. Ubora Umehakikishwa. Imejengwa Kudumu.</strong></p><p>Kutoka nyumba na majengo ya biashara hadi miundombinu mikubwa, Musumba Steel inatoa nguvu ya maendeleo.</p>'],
    ['residential-roofing', 'building', 'Residential Roofing Solutions', 'Suluhisho za Paa za Makazi',
        'Durable roofing sheets for homes across the Lake Zone.',
        'Bati za kudumu kwa nyumba katika Ukanda wa Ziwa.',
        '<p>Explore corrugated, box profile, tile-effect and colour-coated roofing manufactured in Kahama for Tanzanian homes.</p>',
        '<p>Chunguza bati corrugated, box profile, tile na rangi kutoka Kahama kwa nyumba za Tanzania.</p>'],
    ['industrial-roofing', 'building', 'Industrial & Commercial Roofing', 'Paa za Viwanda na Biashara',
        'High-performance roofing for industrial and commercial builds.',
        'Paa bora kwa majengo ya viwanda na biashara.',
        '<p>IT4 box profiles, heavy-duty sheets and accessories for warehouses, factories and commercial projects.</p>',
        '<p>Profaili za IT4, bati nzito na vifaa kwa maghala, viwanda na miradi ya biashara.</p>'],
    ['pipes-tubes', 'building', 'Pipes and Tubes', 'Mabomba na Tube',
        'Hollow sections, square and round pipes for construction.',
        'Hollow sections na mabomba kwa ujenzi.',
        '<p>Construction and industrial pipe solutions manufactured locally in Kahama.</p>',
        '<p>Suluhisho za mabomba kwa ujenzi na viwanda kutoka Kahama.</p>'],
    ['flashings', 'building', 'Flashings & Accessories', 'Flashing na Vifaa',
        'Ridges, flashings, crimp and curve accessories.',
        'Ridge, flashing, crimp na curve.',
        '<p>Complete your roofing system with flashings, ridges and formed accessories from Musumba Steel.</p>',
        '<p>Kamilisha mfumo wa paa kwa flashing, ridge na vifaa kutoka Musumba Steel.</p>'],
    ['coated-steel', 'coated', 'Coated Steel', 'Chuma Kilichopakwa',
        'Alu-Zinc and pre-painted steel systems.',
        'Mifumo ya Alu-Zinc na chuma kilichopakwa rangi.',
        '<p>Our coated steel range includes Alu-Zinc corrugated sheets, pre-painted systems and colour profiles engineered for East African conditions.</p>',
        '<p>Aina zetu za chuma kilichopakwa ni pamoja na bati za Alu-Zinc, mifumo ya rangi na profaili kwa hali za Afrika Mashariki.</p>'],
    ['need-a-new-roof', 'resources', 'Need a New Roof?', 'Unahitaji Paa Jipya?',
        'Guidance for homeowners planning a new roof.',
        'Mwongozo kwa wamiliki wa nyumba.',
        '<p>Choosing the right steel roofing starts with climate, design, gauge and colour. Musumba Steel helps homeowners select durable, locally manufactured solutions.</p><p><a href="?page=contact-us">Talk to our team</a> for advice on profiles and quantities.</p>',
        '<p>Kuchagua paa sahihi huanza na hali ya hewa, muundo, gauge na rangi. Musumba Steel inasaidia wamiliki wa nyumba.</p>'],
    ['why-steel-roofing', 'resources', 'Why Steel Roofing', 'Kwa Nini Paa za Chuma',
        'Strength, durability and value for Tanzanian buildings.',
        'Nguvu, uimara na thamani kwa majengo ya Tanzania.',
        '<p>Steel roofing offers durability, design flexibility, fire resistance and long-term value. Locally manufactured Musumba Steel brings quality closer to Lake Zone customers.</p>',
        '<p>Paa za chuma hutoa uimara, unyumbufu wa muundo, na thamani ya muda mrefu.</p>'],
    ['roof-designs', 'resources', 'Roof Designs', 'Miundo ya Paa',
        'Profiles and looks for modern and traditional roofs.',
        'Profaili kwa paa za kisasa na za jadi.',
        '<p>From classic corrugated to box profile and tile-effect Versa Tile, browse designs that fit residential and commercial projects.</p>',
        '<p>Kutoka corrugated hadi box profile na Versa Tile — miundo kwa majengo ya makazi na biashara.</p>'],
    ['technical-specs', 'resources', 'Specifications & Technical Sheets', 'Vipimo na Karatasi za Kiufundi',
        'Product guidance for architects, engineers and contractors.',
        'Mwongozo kwa wasanifu, wahandisi na wakandarasi.',
        '<p>Contact sales@musumba-steel.com for gauges, lengths, coatings and technical data for Musumba Steel products.</p>',
        '<p>Wasiliana sales@musumba-steel.com kwa vipimo na data za kiufundi.</p>'],
    ['storage-handling', 'resources', 'Storage & Handling of Steel', 'Uhifadhi na Utunzaji wa Chuma',
        'Best practices for storing and handling steel sheets.',
        'Mbinu bora za kuhifadhi na kushughulikia bati.',
        '<p>Store sheets dry, elevated and covered. Handle with care to protect coatings. Our team can advise on transport and on-site handling.</p>',
        '<p>Hifadhi bati mahali pakavu, juu na zimefunikwa. Shughulikia kwa uangalifu.</p>'],
    ['faqs', 'resources', 'FAQs', 'Maswali Yanayoulizwa Mara kwa Mara',
        'Answers to common questions about Musumba Steel products.',
        'Majibu ya maswali ya kawaida.',
        '<p>Browse frequently asked questions below. Still need help? Call +255 761 037 271 or email info@musumba-steel.com.</p>',
        '<p>Angalia maswali yanayoulizwa mara kwa mara. Unaweza kupiga +255 761 037 271.</p>'],
    ['deals', 'buy', 'Buy / Deals', 'Nunua / Ofa',
        'Order quality steel from Musumba Steel Tanzania.',
        'Agiza chuma bora kutoka Musumba Steel.',
        '<p>Ready to buy? Contact our sales desk for quotes, availability and delivery across the Lake Zone.</p><p>Email: <a href="mailto:sales@musumba-steel.com">sales@musumba-steel.com</a><br>Phone: <a href="tel:+255761037271">+255 761 037 271</a> · <a href="tel:+255781502260">+255 781 502 260</a></p>',
        '<p>Tayari kununua? Wasiliana na ofisi ya mauzo.</p>'],
    ['retail-centres', 'buy', 'Retail & Service Centres', 'Vituo vya Huduma',
        'Visit us in Kahama — factory and corporate offices.',
        'Tembelea Kahama — kiwanda na ofisi.',
        '<p><strong>Factory:</strong> Plot No. 71, Chapulwa Industrial Area, Mwendakulima Ward, Kahama, Shinyanga. Tel +255 781 502 260</p><p><strong>Corporate:</strong> 2nd Floor, Mongo Complex, Phantom St / Isaka Road, Kahama. Tel +255 761 037 271</p><p>Hours: Mon–Fri 7:30–17:30 · Sat 7:30–13:30</p>',
        '<p><strong>Kiwanda:</strong> Kiwanja 71, Chapulwa, Kahama. Simu +255 781 502 260</p><p><strong>Ofisi:</strong> Mongo Complex, Kahama. Simu +255 761 037 271</p>'],
    ['distributors', 'buy', 'Authorized Distributors', 'Wasambazaji Walioidhinishwa',
        'Partner network across Tanzania and the Lake Zone.',
        'Mtandao wa washirika Tanzania na Ukanda wa Ziwa.',
        '<p>Musumba Steel works with distributors to bring quality steel closer to customers. Interested in becoming a partner? Contact marketing@musumba-steel.com or sales@musumba-steel.com.</p>',
        '<p>Musumba Steel inashirikiana na wasambazaji. Unataka kuwa mshirika? Wasiliana marketing@musumba-steel.com.</p>'],
    ['services', 'services', 'Services', 'Huduma',
        'Support from enquiry to delivery.',
        'Msaada kutoka ombi hadi usafirishaji.',
        '<p>We support customers with product selection, quotations, custom lengths where available, and reliable supply from our Kahama plant.</p><p>Talk to us: +255 761 037 271 · sales@musumba-steel.com</p>',
        '<p>Tunasaidia wateja kuchagua bidhaa, nukuu, na usambazaji kutoka Kahama.</p>'],
    ['our-projects', 'projects', 'Our Projects', 'Miradi Yetu',
        'Steel behind development across the Lake Zone.',
        'Chuma kinachojenga Ukanda wa Ziwa.',
        '<p>From homes and commercial buildings to major infrastructure and industrial projects, Musumba Steel provides the strength behind development.</p>',
        '<p>Kutoka nyumba hadi miundombinu — Musumba Steel inatoa nguvu ya maendeleo.</p>'],
    ['contact-us', 'contact', 'Contact Us', 'Wasiliana Nasi',
        'Talk to Musumba Steel Tanzania Limited.',
        'Zungumza na Musumba Steel Tanzania Limited.',
        '<p><strong>Corporate / Customer Care:</strong> +255 761 037 271<br><strong>Factory:</strong> +255 781 502 260</p><p>info@musumba-steel.com · sales@musumba-steel.com · marketing@musumba-steel.com</p>',
        '<p>Ofisi: +255 761 037 271 · Kiwanda: +255 781 502 260</p>'],
    ['warranty', 'resources', 'Quality & Warranty', 'Ubora na Dhamana',
        'Quality first — products built to last.',
        'Ubora kwanza — bidhaa zilizojengwa kudumu.',
        '<p>Quality First is a core value at Musumba Steel. We never compromise on the quality and reliability of our products. Contact customer care for product guidance and after-sales support.</p>',
        '<p>Ubora Kwanza ni dhamira yetu. Wasiliana nasi kwa mwongozo wa bidhaa.</p>'],
];

$stmt = $db->prepare('INSERT INTO pages (slug, section, title_en, title_sw, summary_en, summary_sw, content_en, content_sw)
VALUES (?,?,?,?,?,?,?,?)
ON DUPLICATE KEY UPDATE section=VALUES(section), title_en=VALUES(title_en), title_sw=VALUES(title_sw),
summary_en=VALUES(summary_en), summary_sw=VALUES(summary_sw), content_en=VALUES(content_en), content_sw=VALUES(content_sw)');

foreach ($pages as $p) {
    $stmt->bind_param('ssssssss', $p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7]);
    $stmt->execute();
}
$messages[] = 'Pages upserted: ' . count($pages);
$stmt->close();

$db->query('TRUNCATE TABLE home_slides');
$db->query("INSERT INTO home_slides (title_en, title_sw, subtitle_en, subtitle_sw, cta_label_en, cta_label_sw, cta_url, image_path, sort_order) VALUES
('Building Tanzania', 'Kujenga Tanzania', 'Strengthening the Lake Zone', 'Kuimarisha Ukanda wa Ziwa', 'Who We Are', 'Sisi Ni Nani', '?page=about', NULL, 1),
('Strong Steel. Local Production.', 'Chuma Imara. Uzalishaji wa Ndani.', 'Lasting Impact for Tanzania’s Lake Zone', 'Athari ya Kudumu kwa Ukanda wa Ziwa', 'Our Products', 'Bidhaa Zetu', '?page=residential-roofing', NULL, 2),
('Why Musumba Steel?', 'Kwa Nini Musumba Steel?', 'Locally Manufactured. Quality Assured. Built to Last.', 'Imetengenezwa Ndani. Ubora Umehakikishwa. Imejengwa Kudumu.', 'Learn More', 'Jifunze Zaidi', '?page=why-musumba', NULL, 3)");
$messages[] = 'Home slides reset';

$db->query('TRUNCATE TABLE home_help_cards');
$db->query("INSERT INTO home_help_cards (title_en, title_sw, body_en, body_sw, link_url, link_label_en, link_label_sw, sort_order) VALUES
('Homeowners', 'Wamiliki wa Nyumba', 'Turning your house into a home starts with the right steel. From cost to colour, we have you covered.', 'Nyumba yako inaanza na chuma sahihi. Kutoka gharama hadi rangi — tuko pamoja.', '?page=need-a-new-roof', 'Learn More', 'Jifunze Zaidi', 1),
('Property Managers & Builders', 'Wasimamizi na Wajenzi', 'Explore high-performing roofing and construction steel for commercial and industrial projects.', 'Chunguza paa na chuma cha ujenzi kwa miradi ya biashara na viwanda.', '?page=industrial-roofing', 'Learn More', 'Jifunze Zaidi', 2),
('Professionals', 'Wataalamu', 'Architects · Contractors · Distributors — technical support and reliable supply from Kahama.', 'Wasanifu · Wakandarasi · Wasambazaji — msaada wa kiufundi kutoka Kahama.', '?page=technical-specs', 'Learn More', 'Jifunze Zaidi', 3)");
$messages[] = 'Help cards reset';

$db->query('TRUNCATE TABLE faqs');
$db->query("INSERT INTO faqs (question_en, question_sw, answer_en, answer_sw, category, sort_order) VALUES
('Where is Musumba Steel located?', 'Musumba Steel iko wapi?', 'Our factory is at Plot No. 71, Chapulwa Industrial Area, Kahama, Shinyanga. Corporate office: 2nd Floor, Mongo Complex, Kahama.', 'Kiwanda kiko Chapulwa, Kahama. Ofisi: Mongo Complex, Kahama.', 'general', 1),
('What products do you manufacture?', 'Mnatengeneza bidhaa gani?', 'Roofing sheets (corrugated, box profile, tile-effect, colour-coated), flashings, hollow sections, pipes, MS plates and nails.', 'Bati, flashing, hollow sections, mabomba, sahani za MS na misumari.', 'products', 2),
('How can I place an order?', 'Nawezaje kuagiza?', 'Call +255 761 037 271 or +255 781 502 260, or email sales@musumba-steel.com.', 'Piga +255 761 037 271 au andika sales@musumba-steel.com.', 'sales', 3),
('What are your working hours?', 'Saa zenu za kazi ni zipi?', 'Monday–Friday 7:30–17:30 and Saturday 7:30–13:30.', 'Jumatatu–Ijumaa 7:30–17:30 na Jumamosi 7:30–13:30.', 'general', 4),
('Do you supply outside Kahama?', 'Mnasambaza nje ya Kahama?', 'Yes. We proudly serve Tanzania and the wider Lake Zone with locally produced steel solutions.', 'Ndiyo. Tunatumikia Tanzania na Ukanda wa Ziwa.', 'sales', 5)");
$messages[] = 'FAQs seeded';

$db->query('TRUNCATE TABLE testimonials');
$messages[] = 'Testimonials cleared — add real Google reviews from admin/testimonials.php';

$db->query('TRUNCATE TABLE product_categories');
$db->query("INSERT INTO product_categories (slug, parent_slug, menu_group, title_en, title_sw, summary_en, summary_sw, sort_order) VALUES
('residential-roofing', NULL, 'building', 'Residential Roofing Solutions', 'Suluhisho za Paa za Makazi', 'Home roofing systems', 'Paa za nyumba', 1),
('industrial-roofing', NULL, 'building', 'Industrial & Commercial Roofing', 'Paa za Viwanda na Biashara', 'Industrial roofing', 'Paa za viwanda', 2),
('pipes-tubes', NULL, 'building', 'Pipes and Tubes', 'Mabomba na Tube', 'Construction pipes', 'Mabomba', 3),
('flashings', NULL, 'building', 'Flashings & Accessories', 'Flashing na Vifaa', 'Roof accessories', 'Vifaa vya paa', 4),
('coated-steel', NULL, 'coated', 'Coated Steel', 'Chuma Kilichopakwa', 'Coated systems', 'Mifumo ya rangi', 5)");
$messages[] = 'Product categories seeded';

// Remap services categories for Mabati-like hubs
$db->query("UPDATE services SET category = 'residential-roofing' WHERE category IN ('roofings') AND (name_en LIKE '%Corrugated%' OR name_en LIKE '%Versa%' OR name_en LIKE '%Rangi Max%' OR name_en LIKE '%Pre-Painted%')");
$db->query("UPDATE services SET category = 'industrial-roofing' WHERE category IN ('roofings','residential-roofing') AND (name_en LIKE '%IT4%' OR name_en LIKE '%Box Profile%')");
$db->query("UPDATE services SET category = 'flashings' WHERE name_en LIKE '%Flash%' OR name_en LIKE '%Crimp%' OR name_en LIKE '%Ridge%'");
$db->query("UPDATE services SET category = 'pipes-tubes' WHERE category = 'construction-materials' AND (name_en LIKE '%Pipe%' OR name_en LIKE '%Hollow%' OR name_en LIKE '%Plate%' OR name_en LIKE '%Nail%')");
$db->query("UPDATE services SET category = 'coated-steel' WHERE name_en LIKE '%Alu-Zinc%' OR name_en LIKE '%Pre-Painted%' OR name_en LIKE '%Rangi Max%'");
// Keep any leftover roofings under residential
$db->query("UPDATE services SET category = 'residential-roofing' WHERE category = 'roofings'");
$db->query("UPDATE services SET category = 'pipes-tubes' WHERE category = 'construction-materials'");
$messages[] = 'Services categories remapped';

echo '<!DOCTYPE html><html><head><title>Migrate Mabati</title></head><body style="font-family:sans-serif;padding:2rem">';
echo '<h1>Mabati structure migration</h1><ul>';
foreach ($messages as $m) {
    echo '<li>' . htmlspecialchars($m) . '</li>';
}
echo '</ul><p><a href="../?page=welcome">View homepage</a> · <a href="dashboard.php">Admin</a></p></body></html>';

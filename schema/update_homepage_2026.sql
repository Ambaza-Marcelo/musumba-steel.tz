-- Home page content refresh from Word + Excel recommendations (2026)
USE musumbasteeltz;

UPDATE pages SET
    title_en = 'Welcome to Musumba Steel Tanzania Limited',
    title_sw = 'Karibu Musumba Steel Tanzania Limited',
    summary_en = 'Building Tanzania. Strengthening the Lake Zone.',
    summary_sw = 'Kujenga Tanzania. Kuimarisha Ukanda wa Ziwa.',
    content_en = '<p>Musumba Steel Tanzania Limited is a leading manufacturer of high-quality steel products, proudly serving Tanzania and the wider Lake Zone with reliable, durable, and locally produced steel solutions.</p><p><strong>Strong Steel. Local Production. Lasting Impact.</strong></p>',
    content_sw = '<p>Musumba Steel Tanzania Limited ni mtengenezaji wa bidhaa za chuma bora, ikitumikia Tanzania na Ukanda wa Ziwa.</p><p><strong>Chuma Imara. Uzalishaji wa Ndani. Athari ya Kudumu.</strong></p>'
WHERE slug = 'welcome';

UPDATE pages SET
    title_en = 'About Musumba Steel Tanzania Limited',
    title_sw = 'Kuhusu Musumba Steel Tanzania Limited',
    summary_en = 'Locally manufactured steel from Kahama, Shinyanga — serving the Lake Zone and beyond.',
    summary_sw = 'Chuma kinachotengenezwa Kahama, Shinyanga — kwa Ukanda wa Ziwa na zaidi.',
    content_en = '<p>Musumba Steel Tanzania Limited is a Tanzanian steel manufacturing company established to meet the growing demand for quality steel products in the country. Located in Kahama, Shinyanga, we manufacture steel products that serve the construction, infrastructure, industrial, commercial, and agricultural sectors.</p><p>Our strategic location enables us to produce locally, supply efficiently, reduce dependence on distant sources, and bring quality steel closer to customers across the Lake Zone and beyond.</p><p>As the only manufacturer of high-quality steel products in the Lake Zone, we are strategically positioned to support industrial growth and economic transformation. We are more than a steel manufacturer — we are an industrial partner committed to building Tanzania through local production, quality, innovation, and sustainable growth.</p>',
    content_sw = '<p>Musumba Steel Tanzania Limited ni kampuni ya Tanzania inayotengeneza chuma ili kukidhi mahitaji yanayoongezeka ya bidhaa bora za chuma. Iko Kahama, Shinyanga, na inatumikia sekta za ujenzi, miundombinu, viwanda, biashara na kilimo.</p><p>Eneo letu la kimkakati linatuwezesha kuzalisha ndani, kusambaza kwa ufanisi, na kuleta chuma bora karibu na wateja wa Ukanda wa Ziwa na zaidi.</p><p>Kama mtengenezaji pekee wa bidhaa bora za chuma katika Ukanda wa Ziwa, sisi ni mshirika wa viwanda unaojenga Tanzania.</p>'
WHERE slug = 'about';

UPDATE pages SET
    title_en = 'Our Vision',
    title_sw = 'Dira Yetu',
    summary_en = 'Tanzania’s most trusted and preferred steel manufacturer.',
    summary_sw = 'Mtengenezaji wa chuma anayeaminika na anayependelewa zaidi Tanzania.',
    content_en = '<p>To be Tanzania’s most trusted and preferred steel manufacturer, driving industrial growth and building a stronger, self-reliant future.</p>',
    content_sw = '<p>Kuwa mtengenezaji wa chuma anayeaminika na anayependelewa zaidi Tanzania, kukuza ukuaji wa viwanda na kujenga mustakabali thabiti na wenye kujitegemea.</p>'
WHERE slug = 'vision';

UPDATE pages SET
    title_en = 'Our Mission',
    title_sw = 'Misheni Yetu',
    summary_en = 'High-quality, durable, competitively priced steel for Tanzania.',
    summary_sw = 'Chuma bora, cha kudumu na chenye bei shindani kwa Tanzania.',
    content_en = '<p>To manufacture high-quality, durable, and competitively priced steel products that meet customer needs, strengthen local industries, create employment, and contribute to Tanzania’s sustainable development.</p>',
    content_sw = '<p>Kutengeneza bidhaa za chuma bora, za kudumu na zenye bei shindani zinazokidhi mahitaji ya wateja, kuimarisha viwanda vya ndani, kuunda ajira, na kuchangia maendeleo endelevu ya Tanzania.</p>'
WHERE slug = 'mission';

INSERT INTO pages (slug, section, title_en, title_sw, summary_en, summary_sw, content_en, content_sw)
VALUES (
    'values',
    'about',
    'Our Core Values',
    'Maadili Yetu Muhimu',
    'Quality, integrity, customer focus, innovation, safety, excellence, and local impact.',
    'Ubora, uadilifu, mteja, uvumbuzi, usalama, ubora wa juu, na athari ya ndani.',
    '<ul><li><strong>Quality First</strong> — We never compromise on the quality and reliability of our products.</li><li><strong>Integrity</strong> — We do business with honesty, transparency, and accountability.</li><li><strong>Customer Focus</strong> — Our customers are at the heart of everything we do.</li><li><strong>Innovation</strong> — We continuously improve our products, processes, and technology.</li><li><strong>Safety</strong> — We prioritize the safety of our people, customers, and communities.</li><li><strong>Excellence</strong> — We strive for high standards in everything we deliver.</li><li><strong>Local Impact</strong> — We believe in creating value through local manufacturing, skills, employment, and economic development.</li></ul>',
    '<ul><li><strong>Ubora Kwanza</strong> — Hatutoi msamaha kwa ubora na uaminifu wa bidhaa zetu.</li><li><strong>Uadilifu</strong> — Tunafanya biashara kwa uaminifu, uwazi na uwajibikaji.</li><li><strong>Mteja Kwanza</strong> — Wateja wetu ni kiini cha kila tunachofanya.</li><li><strong>Uvumbuzi</strong> — Tunaboresha daima bidhaa, michakato na teknolojia.</li><li><strong>Usalama</strong> — Tunaweka mbele usalama wa watu wetu, wateja na jamii.</li><li><strong>Ubora wa Juu</strong> — Tunatafuta viwango vya juu katika kila tunachotoa.</li><li><strong>Athari ya Ndani</strong> — Tunaamini kuunda thamani kupitia uzalishaji wa ndani, ujuzi, ajira na maendeleo.</li></ul>'
)
ON DUPLICATE KEY UPDATE
    title_en = VALUES(title_en),
    title_sw = VALUES(title_sw),
    summary_en = VALUES(summary_en),
    summary_sw = VALUES(summary_sw),
    content_en = VALUES(content_en),
    content_sw = VALUES(content_sw);

UPDATE pages SET
    title_en = 'Contact Musumba Steel Tanzania Limited',
    title_sw = 'Wasiliana na Musumba Steel Tanzania Limited',
    summary_en = 'Factory and corporate offices in Kahama. Mon–Fri 7:30–17:30 · Sat 7:30–13:30.',
    summary_sw = 'Ofisi za kiwanda na kampuni Kahama. Jtatu–Ijmaa 7:30–17:30 · Jumamosi 7:30–13:30.',
    content_en = '<p><strong>Emails:</strong> info@musumba-steel.com (administration) · sales@musumba-steel.com · marketing@musumba-steel.com</p><p><strong>Factory:</strong> +255 781 502 260 · <strong>Corporate / Customer Care:</strong> +255 761 037 271</p><p><strong>Hours:</strong> Monday–Friday 7:30–17:30 · Saturday 7:30–13:30</p>',
    content_sw = '<p><strong>Barua pepe:</strong> info@musumba-steel.com · sales@musumba-steel.com · marketing@musumba-steel.com</p><p><strong>Kiwanda:</strong> +255 781 502 260 · <strong>Kampuni / Huduma kwa Wateja:</strong> +255 761 037 271</p><p><strong>Saa:</strong> Jumatatu–Ijumaa 7:30–17:30 · Jumamosi 7:30–13:30</p>'
WHERE slug = 'contact-us';

-- Remove personal sales / Albert credentials; keep company desks only
DELETE FROM contacts;

INSERT INTO contacts (department, name, position, email, phone, priority) VALUES
('general-management', 'Corporate / Customer Care', 'Kahama Corporate Office', 'info@musumba-steel.com', '+255 761 037 271', 1),
('sales-management', 'Sales Desk', 'Sales', 'sales@musumba-steel.com', '+255 761 037 271', 1),
('sales-management', 'Factory Desk', 'Factory Office', 'info@musumba-steel.com', '+255 781 502 260', 2),
('sales-management', 'Marketing Desk', 'Marketing', 'marketing@musumba-steel.com', '+255 761 037 271', 3);

-- Content refresh from COMPANY PROFILE + Website Improvement sheet (2026)
-- Run: mysql -u root musumbasteeltz < schema/update_content_2026.sql

USE musumbasteeltz;

UPDATE pages SET
    title_en = 'Welcome to Musumba Steel',
    title_sw = 'Karibu Musumba Steel',
    summary_en = 'Precision steel for East Africa — Kahama manufacturing plant.',
    summary_sw = 'Chuma sahihi kwa Afrika Mashariki — kiwanda cha Kahama.',
    content_en = '<p>Musumba Steel (Tanzania) Ltd is a private company located in Kahama, Shinyanga Region, focusing on steel manufacturing for the Tanzanian construction market. Registered in 2022 under the Tanzania Investment Center (TIC) and led by Francois Uwiragiye and family, the company is establishing a steel processing plant with an initial annual capacity of <strong>33,000 metric tons</strong>.</p><p>Our product lines include iron sheets, tubes and round pipes, nails, wire products and high-strength plates — supplied from Plot 71, Chapulwa Industrial Area, Mwendakulima.</p>',
    content_sw = '<p>Musumba Steel (Tanzania) Ltd ni kampuni binafsi iliyoko Kahama, Mkoa wa Shinyanga, inayojikita kwenye utengenezaji wa chuma kwa soko la ujenzi Tanzania. Ilisajiliwa mwaka 2022 chini ya TIC na inaongozwa na Francois Uwiragiye na familia. Kiwanda chetu kinalenga uwezo wa awali wa <strong>tani 33,000</strong> kwa mwaka.</p><p>Bidhaa zetu zinajumuisha bati, mabomba, misumari, waya na sahani za nguvu — kutoka Kiwanja 71, Eneo la Viwanda Chapulwa, Mwendakulima.</p>'
WHERE slug = 'welcome';

UPDATE pages SET
    title_en = 'About Musumba Steel',
    title_sw = 'Kuhusu Musumba Steel',
    summary_en = 'Steel manufacturer based in Kahama, Shinyanga, Tanzania.',
    summary_sw = 'Mtengenezaji wa chuma aliye Kahama, Shinyanga, Tanzania.',
    content_en = '<p>Musumba Steel (Tanzania) Ltd serves the growing demand for iron and steel products in Tanzania. Our Kahama plant benefits from strong power supply and road links to Dar es Salaam (999 km), Dodoma (537 km), Mwanza (255 km) and Shinyanga (109 km).</p><p><strong>Factory:</strong> Plot No. 71, Block A, Chapulwa Industrial Area, P.O. Box 612, Mwendakulima Village, near Kahama Airport.<br><strong>Corporate office:</strong> 2nd Floor, Mongo Complex, Isaka Road / Phantom St, Kahama Municipality.<br><strong>TIN:</strong> 157-898-175 · <strong>VAT:</strong> 40-315814-F</p>',
    content_sw = '<p>Musumba Steel (Tanzania) Ltd inakidhi mahitaji yanayoongezeka ya chuma nchini Tanzania. Kiwanda chetu Kahama kina umeme thabiti na miunganisho ya barabara kwenda Dar es Salaam (km 999), Dodoma (km 537), Mwanza (km 255) na Shinyanga (km 109).</p><p><strong>Kiwanda:</strong> Kiwanja Na. 71, Block A, Eneo la Viwanda Chapulwa, S.L.P. 612, Mwendakulima.<br><strong>Ofisi ya kampuni:</strong> Ghorofa ya 2, Mongo Complex, Barabara ya Isaka, Kahama.<br><strong>TIN:</strong> 157-898-175 · <strong>VAT:</strong> 40-315814-F</p>'
WHERE slug = 'about';

UPDATE pages SET
    title_en = 'Parent Company — Musumba Holding',
    title_sw = 'Kampuni Mama — Musumba Holding',
    summary_en = 'Part of Musumba Holding, led by Chairman & CEO Francois Uwiragiye (Bujumbura, Burundi).',
    summary_sw = 'Sehemu ya Musumba Holding, chini ya Mwenyekiti na Mkurugenzi Mkuu Francois Uwiragiye (Bujumbura, Burundi).',
    content_en = '<p>Musumba Steel Tanzania belongs to <strong>Musumba Holding</strong>. Group companies include:</p><ul><li>Musumba Steel — Burundi &amp; Tanzania</li><li>Musumba Cargo — Burundi</li><li>New Logistics Co. (NLCOM) — Burundi</li><li>Musumba Petroleum — Burundi</li><li>Fabrimetal — Burundi (owned by MMD)</li><li>Eden Garden Resort — Burundi</li><li>Uwiragiye Foundation — Burundi</li><li>SOTB (Mining) — Burundi</li><li>Bigger Steel Limited — Kenya</li><li>Bigger Global Services — Kenya &amp; Tanzania</li></ul>',
    content_sw = '<p>Musumba Steel Tanzania ni sehemu ya <strong>Musumba Holding</strong>. Kampuni za kikundi zinajumuisha:</p><ul><li>Musumba Steel — Burundi &amp; Tanzania</li><li>Musumba Cargo — Burundi</li><li>New Logistics Co. (NLCOM) — Burundi</li><li>Musumba Petroleum — Burundi</li><li>Fabrimetal — Burundi</li><li>Eden Garden Resort — Burundi</li><li>Uwiragiye Foundation — Burundi</li><li>SOTB (Mining) — Burundi</li><li>Bigger Steel Limited — Kenya</li><li>Bigger Global Services — Kenya &amp; Tanzania</li></ul>'
WHERE slug = 'parent-company';

UPDATE pages SET
    title_en = 'Our History',
    title_sw = 'Historia Yetu',
    summary_en = 'Registered in 2022 under the Tanzania Investment Center (TIC).',
    summary_sw = 'Ilisajiliwa mwaka 2022 chini ya Tanzania Investment Center (TIC).',
    content_en = '<p>Musumba Steel (Tanzania) Ltd was registered in <strong>2022</strong> with significant authorized share capital under the Tanzania Investment Center. Entirely owned by Burundian nationals and led by Francois Uwiragiye and family, the company was founded to supply quality steel to Tanzania’s construction boom from a strategically located Kahama plant.</p>',
    content_sw = '<p>Musumba Steel (Tanzania) Ltd ilisajiliwa mwaka <strong>2022</strong> chini ya TIC. Inamilikiwa na raia wa Burundi na inaongozwa na Francois Uwiragiye na familia, ili kutoa chuma bora kwa boom ya ujenzi Tanzania kutoka kiwanda cha Kahama.</p>'
WHERE slug = 'historic';

UPDATE pages SET
    title_en = 'Management',
    title_sw = 'Uongozi',
    summary_en = 'Chairman & CEO: Mr. Francois Uwiragiye — Musumba Holding, Bujumbura.',
    summary_sw = 'Mwenyekiti na Mkurugenzi Mkuu: Bw. Francois Uwiragiye — Musumba Holding, Bujumbura.',
    content_en = '<p>Mr. <strong>Francois Uwiragiye</strong>, Chairman and CEO of Musumba Holding (Bujumbura, Burundi), leads the entrepreneurial vision behind Musumba Steel Tanzania and sister companies across the region.</p>',
    content_sw = '<p>Bw. <strong>Francois Uwiragiye</strong>, Mwenyekiti na Mkurugenzi Mkuu wa Musumba Holding (Bujumbura, Burundi), anaongoza maono ya biashara ya Musumba Steel Tanzania na kampuni dada katika eneo hilo.</p>'
WHERE slug = 'management';

UPDATE pages SET
    content_en = '<p>Our vision is to become the most trusted East African source for engineered steel solutions — advancing Africa’s steel independence.</p>',
    content_sw = '<p>Dira yetu ni kuwa chanzo kinachoaminika cha suluhisho za chuma Afrika Mashariki — kukuza uhuru wa chuma Afrika.</p>',
    summary_en = 'Advance Africa''s steel independence.',
    summary_sw = 'Kuendeleza uhuru wa chuma Afrika.'
WHERE slug = 'vision';

UPDATE pages SET
    content_en = '<p>Deliver quality, affordable steel with Tanzanian talent. We invest in people, technology and sustainability to deliver value to every stakeholder.</p>',
    content_sw = '<p>Kutoa chuma bora na nafuu kwa vipaji vya Tanzania. Tunawekeza katika watu, teknolojia na uendelevu ili kuleta thamani kwa kila mdau.</p>',
    summary_en = 'Deliver quality, affordable steel with Tanzanian talent.',
    summary_sw = 'Kutoa chuma bora na nafuu kwa vipaji vya Tanzania.'
WHERE slug = 'mission';

UPDATE pages SET
    title_en = 'Roofing Portfolio',
    title_sw = 'Bidhaa za Paa',
    summary_en = 'Galvanized, AZ and colour-coated roofing lines with nationwide distribution.',
    summary_sw = 'Mistari ya bati za galvanized, AZ na rangi yenye usambazaji nchi nzima.',
    content_en = '<p>Our roofing line includes corrugated, box profile and concealed-fix systems. Brands and profiles include Alu-Zinc corrugated sheets, pre-painted corrugated sheets, IT4 box profile (colour and non-colour), Versa Tile, Musumba Rangi Max / Rangi Max+, crimp/curve sheets, plain &amp; tile ridges and flashing.</p><p>Gauges typically G32–G28; lengths 2.50 m and 3.00 m (special lengths on request). Colour options include Traffic Black, Leaf Green, Rose, Oxide Red and Sky Blue.</p>',
    content_sw = '<p>Paa zetu zinajumuisha bati za corrugated, box profile na mifumo ya concealed-fix. Bidhaa: bati za Alu-Zinc, zilizopakwa rangi, IT4 box profile, Versa Tile, Musumba Rangi Max / Rangi Max+, crimp/curve, ridge na flashing.</p><p>Vipimo vya kawaida G32–G28; urefu 2.50 m na 3.00 m (urefu maalum kwa ombi). Rangi: Traffic Black, Leaf Green, Rose, Oxide Red na Sky Blue.</p>'
WHERE slug = 'roofings';

UPDATE pages SET
    title_en = 'Construction Materials',
    title_sw = 'Vifaa vya Ujenzi',
    summary_en = 'Hollow sections, pipes, MS plates and nails — tested for construction use.',
    summary_sw = 'Hollow sections, mabomba, sahani za MS na misumari — kwa matumizi ya ujenzi.',
    content_en = '<p>We supply reinforcement accessories, hollow sections, round and square pipes, MS plates and common mild steel nails. Hollow section production unit is operating in Kahama (TZS 1685:2020 / EAS 134:2019, grade SPHT1).</p><p>MS plates from 0.80 mm to 3.00 mm, cut to 2400 × 1200 mm (8 ft × 4 ft), in accordance with JIS G 3131-2018. Nails available from 1" to 6" in 50 kg bags.</p>',
    content_sw = '<p>Tunatoa hollow sections, mabomba ya pande zote na mraba, sahani za MS na misumari. Kitengo cha hollow sections kinafanya kazi Kahama (TZS 1685:2020 / EAS 134:2019, grade SPHT1).</p><p>Sahani za MS kutoka 0.80 mm hadi 3.00 mm, 2400 × 1200 mm, kulingana na JIS G 3131-2018. Misumari kutoka inchi 1 hadi 6 katika mifuko ya kilo 50.</p>'
WHERE slug = 'construction-materials';

UPDATE pages SET
    title_en = 'Our Products',
    title_sw = 'Bidhaa Zetu',
    summary_en = 'Roofing systems and construction steel from the Kahama plant.',
    summary_sw = 'Mifumo ya paa na chuma cha ujenzi kutoka kiwanda cha Kahama.',
    content_en = '<p>Browse our <a href="?page=roofings">Roofing Portfolio</a> and <a href="?page=construction-materials">Construction Materials</a> — manufactured for builders across East Africa.</p>',
    content_sw = '<p>Angalia <a href="?page=roofings">Bidhaa za Paa</a> na <a href="?page=construction-materials">Vifaa vya Ujenzi</a> — vilivyotengenezwa kwa wajenzi wa Afrika Mashariki.</p>'
WHERE slug = 'services';

UPDATE pages SET
    title_en = 'Contact Musumba Steel',
    title_sw = 'Wasiliana na Musumba Steel',
    summary_en = 'Factory and corporate offices in Kahama — sales ready to assist.',
    summary_sw = 'Ofisi za kiwanda na kampuni Kahama — mauzo yako tayari kukusaidia.',
    content_en = '<p>Email <a href="mailto:info@musumba-steel.com">info@musumba-steel.com</a> or <a href="mailto:sales@musumba-steel.com">sales@musumba-steel.com</a>. Landline +255 761 037 271 · Mobile +255 781 502 260.</p>',
    content_sw = '<p>Barua pepe <a href="mailto:info@musumba-steel.com">info@musumba-steel.com</a> au <a href="mailto:sales@musumba-steel.com">sales@musumba-steel.com</a>. Simu +255 761 037 271 · Simu ya mkononi +255 781 502 260.</p>'
WHERE slug = 'contact-us';

DELETE FROM services;

INSERT INTO services (category, name_en, name_sw, description_en, description_sw) VALUES
('roofings', 'Alu-Zinc Corrugated Sheet (Plain)', 'Bati za Alu-Zinc (Bila Rangi)', 'High-tensile AZ corrugated iron sheets with anti-corrosion coating. Gauges G32–G28; 2.5 m & 3.0 m (special lengths available).', 'Bati za AZ zenye kinga dhidi ya kutu. Vipimo G32–G28; 2.5 m na 3.0 m (urefu maalum unapatikana).'),
('roofings', 'Pre-Painted Corrugated Sheet', 'Bati Zilizopakwa Rangi', 'Factory colour-coated corrugated sheets with UV shield. Colours: Traffic Black, Leaf Green, Rose, Oxide Red, Sky Blue.', 'Bati zilizopakwa rangi kiwandani zenye kinga ya UV. Rangi: nyeusi, kijani, rose, nyekundu, bluu.'),
('roofings', 'IT4 Box Profile Sheet (Colour)', 'IT4 Box Profile (Rangi)', 'Colour-coated IT4 box profile sheets for modern roofing and cladding.', 'Profaili za IT4 box zilizopakwa rangi kwa paa na kuta za kisasa.'),
('roofings', 'IT4 Box Profile Sheet (Non-Colour)', 'IT4 Box Profile (Bila Rangi)', 'Unpainted IT4 box profile sheets for durable structural roofing.', 'Profaili za IT4 box bila rangi kwa paa thabiti.'),
('roofings', 'Musumba Rangi Max / Rangi Max+', 'Musumba Rangi Max / Rangi Max+', 'Rangi Max 840 mm / 24 mm cover and Rangi Max+ 780 mm / 34 mm — precision profiled roofing systems.', 'Rangi Max 840 mm / 24 mm na Rangi Max+ 780 mm / 34 mm — mifumo sahihi ya paa.'),
('roofings', 'Versa Tile', 'Versa Tile', 'Tile-effect roofing profile for residential and commercial projects.', 'Profaili ya paa yenye muonekano wa tiles kwa majengo ya makazi na biashara.'),
('roofings', 'Crimp / Curve Sheet & Ridges', 'Crimp / Curve na Ridge', 'Crimp/curve sheets, plain ridge and tile ridge accessories. G30–G28.', 'Karatasi za crimp/curve, plain ridge na tile ridge. G30–G28.'),
('roofings', 'Flashing', 'Flashing', 'Roof flashing in gauge 30 (0.25 mm) and gauge 28 (0.32 mm); 2.5 m & 3.0 m lengths.', 'Flashing ya paa gauge 30 (0.25 mm) na 28 (0.32 mm); urefu 2.5 m na 3.0 m.'),
('construction-materials', 'Hollow Sections', 'Hollow Sections', 'Square, rectangular and round hollow sections produced in Kahama. Sizes from 16×16 to 50×50 mm, 40×20–60×40 mm, rounds 20–76 mm. TZS 1685:2020 / EAS 134:2019.', 'Hollow sections za mraba, mstatili na pande zote zinazotengenezwa Kahama. Vipimo kuanzia 16×16 hadi 76 mm. TZS 1685:2020 / EAS 134:2019.'),
('construction-materials', 'Square Pipes', 'Mabomba ya Mraba', 'Precision-rolled square structural sections for industrial builds.', 'Sehemu za muundo za mraba kwa ujenzi wa viwanda.'),
('construction-materials', 'Round Pipes', 'Mabomba ya Pande Zote', 'Round pipes for furniture fabrication, cabinet work and construction.', 'Mabomba ya pande zote kwa samani, kabati na ujenzi.'),
('construction-materials', 'MS Plates', 'Sahani za MS', 'Strong MS plates 0.80–3.00 mm, cut to 2400×1200 mm (8×4 ft). JIS G 3131-2018, grade SPHT1.', 'Sahani za MS 0.80–3.00 mm, 2400×1200 mm. JIS G 3131-2018, grade SPHT1.'),
('construction-materials', 'Common Mild Steel Nails', 'Misumari ya Chuma', 'Common nails from 1" to 6" (also 1.5"–5"), packed in 50 kg bags. For wood construction, roofing and crafts.', 'Misumari kutoka inchi 1 hadi 6, mifuko ya kilo 50. Kwa ujenzi wa mbao, paa na ufundi.');

DELETE FROM contacts;

INSERT INTO contacts (department, name, position, email, phone, priority) VALUES
('general-management', 'Francois Uwiragiye', 'Chairman & CEO — Musumba Holding', 'info@musumba-steel.com', '+255 761 037 271', 1),
('sales-management', 'Corporate Sales Office', 'Sales — Kahama', 'sales@musumba-steel.com', '+255 761 037 271', 1),
('sales-management', 'Kahama Factory Board Line', 'Factory Office', 'info@musumba-steel.com', '+255 781 502 260', 2);

<?php
/**
 * Defaults of schema v5 that v6 replaced with texts aimed at "restaurant audit". A value that still
 * equals its v5 default was never edited in the admin, so the v6 migration may replace it (see db.php).
 */

declare(strict_types=1);
defined('CCS') || exit;

return [
    'texts' => [
        'hero.eyebrow' => ['az' => 'İaşə və restoran sektoru üçün xərclərə nəzarət', 'ru' => 'Контроль затрат для ресторанов и общепита', 'en' => 'Cost control for restaurants and food service'],
        'hero.title' => ['az' => 'Restoranınızın *hər qəpiyi* nəzarət altında', 'ru' => '*Каждый манат* вашего ресторана под контролем', 'en' => '*Every manat* of your restaurant under control'],
        'hero.lead' => ['az' => 'Restoran biznesində uğurun sirri təkcə dadlı yemək deyil. Hər bir xammalın, resursun və maliyyə axınının dəqiq uçotunu qurur, gizli israfları və boşluqları dərhal aşkarlayırıq.', 'ru' => 'Успех ресторана — это не только вкусная еда. Мы выстраиваем точный учёт каждого продукта, ресурса и денежного потока и сразу находим скрытые потери и слабые места.', 'en' => 'A successful restaurant takes more than great food. We set up precise accounting for every ingredient, resource and cash flow, and quickly uncover hidden waste and gaps.'],
        'hero.cta' => ['az' => 'Konsultasiya al', 'ru' => 'Получить консультацию', 'en' => 'Get a consultation'],
        'about.p1' => ['az' => '“Cost Control” şirkəti post-pandemiya dövrünün çətin və dinamik reallıqları fonunda — 2021-ci ilin sonlarından etibarən iaşə və restoran sektorunda peşəkar idarəetmə boşluqlarını doldurmaq məqsədilə təsis edilmişdir.', 'ru' => 'Компания «Cost Control» основана в конце 2021 года, на фоне непростых и быстро меняющихся реалий постпандемийного времени, чтобы восполнить нехватку профессионального управления в ресторанной сфере и общепите.', 'en' => 'Cost Control was founded in late 2021, amid the challenging and fast-changing realities of the post-pandemic period, to fill the gap in professional management across the restaurant and food service sector.'],
        'about.p2' => ['az' => 'Restoran biznesində uğurun sirri təkcə dadlı yemək deyil, hər bir xammalın, resursun və maliyyə axınının dəqiq uçotundan asılıdır. Cost Control mexanizmlərimiz sayəsində biznesdəki **gizli israflar, artan maya dəyəri** və **ehtiyatların idarə olunmasındakı boşluqlar** dərhal aşkarlanır.', 'ru' => 'Успех ресторана зависит не только от вкусной еды, но и от точного учёта каждого продукта, ресурса и денежного потока. Наши механизмы cost control сразу выявляют **скрытые потери, рост себестоимости** и **пробелы в управлении запасами**.', 'en' => 'Restaurant success depends not only on great food but on precise accounting of every ingredient, resource and cash flow. Our cost control mechanisms quickly reveal **hidden waste, rising food costs** and **gaps in inventory management**.'],
        'services.eyebrow' => ['az' => 'Xidmətlərimiz', 'ru' => 'Наши услуги', 'en' => 'Our services'],
        'services.lead' => ['az' => 'Maliyyə uçotundan anbar sayımlarına qədər restoranın bütün xərc zəncirini şəffaf və idarə olunan edirik.', 'ru' => 'От финансового учёта до инвентаризации склада — делаем всю цепочку затрат ресторана прозрачной и управляемой.', 'en' => 'From financial accounting to stock counts, we make the whole cost chain of your restaurant transparent and manageable.'],
        'clients.lead' => ['az' => 'Restoran, kafe, lounge və otellər — müxtəlif formatlı 18 brendlə əməkdaşlıq etmişik.', 'ru' => 'Рестораны, кафе, лаунжи и отели — мы работали с 18 брендами разных форматов.', 'en' => 'Restaurants, cafés, lounges and hotels — we have worked with 18 brands of different formats.'],
        'faq.title' => ['az' => 'Tez-tez verilən suallar', 'ru' => 'Частые вопросы', 'en' => 'Frequently asked questions'],
        'contact.lead' => ['az' => 'Müraciətinizi qoyun — mütəxəssisimiz sizinlə əlaqə saxlayacaq və restoranınız üçün uyğun həlli müzakirə edəcək.', 'ru' => 'Оставьте заявку — наш специалист свяжется с вами и обсудит подходящее решение для вашего ресторана.', 'en' => 'Leave a request and our specialist will contact you to discuss the right solution for your restaurant.'],
        'contact.form_title' => ['az' => 'Konsultasiya üçün müraciət', 'ru' => 'Заявка на консультацию', 'en' => 'Request a consultation'],
        'footer.text' => ['az' => 'İaşə və restoran sektorunda peşəkar cost control xidmətləri. 2021-ci ildən bəri.', 'ru' => 'Профессиональный cost control для ресторанов и общепита. С 2021 года.', 'en' => 'Professional cost control services for restaurants and food service. Since 2021.'],
    ],
    // [SEO title, SEO description]
    'pages' => [
        'home' => [
            ['az' => 'Restoranlar üçün xərclərə nəzarət — Cost Control Services', 'ru' => 'Контроль затрат для ресторанов — Cost Control Services', 'en' => 'Cost control for restaurants — Cost Control Services'],
            ['az' => 'Restoran və iaşə biznesi üçün xərclərə nəzarət: maliyyə və P&L, maya dəyəri, texnoloji kartlar, satınalma, inventar və daxili nəzarət. Bakı.', 'ru' => 'Контроль затрат для ресторанов и общепита: финансы и P&L, себестоимость, технологические карты, закупки, инвентаризация и внутренний контроль. Баку.', 'en' => 'Cost control for restaurants and food service: finance and P&L, food costing, recipe cards, purchasing, inventory and internal control. Baku.'],
        ],
        'about' => [
            ['az' => 'Haqqımızda — Cost Control Services', 'ru' => 'О компании — Cost Control Services', 'en' => 'About us — Cost Control Services'],
            ['az' => 'Cost Control 2021-ci ildən iaşə və restoran sektorunda xərclərə nəzarət, maya dəyəri və maliyyə şəffaflığı üzrə peşəkar xidmət göstərir.', 'ru' => 'С 2021 года Cost Control помогает ресторанам и заведениям общепита контролировать затраты, себестоимость и финансы.', 'en' => 'Since 2021, Cost Control has provided professional cost control, food costing and financial transparency services to restaurants and food service businesses.'],
        ],
        'services' => [
            ['az' => 'Xərclərə nəzarət xidmətləri — Cost Control Services', 'ru' => 'Услуги по контролю затрат — Cost Control Services', 'en' => 'Cost control services — Cost Control Services'],
            ['az' => 'Maliyyə və P&L, xərc analizi, xərclərin optimallaşdırılması, maya dəyəri və texnoloji kartlar, satınalma, inventar və daxili nəzarət — restoranlar üçün.', 'ru' => 'Финансы и P&L, анализ и оптимизация затрат, себестоимость и технологические карты, закупки, инвентаризация и внутренний контроль — для ресторанов.', 'en' => 'Finance and P&L, cost analysis and optimization, food costing and recipe cards, purchasing, inventory and internal control — for restaurants.'],
        ],
        'clients' => [
            ['az' => 'Müştərilərimiz — Cost Control Services', 'ru' => 'Наши клиенты — Cost Control Services', 'en' => 'Our clients — Cost Control Services'],
            ['az' => 'Restoran, kafe, lounge və otel brendləri: Cost Control Services ilə əməkdaşlıq edən şirkətlər.', 'ru' => 'Рестораны, кафе, лаунжи и отели, которые работают с Cost Control Services.', 'en' => 'Restaurants, cafés, lounges and hotels that work with Cost Control Services.'],
        ],
        'faq' => [
            ['az' => 'Tez-tez verilən suallar — Cost Control Services', 'ru' => 'Частые вопросы — Cost Control Services', 'en' => 'FAQ — Cost Control Services'],
            ['az' => 'Restoranlar üçün xərclərə nəzarət xidmətləri haqqında suallar və cavablar: kimlərlə işləyirik, hansı xidmətləri göstəririk, necə əlaqə saxlamaq olar.', 'ru' => 'Ответы на вопросы о контроле затрат для ресторанов: с кем мы работаем, какие услуги оказываем и как с нами связаться.', 'en' => 'Answers about cost control for restaurants: who we work with, what services we offer and how to get in touch.'],
        ],
        'contact' => [
            ['az' => 'Əlaqə — Cost Control Services, Bakı', 'ru' => 'Контакты — Cost Control Services, Баку', 'en' => 'Contact — Cost Control Services, Baku'],
            ['az' => 'Cost Control Services ilə əlaqə: +994 55 855 61 52, WhatsApp və müraciət forması. Bakı, Nizami rayonu, Heydər Əliyev prospekti 5.', 'ru' => 'Свяжитесь с Cost Control Services: +994 55 855 61 52, WhatsApp и форма заявки. Баку, Низаминский район, проспект Гейдара Алиева, 5.', 'en' => 'Contact Cost Control Services: +994 55 855 61 52, WhatsApp and request form. 5 Heydar Aliyev Avenue, Nizami district, Baku.'],
        ],
    ],
    // AZ questions of the FAQ seed; the other seed questions are new in v6
    'faqs' => [
        'Hansı müəssisələrlə işləyirsiniz?',
        'Hansı xidmətləri göstərirsiniz?',
        'Yeni restoran açırıq — necə kömək edə bilərsiniz?',
        'Azərbaycandan kənarda işləyirsiniz?',
        'Sizinlə necə əlaqə saxlaya bilərəm?',
    ],
];

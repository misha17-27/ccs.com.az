<?php
/**
 * What the admin can edit: site texts (per content language), pages and settings,
 * plus the initial content of the site (design V1) in AZ, RU and EN.
 *
 * Texts may use light markup, see rich(): *word* — accent colour, **word** — bold, line break — new line.
 */

declare(strict_types=1);
defined('CCS') || exit;

/** Text groups shown as tabs on the "Texts" page. SEO is edited per page (see PAGES). */
const TEXT_GROUPS = [
    'hero' => ['az' => 'Əsas ekran', 'ru' => 'Главный экран'],
    'facts' => ['az' => 'Rəqəmlər', 'ru' => 'Цифры'],
    'about' => ['az' => 'Haqqımızda', 'ru' => 'О нас'],
    'services' => ['az' => 'Xidmətlər bölməsi', 'ru' => 'Блок услуг'],
    'calc' => ['az' => 'Kalkulyator', 'ru' => 'Калькулятор'],
    'clients' => ['az' => 'Müştərilər', 'ru' => 'Клиенты'],
    'experience' => ['az' => 'Təcrübə', 'ru' => 'Опыт'],
    'faq' => ['az' => 'Suallar bölməsi', 'ru' => 'Блок вопросов'],
    'contact' => ['az' => 'Əlaqə bölməsi', 'ru' => 'Блок контактов'],
    'tiktok' => ['az' => 'TikTok', 'ru' => 'TikTok'],
    'instagram' => ['az' => 'Instagram', 'ru' => 'Instagram'],
    'footer' => ['az' => 'Footer', 'ru' => 'Подвал'],
];

/** key => [group, label az, label ru, 'line'|'area', default ['az' => …, 'ru' => …, 'en' => …]] */
const TEXT_FIELDS = [
    'hero.eyebrow' => ['hero', 'Kiçik başlıq', 'Надзаголовок', 'line', [
        'az' => 'İaşə və restoran sektoru üçün xərclərə nəzarət',
        'ru' => 'Контроль затрат для ресторанов и общепита',
        'en' => 'Cost control for restaurants and food service',
    ]],
    'hero.title' => ['hero', 'Başlıq', 'Заголовок', 'line', [
        'az' => 'Restoranınızın *hər qəpiyi* nəzarət altında',
        'ru' => '*Каждый манат* вашего ресторана под контролем',
        'en' => '*Every manat* of your restaurant under control',
    ]],
    'hero.lead' => ['hero', 'Mətn', 'Текст', 'area', [
        'az' => 'Restoran biznesində uğurun sirri təkcə dadlı yemək deyil. Hər bir xammalın, resursun və maliyyə axınının dəqiq uçotunu qurur, gizli israfları və boşluqları dərhal aşkarlayırıq.',
        'ru' => 'Успех ресторана — это не только вкусная еда. Мы выстраиваем точный учёт каждого продукта, ресурса и денежного потока и сразу находим скрытые потери и слабые места.',
        'en' => 'A successful restaurant takes more than great food. We set up precise accounting for every ingredient, resource and cash flow, and quickly uncover hidden waste and gaps.',
    ]],
    'hero.cta' => ['hero', 'Düymə mətni', 'Текст кнопки', 'line', [
        'az' => 'Konsultasiya al',
        'ru' => 'Получить консультацию',
        'en' => 'Get a consultation',
    ]],
    'hero.card_title' => ['hero', 'Kartın başlığı', 'Заголовок карточки', 'line', [
        'az' => 'Restoranınız üçün',
        'ru' => 'Для вашего ресторана',
        'en' => 'For your restaurant',
    ]],
    'hero.card_items' => ['hero', 'Kartdakı siyahı (hər sətir — bir bənd)', 'Список в карточке (каждая строка — пункт)', 'area', [
        'az' => "Texnoloji kartlar\nMaya dəyəri\nAnbar sayımları\nAylıq P&L hesabatları",
        'ru' => "Технологические карты\nСебестоимость\nИнвентаризация склада\nЕжемесячные отчёты P&L",
        'en' => "Recipe cards\nFood costing\nStock counts\nMonthly P&L reports",
    ]],

    'facts.1_num' => ['facts', 'Rəqəm 1', 'Цифра 1', 'line', ['az' => '2021', 'ru' => '2021', 'en' => '2021']],
    'facts.1_label' => ['facts', 'Rəqəm 1 — izah', 'Цифра 1 — подпись', 'line', [
        'az' => 'ilin sonundan iaşə sektorunda',
        'ru' => 'год, когда мы начали работать в общепите',
        'en' => 'the year we started in food service',
    ]],
    'facts.2_num' => ['facts', 'Rəqəm 2', 'Цифра 2', 'line', ['az' => '18', 'ru' => '18', 'en' => '18']],
    'facts.2_label' => ['facts', 'Rəqəm 2 — izah', 'Цифра 2 — подпись', 'line', [
        'az' => 'tərəfdaş restoran və brend',
        'ru' => 'ресторанов и брендов-партнёров',
        'en' => 'partner restaurants and brands',
    ]],
    'facts.3_num' => ['facts', 'Rəqəm 3', 'Цифра 3', 'line', ['az' => '6', 'ru' => '6', 'en' => '6']],
    'facts.3_label' => ['facts', 'Rəqəm 3 — izah', 'Цифра 3 — подпись', 'line', [
        'az' => 'xidmət istiqaməti',
        'ru' => 'направлений услуг',
        'en' => 'service areas',
    ]],
    'facts.4_num' => ['facts', 'Rəqəm 4', 'Цифра 4', 'line', ['az' => '200+', 'ru' => '200+', 'en' => '200+']],
    'facts.4_label' => ['facts', 'Rəqəm 4 — izah', 'Цифра 4 — подпись', 'line', [
        'az' => 'ölkənin qatıldığı çempionatda qida nəzarəti',
        'ru' => 'стран на чемпионате, где мы контролировали питание',
        'en' => 'countries at the championship where we ran food control',
    ]],

    'about.eyebrow' => ['about', 'Kiçik başlıq', 'Надзаголовок', 'line', ['az' => 'Haqqımızda', 'ru' => 'О нас', 'en' => 'About us']],
    'about.title' => ['about', 'Başlıq', 'Заголовок', 'line', ['az' => 'Biz kimik?', 'ru' => 'Кто мы?', 'en' => 'Who we are']],
    'about.since' => ['about', 'Başlığın altında', 'Под заголовком', 'line', [
        'az' => '2021-ci ilin sonundan bəri',
        'ru' => 'С конца 2021 года',
        'en' => 'Since late 2021',
    ]],
    'about.p1' => ['about', 'Abzas 1', 'Абзац 1', 'area', [
        'az' => '“Cost Control” şirkəti post-pandemiya dövrünün çətin və dinamik reallıqları fonunda — 2021-ci ilin sonlarından etibarən iaşə və restoran sektorunda peşəkar idarəetmə boşluqlarını doldurmaq məqsədilə təsis edilmişdir.',
        'ru' => 'Компания «Cost Control» основана в конце 2021 года, на фоне непростых и быстро меняющихся реалий постпандемийного времени, чтобы восполнить нехватку профессионального управления в ресторанной сфере и общепите.',
        'en' => 'Cost Control was founded in late 2021, amid the challenging and fast-changing realities of the post-pandemic period, to fill the gap in professional management across the restaurant and food service sector.',
    ]],
    'about.p2' => ['about', 'Abzas 2', 'Абзац 2', 'area', [
        'az' => 'Restoran biznesində uğurun sirri təkcə dadlı yemək deyil, hər bir xammalın, resursun və maliyyə axınının dəqiq uçotundan asılıdır. Cost Control mexanizmlərimiz sayəsində biznesdəki **gizli israflar, artan maya dəyəri** və **ehtiyatların idarə olunmasındakı boşluqlar** dərhal aşkarlanır.',
        'ru' => 'Успех ресторана зависит не только от вкусной еды, но и от точного учёта каждого продукта, ресурса и денежного потока. Наши механизмы cost control сразу выявляют **скрытые потери, рост себестоимости** и **пробелы в управлении запасами**.',
        'en' => 'Restaurant success depends not only on great food but on precise accounting of every ingredient, resource and cash flow. Our cost control mechanisms quickly reveal **hidden waste, rising food costs** and **gaps in inventory management**.',
    ]],
    'about.p3' => ['about', 'Abzas 3', 'Абзац 3', 'area', [
        'az' => 'Böyük şəbəkələrin idarə olunmasında qazanılmış qabaqcıl təcrübələr və standartlar kiçik və orta ölçülü restoranlara uyğunlaşdırılır. Bu yanaşma həm yerli bazarda yeni obyektlərin sıfırdan qurulmasına münbit şərait yaradır, həm də mövcud restoranların gəlirliliyini maksimum səviyyəyə qaldırır.',
        'ru' => 'Передовые практики и стандарты, отработанные в крупных сетях, мы адаптируем для небольших и средних ресторанов. Такой подход помогает с нуля запускать новые заведения на местном рынке и выводить доходность действующих ресторанов на максимум.',
        'en' => 'We adapt best practices and standards proven in large chains to small and mid-sized restaurants. This approach helps launch new venues from scratch in the local market and pushes the profitability of existing restaurants to its maximum.',
    ]],

    'services.eyebrow' => ['services', 'Kiçik başlıq', 'Надзаголовок', 'line', ['az' => 'Xidmətlərimiz', 'ru' => 'Наши услуги', 'en' => 'Our services']],
    'services.title' => ['services', 'Başlıq', 'Заголовок', 'line', [
        'az' => 'Altı istiqamət, bir məqsəd — *gəlirli restoran*',
        'ru' => 'Шесть направлений, одна цель — *прибыльный ресторан*',
        'en' => 'Six areas, one goal — *a profitable restaurant*',
    ]],
    'services.lead' => ['services', 'Mətn', 'Текст', 'area', [
        'az' => 'Maliyyə uçotundan anbar sayımlarına qədər restoranın bütün xərc zəncirini şəffaf və idarə olunan edirik.',
        'ru' => 'От финансового учёта до инвентаризации склада — делаем всю цепочку затрат ресторана прозрачной и управляемой.',
        'en' => 'From financial accounting to stock counts, we make the whole cost chain of your restaurant transparent and manageable.',
    ]],

    'calc.eyebrow' => ['calc', 'Kiçik başlıq', 'Надзаголовок', 'line', ['az' => 'Alət', 'ru' => 'Инструмент', 'en' => 'Tool']],
    'calc.title' => ['calc', 'Başlıq', 'Заголовок', 'line', [
        'az' => 'Maya dəyəri *kalkulyatoru*',
        'ru' => 'Калькулятор *себестоимости*',
        'en' => '*Food cost* calculator',
    ]],
    'calc.lead' => ['calc', 'Mətn', 'Текст', 'area', [
        'az' => 'Bir yeməyin food cost faizini və marjasını saniyələr içində yoxlayın. Hədəf faizi özünüz təyin edin.',
        'ru' => 'Проверьте фудкост и маржу блюда за несколько секунд. Целевой процент задаёте сами.',
        'en' => 'Check the food cost percentage and margin of a dish in seconds. You set the target.',
    ]],
    'calc.note' => ['calc', 'Qeyd', 'Примечание', 'area', [
        'az' => 'Bu, ilkin təsəvvür üçün sadə hesablamadır. Hər yemək üçün dəqiq maya dəyəri texnoloji kart əsasında hesablanır — bu, «Maya dəyəri və texnoloji kartlar» xidmətimizin bir hissəsidir.',
        'ru' => 'Это простой расчёт для первого представления. Точная себестоимость каждого блюда считается по технологической карте — это часть нашей услуги «Себестоимость и технологические карты».',
        'en' => 'This is a simple estimate to give you a first idea. The exact cost of each dish is calculated from its recipe card — part of our “Food costing and recipe cards” service.',
    ]],
    'calc.cta' => ['calc', 'Düymə mətni', 'Текст кнопки', 'line', [
        'az' => 'Menyunu birlikdə hesablayaq',
        'ru' => 'Посчитаем меню вместе',
        'en' => 'Let’s cost your menu together',
    ]],

    'clients.eyebrow' => ['clients', 'Kiçik başlıq', 'Надзаголовок', 'line', ['az' => 'Müştərilərimiz', 'ru' => 'Наши клиенты', 'en' => 'Our clients']],
    'clients.title' => ['clients', 'Başlıq', 'Заголовок', 'line', [
        'az' => 'Çalışdığımız şirkətlər',
        'ru' => 'Компании, с которыми мы работаем',
        'en' => 'Companies we work with',
    ]],
    'clients.lead' => ['clients', 'Mətn', 'Текст', 'area', [
        'az' => 'Restoran, kafe, lounge və otellər — müxtəlif formatlı 18 brendlə əməkdaşlıq etmişik.',
        'ru' => 'Рестораны, кафе, лаунжи и отели — мы работали с 18 брендами разных форматов.',
        'en' => 'Restaurants, cafés, lounges and hotels — we have worked with 18 brands of different formats.',
    ]],

    'experience.eyebrow' => ['experience', 'Kiçik başlıq', 'Надзаголовок', 'line', [
        'az' => 'Beynəlxalq və xüsusi təcrübə',
        'ru' => 'Международный и особый опыт',
        'en' => 'International and special experience',
    ]],
    'experience.title' => ['experience', 'Başlıq', 'Заголовок', 'line', [
        'az' => 'Dünya çempionatından *milli mətbəx* layihələrinə',
        'ru' => 'От чемпионата мира до проектов *национальной кухни*',
        'en' => 'From a world championship to *national cuisine* projects',
    ]],
    'experience.tag1' => ['experience', 'Nişan 1', 'Метка 1', 'line', ['az' => '200+ ölkə', 'ru' => '200+ стран', 'en' => '200+ countries']],
    'experience.p1' => ['experience', 'Abzas 1', 'Абзац 1', 'area', [
        'az' => '200-dən çox ölkənin qatıldığı Taekvondo üzrə Dünya Çempionatında qida sektorunun keyfiyyətə nəzarət və tənzimləmə prosesinin birbaşa həyata keçirilməsi.',
        'ru' => 'Непосредственное ведение контроля качества и регулирования питания на Чемпионате мира по тхэквондо, в котором участвовали более 200 стран.',
        'en' => 'Directly running quality control and regulation of food services at the World Taekwondo Championships, with more than 200 participating countries.',
    ]],
    'experience.tag2' => ['experience', 'Nişan 2', 'Метка 2', 'line', ['az' => 'AZ · KZ', 'ru' => 'AZ · KZ', 'en' => 'AZ · KZ']],
    'experience.p2' => ['experience', 'Abzas 2', 'Абзац 2', 'area', [
        'az' => '**Beynəlxalq fəaliyyət:** Azərbaycanla yanaşı, Qazaxıstan kimi xarici bazarlarda da milli mətbəx layihələri ilə əməkdaşlıq.',
        'ru' => '**Международная работа:** помимо Азербайджана — сотрудничество с проектами национальной кухни на зарубежных рынках, например в Казахстане.',
        'en' => '**International work:** besides Azerbaijan, we collaborate with national cuisine projects in foreign markets such as Kazakhstan.',
    ]],

    'faq.eyebrow' => ['faq', 'Kiçik başlıq', 'Надзаголовок', 'line', ['az' => 'Suallar', 'ru' => 'Вопросы', 'en' => 'FAQ']],
    'faq.title' => ['faq', 'Başlıq', 'Заголовок', 'line', [
        'az' => 'Tez-tez verilən suallar',
        'ru' => 'Частые вопросы',
        'en' => 'Frequently asked questions',
    ]],
    'faq.lead' => ['faq', 'Mətn', 'Текст', 'area', [
        'az' => 'Cavabını tapmadığınız sual varsa, bizə birbaşa yazın — məmnuniyyətlə izah edərik.',
        'ru' => 'Не нашли ответ на свой вопрос? Напишите нам напрямую — с радостью объясним.',
        'en' => 'Didn’t find your answer? Write to us directly — we’ll be happy to explain.',
    ]],

    'contact.eyebrow' => ['contact', 'Kiçik başlıq', 'Надзаголовок', 'line', ['az' => 'Əlaqə', 'ru' => 'Контакты', 'en' => 'Contact']],
    'contact.title' => ['contact', 'Başlıq', 'Заголовок', 'line', [
        'az' => 'Restoranınızın rəqəmlərini *birlikdə yoxlayaq*',
        'ru' => 'Давайте *вместе проверим* цифры вашего ресторана',
        'en' => 'Let’s *check your restaurant’s numbers* together',
    ]],
    'contact.lead' => ['contact', 'Mətn', 'Текст', 'area', [
        'az' => 'Müraciətinizi qoyun — mütəxəssisimiz sizinlə əlaqə saxlayacaq və restoranınız üçün uyğun həlli müzakirə edəcək.',
        'ru' => 'Оставьте заявку — наш специалист свяжется с вами и обсудит подходящее решение для вашего ресторана.',
        'en' => 'Leave a request and our specialist will contact you to discuss the right solution for your restaurant.',
    ]],
    'contact.address' => ['contact', 'Ünvan', 'Адрес', 'line', [
        'az' => 'Bakı şəhəri, Nizami rayonu, Heydər Əliyev prospekti 5',
        'ru' => 'г. Баку, Низаминский район, проспект Гейдара Алиева, 5',
        'en' => '5 Heydar Aliyev Avenue, Nizami district, Baku',
    ]],
    'contact.form_title' => ['contact', 'Formanın başlığı', 'Заголовок формы', 'line', [
        'az' => 'Konsultasiya üçün müraciət',
        'ru' => 'Заявка на консультацию',
        'en' => 'Request a consultation',
    ]],
    'contact.success_title' => ['contact', 'Göndərildikdən sonra — başlıq', 'После отправки — заголовок', 'line', [
        'az' => 'Təşəkkür edirik!',
        'ru' => 'Спасибо!',
        'en' => 'Thank you!',
    ]],
    'contact.success' => ['contact', 'Göndərildikdən sonra — mətn', 'После отправки — текст', 'area', [
        'az' => 'Müraciətiniz qəbul olundu. Tezliklə sizinlə əlaqə saxlayacağıq.',
        'ru' => 'Ваша заявка принята. Мы скоро с вами свяжемся.',
        'en' => 'Your request has been received. We will contact you soon.',
    ]],

    'instagram.text' => ['instagram', 'Mətn', 'Текст', 'area', [
        'az' => 'Restoran biznesi üçün yeni videolar və praktiki məsləhətlər — mütəmadi olaraq Instagram səhifəmizdə.',
        'ru' => 'Новые видео и практические советы для ресторанного бизнеса — регулярно в нашем Instagram.',
        'en' => 'New videos and practical tips for the restaurant business — regularly on our Instagram.',
    ]],

    'tiktok.eyebrow' => ['tiktok', 'Kiçik başlıq', 'Надзаголовок', 'line', ['az' => 'TikTok', 'ru' => 'TikTok', 'en' => 'TikTok']],
    'tiktok.title' => ['tiktok', 'Başlıq', 'Заголовок', 'line', [
        'az' => 'Real layihələr — *videoda*',
        'ru' => 'Реальные проекты — *на видео*',
        'en' => 'Real projects — *on video*',
    ]],
    'tiktok.lead' => ['tiktok', 'Mətn', 'Текст', 'area', [
        'az' => 'Restoranlarda gördüyümüz işlər, xərclərə nəzarət üzrə məsləhətlər və yeni əməkdaşlıqlar — qısa videolarla TikTok səhifəmizdə.',
        'ru' => 'Наша работа в ресторанах, советы по контролю затрат и новые проекты — в коротких видео в нашем TikTok.',
        'en' => 'Our work in restaurants, cost control tips and new partnerships — in short videos on our TikTok.',
    ]],

    'footer.text' => ['footer', 'Qısa təsvir', 'Краткое описание', 'area', [
        'az' => 'İaşə və restoran sektorunda peşəkar cost control xidmətləri. 2021-ci ildən bəri.',
        'ru' => 'Профессиональный cost control для ресторанов и общепита. С 2021 года.',
        'en' => 'Professional cost control services for restaurants and food service. Since 2021.',
    ]],
];

/**
 * Site pages: fixed keys with an editable address and SEO. 'sections' are the blocks the page
 * shows (app/views/sections); the page text lives in the 'groups' of TEXT_FIELDS and in the
 * 'items' tables, and their translations make up the "Text" status in the admin.
 */
const PAGES = [
    'home' => ['az' => 'Ana səhifə', 'ru' => 'Главная', 'slug' => '', 'groups' => ['hero', 'facts', 'calc', 'tiktok', 'instagram'], 'items' => [],
        'sections' => ['hero', 'about', 'services', 'calc', 'clients', 'experience', 'tiktok', 'contact', 'instagram']],
    'about' => ['az' => 'Haqqımızda', 'ru' => 'О нас', 'slug' => 'haqqimizda', 'groups' => ['about', 'experience'], 'items' => [],
        'sections' => ['about', 'experience', 'clients', 'contact']],
    'services' => ['az' => 'Xidmətlər (siyahı)', 'ru' => 'Услуги (список)', 'slug' => 'xidmetler', 'groups' => ['services', 'calc'], 'items' => ['services'],
        'sections' => ['services', 'calc', 'contact']],
    'clients' => ['az' => 'Müştərilər', 'ru' => 'Клиенты', 'slug' => 'musteriler', 'groups' => ['clients'], 'items' => [],
        'sections' => ['clients', 'experience', 'contact']],
    'faq' => ['az' => 'Suallar', 'ru' => 'Вопросы', 'slug' => 'suallar', 'groups' => ['faq'], 'items' => ['faqs'],
        'sections' => ['faq', 'contact']],
    'contact' => ['az' => 'Əlaqə', 'ru' => 'Контакты', 'slug' => 'elaqe', 'groups' => ['contact'], 'items' => [],
        'sections' => ['contact', 'tiktok', 'instagram']],
];

/** Addresses the site itself uses, so pages cannot take them. */
const RESERVED_SLUGS = ['admin', 'api', 'app', 'assets', 'media', 'uploads', 'index', 'sitemap', 'v1', 'v2', 'v3', 'az', 'ru', 'en'];

/** Settings page fields: key => [section, label az, label ru, input type, default] */
const SETTING_FIELDS = [
    'contact_phone' => ['contacts', 'Telefon', 'Телефон', 'text', '+994 55 855 61 52'],
    'contact_whatsapp' => ['contacts', 'WhatsApp nömrəsi (yalnız rəqəmlər)', 'Номер WhatsApp (только цифры)', 'text', '994558556152'],
    'contact_email' => ['contacts', 'E-poçt', 'E-mail', 'email', 'info@ccs.com.az'],
    'contact_instagram' => ['contacts', 'Instagram linki', 'Ссылка на Instagram', 'url', 'https://www.instagram.com/costcontrol.services/'],
    'contact_tiktok' => ['contacts', 'TikTok linki', 'Ссылка на TikTok', 'url', 'https://www.tiktok.com/@costcontrol.services'],
    'contact_map' => ['contacts', 'Google Maps linki', 'Ссылка на Google Maps', 'url', ''],
    'notify_email' => ['notify', 'Yeni sorğular bu ünvana göndərilsin', 'Отправлять новые заявки на адрес', 'email', ''],
    'turnstile_site_key' => ['security', 'Turnstile Site Key', 'Turnstile Site Key', 'text', ''],
    'turnstile_secret_key' => ['security', 'Turnstile Secret Key', 'Turnstile Secret Key', 'password', ''],
];

const SETTING_SECTIONS = [
    'contacts' => ['az' => 'Əlaqə məlumatları', 'ru' => 'Контактные данные'],
    'notify' => ['az' => 'Bildirişlər', 'ru' => 'Уведомления'],
    'security' => ['az' => 'Giriş qorunması (Cloudflare Turnstile)', 'ru' => 'Защита входа (Cloudflare Turnstile)'],
];

/** Icons available for services (see icon() in the admin and the sprite in app/views/layout.php). */
const SERVICE_ICONS = ['finance', 'analysis', 'optimize', 'recipe', 'inventory', 'shield'];

/** [icon, image, title, lead, [[point title, point text], …]]; every text is ['az' => …, 'ru' => …, 'en' => …] */
const SEED_SERVICES = [
    ['finance', '/assets/img/render/wallet.jpg',
        ['az' => 'Maliyyə və P&L', 'ru' => 'Финансы и P&L', 'en' => 'Finance and P&L'],
        [
            'az' => 'Restoranın maliyyəsini şəffaf, rəqəmləri isə qərar qəbul etmək üçün aydın edirik.',
            'ru' => 'Делаем финансы ресторана прозрачными, а цифры — понятными для принятия решений.',
            'en' => 'We make your restaurant’s finances transparent and the numbers clear enough to base decisions on.',
        ], [
            [
                ['az' => 'Şəffaf maliyyə sistemi', 'ru' => 'Прозрачная финансовая система', 'en' => 'A transparent financial system'],
                [
                    'az' => 'Restoranın bütün gəlir və xərclərinin qeydiyyatı üçün etibarlı uçot mexanizminin qurulması.',
                    'ru' => 'Настройка надёжного учёта всех доходов и расходов ресторана.',
                    'en' => 'Setting up reliable accounting for all of the restaurant’s income and expenses.',
                ],
            ], [
                ['az' => 'Aylıq P&L (mənfəət və zərər) hesabatları', 'ru' => 'Ежемесячные отчёты P&L (прибыли и убытки)', 'en' => 'Monthly P&L (profit and loss) reports'],
                [
                    'az' => 'Rəhbərliyin qərar qəbul etməsi üçün hər ay üzrə maliyyə nəticələrinin dəqiq və şifrəsiz təhlili.',
                    'ru' => 'Точный и понятный разбор финансовых результатов за каждый месяц — для решений руководства.',
                    'en' => 'A precise, plain-language analysis of each month’s financial results to support management decisions.',
                ],
            ], [
                ['az' => 'Pul axınının idarə edilməsi', 'ru' => 'Управление денежным потоком', 'en' => 'Cash flow management'],
                [
                    'az' => 'Nağd və nağdsız vəsaitlərin hərəkətinə daimi nəzarət.',
                    'ru' => 'Постоянный контроль движения наличных и безналичных средств.',
                    'en' => 'Ongoing control over the movement of cash and cashless funds.',
                ],
            ],
        ]],
    ['analysis', '/assets/img/render/chart.jpg',
        ['az' => 'Xərc analizi', 'ru' => 'Анализ затрат', 'en' => 'Cost analysis'],
        [
            'az' => 'Hər qəpiyin hara getdiyini görürük — gizli itkilərdən xərclərin effektivliyinə qədər.',
            'ru' => 'Видим, куда уходит каждый манат, — от скрытых потерь до эффективности расходов.',
            'en' => 'We see where every manat goes — from hidden losses to the efficiency of spending.',
        ], [
            [
                ['az' => 'İtkilərin aşkar edilməsi', 'ru' => 'Выявление потерь', 'en' => 'Finding losses'],
                [
                    'az' => 'Proseslərdə və ya mətbəxdə baş verən gizli israf və artıq xərclərin müəyyənləşdirilməsi.',
                    'ru' => 'Поиск скрытых потерь и лишних расходов в процессах и на кухне.',
                    'en' => 'Identifying hidden waste and excess costs in processes or in the kitchen.',
                ],
            ], [
                ['az' => 'Xərclərin izlənməsi', 'ru' => 'Отслеживание расходов', 'en' => 'Expense tracking'],
                [
                    'az' => 'Biznesə daxil olan hər bir qəpiyin hara xərcləndiyinin şəffaf şəkildə auditi və hesabatı.',
                    'ru' => 'Прозрачный аудит и отчётность о том, куда тратится каждый манат бизнеса.',
                    'en' => 'A transparent audit and report of where every manat coming into the business is spent.',
                ],
            ], [
                ['az' => 'Effektivlik təhlili', 'ru' => 'Анализ эффективности', 'en' => 'Efficiency analysis'],
                [
                    'az' => 'Xərclərin real nəticə verib-vermədiyinin qiymətləndirilməsi.',
                    'ru' => 'Оценка того, приносят ли расходы реальный результат.',
                    'en' => 'Assessing whether spending delivers real results.',
                ],
            ],
        ]],
    ['optimize', '/assets/img/render/gear.jpg',
        ['az' => 'Xərclərin optimallaşdırılması', 'ru' => 'Оптимизация затрат', 'en' => 'Cost optimization'],
        [
            'az' => 'Keyfiyyəti qoruyaraq xərcləri təchizat, ştat və kommunal səviyyəsində tənzimləyirik.',
            'ru' => 'Сохраняя качество, настраиваем затраты на закупки, персонал и коммунальные услуги.',
            'en' => 'We fine-tune supply, staffing and utility costs without compromising on quality.',
        ], [
            [
                ['az' => 'Bazar xərclərinin tənzimlənməsi', 'ru' => 'Работа с закупочными ценами', 'en' => 'Aligning purchase prices'],
                [
                    'az' => 'Təchizatçı qiymətlərinin bazar dəyərinə uyğunlaşdırılması və sərfəli şərtlərin əldə olunması.',
                    'ru' => 'Приведение цен поставщиков к рыночным и получение выгодных условий.',
                    'en' => 'Bringing supplier prices in line with the market and securing better terms.',
                ],
            ], [
                ['az' => 'Əməkhaqqı fondunun optimallaşdırılması', 'ru' => 'Оптимизация фонда оплаты труда', 'en' => 'Payroll optimization'],
                [
                    'az' => 'Ştat cədvəlinin iş yükünə uyğun şəkildə düzgün qurulması.',
                    'ru' => 'Правильное штатное расписание под реальную нагрузку.',
                    'en' => 'Building a staffing schedule that matches the actual workload.',
                ],
            ], [
                ['az' => 'Kommunal və digər xərclər', 'ru' => 'Коммунальные и прочие расходы', 'en' => 'Utilities and other costs'],
                [
                    'az' => 'İdarəetmə xərclərinin minimuma endirilməsi üçün qənaət tədbirlərinin görülməsi.',
                    'ru' => 'Меры экономии, чтобы свести операционные расходы к минимуму.',
                    'en' => 'Savings measures that keep overheads to a minimum.',
                ],
            ],
        ]],
    ['recipe', '/assets/img/render/document.jpg',
        ['az' => 'Maya dəyəri və texnoloji kartlar', 'ru' => 'Себестоимость и технологические карты', 'en' => 'Food costing and recipe cards'],
        [
            'az' => 'Hər yeməyin real maya dəyərini bilir, qiymət siyasətini buna uyğun qururuq.',
            'ru' => 'Знаем реальную себестоимость каждого блюда и строим ценовую политику на её основе.',
            'en' => 'We know the real cost of every dish and build your pricing around it.',
        ], [
            [
                ['az' => 'Texnoloji kartların hazırlanması', 'ru' => 'Разработка технологических карт', 'en' => 'Creating recipe cards'],
                [
                    'az' => 'Hər bir yemək və içki üçün tərkib hissələrinin dəqiq reseptura ilə sistemə işlənməsi.',
                    'ru' => 'Точная рецептура каждого блюда и напитка, внесённая в систему.',
                    'en' => 'Entering a precise recipe for every dish and drink into the system.',
                ],
            ], [
                ['az' => 'Maya dəyərinin hesablanması', 'ru' => 'Расчёт себестоимости', 'en' => 'Calculating food cost'],
                [
                    'az' => 'Xammal qiymətlərindəki dəyişikliklərin məhsulun maya dəyərinə təsirinin dərhal izlənilməsi.',
                    'ru' => 'Мгновенное отслеживание того, как изменения цен на продукты влияют на себестоимость блюд.',
                    'en' => 'Tracking at once how changes in ingredient prices affect the cost of each dish.',
                ],
            ], [
                ['az' => 'Satış qiymətlərinin tənzimlənməsi', 'ru' => 'Настройка цен продажи', 'en' => 'Setting menu prices'],
                [
                    'az' => 'Optimal qazanc marjasını təmin etmək üçün qiymət siyasətinin qurulması.',
                    'ru' => 'Ценовая политика, обеспечивающая оптимальную маржу.',
                    'en' => 'A pricing policy that secures the optimal profit margin.',
                ],
            ],
        ]],
    ['inventory', '/assets/img/render/box.jpg',
        ['az' => 'Satınalma və inventar', 'ru' => 'Закупки и инвентаризация', 'en' => 'Purchasing and inventory'],
        [
            'az' => 'Sərfəli təchizat, dəqiq sayım və sağlam anbar dövriyyəsi.',
            'ru' => 'Выгодные закупки, точный учёт и здоровая оборачиваемость склада.',
            'en' => 'Cost-effective supply, accurate counts and healthy stock turnover.',
        ], [
            [
                ['az' => 'Sərfəli təchizat', 'ru' => 'Выгодные закупки', 'en' => 'Cost-effective supply'],
                [
                    'az' => 'Keyfiyyətdən ödün vermədən ən optimal satınalma şərtlərinin yaradılması.',
                    'ru' => 'Самые выгодные условия закупок без ущерба для качества.',
                    'en' => 'The best purchasing terms without compromising on quality.',
                ],
            ], [
                ['az' => 'Aylıq peşəkar sayımlar', 'ru' => 'Ежемесячная профессиональная инвентаризация', 'en' => 'Monthly professional stock counts'],
                [
                    'az' => 'Anbarda olan məhsulların dəqiq inventarizasiyası və qalıqların yoxlanılması.',
                    'ru' => 'Точная инвентаризация продуктов на складе и проверка остатков.',
                    'en' => 'Accurate inventory of the products in stock and checks of the balances.',
                ],
            ], [
                ['az' => 'Artıq ehtiyatların qarşısının alınması', 'ru' => 'Предотвращение излишков', 'en' => 'Preventing overstock'],
                [
                    'az' => 'Məhsulların xarab olma riskini azaltmaq üçün anbar dövriyyəsinin idarə edilməsi.',
                    'ru' => 'Управление оборачиваемостью склада, чтобы снизить риск порчи продуктов.',
                    'en' => 'Managing stock turnover to reduce the risk of spoilage.',
                ],
            ],
        ]],
    ['shield', '/assets/img/render/lock.jpg',
        ['az' => 'Daxili nəzarət və təhlükəsizlik', 'ru' => 'Внутренний контроль и безопасность', 'en' => 'Internal control and security'],
        [
            'az' => 'Səlahiyyətləri bölüşdürür, sui-istifadəni erkən mərhələdə aşkarlayırıq.',
            'ru' => 'Распределяем полномочия и выявляем злоупотребления на ранней стадии.',
            'en' => 'We divide responsibilities and detect misconduct early.',
        ], [
            [
                ['az' => 'Səlahiyyət bölgüsü', 'ru' => 'Распределение полномочий', 'en' => 'Division of responsibilities'],
                [
                    'az' => 'İşçi heyəti arasında vəzifə öhdəliklərinin dəqiq bölüşdürülməsi.',
                    'ru' => 'Чёткое распределение обязанностей между сотрудниками.',
                    'en' => 'A clear division of duties among staff.',
                ],
            ], [
                ['az' => 'Sui-istifadənin qarşısının alınması', 'ru' => 'Предотвращение злоупотреблений', 'en' => 'Preventing misconduct'],
                [
                    'az' => 'Müəssisədə yarana biləcək oğurluq və ya maliyyə pozuntularının erkən mərhələdə aşkarlanması və aradan qaldırılması.',
                    'ru' => 'Раннее выявление и устранение краж и финансовых нарушений в заведении.',
                    'en' => 'Detecting and stopping theft or financial violations at an early stage.',
                ],
            ], [
                ['az' => 'Nəzarət mexanizmləri', 'ru' => 'Механизмы контроля', 'en' => 'Control mechanisms'],
                [
                    'az' => 'Gündəlik əməliyyatların audit olunması.',
                    'ru' => 'Аудит ежедневных операций.',
                    'en' => 'Auditing daily operations.',
                ],
            ],
        ]],
];

/** [name, logo file in /assets/img/clients/] */
const SEED_PARTNERS = [
    ['Vasilchuki', 'vasilchuki'], ['Baribal', 'baribal'], ['Vətən', 'vatan'], ['The Real McCoy', 'the-real-mccoy'],
    ['Sess restobar', 'sess'], ['Green City Resort & Residence', 'green-city'], ['Şirvanşah muzey restoran', 'sirvansah'],
    ['Monkey Pub', 'monkey-pub'], ['Xarı Bülbül', 'xari-bulbul'], ['Gondola cafe & lounge', 'gondola'],
    ['Pivnaya Apteka', 'pivnaya-apteka'], ['Manzara', 'manzara'], ['Resant Hotels & Restaurants', 'resant'],
    ['Cəviz Tea & Lounge', 'caviz'], ['Stop Cafe', 'stop-cafe'], ['Water Garden', 'water-garden'],
    ['Lupin Coffee', 'lupin-coffee'], ['Best Place Baku', 'best-place'],
];

/** [question, answer]; every text is ['az' => …, 'ru' => …, 'en' => …] */
const SEED_FAQS = [
    [
        ['az' => 'Hansı müəssisələrlə işləyirsiniz?', 'ru' => 'С какими заведениями вы работаете?', 'en' => 'What kinds of businesses do you work with?'],
        [
            'az' => 'İaşə və restoran sektorunun kiçik və orta ölçülü müəssisələri ilə — restoran, kafe, lounge və otel brendləri. Həm sıfırdan qurulan yeni obyektlərə, həm də fəaliyyətdə olan restoranlara xidmət göstəririk.',
            'ru' => 'С малыми и средними заведениями общепита — ресторанами, кафе, лаунжами и отельными брендами. Работаем и с новыми заведениями, которые открываются с нуля, и с действующими ресторанами.',
            'en' => 'Small and mid-sized food service businesses — restaurants, cafés, lounges and hotel brands. We work both with new venues opening from scratch and with restaurants already in operation.',
        ],
    ],
    [
        ['az' => 'Hansı xidmətləri göstərirsiniz?', 'ru' => 'Какие услуги вы оказываете?', 'en' => 'What services do you offer?'],
        [
            'az' => 'Altı istiqamətdə: maliyyə və P&L, xərc analizi, xərclərin optimallaşdırılması, maya dəyəri və texnoloji kartlar, satınalma və inventar, daxili nəzarət və təhlükəsizlik.',
            'ru' => 'По шести направлениям: финансы и P&L, анализ затрат, оптимизация затрат, себестоимость и технологические карты, закупки и инвентаризация, внутренний контроль и безопасность.',
            'en' => 'Six areas: finance and P&L, cost analysis, cost optimization, food costing and recipe cards, purchasing and inventory, internal control and security.',
        ],
    ],
    [
        ['az' => 'Yeni restoran açırıq — necə kömək edə bilərsiniz?', 'ru' => 'Мы открываем новый ресторан — чем вы поможете?', 'en' => 'We’re opening a new restaurant — how can you help?'],
        [
            'az' => 'Böyük şəbəkələrin idarə olunmasında qazanılmış qabaqcıl təcrübə və standartları yeni obyektə uyğunlaşdırırıq: texnoloji kartlar, maya dəyəri, satınalma və uçot sistemi ilk gündən düzgün qurulur.',
            'ru' => 'Адаптируем к новому заведению передовой опыт и стандарты крупных сетей: технологические карты, себестоимость, закупки и система учёта выстраиваются правильно с первого дня.',
            'en' => 'We adapt the best practices and standards of large chains to your new venue, so recipe cards, food costing, purchasing and accounting are set up right from day one.',
        ],
    ],
    [
        ['az' => 'Azərbaycandan kənarda işləyirsiniz?', 'ru' => 'Вы работаете за пределами Азербайджана?', 'en' => 'Do you work outside Azerbaijan?'],
        [
            'az' => 'Bəli. Azərbaycanla yanaşı, Qazaxıstan kimi xarici bazarlarda da milli mətbəx layihələri ilə əməkdaşlıq edirik.',
            'ru' => 'Да. Помимо Азербайджана, мы сотрудничаем с проектами национальной кухни на зарубежных рынках, например в Казахстане.',
            'en' => 'Yes. Besides Azerbaijan, we work with national cuisine projects in foreign markets such as Kazakhstan.',
        ],
    ],
    [
        ['az' => 'Sizinlə necə əlaqə saxlaya bilərəm?', 'ru' => 'Как с вами связаться?', 'en' => 'How can I contact you?'],
        [
            'az' => '+994 55 855 61 52 nömrəsinə zəng edin, WhatsApp-da yazın və ya saytdakı formanı doldurun — sizinlə əlaqə saxlayacağıq.',
            'ru' => 'Позвоните по номеру +994 55 855 61 52, напишите в WhatsApp или заполните форму на сайте — мы с вами свяжемся.',
            'en' => 'Call +994 55 855 61 52, message us on WhatsApp or fill in the form on the website, and we will get back to you.',
        ],
    ],
];

/** key => [menu name, SEO title, SEO description]; every text is ['az' => …, 'ru' => …, 'en' => …] */
const SEED_PAGES = [
    'home' => [
        ['az' => 'Ana səhifə', 'ru' => 'Главная', 'en' => 'Home'],
        [
            'az' => 'Restoranlar üçün xərclərə nəzarət — Cost Control Services',
            'ru' => 'Контроль затрат для ресторанов — Cost Control Services',
            'en' => 'Cost control for restaurants — Cost Control Services',
        ],
        [
            'az' => 'Restoran və iaşə biznesi üçün xərclərə nəzarət: maliyyə və P&L, maya dəyəri, texnoloji kartlar, satınalma, inventar və daxili nəzarət. Bakı.',
            'ru' => 'Контроль затрат для ресторанов и общепита: финансы и P&L, себестоимость, технологические карты, закупки, инвентаризация и внутренний контроль. Баку.',
            'en' => 'Cost control for restaurants and food service: finance and P&L, food costing, recipe cards, purchasing, inventory and internal control. Baku.',
        ],
    ],
    'about' => [
        ['az' => 'Haqqımızda', 'ru' => 'О нас', 'en' => 'About us'],
        ['az' => 'Haqqımızda — Cost Control Services', 'ru' => 'О компании — Cost Control Services', 'en' => 'About us — Cost Control Services'],
        [
            'az' => 'Cost Control 2021-ci ildən iaşə və restoran sektorunda xərclərə nəzarət, maya dəyəri və maliyyə şəffaflığı üzrə peşəkar xidmət göstərir.',
            'ru' => 'С 2021 года Cost Control помогает ресторанам и заведениям общепита контролировать затраты, себестоимость и финансы.',
            'en' => 'Since 2021, Cost Control has provided professional cost control, food costing and financial transparency services to restaurants and food service businesses.',
        ],
    ],
    'services' => [
        ['az' => 'Xidmətlər', 'ru' => 'Услуги', 'en' => 'Services'],
        [
            'az' => 'Xərclərə nəzarət xidmətləri — Cost Control Services',
            'ru' => 'Услуги по контролю затрат — Cost Control Services',
            'en' => 'Cost control services — Cost Control Services',
        ],
        [
            'az' => 'Maliyyə və P&L, xərc analizi, xərclərin optimallaşdırılması, maya dəyəri və texnoloji kartlar, satınalma, inventar və daxili nəzarət — restoranlar üçün.',
            'ru' => 'Финансы и P&L, анализ и оптимизация затрат, себестоимость и технологические карты, закупки, инвентаризация и внутренний контроль — для ресторанов.',
            'en' => 'Finance and P&L, cost analysis and optimization, food costing and recipe cards, purchasing, inventory and internal control — for restaurants.',
        ],
    ],
    'clients' => [
        ['az' => 'Müştərilər', 'ru' => 'Клиенты', 'en' => 'Clients'],
        ['az' => 'Müştərilərimiz — Cost Control Services', 'ru' => 'Наши клиенты — Cost Control Services', 'en' => 'Our clients — Cost Control Services'],
        [
            'az' => 'Restoran, kafe, lounge və otel brendləri: Cost Control Services ilə əməkdaşlıq edən şirkətlər.',
            'ru' => 'Рестораны, кафе, лаунжи и отели, которые работают с Cost Control Services.',
            'en' => 'Restaurants, cafés, lounges and hotels that work with Cost Control Services.',
        ],
    ],
    'faq' => [
        ['az' => 'Suallar', 'ru' => 'Вопросы', 'en' => 'FAQ'],
        [
            'az' => 'Tez-tez verilən suallar — Cost Control Services',
            'ru' => 'Частые вопросы — Cost Control Services',
            'en' => 'FAQ — Cost Control Services',
        ],
        [
            'az' => 'Restoranlar üçün xərclərə nəzarət xidmətləri haqqında suallar və cavablar: kimlərlə işləyirik, hansı xidmətləri göstəririk, necə əlaqə saxlamaq olar.',
            'ru' => 'Ответы на вопросы о контроле затрат для ресторанов: с кем мы работаем, какие услуги оказываем и как с нами связаться.',
            'en' => 'Answers about cost control for restaurants: who we work with, what services we offer and how to get in touch.',
        ],
    ],
    'contact' => [
        ['az' => 'Əlaqə', 'ru' => 'Контакты', 'en' => 'Contact'],
        ['az' => 'Əlaqə — Cost Control Services, Bakı', 'ru' => 'Контакты — Cost Control Services, Баку', 'en' => 'Contact — Cost Control Services, Baku'],
        [
            'az' => 'Cost Control Services ilə əlaqə: +994 55 855 61 52, WhatsApp və müraciət forması. Bakı, Nizami rayonu, Heydər Əliyev prospekti 5.',
            'ru' => 'Свяжитесь с Cost Control Services: +994 55 855 61 52, WhatsApp и форма заявки. Баку, Низаминский район, проспект Гейдара Алиева, 5.',
            'en' => 'Contact Cost Control Services: +994 55 855 61 52, WhatsApp and request form. 5 Heydar Aliyev Avenue, Nizami district, Baku.',
        ],
    ],
];

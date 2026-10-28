<?php
/**
 * What the admin can edit: site texts (per content language) and settings,
 * plus the initial content taken from the presentation and the homepage prototypes.
 */

declare(strict_types=1);
defined('CCS') || exit;

/** Text groups shown as tabs on the "Texts" page. SEO is edited per page (see PAGES). */
const TEXT_GROUPS = [
    'hero' => ['az' => 'Əsas ekran', 'ru' => 'Главный экран'],
    'about' => ['az' => 'Haqqımızda', 'ru' => 'О нас'],
    'services' => ['az' => 'Xidmətlər bölməsi', 'ru' => 'Блок услуг'],
    'clients' => ['az' => 'Müştərilər', 'ru' => 'Клиенты'],
    'experience' => ['az' => 'Təcrübə', 'ru' => 'Опыт'],
    'faq' => ['az' => 'Suallar bölməsi', 'ru' => 'Блок вопросов'],
    'contact' => ['az' => 'Əlaqə bölməsi', 'ru' => 'Блок контактов'],
    'instagram' => ['az' => 'Instagram', 'ru' => 'Instagram'],
    'footer' => ['az' => 'Footer', 'ru' => 'Подвал'],
];

/** key => [group, label az, label ru, 'line'|'area', default (az)] */
const TEXT_FIELDS = [
    'hero.eyebrow' => ['hero', 'Kiçik başlıq', 'Надзаголовок', 'line', 'İaşə və restoran sektoru üçün xərclərə nəzarət'],
    'hero.title' => ['hero', 'Başlıq', 'Заголовок', 'line', 'Restoranınızın hər qəpiyi nəzarət altında'],
    'hero.lead' => ['hero', 'Mətn', 'Текст', 'area', 'Restoran biznesində uğurun sirri təkcə dadlı yemək deyil. Hər bir xammalın, resursun və maliyyə axınının dəqiq uçotunu qurur, gizli israfları və boşluqları dərhal aşkarlayırıq.'],
    'hero.cta' => ['hero', 'Düymə mətni', 'Текст кнопки', 'line', 'Konsultasiya al'],

    'about.title' => ['about', 'Başlıq', 'Заголовок', 'line', 'Biz kimik?'],
    'about.p1' => ['about', 'Abzas 1', 'Абзац 1', 'area', '"Cost Control" şirkəti post-pandemiya dövrünün çətin və dinamik reallıqları fonunda — 2021-ci ilin sonlarından etibarən iaşə və restoran sektorunda peşəkar idarəetmə boşluqlarını doldurmaq məqsədilə təsis edilmişdir.'],
    'about.p2' => ['about', 'Abzas 2', 'Абзац 2', 'area', 'Restoran biznesində uğurun sirri təkcə dadlı yemək deyil, hər bir xammalın, resursun və maliyyə axınının dəqiq uçotundan asılıdır. Cost Control mexanizmlərimiz sayəsində biznesdəki gizli israflar, artan maya dəyəri və ehtiyatların idarə olunmasındakı boşluqlar dərhal aşkarlanır.'],
    'about.p3' => ['about', 'Abzas 3', 'Абзац 3', 'area', 'Böyük şəbəkələrin idarə olunmasında qazanılmış qabaqcıl təcrübələr və standartlar kiçik və orta ölçülü restoranlara uyğunlaşdırılır. Bu yanaşma həm yerli bazarda yeni obyektlərin sıfırdan qurulmasına münbit şərait yaradır, həm də mövcud restoranların gəlirliliyini maksimum səviyyəyə qaldırır.'],

    'services.title' => ['services', 'Başlıq', 'Заголовок', 'line', 'Xidmətlərimiz'],
    'services.lead' => ['services', 'Mətn', 'Текст', 'area', 'Maliyyə uçotundan anbar sayımlarına qədər restoranın bütün xərc zəncirini şəffaf və idarə olunan edirik.'],

    'clients.title' => ['clients', 'Başlıq', 'Заголовок', 'line', 'Çalışdığımız şirkətlər'],
    'clients.lead' => ['clients', 'Mətn', 'Текст', 'area', 'Restoran, kafe, lounge və otel brendləri ilə əməkdaşlıq edirik.'],

    'experience.title' => ['experience', 'Başlıq', 'Заголовок', 'line', 'Beynəlxalq və xüsusi təcrübə'],
    'experience.p1' => ['experience', 'Abzas 1', 'Абзац 1', 'area', '200-dən çox ölkənin qatıldığı Taekvondo üzrə Dünya Çempionatında qida sektorunun keyfiyyətə nəzarət və tənzimləmə prosesinin birbaşa həyata keçirilməsi.'],
    'experience.p2' => ['experience', 'Abzas 2', 'Абзац 2', 'area', 'Beynəlxalq fəaliyyət: Azərbaycanla yanaşı, Qazaxıstan kimi xarici bazarlarda da milli mətbəx layihələri ilə əməkdaşlıq.'],

    'faq.title' => ['faq', 'Başlıq', 'Заголовок', 'line', 'Tez-tez verilən suallar'],
    'faq.lead' => ['faq', 'Mətn', 'Текст', 'area', 'Cavabını tapmadığınız sual varsa, bizə birbaşa yazın — məmnuniyyətlə izah edərik.'],

    'contact.title' => ['contact', 'Başlıq', 'Заголовок', 'line', 'Bizimlə əlaqə'],
    'contact.lead' => ['contact', 'Mətn', 'Текст', 'area', 'Müraciətinizi qoyun — mütəxəssisimiz sizinlə əlaqə saxlayacaq və restoranınız üçün uyğun həlli müzakirə edəcək.'],
    'contact.address' => ['contact', 'Ünvan', 'Адрес', 'line', 'Bakı şəhəri, Nizami rayonu, Heydər Əliyev prospekti 5'],
    'contact.success' => ['contact', 'Göndərildikdən sonra mesaj', 'Сообщение после отправки', 'area', 'Təşəkkür edirik! Müraciətiniz qəbul olundu. Tezliklə sizinlə əlaqə saxlayacağıq.'],

    'instagram.text' => ['instagram', 'Mətn', 'Текст', 'area', 'Restoran biznesi üçün yeni videolar və praktiki məsləhətlər — mütəmadi olaraq Instagram səhifəmizdə.'],

    'footer.text' => ['footer', 'Qısa təsvir', 'Краткое описание', 'area', 'Restoran və iaşə biznesi üçün xərclərə nəzarət, maya dəyəri və maliyyə şəffaflığı.'],
];

/**
 * Site pages: fixed keys with an editable address and SEO. The page text lives in the
 * 'groups' of TEXT_FIELDS and in the 'items' tables; their translations make up the "Text" status.
 */
const PAGES = [
    'home' => ['az' => 'Ana səhifə', 'ru' => 'Главная', 'slug' => '', 'groups' => ['hero', 'instagram'], 'items' => []],
    'about' => ['az' => 'Haqqımızda', 'ru' => 'О нас', 'slug' => 'haqqimizda', 'groups' => ['about', 'experience'], 'items' => []],
    'services' => ['az' => 'Xidmətlər (siyahı)', 'ru' => 'Услуги (список)', 'slug' => 'xidmetler', 'groups' => ['services'], 'items' => ['services']],
    'clients' => ['az' => 'Müştərilər', 'ru' => 'Клиенты', 'slug' => 'musteriler', 'groups' => ['clients'], 'items' => []],
    'faq' => ['az' => 'Suallar', 'ru' => 'Вопросы', 'slug' => 'suallar', 'groups' => ['faq'], 'items' => ['faqs']],
    'contact' => ['az' => 'Əlaqə', 'ru' => 'Контакты', 'slug' => 'elaqe', 'groups' => ['contact'], 'items' => []],
];

/** Addresses the site itself uses, so pages cannot take them. */
const RESERVED_SLUGS = ['admin', 'api', 'app', 'assets', 'media', 'uploads', 'index', 'v1', 'v2', 'v3', 'az', 'ru', 'en'];

/**
 * Until the chosen design is converted to PHP the site is static and one page long,
 * so "Open" shows the page's section on a prototype. Remove once the pages are live.
 */
const PAGE_PREVIEW = [
    'home' => '/',
    'about' => '/v1/#haqqimizda',
    'services' => '/v1/#xidmetler',
    'clients' => '/v1/#musteriler',
    'faq' => '/v2/#suallar',
    'contact' => '/v1/#elaqe',
];

/** Settings page fields: key => [section, label az, label ru, input type, default] */
const SETTING_FIELDS = [
    'contact_phone' => ['contacts', 'Telefon', 'Телефон', 'text', '+994 55 855 61 52'],
    'contact_whatsapp' => ['contacts', 'WhatsApp nömrəsi (yalnız rəqəmlər)', 'Номер WhatsApp (только цифры)', 'text', '994558556152'],
    'contact_email' => ['contacts', 'E-poçt', 'E-mail', 'email', 'info@ccs.com.az'],
    'contact_instagram' => ['contacts', 'Instagram linki', 'Ссылка на Instagram', 'url', 'https://www.instagram.com/costcontrol.services/'],
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

/** Icons available for services (see icon()). */
const SERVICE_ICONS = ['finance', 'analysis', 'optimize', 'recipe', 'inventory', 'shield'];

const SEED_SERVICES = [
    ['finance', '/assets/img/render/wallet.jpg', 'Maliyyə və P&L', 'Restoranın maliyyəsini şəffaf, rəqəmləri isə qərar qəbul etmək üçün aydın edirik.', [
        ['Şəffaf Maliyyə Sistemi', 'Restoranın bütün gəlir və xərclərinin qeydiyyatı üçün etibarlı uçot mexanizminin qurulması.'],
        ['Aylıq P&L (Mənfəət və Zərər) Hesabatları', 'Rəhbərliyin qərar qəbul etməsi üçün hər ay üzrə maliyyə nəticələrinin dəqiq və şifrəsiz təhlili.'],
        ['Pul Axınının İdarə Edilməsi', 'Nağd və nağdsız vəsaitlərin hərəkətinə daimi nəzarət.'],
    ]],
    ['analysis', '/assets/img/render/chart.jpg', 'Xərc analizi', 'Pulun harada itdiyini tapır və hər xərcin nəticəsini ölçürük.', [
        ['İtkilərin Aşkar Edilməsi', 'Proseslərdə və ya mətbəxdə baş verən gizli israf və artıq xərclərin müəyyənləşdirilməsi.'],
        ['Xərclərin İzlənməsi', 'Biznesə daxil olan hər bir qəpiyin hara xərcləndiyinin şəffaf şəkildə auditi və hesabatı.'],
        ['Effektivlik Təhlili', 'Xərclərin real nəticə verib-vermədiyinin qiymətləndirilməsi.'],
    ]],
    ['optimize', '/assets/img/render/gear.jpg', 'Xərclərin optimallaşdırılması', 'Keyfiyyəti qoruyaraq xərcləri bazar və iş yükü ilə uyğunlaşdırırıq.', [
        ['Bazar Xərclərinin Tənzimlənməsi', 'Təchizatçı qiymətlərinin bazar dəyərinə uyğunlaşdırılması və sərfəli şərtlərin əldə olunması.'],
        ['Əməkhaqqı Fondu Optimallaşdırılması', 'Ştat cədvəlinin iş yükünə uyğun şəkildə düzgün qurulması.'],
        ['Kommunal və Digər Xərclər', 'İdarəetmə xərclərinin minimuma endirilməsi üçün qənaət tədbirlərinin görülməsi.'],
    ]],
    ['recipe', '/assets/img/render/document.jpg', 'Maya dəyəri və texnoloji kartlar', 'Hər yeməyin real maya dəyərini və düzgün satış qiymətini müəyyən edirik.', [
        ['Texnoloji Kartların Hazırlanması', 'Hər bir yemək və içki üçün tərkib hissələrinin dəqiq reseptura ilə sistemə işlənməsi.'],
        ['Maya Dəyərinin Hesablanması', 'Xammal qiymətlərindəki dəyişikliklərin məhsulun maya dəyərinə təsirinin dərhal izlənilməsi.'],
        ['Satış Qiymətlərinin Tənzimlənməsi', 'Optimal qazanc marjasını təmin etmək üçün qiymət siyasətinin qurulması.'],
    ]],
    ['inventory', '/assets/img/render/box.jpg', 'Satınalma və inventar', 'Satınalmanı sərfəli, anbarı isə nəzarətli edirik.', [
        ['Sərfəli Təchizat', 'Keyfiyyətdən ödün vermədən ən optimal satınalma şərtlərinin yaradılması.'],
        ['Aylıq Peşəkar Sayımlar', 'Anbarda olan məhsulların dəqiq inventarizasiyası və qalıqların yoxlanılması.'],
        ['Artıq Ehtiyatların Qarşısının Alınması', 'Məhsulların xarab olma riskini azaltmaq üçün anbar dövriyyəsinin idarə edilməsi.'],
    ]],
    ['shield', '/assets/img/render/lock.jpg', 'Daxili nəzarət və təhlükəsizlik', 'Məsuliyyəti bölüşdürür, sui-istifadənin qarşısını erkən alırıq.', [
        ['Səlahiyyət Bölgüsü', 'İşçi heyəti arasında vəzifə öhdəliklərinin dəqiq bölüşdürülməsi.'],
        ['Sui-istifadənin Qarşısının Alınması', 'Müəssisədə yarana biləcək oğurluq və ya maliyyə pozuntularının erkən mərhələdə aşkarlanması və aradan qaldırılması.'],
        ['Nəzarət Mexanizmləri', 'Gündəlik əməliyyatların audit olunması.'],
    ]],
];

/** [name, logo file in /assets/img/clients/] */
const SEED_PARTNERS = [
    ['Vasilchuki', 'vasilchuki'], ['Baribal', 'baribal'], ['Vətən', 'vatan'], ['The Real McCoy', 'the-real-mccoy'],
    ['Sess restobar', 'sess'], ['Green City Resort & Residence', 'green-city'], ['Şirvanşah muzey restoran', 'sirvansah'],
    ['Monkey Pub', 'monkey-pub'], ['Xarı Bülbül', 'xari-bulbul'], ['Gondola cafe & lounge', 'gondola'],
    ['Pivnaya Apteka', 'pivnaya-apteka'], ['Manzara', 'manzara'], ['Resant Hotels & Restaurants', 'resant'],
    ['Caviz Tea & Lounge', 'caviz'], ['Stop Cafe', 'stop-cafe'], ['Water Garden', 'water-garden'],
    ['Lupin Coffee', 'lupin-coffee'], ['Best Place Baku', 'best-place'],
];

/** key => [menu name, SEO title, SEO description] (AZ) */
const SEED_PAGES = [
    'home' => ['Ana səhifə', 'Restoranlar üçün xərclərə nəzarət — Cost Control Services', 'Restoran və iaşə biznesi üçün xərclərə nəzarət: maliyyə və P&L, maya dəyəri, texnoloji kartlar, satınalma, inventar və daxili nəzarət. Bakı.'],
    'about' => ['Haqqımızda', 'Haqqımızda — Cost Control Services', 'Cost Control 2021-ci ildən iaşə və restoran sektorunda xərclərə nəzarət, maya dəyəri və maliyyə şəffaflığı üzrə peşəkar xidmət göstərir.'],
    'services' => ['Xidmətlər', 'Xərclərə nəzarət xidmətləri — Cost Control Services', 'Maliyyə və P&L, xərc analizi, xərclərin optimallaşdırılması, maya dəyəri və texnoloji kartlar, satınalma, inventar və daxili nəzarət — restoranlar üçün.'],
    'clients' => ['Müştərilər', 'Müştərilərimiz — Cost Control Services', 'Restoran, kafe, lounge və otel brendləri: Cost Control Services ilə əməkdaşlıq edən şirkətlər.'],
    'faq' => ['Suallar', 'Tez-tez verilən suallar — Cost Control Services', 'Restoranlar üçün xərclərə nəzarət xidmətləri haqqında suallar və cavablar: kimlərlə işləyirik, hansı xidmətləri göstəririk, necə əlaqə saxlamaq olar.'],
    'contact' => ['Əlaqə', 'Əlaqə — Cost Control Services, Bakı', 'Cost Control Services ilə əlaqə: +994 55 855 61 52, WhatsApp və müraciət forması. Bakı, Nizami rayonu, Heydər Əliyev prospekti 5.'],
];

const SEED_FAQS = [
    ['Hansı müəssisələrlə işləyirsiniz?', 'İaşə və restoran sektorunun kiçik və orta ölçülü müəssisələri ilə — restoran, kafe, lounge və otel brendləri. Həm sıfırdan qurulan yeni obyektlərə, həm də fəaliyyətdə olan restoranlara xidmət göstəririk.'],
    ['Hansı xidmətləri göstərirsiniz?', 'Altı istiqamətdə: maliyyə və P&L, xərc analizi, xərclərin optimallaşdırılması, maya dəyəri və texnoloji kartlar, satınalma və inventar, daxili nəzarət və təhlükəsizlik.'],
    ['Yeni restoran açırıq — necə kömək edə bilərsiniz?', 'Böyük şəbəkələrin idarə olunmasında qazanılmış qabaqcıl təcrübə və standartları yeni obyektə uyğunlaşdırırıq: texnoloji kartlar, maya dəyəri, satınalma və uçot sistemi ilk gündən düzgün qurulur.'],
    ['Azərbaycandan kənarda işləyirsiniz?', 'Bəli. Azərbaycanla yanaşı, Qazaxıstan kimi xarici bazarlarda da milli mətbəx layihələri ilə əməkdaşlıq edirik.'],
    ['Sizinlə necə əlaqə saxlaya bilərəm?', '+994 55 855 61 52 nömrəsinə zəng edin, WhatsApp-da yazın və ya saytdakı formanı doldurun — sizinlə əlaqə saxlayacağıq.'],
];
